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
        $encargado = Encargado::find($id);
        if ($encargado != null) {
            return $response->withJson($encargado, 200);
        }
        return $response->withJson("ID invalido", 400);
    }

    public function CargarUno($request, $response, $args)
    {
        $body = $request->getParsedBody();
        $encargadoNuevo = new Encargado;
        $encargadoNuevo->nombre = $body["nombre"];
        $encargadoNuevo->apellido = $body["apellido"];
        $encargadoNuevo->usuario = $body["usuario"];
        $encargadoNuevo->clave = $body["clave"];
        $encargadoNuevo->idRol = $body["idRol"];
        $encargadoNuevo->save();
        return $response->withJson($encargadoNuevo, 200);
    }
    public function BorrarUno($request, $response, $args)
    {
        $body = $request->getParsedBody();
        $id = $body['id'];
        $encargado = Encargado::find($id);
        if ($encargado != null) {
            $encargado->delete();
            return $response->withJson($encargado, 200);
        }
        return $response->withJson('El encargado no existe', 200);
    }

//ME QUEDE ACA

    public function ModificarUno($request, $response, $args)
    {
        $body = $request->getParsedBody();
        $id = null;
        $encargado = null;
        $contadorModificaciones = 0;
        if (array_key_exists("id", $body)) {
            $id = $body['id'];
            $encargado = Encargado::find($id);
        }
        if (array_key_exists("nombre", $body) && $id != null && $encargado != null) {
            $encargado->nombre = $body["nombre"];
            $encargado->usuario = strtolower(substr($body["nombre"], 0, 1)) . strtolower($encargado->apellido);
            $contadorModificaciones++;
        }
        if (array_key_exists("apellido", $body) && $id != null && $encargado != null) {
            $encargado->apellido = $body["apellido"];
            $encargado->usuario = strtolower(substr($encargado->nombre, 0, 1)) . strtolower($body["apellido"]);
            $contadorModificaciones++;
        }
        if (array_key_exists("usuario", $body) && $id != null && $encargado != null) {
            $encargado->usuario = (strtolower(substr($encargado->nombre, 0, 1)) . strtolower($encargado->apellido));
            $contadorModificaciones++;
        }
        if (array_key_exists("idRol", $body) && $id != null && $encargado != null) {
            $encargado->idRol = $body["idRol"];
            $contadorModificaciones++;
        }
        if (array_key_exists("clave", $body) && $id != null && $encargado != null) {
            $encargado->clave = $body["clave"];
            $contadorModificaciones++;
        }
        if ($contadorModificaciones > 0 && $contadorModificaciones <= 5 && $id != null && $encargado != null) {
            $encargado->save();
            $newResponse = $response->withJson('Encargado ' . $encargado->usuario . ' modificado', 200);
        } else if ($id == null) {
            $newResponse = $response->withJson('No se introducido un id valido', 200);
        } else if ($id != null && $encargado == null) {
            $newResponse = $response->withJson("No hay un encargado con ese ID", 200);
        } else {
            $newResponse = $response->withJson("No se ha modificado ningun campo ", 200);
        }
        return $newResponse;
    }

    public function IniciarSesion($request, $response)
    {
        $body = $request->getParsedBody();

        $encargado = Encargado::where('usuario', '=', $body["usuario"])
            ->join('roles', 'encargados.idRol', 'roles.id')
            ->select("encargados.id", "nombre", "apellido", "usuario", "clave", "idRol", "cargo")
            ->get()
            ->toArray();

        if (count($encargado) == 1 && $encargado[0]["clave"] == $body["clave"]) {
            unset($encargado[0]["clave"]);

            $token = AutentificadorJWT::CrearToken($encargado[0]);
            $newResponse = $response->withJson($token, 200);
        } else {
            $newResponse = $response->withJson("Nop", 200);
        }

        return $newResponse;
    }

}
