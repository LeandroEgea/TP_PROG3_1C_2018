<?php

namespace App\Models\ORM;

use App\Models\AutentificadorJWT;
use App\Models\IApiControler;
use App\Models\ORM\Producto;

include_once __DIR__ . '/producto.php';
include_once __DIR__ . '../../modelAPI/IApiControler.php';

class ProductoController implements IApiControler
{
    public function TraerTodos($request, $response, $args)
    {
        $todosLosProductos = Producto::all();
        if (count($todosLosProductos) > 0) {
            $newResponse = $response->withJson($todosLosProductos, 200);
        } else {
            $newResponse = $response->withJson("No hay productos", 200);
        }
        return $newResponse;
    }
    public function TraerUno($request, $response, $args)
    {
        $id = $args["id"];
        $producto = Producto::find($id);
        if ($producto != null) {
            $newResponse = $response->withJson($producto, 200);
        } else {
            $newResponse = $response->withJson("No existe producto con ese ID", 200);
        }
        return $newResponse;
    }

    public function CargarUno($request, $response, $args)
    {
        $arrayDeParametros = $request->getParsedBody();
        $productoNuevo = new Producto;
        $productoNuevo->descripcion = $arrayDeParametros["descripcion"];
        $productoNuevo->precio = $arrayDeParametros["precio"];
        $productoNuevo->idRol = $arrayDeParametros["idRol"];
        $productoNuevo->tiempoPreparacion = $arrayDeParametros["tiempoPreparacion"];
        $productoNuevo->save();
        $idProductoCargado = $productoNuevo->id;
        $newResponse = $response->withJson('Producto ' . $idProductoCargado . ' cargado', 200);
        return $newResponse;
    }
    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $id = $parametros['id'];
        $producto = Producto::find($id);
        if ($producto != null) {
            $producto->delete();
            $newResponse = $response->withJson('Producto ' . $id . ' borrado', 200);
        } else {
            $newResponse = $response->withJson('El producto no existe', 200);
        }
        return $newResponse;
    }

    public function ModificarUno($request, $response, $args)
    {
        $arrayDeParametros = $request->getParsedBody();
        $id = null;
        $producto = null;
        $contadorModificaciones = 0;
        if (array_key_exists("id", $arrayDeParametros)) {
            $id = $arrayDeParametros['id'];
            $producto = Producto::find($id);
        }
        if (array_key_exists("descripcion", $arrayDeParametros) && $id != null && $producto != null) {
            $producto->descripcion = $arrayDeParametros["descripcion"];
            $contadorModificaciones++;
        }
        if (array_key_exists("precio", $arrayDeParametros) && $id != null && $producto != null) {
            $producto->precio = $arrayDeParametros["precio"];
            $contadorModificaciones++;
        }
        if (array_key_exists("tipo", $arrayDeParametros) && $id != null && $producto != null) {
            $producto->tipo = $arrayDeParametros["tipo"];
            $contadorModificaciones++;
        }
        if (array_key_exists("idRol", $arrayDeParametros) && $id != null && $producto != null) {
            $producto->idRol = $arrayDeParametros["idRol"];
            $contadorModificaciones++;
        }
        if (array_key_exists("tiempoPreparacion", $arrayDeParametros) && $id != null && $producto != null) {
            $producto->tiempoPreparacion = $arrayDeParametros["tiempoPreparacion"];
            $contadorModificaciones++;
        }
        if ($contadorModificaciones > 0 && $contadorModificaciones <= 4 && $id != null && $producto != null) {
            $producto->save();
            $newResponse = $response->withJson('Producto ' . $id . ' modificado', 200);
        } else if ($id == null) {
            $newResponse = $response->withJson('No se introducido un id valido', 200);
        } else if ($id != null && $producto == null) {
            $newResponse = $response->withJson("No hay un producto con ese ID", 200);
        } else {
            $newResponse = $response->withJson("No se ha modificado ningun campo ", 200);
        }
        return $newResponse;
    }

    public function VerPendientes($request, $response, $args)
    {
        $token = $request->getHeader('token');
        $arrayDeParametros = $request->getParams();
        $datos = AutentificadorJWT::ObtenerData($token[0]);
        $respuesta = PedidoProductoController::verPendientes($arrayDeParametros["codigoPedido"], $datos->idRol);
        if (count($respuesta) > 0) {
            $newResponse = $response->withJson($respuesta, 200);
        } else {
            $newResponse = $response->withJson("No hay pedidos pendientes para el encargado", 200);
        }
        return $newResponse;
    }
}
