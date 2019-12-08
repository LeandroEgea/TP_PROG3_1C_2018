<?php

namespace App\Models\ORM;

use App\Models\IApiControler;
use App\Models\ORM\MesaController;
use App\Models\ORM\Pedido;
use App\Models\ORM\Ticket;

include_once __DIR__ . '/pedido.php';
include_once __DIR__ . '/producto.php';
include_once __DIR__ . '/ticket.php';
include_once __DIR__ . '/mesaController.php';
include_once __DIR__ . '/pedido_producto.php';
include_once __DIR__ . '../../modelAPI/IApiControler.php';

use \stdClass;

class PedidoController implements IApiControler
{
    public function TraerTodos($request, $response, $args)
    {
        $pedidos = Pedido::all();
        if (count($pedidos) > 0) {
            return $response->withJson($pedidos, 200);
        }
        return $response->withJson("No hay pedidos cargados", 400);
    }

    public function TraerUno($request, $response, $args)
    {
        $codigoPedido = $args['codigo'];
        try {
            $pedido = Pedido::join('mesas', 'pedidos.codigoMesa', 'mesas.codigoMesa')
                ->join('estados_pedidos', 'pedidos.idEstadoPedido', 'estados_pedidos.id')
                ->where('codigoPedido', $codigoPedido)
                ->get();

            if ($pedido != null && count($pedido) == 1) {
                return $response->withJson($retorno, 200);
            } else {
                return $response->withJson("No hay pedidos cargados con ese codigo", 400);
            }
        } catch (Exception $e) {
            return $response->withJson("Se produjo un error con el codigo ingresado", 500);
        }
    }

    public function CargarUno($request, $response, $args)
    {
        //TODO: GUARD
        $body = $request->getParsedBody();
        $tokenData = $request->getAttribute('tokenData');
        $codigoMesa = MesaController::ObtenerMesaLibre();
        if ($codigoMesa != null) {
            $nuevoPedido = new Pedido;
            $nuevoPedido->idEstadoPedido = 1; // 1 = pendiente
            $nuevoPedido->codigoPedido = PedidoController::GenerarCodigoPedido();
            $nuevoPedido->codigoMesa = $codigoMesa;
            $nuevoPedido->idEncargado = $tokenData->id;
            $nuevoPedido->nombreCliente = $body["nombreCliente"];
            $archivos = $request->getUploadedFiles();
            if ($archivos != null && $archivos["imagen"] != null) {
                $nuevoPedido->imagen = $archivos["imagen"]->file;
            }
            $nuevoPedido->tiempo = 0;
            $nuevoPedido->save();

            $productos = explode(",", $body["productos"]);
            $tieneProductos = false;
            foreach ($productos as $idProducto) {
                $producto = Producto::find($idProducto);
                if ($producto != null) {
                    $pedidoProducto = new PedidoProducto;
                    $pedidoProducto->idPedido = $nuevoPedido->id;
                    $pedidoProducto->idProducto = $producto->id;
                    $pedidoProducto->idEstadoProducto = 1; // 1 = pendiente
                    $pedidoProducto->save();
                    if ($producto->tiempoPreparacion > $nuevoPedido->tiempo) {
                        $nuevoPedido->tiempo = $producto->tiempoPreparacion;
                    }
                }

                $tieneProductos = true;
            }

            if ($tieneProductos === true) {
                $nuevoPedido->save();
                return $response->withJson($nuevoPedido, 200);
            } else {
                MesaController::CambiarEstado($nuevoPedido->codigoMesa, 4); // 4 = libre
                $nuevoPedido->delete();
                return $response->withJson('No se puede cargar un pedido sin productos. Pedido eliminado', 400);
            }
        } else {
            return $response->withJson('No hay mesas disponibles', 400);
        }
    }

    public function BorrarUno($request, $response, $args)
    {
        $codigo = $args["codigo"];
        $pedido = Pedido::where('codigoPedido', $codigo)
            ->get();
        if ($pedido != null) {
            $pedido->delete();
            return $response->withJson($pedido, 200);
        }
        return $response->withJson('El pedido no existe', 400);
    }

    //TODO: Modificar

