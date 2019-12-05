<?php

namespace App\Models\ORM;

use App\Models\IApiControler;
use App\Models\ORM\PedidoProductoController;
use App\Models\ORM\Producto;

include_once __DIR__ . '/producto.php';
include_once __DIR__ . '/pedido_productoController.php';
include_once __DIR__ . '../../modelAPI/IApiControler.php';

class ProductoController implements IApiControler
{
    public function TraerTodos($request, $response, $args)
    {
        $productos = Producto::all();
        if (count($productos) > 0) {
            return $response->withJson($productos, 200);
        }
        return $response->withJson("No hay productos cargados", 400);
    }

    public function TraerUno($request, $response, $args)
    {
        $id = $args["id"];
        $producto = Producto::find($id);
        if ($producto != null) {
            return $response->withJson($producto, 200);
        }
        return $response->withJson("ID invalido", 400);
    }

    public function CargarUno($request, $response, $args)
    {
        //TODO: Guard
        $body = $request->getParsedBody();
        $productoNuevo = new Producto;
        $productoNuevo->descripcion = $body["descripcion"];
        $productoNuevo->precio = $body["precio"];
        $productoNuevo->idRol = $body["idRol"];
        $productoNuevo->tiempoPreparacion = $body["tiempoPreparacion"];
        $productoNuevo->save();
        $idProductoCargado = $productoNuevo->id;
        return $response->withJson($productoNuevo, 200);
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args["id"];
        $producto = Producto::find($id);
        if ($producto != null) {
            $producto->delete();
            return $response->withJson($producto, 200);
        }
        return $response->withJson('El producto no existe', 400);
    }

    public function ModificarUno($request, $response, $args)
    {
        $body = $request->getParsedBody();
        $id = $args["id"];
        if ($id == null) {
            return $response->withJson('Introduzca ID', 400);
        }
        $producto = Producto::find($id);
        if ($producto == null) {
            return $response->withJson("No se encontro producto", 400);
        }

        $modificado = false;
        if (array_key_exists("descripcion", $body)) {
            $producto->descripcion = $body["descripcion"];
            $modificado = true;
        }
        if (array_key_exists("precio", $body)) {
            $producto->precio = $body["precio"];
            $modificado = true;
        }
        if (array_key_exists("idRol", $body)) {
            $producto->idRol = $body["idRol"];
            $modificado = true;
        }
        if (array_key_exists("tiempoPreparacion", $body)) {
            $producto->tiempoPreparacion = $body["tiempoPreparacion"];
            $modificado = true;
        }
        if ($modificado === true) {
            $producto->save();
            return $response->withJson($producto, 200);
        }
        return $response->withJson("No se ha modificado ningun campo", 400);
    }

    public function VerPendientes($request, $response, $args)
    {
        $tokenData = $request->getAttribute('tokenData');
        $pendientes = PedidoProductoController::VerPendientes($tokenData->cargo);
        if ($pendientes != null && count($pendientes) > 0) {
            return $response->withJson($pendientes, 200);
        } else {
            return $response->withJson("No hay pedidos pendientes para el encargado", 400);
        }
    }
}
