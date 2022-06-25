<?php
date_default_timezone_set("America/Buenos_Aires");
require_once './interfaces/IApiUsable.php';
require_once './models/Pedido.php';
require_once './models/PedidoDetalle.php';
require_once './models/Producto.php';

use \App\Models\Pedido as Pedido;
use \App\Models\PedidoDetalle as PedidoDetalle;
use \App\Models\AuditoriaAcciones as AuditoriaAcciones;
use \App\Models\Producto as Producto;
use \App\Models\Usuario as Usuario;
use \App\Models\Mesa as Mesa;


class PedidoController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        try {
            $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->IdUsuario;
            $header = $request->getHeaderLine('Authorization');

            $body = json_decode(file_get_contents("php://input"), true);
            $idMesa = $body['IdMesa'];
            $nombreCliente = $body['nombreCliente'];
            $productosPedidos = $body['productosPedidos'];

            $mesaEncontrada = Mesa::find($idMesa);
            if ($mesaEncontrada != null && $mesaEncontrada->Estado == 'Libre') {
                $usuario = Usuario::find($idUsuarioLogeado);
                if ($usuario != null  || $usuario["Estado"] != 'Ocupado') {

                    $importeParcial = 0;
                    $flag = false;

                    if (count($productosPedidos) > 0) {

                        foreach ($productosPedidos as $productoPostman) {
                            $producto = Producto::where('IdProducto', '=', $productoPostman["idProducto"])->first();
                            if ($producto != null) {
                                $cantidad = $productoPostman["cantidad"];
                                if (intval($producto->Stock) >= intval($productoPostman["cantidad"])) {
                                    $importeParcial += $producto->PrecioUnidad * $cantidad;
                                    //Saco el tiempo estimado del pedido
                                    if (!$flag)
                                        $tiempoEstimado = $producto->TiempoEspera;
                                    if ($producto->TiempoEspera > $tiempoEstimado)
                                        $tiempoEstimado = $producto->TiempoEspera;
                                } else { //NO HAY STOCK DEL PRODUCTO
                                    $response->getBody()->write('No hay stock del producto pedido.');
                                    return $response->withHeader('Content-Type', 'application/json');
                                }
                            } else { //PRODUCTO NO EXISTE
                                $response->getBody()->write('El producto no esta disponible. <br>Revise el menu.');
                                return $response->withHeader('Content-Type', 'application/json');
                                break;
                            }
                        }
                        //-------------------------------- CREACION DEL PEDIDO ---------------------------------------------//

                        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
                        $codigoAlfanumericoCreado = substr(str_shuffle($permitted_chars), 0, 10);

                        $pedidoCreado = new Pedido();
                        $pedidoCreado->CodigoPedido = $codigoAlfanumericoCreado;
                        $pedidoCreado->Estado = "Preparando";
                        $pedidoCreado->ImporteTotal = $importeParcial;
                        $pedidoCreado->TiempoPreparacion = $tiempoEstimado;
                        $pedidoCreado->NombreCliente = $nombreCliente;
                        $pedidoCreado->IdMesa = $mesaEncontrada->IdMesa;
                        $pedidoCreado->IdUsuario = $usuario->IdUsuario;
                        $usuario->Estado = 'Ocupado';
                        $usuario->save();
                        $pedidoCreado->save();
                        $mesaEncontrada->Estado = 'Ocupada';
                        $mesaEncontrada->save();
                        $payload = json_encode(
                            array(
                                "IdUsuario" => strval($idUsuarioLogeado),
                                "IdRefUsuario" => $pedidoCreado->IdUsuario,
                                "IdAccion" =>  strval(AuditoriaAcciones::Alta),
                                "mensaje" => "Pedido creado con éxito",
                                "IdPedido" => $pedidoCreado->IdPedido,
                                "IdPedidoDetalle" => null,
                                "IdMesa" => $pedidoCreado->IdMesa,
                                "IdProducto" => null,
                                "IdArea" => null,
                                "Hora" => date('h:i:s')
                            )
                        );

                        foreach ($productosPedidos as $producto2) {
                            $pedidoDetalle = new PedidoDetalle();
                            $pedidoDetalle->Cantidad = $producto2["cantidad"];
                            $pedidoDetalle->Estado = "Pendiente";
                            $pedidoDetalle->IdProducto = $producto2["idProducto"];
                            $pedidoDetalle->IdPedido = $pedidoCreado->IdPedido;
                            $pedidoDetalle->save();
                            //RESTO STOCK A LOS PRODUCTOS
                            $producto = Producto::find($producto2["idProducto"]);
                            $producto->Stock = intval($producto->Stock) - intval($pedidoDetalle->Cantidad);
                            $producto->save();
                        }

                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    } else {
                        $payload = json_encode(array("mensaje" => "No hay productos en el pedido."));
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json');
                } else {
                    $response->getBody()->write('No se encontro el usuario o esta ocupado.');
                    return $response->withHeader('Content-Type', 'application/json');
                }
            } else {
                throw new Exception("Mesa no disponible.");
            }
        } catch (Exception $e) {
            $response = $response->withStatus(401);
            $response->getBody()->write(json_encode(array('error' => $e->getMessage())));
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
    public function CargarFoto($request, $response, $args)
    {
        try {
            $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->IdUsuario;
            $header = $request->getHeaderLine('Authorization');

            $body = $request->getParsedBody();
            $codigo = $body['CodigoPedido'];
            $pedido = Pedido::where('CodigoPedido', '=', $codigo)->first();
            if ($pedido != null ) {
                if($pedido->PathFoto == './imagenes/' . $pedido->CodigoPedido . '.png')
                throw new Exception('El pedido ya tiene una foto cargada');

                $directory = './imagenes/';
                $fileName = $pedido->CodigoPedido;
                $pedido->PathFoto = './imagenes/' . $pedido->CodigoPedido . '.png';
                $pedido->save();
                if (!Archivos::SaveImage($directory, $fileName, $_FILES)) {
                    throw new Exception('No fue posible guardar imagen del Pedido ');
                }
                $payload = json_encode(
                    array(
                        "IdUsuario" => strval($idUsuarioLogeado),
                        "IdRefUsuario" => $pedido->IdUsuario,
                        "IdAccion" =>  strval(AuditoriaAcciones::Modificacion),
                        "mensaje" => "Imagen Cargada con éxito",
                        "IdPedido" => $pedido->IdPedido,
                        "IdPedidoDetalle" => null,
                        "IdMesa" => $pedido->IdMesa,
                        "IdProducto" => null,
                        "IdArea" => null,
                        "Hora" => date('h:i:s')
                    )
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                throw new Exception("No se encontro el pedido.");
            }
        } catch (Exception $e) {
            $response = $response->withStatus(401);
            $response->getBody()->write(json_encode(array('error' => $e->getMessage())));
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
    public function BorrarUno($request, $response, $args)
    {
        try {
            $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->IdUsuario;
            $header = $request->getHeaderLine('Authorization');
            $id = $args['IdPedido'];

            $pedido = Pedido::find($id);
            $listaDetalles = PedidoDetalle::all();
            if ($pedido != null && count($listaDetalles) > 0) {
                $pedido->delete();

                $payload = json_encode(
                    array(
                        "IdUsuario" => strval($idUsuarioLogeado),
                        "IdRefUsuario" => $pedido->IdUsuario,
                        "IdAccion" =>  strval(AuditoriaAcciones::Baja),
                        "mensaje" => "Pedido cancelado con éxito",
                        "IdPedido" => $pedido->IdPedido,
                        "IdPedidoDetalle" => null,
                        "IdMesa" => $pedido->IdMesa,
                        "IdProducto" => null,
                        "IdArea" => null,
                        "Hora" => date('h:i:s')
                    )
                );

                foreach ($listaDetalles as $detalle) {
                    if ($detalle->IdPedido == $pedido->IdPedido) {
                        $detalle->delete();
                    }
                }
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                throw new Exception('No se encontro el pedido.');
            }
        } catch (Exception $e) {
            $response = $response->withStatus(401);
            $response->getBody()->write(json_encode(array('error' => $e->getMessage())));
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
    public function CancelarUno($request, $response, $args)
    {
        try {
            $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->IdUsuario;
            $header = $request->getHeaderLine('Authorization');
            $codigo = $args['CodigoPedido'];

            $pedido = Pedido::where('CodigoPedido', '=', $codigo)->first();

            $listaDetalles = PedidoDetalle::all();
            if ($pedido != null && count($listaDetalles) > 0 && $pedido->Estado == 'Preparando') {
                $pedido->Estado = 'Cancelado';
                $pedido->save();

                $payload = json_encode(
                    array(
                        "IdUsuario" => strval($idUsuarioLogeado),
                        "IdRefUsuario" => $pedido->IdUsuario,
                        "IdAccion" =>  strval(AuditoriaAcciones::Cancelar),
                        "mensaje" => "Pedido cancelado con éxito",
                        "IdPedido" => $pedido->IdPedido,
                        "IdPedidoDetalle" => null,
                        "IdMesa" => $pedido->IdMesa,
                        "IdProducto" => null,
                        "IdArea" => null,
                        "Hora" => date('h:i:s')
                    )
                );

                foreach ($listaDetalles as $detalle) {
                    if ($detalle->IdPedido == $pedido->IdPedido) {
                        $detalle->delete();
                    }
                }
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                $payload = json_encode(array("mensaje" => "Error al eliminar"));
            }
        } catch (Exception $e) {
            $response = $response->withStatus(401);
            $response->getBody()->write(json_encode(array('error' => $e->getMessage())));
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
    public function ModificarUno($request, $response, $args)
    {
        try {
            $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->IdUsuario;
            $body = json_decode($response->getBody());
            $header = $request->getHeaderLine('Authorization');
            $productosPedidos = $body['productosPedidos'];
            $codigo = $args['CodigoPedido'];
            $body = json_decode(file_get_contents("php://input"), true);
            $pedido = Pedido::where('CodigoPedido', '=', $codigo)->first();
            $listaDetalles = PedidoDetalle::all();

            if ($pedido != null && count($listaDetalles) > 0) {
                if ($pedido->Estado != 'Cobrado' || $pedido->Estado != 'CobradoEncuestado' || $pedido->Estado != 'Servido') {
                    if ($pedido->Estado == 'Preparado')
                        throw new Exception('El pedido ya esta preparado. Realice un nuevo pedido');

                    if (count($productosPedidos) > 0) {
                        $importeParcial = 0;
                        $flag = false;
                        foreach ($productosPedidos as $productoPostman) {
                            $producto = Producto::where('IdProducto', '=', $productoPostman["idProducto"])->first();
                            if ($producto != null) {
                                $cantidad = $productoPostman["cantidad"];
                                if (intval($producto->Stock) >= intval($productoPostman["cantidad"])) {
                                    $importeParcial += $producto->PrecioUnidad * $cantidad;
                                    //Saco el tiempo estimado del pedido
                                    if (!$flag)
                                        $tiempoEstimado = $producto->TiempoEspera;
                                    if ($producto->TiempoEspera > $tiempoEstimado)
                                        $tiempoEstimado = $producto->TiempoEspera;
                                } else { //NO HAY STOCK DEL PRODUCTO
                                    $response->getBody()->write('No hay stock del producto pedido.');
                                    return $response->withHeader('Content-Type', 'application/json');
                                }
                            } else { //PRODUCTO NO EXISTE
                                $response->getBody()->write('El producto no esta disponible. <br>Revise el menu.');
                                return $response->withHeader('Content-Type', 'application/json');
                                break;
                            }
                        }
                    }
                    $pedido->ImporteTotal = $pedido->ImporteTotal + $importeParcial;
                    $pedido->TiempoPreparacion = $tiempoEstimado;

                    foreach ($productosPedidos as $producto2) {
                        $pedidoDetalle = new PedidoDetalle();
                        $pedidoDetalle->Cantidad = $producto2["cantidad"];
                        $pedidoDetalle->Estado = "Pendiente";
                        $pedidoDetalle->IdProducto = $producto2["idProducto"];
                        $pedidoDetalle->IdPedido = $pedido->IdPedido;
                        $pedidoDetalle->save();
                        //RESTO STOCK A LOS PRODUCTOS
                        $producto = Producto::find($producto2["idProducto"]);
                        $producto->Stock = intval($producto->Stock) - intval($pedidoDetalle->Cantidad);
                        $producto->save();
                    }

                    if (!$pedido->save())
                        throw new Exception('Error al guardar el pedido');

                    $payload = json_encode(
                        array(
                            "IdUsuario" => strval($idUsuarioLogeado),
                            "IdRefUsuario" => $pedido->IdUsuario,
                            "IdAccion" =>  strval(AuditoriaAcciones::Modificacion),
                            "mensaje" => "Agrego mas pedidos entregado con éxito",
                            "IdPedido" => $pedido->IdPedido,
                            "IdPedidoDetalle" => null,
                            "IdMesa" => $pedido->IdMesa,
                            "IdProducto" => null,
                            "IdArea" => null,
                            "Hora" => date('h:i:s')
                        )
                    );
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json');
                } else {
                    throw new Exception('El pedido ya fue entregado. Realice un nuevo pedido' . $codigo);
                }
            } else {
                throw new Exception('No se encontro el pedido con codigo ' . $codigo);
            }
        } catch (Exception $e) {
            $response = $response->withStatus(401);
            $response->getBody()->write(json_encode(array('error' => $e->getMessage())));
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
    public function Cancelar($request, $response, $args)
    {
        try {
            $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->IdUsuario;
            $header = $request->getHeaderLine('Authorization');
            $CodigoPedido = $args['CodigoPedido'];
            $pedido = Pedido::where('CodigoPedido', '=', $CodigoPedido)->first();
            $estado = 'Cancelado';
            //$mesa = Mesa::find($pedido->IdMesa)->first();

            $listaDetalles = PedidoDetalle::all();
            if ($pedido != null && $listaDetalles != null && $pedido->Estado == 'En preparacion') {
                $pedido->Estado = $estado;
                $pedido->HoraFin = date('h:i:s');
                foreach ($listaDetalles as $detalle) {
                    if ($detalle->IdPedido == $pedido->IdPedido) {
                        $detalle->delete();
                    }
                }
                $pedido->save();

                $payload = json_encode(
                    array(
                        "IdUsuario" => strval($idUsuarioLogeado),
                        "IdRefUsuario" => $pedido->IdUsuario,
                        "IdAccion" =>  strval(AuditoriaAcciones::Cancelar),
                        "mensaje" => "Pedido entregado con éxito",
                        "IdPedido" => $pedido->IdPedido,
                        "IdPedidoDetalle" => null,
                        "IdMesa" => $pedido->IdMesa,
                        "IdProducto" => null,
                        "IdArea" => null,
                        "Hora" => date('h:i:s')
                    )
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                $payload = json_encode(array("mensaje" => "Error al cancelar"));
            }
        } catch (Exception $e) {
            $response = $response->withStatus(401);
            $response->getBody()->write(json_encode(array('error' => $e->getMessage())));
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
    public function Preparado($request, $response, $args)
    {
        try {
            $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->IdUsuario;
            $header = $request->getHeaderLine('Authorization');
            $CodigoPedido = $args['CodigoPedido'];
            $pedido = Pedido::where('CodigoPedido', '=', $CodigoPedido)->first();
            $estado = 'Preparado';

            $listaDetalles = PedidoDetalle::all();
            if ($pedido != null && $listaDetalles != null && $pedido->Estado == 'Preparando') {

                foreach ($listaDetalles as $detalle) {
                    $detalle->Estado = 'Entregado';
                }
                $pedido->Estado = $estado;
                $pedido->save();

                $payload = json_encode(
                    array(
                        "IdUsuario" => strval($idUsuarioLogeado),
                        "IdRefUsuario" => $pedido->IdUsuario,
                        "IdAccion" =>  strval(AuditoriaAcciones::Preparado),
                        "mensaje" => "Pedido preparado con éxito",
                        "IdPedido" => $pedido->IdPedido,
                        "IdPedidoDetalle" => null,
                        "IdMesa" => $pedido->IdMesa,
                        "IdProducto" => null,
                        "IdArea" => null,
                        "Hora" => date('h:i:s')
                    )
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                throw new Exception("Pedido no esta en preparacion o no existe.");
            }
        } catch (Exception $e) {
            $response = $response->withStatus(401);
            $response->getBody()->write(json_encode(array('error' => $e->getMessage())));
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
    public function Servido($request, $response, $args)
    {
        try {
            $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->IdUsuario;
            $header = $request->getHeaderLine('Authorization');
            $CodigoPedido = $args['CodigoPedido'];
            $pedido = Pedido::where('CodigoPedido', '=', $CodigoPedido)->first();
            $estado = 'Servido';

            $listaDetalles = PedidoDetalle::all();
            if ($pedido != null && $listaDetalles != null && $pedido->Estado == 'Preparado') {
                $pedido->Estado = $estado;
                $pedido->HoraFin = date('h:i:s');
                $pedido->save();

                $payload = json_encode(
                    array(
                        "IdUsuario" => strval($idUsuarioLogeado),
                        "IdRefUsuario" => $pedido->IdUsuario,
                        "IdAccion" =>  strval(AuditoriaAcciones::Servir),
                        "mensaje" => "Pedido entregado con éxito",
                        "IdPedido" => $pedido->IdPedido,
                        "IdPedidoDetalle" => null,
                        "IdMesa" => $pedido->IdMesa,
                        "IdProducto" => null,
                        "IdArea" => null,
                        "Hora" => date('h:i:s')
                    )
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                throw new Exception("El Pedido no esta preparado o no existe.");
            }
        } catch (Exception $e) {
            $response = $response->withStatus(401);
            $response->getBody()->write(json_encode(array('error' => $e->getMessage())));
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
    public function Cobrado($request, $response, $args)
    {
        try {
            $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->IdUsuario;
            $header = $request->getHeaderLine('Authorization');
            $CodigoPedido = $args['CodigoPedido'];
            $pedido = Pedido::where('CodigoPedido', '=', $CodigoPedido)->first();
            $estado = 'Cobrado';
            if ($pedido != null && $pedido->Estado == 'Servido') {
                $mesaEncontrada = Mesa::where('IdMesa', '=', $pedido->IdMesa)->first();
                if ($mesaEncontrada != null) {

                    $mesaEncontrada->Estado = 'Libre';
                    $mesaEncontrada->save();
                    $pedido->Estado = $estado;
                    $pedido->save();
                    $payload = json_encode(
                        array(
                            "IdUsuario" => strval($idUsuarioLogeado),
                            "IdRefUsuario" => $pedido->IdUsuario,
                            "IdAccion" =>  strval(AuditoriaAcciones::Cobrar),
                            "mensaje" => "Pedido cobrado con éxito",
                            "IdPedido" => $pedido->IdPedido,
                            "IdPedidoDetalle" => null,
                            "IdMesa" => $pedido->IdMesa,
                            "IdProducto" => null,
                            "IdArea" => null,
                            "Hora" => date('h:i:s')
                        )
                    );
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json');
                } else {
                    throw new Exception("No encontre la mesa en la que esta.");
                }
            } else {
                throw new Exception("Pedido no esta servido o no existe.");
            }
        } catch (Exception $e) {
            $response = $response->withStatus(401);
            $response->getBody()->write(json_encode(array('error' => $e->getMessage())));
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
    public function TraerTodos($request, $response, $args)
    {
        $lista = Pedido::all();

        $payload = json_encode(array("listaPedido" => $lista));
        if ($lista != null) {
            $string = '';
            foreach ($lista as $pedido) {
                $usuario = Usuario::where('IdUsuario', '=', $pedido->IdUsuario)->first();
                $mesa = Mesa::where('IdMesa', '=', $pedido->IdMesa)->first();
                if ($pedido != null && $usuario != null && $mesa != null) {
                    $string = $string . '<br>' .
                        'IdPedido ->' . $pedido->IdPedido . '<br>' .
                        'IdMesa ->' . $pedido->IdMesa . '<br>' .
                        'Usuario ->' . $usuario->Nombre . '<br>' .
                        'Cliente ->' . $pedido->NombreCliente . '<br>' .
                        'Importe ->' . $pedido->ImporteTotal . '<br>' .
                        'Codigo Pedido ->' . $pedido->CodigoPedido . '<br>' .
                        'Codigo Mesa ->' . $mesa->Codigo . '<br>' .
                        'Estado ->' . $pedido->Estado . '<br>' .
                        'Tiempo de Preparacion ->' . $pedido->TiempoPreparacion . ' (min)<br><br>';
                    $string = $string . '<br>____________________________________________________________________';
                } else {
                    $payload = json_encode('Error al obtener datos');
                }
            }
            $payload = json_encode('TODOS LOS PEDIDOS...<br>' . $string);
        }
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
    public function TraerTodosPreparando($request, $response, $args)
    {
        $lista = Pedido::all();

        $payload = json_encode(array("listaPedido" => $lista));
        if ($lista != null) {
            $string = '';
            foreach ($lista as $pedido) {
                $usuario = Usuario::where('IdUsuario', '=', $pedido->IdUsuario)->first();
                $mesa = Mesa::where('IdMesa', '=', $pedido->IdMesa)->first();
                $estado = $pedido->Estado;
                if ($pedido != null && $usuario != null && $mesa != null &&  strval($estado) == 'Preparando') {
                    $string = $string . '<br>' .
                        'IdPedido ->' . $pedido->IdPedido . '<br>' .
                        'IdMesa ->' . $pedido->IdMesa . '<br>' .
                        'Usuario ->' . $usuario->Nombre . '<br>' .
                        'Cliente ->' . $pedido->NombreCliente . '<br>' .
                        'Importe ->' . $pedido->ImporteTotal . '<br>' .
                        'Codigo Pedido ->' . $pedido->CodigoPedido . '<br>' .
                        'Codigo Mesa ->' . $mesa->Codigo . '<br>' .
                        'Estado ->' . $pedido->Estado . '<br>' .
                        'Tiempo de Preparacion ->' . $pedido->TiempoPreparacion . ' (min)<br><br>';
                    $string = $string . '<br>____________________________________________________________________';
                } else {
                    $payload = json_encode('Error al obtener datos');
                }
            }
            $payload = json_encode('TODOS LOS PEDIDOS PREPARANDO...<br>' . $string);
        }
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodosPreparados($request, $response, $args)
    {
        $lista = Pedido::all();

        $payload = json_encode(array("listaPedido" => $lista));
        if ($lista != null) {
            $string = '';
            foreach ($lista as $pedido) {
                $usuario = Usuario::where('IdUsuario', '=', $pedido->IdUsuario)->first();
                $mesa = Mesa::where('IdMesa', '=', $pedido->IdMesa)->first();
                $estado = $pedido->Estado;
                if ($pedido != null && $usuario != null && $mesa != null &&  strval($estado) == 'Preparado') {
                    $string = $string . '<br>' .
                        'IdPedido ->' . $pedido->IdPedido . '<br>' .
                        'IdMesa ->' . $pedido->IdMesa . '<br>' .
                        'Usuario ->' . $usuario->Nombre . '<br>' .
                        'Cliente ->' . $pedido->NombreCliente . '<br>' .
                        'Importe ->' . $pedido->ImporteTotal . '<br>' .
                        'Codigo Pedido ->' . $pedido->CodigoPedido . '<br>' .
                        'Codigo Mesa ->' . $mesa->Codigo . '<br>' .
                        'Estado ->' . $pedido->Estado . '<br>' .
                        'Tiempo de Preparacion ->' . $pedido->TiempoPreparacion . ' (min)<br><br>';
                    $string = $string . '<br>____________________________________________________________________';
                } else {
                    $payload = json_encode('Error al obtener datos');
                }
            }
            $payload = json_encode('TODOS LOS PEDIDOS PREPARANDO...<br>' . $string);
        }
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
    public function TraerTodosServidos($request, $response, $args)
    {
        $lista = Pedido::all();

        $payload = json_encode(array("listaPedido" => $lista));
        if ($lista != null) {
            $string = '';
            foreach ($lista as $pedido) {
                $usuario = Usuario::where('IdUsuario', '=', $pedido->IdUsuario)->first();
                $mesa = Mesa::where('IdMesa', '=', $pedido->IdMesa)->first();
                if ($pedido != null && $usuario != null && $mesa != null && $pedido->Estado == 'Servido') {
                    $string = $string . '<br>' .
                        'IdPedido ->' . $pedido->IdPedido . '<br>' .
                        'IdMesa ->' . $pedido->IdMesa . '<br>' .
                        'Usuario ->' . $usuario->Nombre . '<br>' .
                        'Cliente ->' . $pedido->NombreCliente . '<br>' .
                        'Importe ->' . $pedido->ImporteTotal . '<br>' .
                        'Codigo Pedido ->' . $pedido->CodigoPedido . '<br>' .
                        'Codigo Mesa ->' . $mesa->Codigo . '<br>' .
                        'Estado ->' . $pedido->Estado . '<br>' .
                        'Tiempo de Preparacion ->' . $pedido->TiempoPreparacion . ' (min)<br><br>';
                    $string = $string . '<br>____________________________________________________________________';
                } else {
                    $payload = json_encode('Error al obtener datos');
                }
            }
            $payload = json_encode('TODOS LOS PEDIDOS SERVIDOS...<br>' . $string);
        }
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
    public function TraerTodosCobrados($request, $response, $args)
    {
        $lista = Pedido::all();

        $payload = json_encode(array("listaPedido" => $lista));
        if ($lista != null) {
            $string = '';
            foreach ($lista as $pedido) {
                $usuario = Usuario::where('IdUsuario', '=', $pedido->IdUsuario)->first();
                $mesa = Mesa::where('IdMesa', '=', $pedido->IdMesa)->first();
                if ($pedido != null && $usuario != null && $mesa != null && $pedido->Estado == 'Cobrado') {
                    $string = $string . '<br>' .
                        'IdPedido ->' . $pedido->IdPedido . '<br>' .
                        'IdMesa ->' . $pedido->IdMesa . '<br>' .
                        'Usuario ->' . $usuario->Nombre . '<br>' .
                        'Cliente ->' . $pedido->NombreCliente . '<br>' .
                        'Importe ->' . $pedido->ImporteTotal . '<br>' .
                        'Codigo Pedido ->' . $pedido->CodigoPedido . '<br>' .
                        'Codigo Mesa ->' . $mesa->Codigo . '<br>' .
                        'Estado ->' . $pedido->Estado . '<br>'. '<br>____________________________________________________________________';
                } else {
                    $payload = json_encode('Error al obtener datos');
                }
            }
            $payload = json_encode('TODOS LOS PEDIDOS COBRADOS...<br>' . $string);
        }
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
    public function TraerTodosEncuestados($request, $response, $args)
    {
        $lista = Pedido::all();

        $payload = json_encode(array("listaPedido" => $lista));
        if ($lista != null) {
            $string = '';
            foreach ($lista as $pedido) {
                $usuario = Usuario::where('IdUsuario', '=', $pedido->IdUsuario)->first();
                $mesa = Mesa::where('IdMesa', '=', $pedido->IdMesa)->first();
                if ($pedido != null && $usuario != null && $mesa != null && $pedido->Estado == 'CobradoEncuestado') {
                    $string = $string . '<br>' .
                        'IdPedido ->' . $pedido->IdPedido . '<br>' .
                        'IdMesa ->' . $pedido->IdMesa . '<br>' .
                        'Usuario ->' . $usuario->Nombre . '<br>' .
                        'Cliente ->' . $pedido->NombreCliente . '<br>' .
                        'Importe ->' . $pedido->ImporteTotal . '<br>' .
                        'Codigo Pedido ->' . $pedido->CodigoPedido . '<br>' .
                        'Codigo Mesa ->' . $mesa->Codigo . '<br>' .
                        'Puntuacion Mesa ->' .$pedido->PuntuacionMesa . '<br>' .
                        'Puntuacion Restaurante ->' .$pedido->PuntuacionRestaurante . '<br>' .
                        'Puntuacion Cocinero ->' .$pedido->PuntuacionCocinero . '<br>' .
                        'Puntuacion Mozo ->' .$pedido->PuntuacionMozo . '<br>' .
                        'Comentario ->' .$pedido->Comentario . '<br>' .
                        'Estado ->' . $pedido->Estado . '<br>'.'____________________________________________________________________';
                } else {
                    $payload = json_encode('Error al obtener datos');
                }
            }
            $payload = json_encode('TODOS LOS PEDIDOS ENCUESTADOS...<br>' . $string);
        }
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
    public function TraerTodosCancelados($request, $response, $args)
    {
        $lista = Pedido::all();

        $payload = json_encode(array("listaPedido" => $lista));
        if ($lista != null) {
            $string = '';
            foreach ($lista as $pedido) {
                $usuario = Usuario::where('IdUsuario', '=', $pedido->IdUsuario)->first();
                $mesa = Mesa::where('IdMesa', '=', $pedido->IdMesa)->first();
                if ($pedido != null && $usuario != null && $mesa != null && $pedido->Estado == 'Cancelado') {
                    $string = $string . '<br>' .
                        'IdPedido ->' . $pedido->IdPedido . '<br>' .
                        'IdMesa ->' . $pedido->IdMesa . '<br>' .
                        'Usuario ->' . $usuario->Nombre . '<br>' .
                        'Cliente ->' . $pedido->NombreCliente . '<br>' .
                        'Importe ->' . $pedido->ImporteTotal . '<br>' .
                        'Codigo Pedido ->' . $pedido->CodigoPedido . '<br>' .
                        'Codigo Mesa ->' . $mesa->Codigo . '<br>' .
                        'Estado ->' . $pedido->Estado . '<br>' .
                        'Tiempo de Preparacion ->' . $pedido->TiempoPreparacion . ' (min)<br><br>';
                    $string = $string . '<br>____________________________________________________________________';
                } else {
                    $payload = json_encode('Error al obtener datos');
                }
            }
            $payload = json_encode('TODOS LOS PEDIDOS CANCELADOS...<br>' . $string);
        }
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
    public function TraerUno($request, $response, $args)
    {
        $id = $args['IdPedido'];
        $pedido = Pedido::where('IdPedido', '=', $id)->first();
        $usuario = Usuario::where('IdUsuario', '=', $pedido->IdUsuario)->first();
        $mesa = Mesa::where('IdMesa', '=', $pedido->IdMesa)->first();
        $detalles = PedidoDetalle::all();
        if ($pedido != null) {
            $titulo =  '----INFORMACION----' . '<br>';
            $string =
                'IdPedido ->' . $pedido->IdPedido . '<br>' .
                'IdMesa ->' . $pedido->IdMesa . '<br>' .
                'Usuario ->' . $usuario->Nombre . '<br>' .
                'Cliente ->' . $usuario->NombreCliente . '<br>' .
                'Importe ->' . $pedido->ImporteTotal . '<br>' .
                'Codigo Pedido ->' . $pedido->CodigoPedido . '<br>' .
                'Codigo Mesa ->' . $mesa->Codigo . '<br>' .
                'Estado ->' . $pedido->Estado . '<br>' .
                'Tiempo de Preparacion ->' . $pedido->TiempoPreparacion . '<br><br>' .
                '--------------------------------';
            foreach ($detalles as $pedidoDetalle) {
                if ($pedido->IdPedido == $pedidoDetalle->IdPedido) {
                    $producto = Producto::where('IdProducto', '=', $pedidoDetalle->IdProducto)->first();;
                    if ($producto != null)
                        $string = $string . '<br>' .
                            'Cantidad->' . $pedidoDetalle->Cantidad . '<br>' .
                            'Producto->' . $producto->Nombre . '<br>' .
                            'Precio->' . $producto->PrecioUnidad . '<br>' .
                            'Area->' . $producto->Area . '<br>' .
                            '------------------------------------';
                }
            }

            $payload = json_encode($titulo . $string);
        } else {
            $payload = json_encode(array("mensaje" => "Pedido no encontrado."));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function BuscarUno($request, $response, $args)
    {
        $codigo = $args['CodigoPedido'];
        $id = $args['IdPedido'];
        $pedido = Pedido::where('CodigoPedido', '=', $codigo)->first();
        $usuario = Usuario::where('IdUsuario', '=', $pedido->IdUsuario)->first();
        $mesa = Mesa::where('IdMesa', '=', $pedido->IdMesa)->first();
        $detalles = PedidoDetalle::all();
        if ($pedido != null) {
            $titulo =  '----INFORMACION----' . '<br>';
            $string =
                'IdPedido -> ' . $pedido->IdPedido . '<br>' .
                'IdMesa -> ' . $pedido->IdMesa . '<br>' .
                'Cliente -> ' . $usuario->NombreCliente . '<br>' .
                'Importe -> ' . $pedido->ImporteTotal . '<br>' .
                'Estado -> ' . $pedido->Estado . '<br>' .
                'Tiempo estimado -> ' . $pedido->TiempoPreparacion . ' (minutos)<br><br>' .
                '--------------------------------';
            foreach ($detalles as $pedidoDetalle) {
                if ($pedido->IdPedido == $pedidoDetalle->IdPedido) {
                    $producto = Producto::where('IdProducto', '=', $pedidoDetalle->IdProducto)->first();
                    if ($producto != null)
                        $string = $string . '<br>' .
                            'Cantidad->' . $pedidoDetalle->Cantidad . '<br>' .
                            'Producto->' . $producto->Nombre . '<br>' .
                            'Precio->' . $producto->PrecioUnidad . '<br>' .
                            'Area->' . $producto->Area . '<br>' .
                            '------------------------------------';
                }
            }

            $payload = json_encode($titulo . $string);
        } else {
            $payload = json_encode(array("mensaje" => "Pedido no encontrado."));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

}
