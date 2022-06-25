<?php

require_once './models/Producto.php';

use \App\Models\Producto as Producto;

use \App\Models\Pedido as Pedido;
use \App\Models\Usuario as Usuario;
use \App\Models\PedidoDetalle as PedidoDetalle;
use \App\Models\Auditoria as Auditoria;
use App\Models\AuditoriaAcciones;

class Archivos
{
    //nombre - precio - tiempoMinutos - area - tipo - stock
    public static function ProductosToCSV(string $path, $arrayObjetos)
    {
        $escrituraSalioBien = true;
        if ($path != null && $arrayObjetos != null) {
            $archivo = fopen($path, "w");
            foreach ($arrayObjetos as $objeto) {
                if (!is_null($objeto)) {
                    $string =   $objeto->Nombre . "," .
                        $objeto->TiempoEspera . "," .
                        $objeto->Area . "," .
                        $objeto->PrecioUnidad . "," .
                        $objeto->TipoProducto . "," .
                        $objeto->Stock;

                    fwrite($archivo, $string . PHP_EOL);
                } else {
                    return false;
                }
            }
            fclose($archivo);
        } else {
            return false;
        }

        return $escrituraSalioBien;
    }
    /*IDUSUARIOBUSCADO ES OPCIONAL, SI NO SE LO PASA NO SERA TOMADO EN CUENTA Y NO FILTRARA POR ID */
    public static function AuditoriaToCSV(string $path, $arrayObjetos, $idUsuarioBuscado = -1)
    {
        $escrituraSalioBien = true;
        if ($path != null && $arrayObjetos != null) {
            $archivo = fopen($path, "w");
            foreach ($arrayObjetos as $objeto) {
                if (!is_null($objeto)) {
                    if (strval($objeto->IdUsuario) == strval($idUsuarioBuscado) || $idUsuarioBuscado == -1) {
                        if (!isset($objeto->IdRefUsuario)) {
                            $IdRefUsuario = 0;
                        }
                        if (!isset($objeto->IdMesa)) {
                            $IdMesa = 0;
                        }
                        if (!isset($objeto->IdPedido)) {
                            $IdPedido = 0;
                        }
                        if (!isset($objeto->IdProducto)) {
                            $IdProducto = 0;
                        }
                        $string =   strval($objeto->IdUsuario) . "," .
                            $IdRefUsuario . "," .
                            $objeto->Hora . "," .
                            $objeto->IdAccion . "," .
                            $IdMesa . "," .
                            $IdPedido . "," .
                            $IdProducto;

                        fwrite($archivo, $string . PHP_EOL);
                    }
                } else {
                    return false;
                }
            }
            fclose($archivo);
        } else {
            return false;
        }
        return $escrituraSalioBien;
    }

