<?php

namespace App\Models\ORM;

use App\Models\AutentificadorJWT;
use App\Models\ORM\Pedido;
use App\Models\ORM\MesaController;
use App\Models\IApiControler;
use App\Models\ORM\Ticket;
use \stdClass;

include_once __DIR__ . '/pedido.php';
include_once __DIR__ . '/producto.php';
include_once __DIR__ . '/ticket.php';
include_once __DIR__ . '/mesaController.php';
include_once __DIR__ . '/pedido_producto.php';
include_once __DIR__ . '../../modelAPI/IApiControler.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


class PedidoController implements IApiControler
{
  public function TraerTodos($request, $response, $args)
  {
    $todosLasPedidos = Pedido::all();
    if (count($todosLasPedidos) > 0) {
      $newResponse = $response->withJson($todosLasPedidos, 200);
    } else {
      $newResponse = $response->withJson("No hay pedidos", 200);
    }
    return $newResponse;
  }
  public function TraerUno($request, $response, $args)
  {
    $arrayDeParametros = $request->getParams();
    $codigoMesa = $arrayDeParametros['codigoMesa'];
    $codigoPedido = $arrayDeParametros['codigoPedido'];
    try {
      $pedido = Pedido::join('mesas', 'pedidos.codigoMesa', 'mesas.codigoMesa')
        ->where('codigoPedido', $codigoPedido)
        ->get();
      $estado = Pedido::join('estados_pedidos', 'pedidos.idEstadoPedido', 'estados_pedidos.id')
        ->where('codigoPedido', $codigoPedido)
        ->select("descripcion")
        ->get();


      if ($pedido[0]->codigoMesa == $codigoMesa && $codigoPedido == $pedido[0]->codigoPedido) {
        $retorno = new stdClass;
        $retorno->codigoPedido = $pedido[0]->codigoPedido;
        $retorno->codigoMesa = $pedido[0]->codigoMesa;
        $retorno->estado = $estado[0]->descripcion;
        $retorno->tiempo = $pedido[0]->tiempo;
        $nuevoResp = $response->withJson($retorno, 200);
      } else {
        $nuevoResp = $response->withJson("La combinacion codigo - mesa es incorrecto");
      }
    } catch (Exception $e) {
      $nuevoResp = $response->withJson("Error al leer los parametros");
    }

    return $nuevoResp;
  }

