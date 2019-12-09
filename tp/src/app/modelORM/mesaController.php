<?php

namespace App\Models\ORM;

use App\Models\IApiControler;
use App\Models\ORM\Mesa;
use App\Models\ORM\Pedido;

include_once __DIR__ . '/mesa.php';
include_once __DIR__ . '/pedido.php';
include_once __DIR__ . '../../modelAPI/AutentificadorJWT.php';
include_once __DIR__ . '../../modelAPI/IApiControler.php';

class MesaController implements IApiControler
{
    public function TraerTodos($request, $response, $args)
    {
        $todasLasMesas = Mesa::all();
        if (count($todasLasMesas) > 0) {
            return $response->withJson($todasLasMesas, 200);
        }
        return $response->withJson("No hay mesas cargadas", 400);
    }

    public function ObtenerMesaLibreResponse($request, $response, $args)
    {
        $mesaLibre = Mesa::where("idEstadoMesa", "=", "4") //4 = Libre
            ->first();

        if ($mesaLibre != null) {
            return $response->withJson($mesaLibre, 200);
        }
        return $response->withJson("No hay mesas libres", 400);
    }

    public function ObtenerMesaLibre()
    {
        $mesaLibre = Mesa::where("idEstadoMesa", "=", "4") //4 = libre
            ->select("codigoMesa")
            ->first();

        if ($mesaLibre != null) {
            self::CambiarEstado($mesaLibre->codigoMesa, 1); //1 = ocupada
            return $mesaLibre->codigoMesa;
        }
        return null;
    }

    public function CambiarEstado($codigMesa, $estado)
    {
        $mesa = Mesa::where('codigoMesa', $codigMesa)
            ->first();
        if ($mesa) {
            $mesa->idEstadoMesa = $estado;
            $mesa->save();
            return true;
        } else {
            return false;
        }
    }

    public function TraerUno($request, $response, $args)
    {
        $codigo = $args['codigo'];
        $mesa = Mesa::where('codigoMesa', $codigo)
            ->first();

        if ($mesa != null) {
            return $response->withJson($mesa, 200);
        }
        return $response->withJson("codigo invalido", 400);
    }

    public function CargarUno($request, $response, $args)
    {
        $mesa = new Mesa;
        $mesa->codigoMesa = " ";
        $mesa->idEstadoMesa = 4; //4 = libre
        $mesa->save();
        if ($mesa->id >= 10) {
            $mesa->codigoMesa = "MSA" . $mesa->id;
        } else {
            $mesa->codigoMesa = "MSA0" . $mesa->id;
        }
        $mesa->save();
        return $response->withJson($mesa, 200);
    }

    public function BorrarUno($request, $response, $args)
    {
        $codigo = $args['codigo'];
        $mesa = Mesa::where('codigoMesa', $codigo)
            ->first();
        if ($mesa == null) {
            return $response->withJson('codigo invalido', 400);
        }

        $pedidos = Pedido::where('codigoMesa', $codigo)
            ->get();
        if ($pedidos != null) {
            return $response->withJson('No se pueden borrar mesas que hayan sido utilizadas en un pedido', 400);
        }
        return $response->withJson($mesa->$codigMesa, 200);
    }

    public function ModificarUno($request, $response, $args)
    {
        return $response->withJson('No hay datos a modificar en las mesas', 400);
    }
}