    public static function PedidosPDF(string $path, $arrayObjetos)
    {
        $escrituraSalioBien = true;
        if ($path != null && $arrayObjetos != null) {
            $archivo = fopen($path, "w");

            $cabecera = '---------------------------RESTAURANT EL PHP----------------------------' . PHP_EOL .
                'Este es un informe detallado de los ultimos 30 dias.' . PHP_EOL .
                PHP_EOL . 'TOTAL VENTAS: $' . self::TotalVendido() . PHP_EOL;

            fwrite($archivo, $cabecera . self::PedidoMasCaro() . self::PedidoMasBarato() . self::PedidoCancelados()); //$string . $corte.PHP_EOL);
            fclose($archivo);
        } else {
            return false;
        }

        return $escrituraSalioBien;
    }
    public static function InformeMasCaro()
    {
        $precio = Producto::all()->max('PrecioUnidad');
        $producto = Producto::where('PrecioUnidad', '=', $precio)->first();
        $string =   '----------------------------PRODUCTO MAS CARO---------------------------' . PHP_EOL .
            'El producto mas caro cuesta $' . $precio . ' por unidad' . PHP_EOL .
            'IdProducto: ' . $producto->IdProducto . PHP_EOL .
            'Nombre: ' . $producto->Nombre . PHP_EOL .
            'Stock: ' . $producto->Stock . PHP_EOL .
            'Tiempo estimado de espera: ' . $producto->TiempoEspera . '(minutos)' . PHP_EOL .
            'Area: ' . $producto->Area . PHP_EOL;
        $corte = '------------------------------------------------------------------------' . PHP_EOL;

        return $string . $corte;
    }
    public static function PedidoMasCaro()
    {
        $max = Pedido::all()->max('ImporteTotal');
        $pedido = Pedido::where('ImporteTotal', '=', $max)->first();

        if ($max > 0 && $pedido != null) {
            $string =   '---------------------------PEDIDO MAS CARO------------------------------' . PHP_EOL .
                'El pedido mas caro costo $' . $pedido->ImporteTotal . PHP_EOL .
                'IdPedido: ' . $pedido->IdPedido . PHP_EOL .
                'IdMesa: ' . $pedido->IdMesa . PHP_EOL .
                'Codigo: ' . $pedido->CodigoPedido . PHP_EOL .
                'Foto: ' . $pedido->PathFoto . PHP_EOL .
                'Nombre Cliente: ' . $pedido->NombreCliente . PHP_EOL .
                'Tiempo preparacion: ' . $pedido->TiempoPreparacion . '(minutos)' . PHP_EOL;
            $corte = '-------------------------------------------------------------------------' . PHP_EOL;
            $string . $corte;
        }
        return "#";
    }
    public static function PedidoMasBarato()
    {
        $max = Pedido::all()->min('ImporteTotal');
        $pedido = Pedido::where('ImporteTotal', '=', $max)->first();

        $string =   '---------------------------PEDIDO MAS BARATO------------------------------' . PHP_EOL .
            'El pedido mas caro costo $' . $pedido->ImporteTotal . PHP_EOL .
            'IdPedido: ' . $pedido->IdPedido . PHP_EOL .
            'IdMesa: ' . $pedido->IdMesa . PHP_EOL .
            'Codigo: ' . $pedido->CodigoPedido . PHP_EOL .
            'Foto: ' . $pedido->PathFoto . PHP_EOL .
            'Nombre Cliente: ' . $pedido->NombreCliente . PHP_EOL .
            'Tiempo preparacion: ' . $pedido->TiempoPreparacion . '(minutos)' . PHP_EOL;
        $corte = '-------------------------------------------------------------------------' . PHP_EOL;

        return $string . $corte;
    }

    public static function PedidoCancelados()
    {

        $pedidos = Pedido::all();
        $string =   '---------------------------PEDIDOS CANCELADOS------------------------------' . PHP_EOL;
        foreach ($pedidos as $detalle) {
            # code...
            if ($detalle->Estado == 'Cancelado') {
                $string = $string . 'Costo $' . $detalle->ImporteTotal . PHP_EOL .
                    'IdPedido: ' . $detalle->IdPedido . PHP_EOL .
                    'IdMesa: ' . $detalle->IdMesa . PHP_EOL .
                    'Codigo: ' . $detalle->CodigoPedido . PHP_EOL .
                    'Foto: ' . $detalle->PathFoto . PHP_EOL .
                    'Nombre Cliente: ' . $detalle->NombreCliente . PHP_EOL .
                    'Tiempo preparacion: ' . $detalle->TiempoPreparacion . '(minutos)' . PHP_EOL .
                    '------------------------------------------------' . PHP_EOL;
            }
        }
        return $string;
    }
    public static function TotalVendido()
    {
        $max = Pedido::all()->sum('ImporteTotal');
        $string =  $max . PHP_EOL;
        $corte = '-------------------------------------------------------------------------';
        return $string . $corte;
    }
 //nombre - precio - tiempoMinutos - area - tipo - stock
    public static function LeerProductosCSV(string $path)
    {
        $list = array();
        $archivo = fopen($path, "r");
        $archivoLength = filesize($path);

        $i = 0;
        while (!feof($archivo)) {
            if ($archivoLength < 2) {
                break;
            }
            $stringLineaLeida = fgets($archivo, $archivoLength);
            if (strlen($stringLineaLeida) > 1) {
                $array = explode(',', $stringLineaLeida);

                $objetoAuxiliar = new Producto(); 
                $objetoAuxiliar->Nombre = $array[0];
                $objetoAuxiliar->Stock = $array[1];
                $objetoAuxiliar->PrecioUnidad = $array[2];
                $objetoAuxiliar->TiempoEspera = $array[3];
                $objetoAuxiliar->Area = $array[4];
                $tipoProducto = explode(PHP_EOL, $array[5]); //SACO SALTO DE LINEA
                $objetoAuxiliar->TipoProducto = $tipoProducto[0];

                array_push($list, $objetoAuxiliar);
            }
            $i++;
        }
        fclose($archivo);

        if (count($list) > 0) {
            foreach ($list as $producto) {
                $prodAux = Producto::where('Nombre', '=', $producto->Nombre)->first();
                if ($prodAux == null) {
                    $producto->save();
                } else {
                    $prodAux->Stock = intval($producto->Stock) + intval($prodAux->Stock);
                    $prodAux->PrecioUnidad = $producto->PrecioUnidad;
                    $prodAux->TiempoEspera = $producto->TiempoEspera;
                    $prodAux->Area = $producto->Area;
                    $prodAux->TipoProducto = $producto->TipoProducto;
                    $prodAux->update();
                }
            }
        }
        return $list;
    }

