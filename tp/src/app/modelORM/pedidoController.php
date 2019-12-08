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
        $codigo = $args['codigo'];
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
        $codigoPedido = $args['codigo'];
        // 1 = pendiente, 2 = en preparacion
        $respuesta = PedidoProductoController::CambiarEstado($codigoPedido, $tokenData->idRol, 1, 2);

        if ($respuesta === true) {
            PedidoController::CambiarEstado($codigoPedido, 2); // 2 = en preparacion
            return $response->withJson("Comienza preparacion del pedido: " . $codigoPedido, 200);
        } else {
            return $response->withJson("No habia pedidos o ya fueron tomados para preparar", 400);
        }
    }

    public function TerminarPedido($request, $response, $args)
    {
        //TODO: GUARD
        $tokenData = $request->getAttribute('tokenData');
        $codigoPedido = $args['codigo'];
        // 2 = en preparacion, 3 = listo para servir
        $respuesta = PedidoProductoController::CambiarEstado($codigoPedido, $tokenData->idRol, 2, 3);

        if ($respuesta === true) {
            $data = PedidoProducto::join('pedidos', 'pedidos_productos.idPedido', 'pedidos.id')
                ->where('pedidos.codigoPedido', '=', $codigoPedido)
                ->get();

            $completo = true;
            foreach ($data as $pedidoProducto) {
                if ($pedidoProducto->idEstadoProducto != 3) { // 3 = listo para servir
                    $completo = false;
                }
            }
            if ($completo === true) {
                PedidoController::CambiarEstado($codigoPedido, 3); // 3 = listo para servir
                return $response->withJson(
                    "Se finalizó la preparacion de los productos, pedido " . $codigoPedido . " listo para servir", 200);
            } else {
                return $response->withJson(
                    "Se finalizó la preparacion de los productos del rol: " . $tokenData->cargo . " para el pedido: " . $codigoPedido, 200);
            }
        } else {
            return $response->withJson("No hay productos pendientes para este pedido", 400);
        }
    }

    public function ServirPedido($request, $response, $args)
    {
        //TODO: GUARD
        $codigoPedido = $args['codigo'];
        $pedido = Pedido::where('codigoPedido', $codigoPedido)->first();
        //TODO: Validar que sea distinto de null
        if ($pedido->idEstadoPedido == 3) { // 3 = listo para servir
            $pedido = pedido::where('codigoPedido', '=', $codigoPedido)
                ->first();
            MesaController::CambiarEstado($pedido->codigoMesa, 2); // 2 = comiendo
            PedidoController::CambiarEstado($codigoPedido, 4); // 4 = servido
            PedidoProductoController::CambiarEstado($codigoPedido, 3, 3, 4); // 4 = servido
            return $response->withJson("Pedido servido", 200);
        } else {
            return $response->withJson("El pedido no esta listo para ser servido", 400);
        }
    }

    public function PedirCuenta($request, $response, $args)
    {
        $total = 0;
        //TODO: cambiar ticket a mostrar
        //$ticketMostrado = new \stdClass;

        $codigoPedido = $args['codigo'];
        $pedido = Pedido::where('codigoPedido', '=', $codigoPedido)
            ->first();
        //TODO: Validar que sea distinto de null
        //TODO: Validar el estado del pedido
        $productos = PedidoProducto::join('productos', 'productos.id', 'productos_pedidos.idProducto')
            ->where('codigoPedido', '=', $codigoPedido)->get();
        //TODO: Validar que sea distinto de null
        //TODO: Validar el estado de los productos
        foreach ($productos as $producto) {
            // $prod = new \stdClass;
            // $prod->producto = $producto->descripcion;
            // $prod->precio = $producto->precio;
            $total = $total + $producto->precio;
            // array_push($ticket, $prod);
        }

        $ticket = new Ticket;
        $ticket->idPedido = $pedido->id;
        $ticket->precioTotal = $total;
        $ticket->pagado = 0;
        $ticket->save();
        MesaController::CambiarEstado($pedido->codigoMesa, 3); // 3 = pagando
        return $response->withJson($ticket, 200);
    }

    public function Cobrar($request, $response, $args)
    {
        $codigoPedido = $args['codigo'];
        //TODO: Validar que sea distinto de null
        $pedido = Pedido::where('codigoPedido', '=', $codigoPedido)
            ->first();
        $ticket = Ticket::where('idPedido', '=', $pedido->id)
            ->first();
        $mesa = Mesa::where('codigoMesa', '=', $pedido->codigoMesa)
            ->first();

        if ($mesa != null && $mesa->idEstadoMesa == 3) { // 3 = pagando
            $ticket->pagado = 1;
            $ticket->save();
            PedidoController::CambiarEstado($codigoPedido, 5); // 5 = cobrado
            MesaController::CambiarEstado($pedido->codigoMesa, 4); // 4 = libre
            return $response->withJson(
                "Pedido: " . $codigoPedido . " cobrado, Mesa: " . $mesa->id . " libre", 200);
        } else {
            return $response->withJson("La mesa no habia solicitado la cuenta", 400);
        }
    }
}
