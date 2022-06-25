
<?php
require_once './models/Mesa.php';
require_once './models/Producto.php';
require_once './models/Usuario.php';
require_once './models/Pedido.php';
require_once './models/Archivos.php';
require_once './models/Reportes.php';
date_default_timezone_set("America/Buenos_Aires");

use App\Models\UsuarioTipo as UsuarioTipo;
use App\Models\Pedido as Pedido;

class ReportesController
{
    public function Pedidos($request, $response, $args)
    {
        try {
            $UsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request);
            $header = $request->getHeaderLine('Authorization');

            if ($UsuarioLogeado->IdUsuarioTipo == UsuarioTipo::Socio || $UsuarioLogeado->IdUsuarioTipo == UsuarioTipo::Administrador) {
                $listaDB = Pedido::all();
                if (count($listaDB) > 0) {
                    try {
                        $payload = json_encode(
                            Reportes::Pedidos()
                        );
                    } catch (Exception $e) {
                        $payload = json_encode(array(
                            "mensajeFinal" => "La descarga de auditoria no fue realizada. No hay datos en DB.",
                        ));
                    }
                }
            }
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $payload = json_encode(array(
                "mensajeFinal" => "Error al hacer el reporte.",
            ));
        }
    }
    public function Mesa($request, $response, $args)
    {
        try {
            $UsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request);
            $header = $request->getHeaderLine('Authorization');

            if ($UsuarioLogeado->IdUsuarioTipo == UsuarioTipo::Socio || $UsuarioLogeado->IdUsuarioTipo == UsuarioTipo::Administrador) {
                $listaDB = Pedido::all();
                if (count($listaDB) > 0) {
                    $payload = json_encode(Reportes::Mesas());
                }
            }
        } catch (Exception $e) {
            $payload = json_encode(array(
                "mensajeFinal" => "Error al hacer el reporte.",
            ));
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function Empleados($request, $response, $args)
    {
        try {
            $UsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request);
            $header = $request->getHeaderLine('Authorization');
            if ($UsuarioLogeado->IdUsuarioTipo == UsuarioTipo::Socio || $UsuarioLogeado->IdUsuarioTipo == UsuarioTipo::Administrador) {
                $listaDB = Pedido::all();
                if (count($listaDB) > 0) {
                    $payload = json_encode(
                        Reportes::Empleados()
                    );
                }
            }
        } catch (Exception $e) {
            $payload = json_encode(array(
                "mensajeFinal" => "Error al hacer el reporte.",
            ));
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

?>
