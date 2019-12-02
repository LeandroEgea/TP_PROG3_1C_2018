<?php

namespace App\Models\ORM;

use App\Models\AutentificadorJWT;
use App\Models\IApiControler;
use App\Models\ORM\Mesa;

include_once __DIR__ . '/mesa.php';
include_once __DIR__ . '../../modelAPI/AutentificadorJWT.php';
include_once __DIR__ . '../../modelAPI/IApiControler.php';

class mesaController implements IApiControler
{
    public function TraerTodos($request, $response, $args)
    {
        $todasLasMesas = Encargado::all();
        if (count($todasLasMesas) > 0) {
            $newResponse = $response->withJson($todasLasMesas, 200);
        } else {
            $newResponse = $response->withJson("No hay mesas", 200);
        }
        return $newResponse;
    }
    public function obtenerMesaLibre()
    {
        $mesaLibre = Mesa::where("idEstadoMesa", "=", "4")
            ->select("codigoMesa")
            ->first();

        if ($mesaLibre != null) {
            $newResponse = $mesaLibre->codigoMesa;
            self::cambiarEstado($mesaLibre->codigoMesa, 1);
        } else {
            $newResponse = null;
        }
        return $newResponse;
    }
    public function TraerUno($request, $response, $args)
    {
        $id = $args["id"];
        $mesa = Mesa::find($id);
        if (count((array) $mesa) > 0) {
            $newResponse = $response->withJson($mesa, 200);
        } else {
            $newResponse = $response->withJson("No hay mesa con ese ID", 200);
        }
        return $newResponse;
    }

    public function CargarUno($request, $response, $args)
    {
        $mesa = new Mesa;
        $mesa->codigoMesa = " ";
        $mesa->idEstadoMesa = 4;
        $mesa->save();
        $mesa->codigoMesa = "MESA-" . $mesa->id;
        $mesa->save();
        //$idEncargadoCargado = $encargadoNuevo->id;
        $newResponse = $response->withJson('Mesa ' . $mesa->codigoMesa . ' cargada', 200);
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

    public function IniciarSesion($request, $response, $args)
    {
        $arrayDeParametros = $request->getParsedBody();

        $encargado = Encargado::where('usuario', '=', $arrayDeParametros["usuario"])
            ->join('roles', 'encargados.idRol', 'roles.id')
            ->get()
            ->toArray();

        unset($encargado[0]["created_at"], $encargado[0]["updated_at"]);

        if (count($encargado) == 1 && $encargado[0]["clave"] == $arrayDeParametros["clave"]) {
            unset($encargado[0]["clave"], $encargado[0]["idRol"]);

            $token = AutentificadorJWT::CrearToken($encargado[0]);
            $newResponse = $response->withJson($token, 200);

        } else {
            $newResponse = $response->withJson("Nop", 200);
        }

        return $newResponse;
    }

    public function cambiarEstado($codigMesa, $estado)
    {
        $mesa = Mesa::where('codigoMesa', $codigMesa)->first();
        if ($mesa) {
            $mesa->idEstadoMesa = $estado;
            $mesa->save();
            return true;
        } else {
            return false;
        }
    }

}