    public function GenerarCodigoPedido()
    {
        $length = 5;
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($str_result), 0, $length);
    }

    public function CambiarEstado($codigoPedido, $estado)
    {
        $pedido = Pedido::where('codigoPedido', $codigoPedido)
            ->first();
        if ($pedido != null) {
            $pedido->idEstadoPedido = $estado;
            if ($estado == 3) { // 3 = listo para servir
                $pedido->tiempo = 0; // ???
            }
            $pedido->save();
        }
    }

    public function PrepararPedido($request, $response, $args)
    {
        $tokenData = $request->getAttribute('tokenData');
        $body = $request->getParsedBody();
        // 1 = pendiente, 2 = en preparacion
        $respuesta = PedidoProductoController::CambiarEstado($body["codigoPedido"], $tokenData->idRol, 1, 2);

        if ($respuesta === true) {
            PedidoController::CambiarEstado($body["codigoPedido"], 2); // 2 = en preparacion
            return $response->withJson("Comienza preparacion del pedido: " . $body["codigoPedido"], 200);
        } else {
            return $response->withJson("No habia pedidos o ya fueron tomados para preparar", 400);
        }
    }

    public function TerminarPedido($request, $response, $args)
    {
        $tokenData = $request->getAttribute('tokenData');
        $body = $request->getParsedBody();
        // 2 = en preparacion, 3 = listo para servir
        $respuesta = PedidoProductoController::CambiarEstado($body["codigoPedido"], $tokenData->idRol, 2, 3);

        if ($respuesta === true) {
            $data = PedidoProducto::where('codigoPedido', $body["codigoPedido"])
                ->get();

            $pedidosProductos = PedidoProducto::join('pedidos', 'pedidos_productos.idPedido', 'pedidos.id')
                ->where('pedidos.codigoPedido', '=', $body["codigoPedido"])
                ->get();

            $completo = true;
            foreach ($data as $pedidoProducto) {
                if ($pedidoProducto->idEstadoProducto != 3) { // 3 = listo para servir
                    $completo = false;
                }
            }
            if ($completo === true) {
                PedidoController::CambiarEstado($body["codigoPedido"], 3);
                PedidoProductoController::CambiarEstado($body["codigoPedido"], $tokenData->idRol, 2, 3);
                return $response->withJson("Se preparon todos los productos. Pedido listo para servir", 200);
            } else {
                return $response->withJson("Se finalizó la preparacion de los productos", 200);
            }
        } else {
            return $response->withJson("No hay productos pendientes para este pedido", 400);
        }
    }

    public function ServirPedido($request, $response, $args)
    {
        $body = $request->getParsedBody();
        $pedido = Pedido::where('codigoPedido', $body["codigoPedido"])->first();
        if ($pedido->idEstadoPedido == 3) {
            $pedido = pedido::where('codigoPedido', '=', $body["codigoPedido"])->first();
            MesaController::CambiarEstado($pedido->codigoMesa, 2);

            PedidoController::CambiarEstado($body["codigoPedido"], 4);

            PedidoProductoController::CambiarEstado($body["codigoPedido"], 3, 3, 4);

            $newResponse = $response->withJson("Pedido entregado", 200);
        } else {
            $newResponse = $response->withJson("El pedido no esta listo para ser entregado", 200);
        }
        return $newResponse;
    }
    public function PedirCuenta($request, $response, $args)
    {
        $total = 0;
        $body = $request->getParams();
        $pedido = Pedido::where('codigoPedido', '=', $body['codigoPedido'])->first();
        $productos = PedidoProducto::join('productos', 'productos.id', 'productos_pedidos.idProducto')
            ->where('codigoPedido', '=', $body['codigoPedido'])->get();
        $ticket = [];
        foreach ($productos as $producto) {
            $prod = new \stdClass;
            $prod->producto = $producto->descripcion;
            $prod->precio = $producto->precio;
            $total = $total + $producto->precio;
            array_push($ticket, $prod);
        }
        $cuenta = new \stdClass;
        $cuenta->nombreCliente = $pedido->nombreCliente;
        $cuenta->ticket = $pedido->codigoPedido;
        $cuenta->pedido = $ticket;
        $cuenta->total = $this->total;
        $nuevoRetorno = $response->withJson($cuenta, 200);
        MesaController::CambiarEstado($pedido->codigoMesa, 3);
        return $nuevoRetorno;
    }

    public function Cobrar($request, $response, $args)
    {
        $total = 0;
        $body = $request->getParams();
        $pedido = Pedido::where('codigoPedido', '=', $body['codigoPedido'])->first();
        if ($pedido != null && $pedido->idEstadoPedido == 4) {
            $ticket = new Ticket();
            $ticket->codigoPedido = $pedido->codigoPedido;
            $productos = PedidoProducto::join('productos', 'productos.id', 'productos_pedidos.idProducto')
                ->where('codigoPedido', '=', $body['codigoPedido'])->get();
            foreach ($productos as $producto) {
                $total = $total + $producto->precio;
            }
            $ticket->precioTotal = $total;
            $ticket->mesa = $pedido->codigoMesa;
            $encargado = Encargado::where('id', '=', $pedido->idEncargado)->first();
            $ticket->encargado = $encargado->usuario;
            $ticket->save();
            PedidoController::CambiarEstado($body['codigoPedido'], 5); //cobrado
            MesaController::CambiarEstado($pedido->codigoMesa, 4); //cerrada
            $newResponse = $response->withJson("Pedido cobrado - Mesa Cerrada", 200);
        } else {
            $newResponse = $response->withJson("Pedido no encontrado", 200);
        }
        return $newResponse;
    }
}