  public function CargarUno($request, $response, $args)
  {
    $token = $request->getHeader('token');
    $arrayDeParametros = $request->getParsedBody();
    $token = AutentificadorJWT::ObtenerData($token[0]);
    $tiempo = 0; 
    $productoExistente = null; 
    $arrayDeProductosExistentes = "";
    $mesaDisponible = MesaController::ObtenerMesaLibre();
    if ($mesaDisponible != null) {
      MesaController::CambiarEstado($mesaDisponible, 1);
      $pedidoNuevo = new Pedido;
      $pedidoNuevo->idEstadoPedido = 1;
      $pedidoNuevo->codigoMesa = $mesaDisponible;
      $pedidoNuevo->codigoPedido = PedidoController::GenerarCodigoTicket();
      $pedidoNuevo->productos = $arrayDeParametros["productos"];
      $pedidoNuevo->idEncargado = $token->id;
      $pedidoNuevo->nombreCliente = $arrayDeParametros["nombreCliente"];
      $archivos = $request->getUploadedFiles();
      $pedidoNuevo->imagen = $archivos["imagen"]->file;
      $pedidoNuevo->tiempo = 1;
      $pedidoNuevo->save();
      $idPedidoCargado = $pedidoNuevo->id;
      $productos = explode(",", $arrayDeParametros["productos"]);

      for ($i = 0; $i < count($productos); $i++) {
        $productoExistente = Producto::find($productos[$i]);
        if ($productoExistente != null) {
          if ($i == 0) {
            $arrayDeProductosExistentes = $arrayDeProductosExistentes . $productos[$i];
            $tiempo = $productoExistente->tiempoPreparacion;
          } else if (empty($arrayDeProductosExistentes)) {
            $arrayDeProductosExistentes = $arrayDeProductosExistentes . $productos[$i];
          } else {
            $arrayDeProductosExistentes = $arrayDeProductosExistentes . "," . $productos[$i];
          }

          $pedidoProducto = new PedidoProducto;
          $pedidoProducto->codigoPedido = $pedidoNuevo->codigoPedido;
          $pedidoProducto->idProducto = $productos[$i];
          $pedidoProducto->idEstadoProducto = 1;
          $pedidoProducto->save();
          if ($tiempo < $productoExistente->tiempoPreparacion) {
            $tiempo = $productoExistente->tiempoPreparacion;
          }
        }
      }

      if (strlen($arrayDeProductosExistentes) > 0) {
        $pedidoNuevo->productos = $arrayDeProductosExistentes;
        $pedidoNuevo->tiempo = $tiempo;
        $pedidoNuevo->save();
        $newResponse = $response->withJson('Pedido ' . $pedidoNuevo->codigoPedido . "-" . $pedidoNuevo->codigoMesa . ' cargado', 200);
      } else {
        MesaController::CambiarEstado($pedidoNuevo->codigoMesa, 4);
        PedidoProducto::where("idPedido", "=", $idPedidoCargado)->delete();
        $pedidoNuevo->delete();
        $newResponse = $response->withJson('No se puede cargar un pedido sin productos. Pedido eliminado', 200);
      }
    } else {
      $newResponse = $response->withJson('No hay mesas disponibles', 200);
    }
    return $newResponse;
  }
  public function BorrarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    $id = $parametros['id'];
    $pedido = Pedido::find($id);
    if ($pedido != null) {
      $pedido->delete();
      PedidoProducto::where("idPedido", "=", $id)->delete();
      $newResponse = $response->withJson('Pedido ' . $id . ' borrado', 200);
    } else {
      $newResponse = $response->withJson('El pedido no existe', 200);
    }
    return $newResponse;
  }

  public function ModificarUno($request, $response, $args)
  {
    $arrayDeParametros = $request->getParsedBody();
    $id = null;
    $pedido = null;
    $contadorModificaciones = 0;
    $archivos = [];
    if (array_key_exists("id", $arrayDeParametros)) {
      $id = $arrayDeParametros['id'];
      $pedido = Pedido::find($id);
      $archivos = $request->getUploadedFiles();
    }
    if (array_key_exists("codigoMesa", $arrayDeParametros) && $id != null && $pedido != null) {
      $pedido->codigoMesa = $arrayDeParametros["codigoMesa"];
      $contadorModificaciones++;
    }
    if (array_key_exists("productos", $arrayDeParametros) && $id != null && $pedido != null) {
      $pedido->Productos = $arrayDeParametros["productos"];
      $contadorModificaciones++;

      PedidoProducto::where("idPedido", "=", $id)->delete();
      $productos = explode(",", $arrayDeParametros["productos"]);
      for ($i = 0; $i < count($productos); $i++) {
        $pedidoProducto = new PedidoProducto;
        $pedidoProducto->codigoPedido = $pedido->codigo;
        $pedidoProducto->idProducto = $productos[$i];
        $pedidoProducto->idEstadoProducto = 1;
        $pedidoProducto->save();
      }
    }

    if (array_key_exists("idEncargado", $arrayDeParametros) && $id != null && $pedido != null) {
      $pedido->idEncargado = $arrayDeParametros["idEncargado"];
      $contadorModificaciones++;
    }

    if (array_key_exists("nombreCliente", $arrayDeParametros) && $id != null && $pedido != null) {
      $pedido->nombreCliente = $arrayDeParametros["nombreCliente"];
      $contadorModificaciones++;
    }

    if (array_key_exists("imagen", $archivos) && $id != null && $pedido != null && $archivos != null) {
      $pedido->imagen = $archivos["imagen"]->file;
      $contadorModificaciones++;
    }

    if (array_key_exists("tiempo", $arrayDeParametros) && $id != null && $pedido != null) {
      $pedido->tiempo  = $arrayDeParametros["tiempo"];
      $contadorModificaciones++;
    }
    if ($contadorModificaciones > 0 && $contadorModificaciones <= 4 && $id != null && $pedido != null) {
      $pedido->idEstadoPedido = 1;
      $pedido->save();
      $newResponse = $response->withJson('Pedido ' . $id . ' modificado', 200);
    } else if ($id == null) {
      $newResponse = $response->withJson('No se introducido un id valido', 200);
    } else if ($id != null && $pedido == null) {
      $newResponse = $response->withJson("No hay un pedido con ese ID", 200);
    } else {
      $newResponse = $response->withJson("No se ha modificado ningun campo ", 200);
    }
    return $newResponse;
  }

  public function GenerarCodigoTicket()
  {
    $length = 5;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }

  public function CambiarEstado($codigoPedido, $estado)
  {
    $pedido = Pedido::where('codigoPedido', $codigoPedido)->first();
    $pedido->idEstadoPedido = $estado;
    if ($estado == 3) { //listo para servir
      $pedido->tiempo = 0;
    }
    $pedido->save();
  }

  public function PrepararPedido($request, $response, $args)
  {
    $token = $request->getHeader('token');
    $arrayDeParametros = $request->getParsedBody();
    $datos = AutentificadorJWT::ObtenerData($token[0]);
    $respuesta = PedidoProductoController::CambiarEstado($arrayDeParametros["codigoPedido"], $datos->idRol, 1, 2);

    if ($respuesta) {
      PedidoController::CambiarEstado($arrayDeParametros["codigoPedido"], 2);
      $newResponse = $response->withJson("Comienza preparacion del pedido", 200);
    } else {
      $newResponse = $response->withJson("No habia pedidos o ya fueron tomados para preparar", 200);
    }
    return $newResponse;
  }

  public function TerminarPedido($request, $response, $args)
  {
    $token = $request->getHeader('token');
    $arrayDeParametros = $request->getParsedBody();
    $datos = AutentificadorJWT::ObtenerData($token[0]);
    $respuesta = PedidoProductoController::CambiarEstado($arrayDeParametros["codigoPedido"], $datos->idRol, 2, 3);

    if($respuesta) {
      $data = PedidoProducto::where('codigoPedido', $arrayDeParametros["codigoPedido"])->get();
      $completo = true;
      foreach ($data as $value) {
        if ($value->idEstadoProducto != 3) {
          $completo = false;
        }
      }
      if ($completo) {
        PedidoController::CambiarEstado($arrayDeParametros["codigoPedido"], 3);
        PedidoProductoController::CambiarEstado($arrayDeParametros["codigoPedido"], $datos->idRol, 2, 3);
        $newResponse = $response->withJson("Se preparon todos los productos. Pedido listo para servir", 200);
      } 
      else {
        $newResponse = $response->withJson("Se finalizó la preparacion de los productos", 200);
      }
    } else {
      $newResponse = $response->withJson("No hay productos pendiente para este pedido", 200);
    }
    return $newResponse;
  }

  public function ServirPedido($request, $response, $args)
  {
    $arrayDeParametros = $request->getParsedBody();
    $pedido = Pedido::where('codigoPedido', $arrayDeParametros["codigoPedido"])->first();
    if($pedido->idEstadoPedido == 3) {
      $pedido = pedido::where('codigoPedido', '=', $arrayDeParametros["codigoPedido"])->first();
      MesaController::CambiarEstado($pedido->codigoMesa, 2);

      PedidoController::CambiarEstado($arrayDeParametros["codigoPedido"], 4);

      PedidoProductoController::CambiarEstado($arrayDeParametros["codigoPedido"], 3, 3, 4);

      $newResponse = $response->withJson("Pedido entregado", 200);
    } else {
      $newResponse = $response->withJson("El pedido no esta listo para ser entregado", 200);
    }
    return $newResponse;
  }
  public function PedirCuenta($request, $response, $args)
  {
    $total = 0;
    $arrayDeParametros = $request->getParams();
    $pedido = Pedido::where('codigoPedido', '=', $arrayDeParametros['codigoPedido'])->first();
    $productos = PedidoProducto::join('productos', 'productos.id', 'productos_pedidos.idProducto')
      ->where('codigoPedido', '=', $arrayDeParametros['codigoPedido'])->get();
    $ticket = [];
    foreach ($productos as $producto) {
      $prod = new \stdClass;
      $prod->producto = $producto->descripcion;
      $prod->precio = $producto->precio;
      $total = $total + $producto->precio;
      array_push($ticket, $prod);
    }
    $cuenta = new \stdClass;
    $cuenta->nombreCliente = $pedido->nombreCliente;
    $cuenta->ticket = $pedido->codigoPedido;
    $cuenta->pedido = $ticket;
    $cuenta->total = $this->total;
    $nuevoRetorno = $response->withJson($cuenta, 200);
    MesaController::CambiarEstado($pedido->codigoMesa, 3);
    return $nuevoRetorno;
  }

  public function Cobrar($request, $response, $args)
  {
    $total = 0;
    $arrayDeParametros = $request->getParams();
    $pedido = Pedido::where('codigoPedido', '=', $arrayDeParametros['codigoPedido'])->first();
    if ($pedido != null && $pedido->idEstadoPedido == 4) {
      $ticket = new Ticket();
      $ticket->codigoPedido = $pedido->codigoPedido;
      $productos = PedidoProducto::join('productos', 'productos.id', 'productos_pedidos.idProducto')
        ->where('codigoPedido', '=', $arrayDeParametros['codigoPedido'])->get();
      foreach ($productos as $producto) {
        $total = $total + $producto->precio;
      }
      $ticket->precioTotal = $total;
      $ticket->mesa = $pedido->codigoMesa;
      $encargado = Encargado::where('id', '=', $pedido->idEncargado)->first();
      $ticket->encargado = $encargado->usuario;
      $ticket->save();
      PedidoController::CambiarEstado($arrayDeParametros['codigoPedido'], 5); //cobrado
      MesaController::CambiarEstado($pedido->codigoMesa, 4); //cerrada
      $newResponse = $response->withJson("Pedido cobrado - Mesa Cerrada", 200);
    } else {
      $newResponse = $response->withJson("Pedido no encontrado", 200);
    }
    return $newResponse;
  }
}
