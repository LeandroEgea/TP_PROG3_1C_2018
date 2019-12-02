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
        $id = $args["id"];
        $encargado = Encargado::find($id);
        if ($encargado != null) {
            $encargado->delete();
            return $response->withJson($encargado, 200);
        }
        return $response->withJson('El encargado no existe', 200);
    }

    public function ModificarUno($request, $response, $args)
    {
        $body = $request->getParsedBody();
        $id = $args["id"];
        $encargado = Encargado::find($id);
        $modificaciones = 0;
        if (array_key_exists("nombre", $body) && $id != null && $encargado != null) {
            $encargado->nombre = $body["nombre"];
            $modificaciones++;
        }
        if (array_key_exists("apellido", $body) && $id != null && $encargado != null) {
            $encargado->apellido = $body["apellido"];
            $modificaciones++;
        }
        if (array_key_exists("usuario", $body) && $id != null && $encargado != null) {
            $encargado->usuario = $body["usuario"];
            $modificaciones++;
        }
        if (array_key_exists("idRol", $body) && $id != null && $encargado != null) {
            $encargado->idRol = $body["idRol"];
            $modificaciones++;
        }
        if (array_key_exists("clave", $body) && $id != null && $encargado != null) {
            $encargado->clave = $body["clave"];
            $modificaciones++;
        }

        if ($modificaciones > 0 && $modificaciones <= 5 && $id != null && $encargado != null) {
            $encargado->save();
            return $response->withJson($encargado, 200);
        } else if ($id == null) {
            return $response->withJson('Introduzca ID Valido', 400);
        } else if ($id != null && $encargado == null) {
            return $response->withJson("No se encontro encargado", 400);
        } else {
            return $response->withJson("No se ha modificado ningun campo", 400);
        }
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
