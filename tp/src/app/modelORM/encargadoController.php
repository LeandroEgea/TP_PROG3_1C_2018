<?php

namespace App\Models\ORM;

use App\Models\AutentificadorJWT;
use App\Models\IApiControler;
use App\Models\ORM\Encargado;

include_once __DIR__ . '/encargado.php';
include_once __DIR__ . '../../modelAPI/AutentificadorJWT.php';
include_once __DIR__ . '../../modelAPI/IApiControler.php';

class EncargadoController implements IApiControler
{
    public function TraerTodos($request, $response, $args)
    {
        $todosLosEncargados = Encargado::where("idRol", "!=", 0)
            ->join('roles', 'encargados.idRol', 'roles.id')
            ->select("encargados.id", "nombre", "apellido", "idRol", "cargo")
            ->get();

        if (count($todosLosEncargados) > 0) {
            return $response->withJson($todosLosEncargados, 200);
        }
        return $response->withJson("No hay encargados", 400);
    }

    public function TraerUno($request, $response, $args)
    {
        $id = $args["id"];
        $encargado = Encargado::find($id)
            ->toArray();
        if ($encargado != null) {
            unset($encargado["clave"], $encargado["created_at"], $encargado["updated_at"]);
            return $response->withJson($encargado, 200);
        }
        return $response->withJson("ID invalido", 400);
    }

    public function CargarUno($request, $response, $args)
    {
        $body = $request->getParsedBody();
        if (!array_key_exists("nombre", $body) ||
            !array_key_exists("apellido", $body) ||
            !array_key_exists("usuario", $body) ||
            !array_key_exists("clave", $body) ||
            !array_key_exists("idRol", $body)) {
            return $response->withJson('Introduzca todos los datos', 400);
        }

        $usuario = Encargado::where('usuario', $body["usuario"])
            ->first();
        if ($usuario != null) {
            return $response->withJson("Ya existe encargado con ese usuario", 200);
        }

        $encargadoNuevo = new Encargado;
        $encargadoNuevo->nombre = $body["nombre"];
        $encargadoNuevo->apellido = $body["apellido"];
        $encargadoNuevo->usuario = $body["usuario"];
        $encargadoNuevo->clave = $body["clave"];
        $encargadoNuevo->idRol = $body["idRol"];
        $encargadoNuevo->save();
        $encargado = Encargado::find($encargadoNuevo->id)
            ->toArray();
        unset($encargado["clave"], $encargado["created_at"], $encargado["updated_at"]);
        return $response->withJson($encargado, 200);
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args["id"];
        $encargado = Encargado::find($id);
        if ($encargado == null) {
            return $response->withJson('El encargado no existe', 400);
        }
        $pedidos = Pedido::where('idEncargado', $id)
            ->first();
        if ($pedidos != null) {
            return $response->withJson('No se pueden borrar encargados que hayan sido utilizadas en un pedido', 400);
        }

        $encargado->delete();
        return $response->withJson($encargado, 200);
    }

    public function ModificarUno($request, $response, $args)
    {
        $body = $request->getParsedBody();
        $id = $args["id"];
        if ($id == null) {
            return $response->withJson('Introduzca ID', 400);
        }
        $encargado = Encargado::find($id);
        if ($encargado == null) {
            return $response->withJson("No se encontro encargado", 400);
        }

        $modificado = false;
        if (array_key_exists("nombre", $body)) {
            $encargado->nombre = $body["nombre"];
            $modificado = true;
        }
        if (array_key_exists("apellido", $body)) {
            $encargado->apellido = $body["apellido"];
            $modificado = true;
        }
        if (array_key_exists("usuario", $body)) {
            $encargado->usuario = $body["usuario"];
            $modificado = true;
        }
        if (array_key_exists("idRol", $body)) {
            $encargado->idRol = $body["idRol"];
            $modificado = true;
        }
        if (array_key_exists("clave", $body)) {
            $encargado->clave = $body["clave"];
            $modificado = true;
        }
        if ($modificado === true) {
            $encargado->save();
            return $response->withJson($encargado, 200);
        }
        return $response->withJson("No se ha modificado ningun campo", 400);
    }

    public function IniciarSesion($request, $response)
    {
        $body = $request->getParsedBody();
        if (!array_key_exists("usuario", $body) || !array_key_exists("clave", $body)) {
            return $response->withJson('Introduzca todos los datos', 400);
        }
        $encargado = Encargado::where('usuario', '=', $body["usuario"])
            ->join('roles', 'encargados.idRol', 'roles.id')
            ->select("encargados.id", "nombre", "apellido", "usuario", "clave", "idRol", "cargo")
            ->get()
            ->toArray();

        if (count($encargado) == 1 && $encargado[0]["clave"] == $body["clave"]) {
            unset($encargado[0]["clave"]);
            $token = AutentificadorJWT::CrearToken($encargado[0]);
            return $response->withJson($token, 200);
        } else {
            return $response->withJson('Datos invalidos', 401);
        }
    }
}
