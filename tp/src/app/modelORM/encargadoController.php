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
            $newResponse = $response->withJson($todosLosEncargados, 200);
        } else {
            $newResponse = $response->withJson("No hay encargados", 200);
        }
        return $newResponse;
    }
    public function TraerUno($request, $response, $args)
    {
        $id = $args["id"];
        $encargado = Encargado::find($id);
        if ($encargado != null) {
            $newResponse = $response->withJson($encargado, 200);
        } else {
            $newResponse = $response->withJson("No existe encargado con ese ID", 200);
        }
        return $newResponse;
    }

    public function CargarUno($request, $response, $args)
    {
        $arrayDeParametros = $request->getParsedBody();
        $encargadoNuevo = new Encargado;
        $encargadoNuevo->nombre = $arrayDeParametros["nombre"];
        $encargadoNuevo->apellido = $arrayDeParametros["apellido"];
        $encargadoNuevo->usuario = strtolower(substr($arrayDeParametros["apellido"], 0, 1)) . strtolower($arrayDeParametros["apellido"]);
        $encargadoNuevo->clave = $arrayDeParametros["clave"];
        $encargadoNuevo->idRol = $arrayDeParametros["idRol"];
        $encargadoNuevo->save();
        $idEncargadoCargado = $encargadoNuevo->id;
        $newResponse = $response->withJson('Encargado ' . $encargadoNuevo->usuario . ' cargado', 200);
        return $newResponse;
    }
    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $id = $parametros['id'];
        $encargado = Encargado::find($id);
        print("Este " . $encargado);
        if ($encargado != null) {
            $encargado->delete();
            $newResponse = $response->withJson('Encargado ' . $id . ' borrado', 200);
        } else {
            $newResponse = $response->withJson('El encargado no existe', 200);
        }
        return $newResponse;
    }

    public function ModificarUno($request, $response, $args)
    {
        $arrayDeParametros = $request->getParsedBody();
        $id = null;
        $encargado = null;
        $contadorModificaciones = 0;
        if (array_key_exists("id", $arrayDeParametros)) {
            $id = $arrayDeParametros['id'];
            $encargado = Encargado::find($id);
        }
        if (array_key_exists("nombre", $arrayDeParametros) && $id != null && $encargado != null) {
            $encargado->nombre = $arrayDeParametros["nombre"];
            $encargado->usuario = strtolower(substr($arrayDeParametros["nombre"], 0, 1)) . strtolower($encargado->apellido);
            $contadorModificaciones++;
        }
        if (array_key_exists("apellido", $arrayDeParametros) && $id != null && $encargado != null) {
            $encargado->apellido = $arrayDeParametros["apellido"];
            $encargado->usuario = strtolower(substr($encargado->nombre, 0, 1)) . strtolower($arrayDeParametros["apellido"]);
            $contadorModificaciones++;
        }
        if (array_key_exists("usuario", $arrayDeParametros) && $id != null && $encargado != null) {
            $encargado->usuario = (strtolower(substr($encargado->nombre, 0, 1)) . strtolower($encargado->apellido));
            $contadorModificaciones++;
        }
        if (array_key_exists("idRol", $arrayDeParametros) && $id != null && $encargado != null) {
            $encargado->idRol = $arrayDeParametros["idRol"];
            $contadorModificaciones++;
        }
        if (array_key_exists("clave", $arrayDeParametros) && $id != null && $encargado != null) {
            $encargado->clave = $arrayDeParametros["clave"];
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
        $arrayDeParametros = $request->getParsedBody();

        $encargado = Encargado::where('usuario', '=', $arrayDeParametros["usuario"])
            ->join('roles', 'encargados.idRol', 'roles.id')
            ->select("encargados.id", "nombre", "apellido", "usuario", "clave", "idRol", "cargo")
            ->get()
            ->toArray();

        if (count($encargado) == 1 && $encargado[0]["clave"] == $arrayDeParametros["clave"]) {
            unset($encargado[0]["clave"]);

            $token = AutentificadorJWT::CrearToken($encargado[0]);
            $newResponse = $response->withJson($token, 200);
        } else {
            $newResponse = $response->withJson("Nop", 200);
        }

        return $newResponse;
    }

}
