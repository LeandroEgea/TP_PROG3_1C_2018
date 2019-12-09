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
                $nuevoPedido->imagen = PedidoController::CargarImagen($archivos["imagen"], $nuevoPedido->codigoPedido);
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

    public function CargarImagen($imagen, $codigoPedido)
    {
        $extension = $imagen->getClientFilename();
        $extension = explode(".", $extension);
        $direccion = "./images/clients/" . $codigoPedido . "." . $extension[1];
        $imagen->moveTo($direccion);
        return $direccion;
    }

    public function BorrarUno($request, $response, $args)
    {
        $codigo = $args['codigo'];
        $pedido = Pedido::where('codigoPedido', $codigo)
            ->first();
        if ($pedido != null) {
            $pedido->delete();
            return $response->withJson($pedido, 200);
        }
        return $response->withJson('El pedido no existe', 400);
    }

    public function ModificarUno($request, $response, $args)
    {
        $body = $request->getParsedBody();
        $archivos = $request->getUploadedFiles();
        $codigo = $args['codigo'];
        if ($codigo == null) {
            return $response->withJson('Introduzca codigo', 400);
        }
        $pedido = Pedido::where('codigoPedido', $codigo)
            ->first();
        if ($pedido == null) {
            return $response->withJson("No se encontro pedido", 400);
        }

        $modificado = false;
        if (array_key_exists("nombreCliente", $body)) {
            $pedido->nombreCliente = $body["nombreCliente"];
            $modificado = true;
        }
        if (array_key_exists("tiempo", $body)) {
            $pedido->tiempo = $body["tiempo"];
            $modificado = true;
        }
        if ($archivos != null && array_key_exists("imagen", $archivos)) {
            $pedido->imagen = PedidoController::CargarImagen($archivos["imagen"], $pedido->codigoPedido);
            $modificado = true;
        }
        if (array_key_exists("productos", $body)) {
            $productos = explode(",", $body["productos"]);
            foreach ($productos as $idProducto) {
                $producto = Producto::find($idProducto);
                if ($producto != null) {
                    $pedidoProducto = new PedidoProducto;
                    $pedidoProducto->idPedido = $pedido->id;
                    $pedidoProducto->idProducto = $producto->id;
                    $pedidoProducto->idEstadoProducto = 1; // 1 = pendiente
                    $pedidoProducto->save();
                    if ($producto->tiempoPreparacion > $pedido->tiempo) {
                        $pedido->tiempo = $producto->tiempoPreparacion;
                    }
                    $pedido->idEstadoPedido = 1; // 1 = pendiente
                    $modificado = true;
                }
            }
        }

        if ($modificado === true) {
            $pedido->save();
            return $response->withJson($pedido, 200);
        }
        return $response->withJson("No se ha modificado ningun campo", 400);

    }

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
        //TODO: GUARD
        $tokenData = $request->getAttribute('tokenData');
        $codigoPedido = $args['codigo'];
        // 1 = pendiente, 2 = en preparacion
        $respuesta = PedidoProductoController::CambiarEstado($codigoPedido, $tokenData->idRol, 1, 2);

        if ($respuesta === true) {
            PedidoController::CambiarEstado($codigoPedido, 2); // 2 = en preparacion
            return $response->withJson("Pedido en preparacion: " . $codigoPedido, 200);
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
        $pedido = Pedido::where('codigoPedido', $codigoPedido)
            ->first();
        //TODO: Validar que sea distinto de null
        if ($pedido->idEstadoPedido == 3) { // 3 = listo para servir
            $pedido = pedido::where('codigoPedido', '=', $codigoPedido)
                ->first();
            MesaController::CambiarEstado($pedido->codigoMesa, 2); // 2 = comiendo
            PedidoController::CambiarEstado($codigoPedido, 4); // 4 = servido
            PedidoProductoController::CambiarEstado($codigoPedido, 3, 3, 4); // 4 = servido
            return $response->withJson("Pedido servido: " . $codigoPedido, 200);
        } else {
            return $response->withJson(
                "El pedido no esta listo para ser servido: " . $codigoPedido, 400);
        }
    }

    public function PedirCuenta($request, $response, $args)
    {
        $total = 0;
        $ticketMostrado = new \stdClass;
        $productosTicketMostrado = [];

        $codigoPedido = $args['codigo'];
        $pedido = Pedido::where('codigoPedido', '=', $codigoPedido)
            ->first();

        //TODO: Validar que no haya ticket
        //TODO: Validar que sea distinto de null
        //TODO: Validar el estado del pedido
        $productos = PedidoProducto::join('productos', 'productos.id', 'pedidos_productos.idProducto')
            ->where('pedidos_productos.idPedido', '=', $pedido->id)
            ->select('productos.precio', 'productos.descripcion')
            ->get();

        //TODO: Validar que sea distinto de null
        //TODO: Validar el estado de los productos
        foreach ($productos as $producto) {
            $total = $total + $producto->precio;

            $prod = new \stdClass;
            $prod->producto = $producto->descripcion;
            $prod->precio = $producto->precio;
            array_push($productosTicketMostrado, $prod);
        }

        $ticket = new Ticket;
        $ticket->idPedido = $pedido->id;
        $ticket->precioTotal = $total;
        $ticket->pagado = 0;
        $ticket->save();
        MesaController::CambiarEstado($pedido->codigoMesa, 3); // 3 = pagando

        $ticketMostrado->total = $total;
        $ticketMostrado->codigoPedido = $pedido->codigoPedido;
        $ticketMostrado->codigoMesa = $pedido->codigoMesa;
        $ticketMostrado->nombreCliente = $pedido->nombreCliente;
        $ticketMostrado->productos = $productosTicketMostrado;
        return $response->withJson($ticketMostrado, 200);
    }

    public function Cobrar($request, $response, $args)
    {
        //TODO: GUARD
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
                "Pedido: " . $codigoPedido . " cobrado, Mesa: " . $mesa->codigoMesa . " libre", 200);
        } else {
            return $response->withJson("La mesa no habia solicitado la cuenta", 400);
        }
    }
}
