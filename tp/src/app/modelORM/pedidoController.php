<?php

namespace App\Models\ORM;

use App\Models\AutentificadorJWT;
use App\Models\ORM\Pedido;
use App\Models\ORM\mesaController;
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


class pedidoController implements IApiControler
{

  public function Beinvenida($request, $response, $args)
  {
    $response->getBody()->write("GET => Bienvenido!!! ,a UTN FRA SlimFramework");

    return $response;
  }

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

  //Pancito, te dejo el codigo servidito y comentadito, para mas placer"
  //Lea, las variables son mias y les pongo el nombre que quiero
  //Ale, no se si puede hacer mas facil, si logras hacerlo avisame.
  public function CargarUno($request, $response, $args)
  {
    $token = $request->getHeader('token');
    $arrayDeParametros = $request->getParsedBody();
    $token = AutentificadorJWT::ObtenerData($token[0]);
    $tiempo = 0; //Para asignar el tiempo se va a tener en cuenta el tiempo mas alto de todos los productos
    $productoExistente = null; //Se utiliza para verificar en el for si el producto seleccionado elige realmente
    $arrayDeProductosExistentes = ""; //Aca me guardo los productos realmente existentes
    $mesaDisponible = mesaController::obtenerMesaLibre();
    if ($mesaDisponible != null) {
      mesaController::cambiarEstado($mesaDisponible, 1);
      $pedidoNuevo = new Pedido; //genero un nuevo pedido
      $pedidoNuevo->idEstadoPedido = 1; //Inicializo el estado del pedido en 1
      $pedidoNuevo->codigoMesa = $mesaDisponible; //Obtengo una mesa que este libre (ESTADO CERRADA)
      $pedidoNuevo->codigoPedido = pedidoController::generarCodigoTicket(); //Auto genero el codigo alfanumerico del pedido
      $pedidoNuevo->productos = $arrayDeParametros["productos"];
      $pedidoNuevo->idEncargado = $token->id; //Agarro el id del encargado desde el token
      $pedidoNuevo->nombreCliente = $arrayDeParametros["nombreCliente"];
      $archivos = $request->getUploadedFiles(); // FALTA MOVER FOTO A CARPETA IMAGENES
      $pedidoNuevo->imagen = $archivos["imagen"]->file;
      $pedidoNuevo->tiempo = 1; //Asigno un tiempo ficticio despues de corrige
      $pedidoNuevo->save();
      $idPedidoCargado = $pedidoNuevo->id;
      $productos = explode(",", $arrayDeParametros["productos"]);
      /*/////////////////////////---RECORRO TODOS LOS PRODUCTOS VALIDANDO SI EXISTEN O NO---///////////////////////////*/
      for ($i = 0; $i < count($productos); $i++) {
        $productoExistente = Producto::find($productos[$i]); // Busco el producto
        if ($productoExistente != null) { // Si el producto existe....
          if ($i == 0) {
            $arrayDeProductosExistentes = $arrayDeProductosExistentes . $productos[$i]; //en la primera iteracion me guardo el primer producto y
            $tiempo = $productoExistente->tiempoPreparacion;                            // su tiempo de preparacion
          } else if (empty($arrayDeProductosExistentes)) { //Esto valida si en la primera iteracion hubo un producto nulo, asique guarda como primero el segundo
            $arrayDeProductosExistentes = $arrayDeProductosExistentes . $productos[$i];
          } else {                                          //Si no es la primera vez y el anterior no es nulo, sigue iterando normalmente y agrega coma entre productos
            $arrayDeProductosExistentes = $arrayDeProductosExistentes . "," . $productos[$i];
          }
          /*---------------------------AGREGO CADA PRODUCTO EXISTENTE A LA TABLA PRODUCTO_PEDIDO----------------------------------------------------*/
          $pedido_producto = new pedido_producto;
          $pedido_producto->codigoPedido = $pedidoNuevo->codigoPedido;
          $pedido_producto->idProducto = $productos[$i];
          $pedido_producto->idEstadoProducto = 1;
          $pedido_producto->save();
          if ($tiempo < $productoExistente->tiempoPreparacion) { //Comparo el ultimo tiempo guardado contra el nuevo y si el actual es menor al nuevo, se reemplaza
            $tiempo = $productoExistente->tiempoPreparacion;
          }
          /*-------------------------------------------------------------------------------*/
        }
      }
      /*/////////////////////////---FIN DEL FOR-----///////////////////////////*/
      if (strlen($arrayDeProductosExistentes) > 0) {
        $pedidoNuevo->productos = $arrayDeProductosExistentes; //Actualizado la verdadera cantidad de productos existentes
        $pedidoNuevo->tiempo = $tiempo; //Guardo el tiempo real del pedido
        $pedidoNuevo->save();
        $newResponse = $response->withJson('Pedido ' . $pedidoNuevo->codigoPedido . "-" . $pedidoNuevo->codigoMesa . ' cargado', 200);
      } else {
        mesaController::cambiarEstado($pedidoNuevo->codigoMesa, 4);
        pedido_producto::where("idPedido", "=", $idPedidoCargado)->delete();
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
      pedido_producto::where("idPedido", "=", $id)->delete();
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
      //Borra el pedido de productos antiguo y lo reemplaza por el nuevo en 
      //la tabla producto_pedido
      pedido_producto::where("idPedido", "=", $id)->delete();
      $productos = explode(",", $arrayDeParametros["productos"]);
      for ($i = 0; $i < count($productos); $i++) {
        $pedido_producto = new pedido_producto;
        $pedido_producto->codigoPedido = $pedido->codigo;
        $pedido_producto->idProducto = $productos[$i];
        $pedido_producto->idEstadoProducto = 1;
        $pedido_producto->save();
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

  public function generarCodigoTicket()
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

  public function cambiarEstado($codigoPedido, $estado)
  {
    $pedido = Pedido::where('codigoPedido', $codigoPedido)->first();
    $pedido->idEstadoPedido = $estado;
    if ($estado == 3) { //listo para servir
      $pedido->tiempo = 0;
    }
    $pedido->save();
  }

  public function prepararPedido($request, $response, $args)
  {
    $token = $request->getHeader('token');
    $arrayDeParametros = $request->getParsedBody();
    $datos = AutentificadorJWT::ObtenerData($token[0]);
    $respuesta = pedido_productoController::cambiarEstado($arrayDeParametros["codigoPedido"], $datos->idRol, 1, 2);

    if ($respuesta) {
      pedidoController::cambiarEstado($arrayDeParametros["codigoPedido"], 2);
      $newResponse = $response->withJson("Comienza preparacion del pedido", 200);
    } else {
      $newResponse = $response->withJson("No habia pedidos o ya fueron tomados para preparar", 200);
    }
    return $newResponse;
  }

  public function terminarPedido($request, $response, $args)
  {
    $token = $request->getHeader('token');
    $arrayDeParametros = $request->getParsedBody();
    $datos = AutentificadorJWT::ObtenerData($token[0]);
    $respuesta = pedido_productoController::cambiarEstado($arrayDeParametros["codigoPedido"], $datos->idRol, 2, 3);

    if($respuesta) {
      $data = pedido_producto::where('codigoPedido', $arrayDeParametros["codigoPedido"])->get();
      $completo = true;
      foreach ($data as $value) {
        if ($value->idEstadoProducto != 3) {
          $completo = false;
        }
      }
      if ($completo) {
        pedidoController::cambiarEstado($arrayDeParametros["codigoPedido"], 3);
        pedido_productoController::cambiarEstado($arrayDeParametros["codigoPedido"], $datos->idRol, 2, 3);
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

  public function servirPedido($request, $response, $args)
  {
    $arrayDeParametros = $request->getParsedBody();
    $pedido = Pedido::where('codigoPedido', $arrayDeParametros["codigoPedido"])->first();
    if($pedido->idEstadoPedido == 3) {
      $pedido = pedido::where('codigoPedido', '=', $arrayDeParametros["codigoPedido"])->first();
      mesaController::cambiarEstado($pedido->codigoMesa, 2);

      pedidoController::cambiarEstado($arrayDeParametros["codigoPedido"], 4);

      pedido_productoController::cambiarEstado($arrayDeParametros["codigoPedido"], 3, 3, 4);

      $newResponse = $response->withJson("Pedido entregado", 200);
    } else {
      $newResponse = $response->withJson("El pedido no esta listo para ser entregado", 200);
    }
    return $newResponse;
  }
  public function pedirCuenta($request, $response, $args)
  {
    $total = 0;
    $arrayDeParametros = $request->getParams();
    $pedido = Pedido::where('codigoPedido', '=', $arrayDeParametros['codigoPedido'])->first();
    $productos = pedido_producto::join('productos', 'productos.id', 'productos_pedidos.idProducto')
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
    mesaController::cambiarEstado($pedido->codigoMesa, 3);
    return $nuevoRetorno;
  }

  public function cobrar($request, $response, $args)
  {
    $total = 0;
    $arrayDeParametros = $request->getParams();
    $pedido = Pedido::where('codigoPedido', '=', $arrayDeParametros['codigoPedido'])->first();
    if ($pedido != null && $pedido->idEstadoPedido == 4) {
      $ticket = new Ticket();
      $ticket->codigoPedido = $pedido->codigoPedido;
      $productos = pedido_producto::join('productos', 'productos.id', 'productos_pedidos.idProducto')
        ->where('codigoPedido', '=', $arrayDeParametros['codigoPedido'])->get();
      foreach ($productos as $producto) {
        $total = $total + $producto->precio;
      }
      $ticket->precioTotal = $total;
      $ticket->mesa = $pedido->codigoMesa;
      $encargado = Encargado::where('id', '=', $pedido->idEncargado)->first();
      $ticket->encargado = $encargado->usuario;
      $ticket->save();
      pedidoController::cambiarEstado($arrayDeParametros['codigoPedido'], 5); //cobrado
      mesaController::cambiarEstado($pedido->codigoMesa, 4); //cerrada
      $newResponse = $response->withJson("Pedido cobrado - Mesa Cerrada", 200);
    } else {
      $newResponse = $response->withJson("Pedido no encontrado", 200);
    }

    return $newResponse;
  }
}