    //*************************REPORTES MESAS******************************* */
    public static function MesasPDF(string $path, $arrayObjetos)
    {
        $escrituraSalioBien = true;
        if ($path != null && $arrayObjetos != null) {
            $archivo = fopen($path, "w");

            $cabecera = '---------------------------RESTAURANT EL PHP----------------------------' . PHP_EOL .
                'Este es un informe detallado de nuestras mesas de los ultimos 30 dias.' . PHP_EOL .
                PHP_EOL . 'TOTAL VENTAS: $' . self::TotalVendido() . PHP_EOL;

            fwrite($archivo,self::MejorImporteMesa().self::PeorImporteMesa().self::MejorComentario().self::PeorComentario()); //$string . $corte.PHP_EOL);
            fclose($archivo);
        } else {
            return false;
        }

        return $escrituraSalioBien;
    }
    public static function MejorComentario()
    {
        $max = Pedido::all()->max('Puntuacion');
        $pedido = Pedido::where('Puntuacion', '=', $max)->first();
        if ($pedido != null) {
            $string =   '---------------------------MEJOR COMENTARIO------------------------------' . PHP_EOL .
                'Este es un informe sobre el mejor comentario del mes!' . PHP_EOL .
                'IdPedido: ' . $pedido->IdPedido . PHP_EOL .
                'IdMesa: ' . $pedido->IdMesa . PHP_EOL .
                'Nombre Cliente: ' . $pedido->NombreCliente . PHP_EOL .
                'Su puntuacion fue :'.$pedido->Puntuacion . PHP_EOL.
                'Comentario: '.$pedido->Comentario . PHP_EOL;
            $corte = '-------------------------------------------------------------------------' . PHP_EOL;
           return $string . $corte;
        }
        return "#";
    }
    public static function PeorComentario()
    {
        $min = Pedido::all()->min('Puntuacion');
        $pedido = Pedido::where('Puntuacion', '=', $min)->first();
        if ($pedido != null) {
            $string =   '---------------------------PEOR COMENTARIO------------------------------' . PHP_EOL .
                'Este es un informe sobre el peor comentario del mes' . PHP_EOL .
                'IdPedido: ' . $pedido->IdPedido . PHP_EOL .
                'IdMesa: ' . $pedido->IdMesa . PHP_EOL .
                'Nombre Cliente: ' . $pedido->NombreCliente . PHP_EOL .
                'Su puntuacion fue :'.$pedido->Puntuacion . PHP_EOL.
                'Comentario: '.$pedido->Comentario . PHP_EOL;
            $corte = '-------------------------------------------------------------------------' . PHP_EOL;
           return $string . $corte;
        }
        return "#";
    }
    public static function MejorImporteMesa()
    {
        $max = Pedido::all()->max('ImporteTotal');
        $pedido = Pedido::where('ImporteTotal', '=', $max)->first();

        if ($pedido != null) {
            $string =   '---------------------------FACTURA MAS CARA------------------------------' . PHP_EOL .
                'La factura de mayor costo fue $' . $pedido->ImporteTotal . PHP_EOL .
                'IdMesa: ' . $pedido->IdMesa . PHP_EOL .
                'Codigo: ' . $pedido->CodigoPedido . PHP_EOL .
                'Nombre Cliente: ' . $pedido->NombreCliente . PHP_EOL;
            $corte = '-------------------------------------------------------------------------' . PHP_EOL;
            return $string . $corte;
        }
        return "#";
    }
    public static function PeorImporteMesa()
    {
        $max = Pedido::all()->min('ImporteTotal');
        $pedido = Pedido::where('ImporteTotal', '=', $max)->first();

        if ($pedido != null) {
            $string =   '---------------------------FACTURA MAS BAJA------------------------------' . PHP_EOL .
                'La factura de menor costo fue $' . $pedido->ImporteTotal . PHP_EOL .
                'IdMesa: ' . $pedido->IdMesa . PHP_EOL .
                'Codigo: ' . $pedido->CodigoPedido . PHP_EOL .
                'Nombre Cliente: ' . $pedido->NombreCliente . PHP_EOL;
            $corte = '-------------------------------------------------------------------------' . PHP_EOL;
            return $string . $corte;
        }
        return "#";
    }
    public static function MesaMasUsada()
    {
        $max = Pedido::all()->count('IdMesa');
        $max = Pedido::all()->count('IdMesa');
        $pedido = Pedido::where('ImporteTotal', '=', $max)->first();

        if ($pedido != null) {
            $string =   '---------------------------FACTURA MAS CARA------------------------------' . PHP_EOL .
                'La factura de mayor costo fue $' . $pedido->ImporteTotal . PHP_EOL .
                'IdMesa: ' . $pedido->IdMesa . PHP_EOL .
                'Codigo: ' . $pedido->CodigoPedido . PHP_EOL .
                'Nombre Cliente: ' . $pedido->NombreCliente . PHP_EOL;
            $corte = '-------------------------------------------------------------------------' . PHP_EOL;
            return $string . $corte;
        }
        return "#";
    }
//************************************************************************************* */
    //*************************REPORTES EMPLEADOS******************************* */
    public static function EmpleadosPDF(string $path)
    {
        $escrituraSalioBien = true;
        if ($path != null) {
            $archivo = fopen($path, "w");

            $cabecera = '---------------------------RESTAURANT EL PHP----------------------------' . PHP_EOL .
                'Este es un informe detallado de la actividad de los usuarios de los ultimos 30 dias.' . PHP_EOL ;

            fwrite($archivo,self::Logeos()); //$string . $corte.PHP_EOL);
            fclose($archivo);
        } else {
            return false;
        }

        return $escrituraSalioBien;
    }

public static function Logeos(){
    $historial = Auditoria::all();
    $string = '';
    foreach ($historial as $obj) {
        if($obj != null){
            if($obj->IdAccion == AuditoriaAcciones::Login){

                $usuario = Usuario::where('IdUsuario','=',$obj->IdUsuario)->first();
                
                $string =  $string . '' . PHP_EOL .
                'Usuario: ' . $usuario->Usuario . ' - Estado: ' .$usuario->Estado . PHP_EOL .
                'Nombre: ' . $obj->Nombre . PHP_EOL .
                'Fecha : ' . $obj->FechaAlta . PHP_EOL .
                'Hora:  ' . $obj->Hora . PHP_EOL .
                '------------------------------------------------------------------------' . PHP_EOL;
                
            }
        }
    }
    return '----------------------------LOGEOS USUARIOS---------------------------'.$string ;
}
    static public function SaveImage($directory, $fileName, $array_FILE)
    {
        try {
            if(!is_null($directory) && !is_null($fileName) && !is_null($array_FILE))
            {
                // Verifico que envie un file con KEY "image"
                if(is_null($array_FILE['image'])) {  
                    echo 'No se encontró archivo con KEY "image". Verifique!!'. PHP_EOL;
                    return false; 
                }

                // Si no existe directorio, lo creo.
                // !!!  Acordarse que solo crea asi './nuevaCarpeta' o asi nuevaCarpeta !!!
                if (!file_exists($directory)) { mkdir($directory, 0777, true); }

                // Seteo el nombre de la Imagen 
                $array_FILE['image']['name'] = $fileName;
                
                // Preparo path para alojar Imagen
                $destino = $directory . '/' . $array_FILE['image']['name'] . '.png';

                return move_uploaded_file($array_FILE['image']['tmp_name'], $destino);
            }
            else
            {
                echo '¡Ocurrió un error! fileName o array_FILE al menos uno es NULL. Verifique!'. PHP_EOL;
            }
        } catch (Exception $th) {
            echo '¡Ocurrió un error! ', $th->getMessage();
        }
    }
}
