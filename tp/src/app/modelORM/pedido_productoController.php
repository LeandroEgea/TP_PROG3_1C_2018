<?php

namespace App\Models\ORM;

use App\Models\ORM\PedidoProducto;

include_once __DIR__ . '/pedido_producto.php';
include_once __DIR__ . '/producto.php';

class PedidoProductoController
{
    public function VerPendientes($cargo)
    {
        if ($cargo == "socio") {
            $data = PedidoProducto::join('productos', 'pedidos_productos.idProducto', 'productos.id')
                ->join('pedidos', 'pedidos_productos.idPedido', 'pedidos.id')
                ->join('roles', 'productos.idRol', 'roles.id')
                ->where('pedidos_productos.idEstadoProducto', '=', '1')
                ->select('pedidos.codigoPedido', 'pedidos.codigoMesa', 'productos.descripcion', 'roles.cargo')
                ->get();
        } else {
            $data = PedidoProducto::join('productos', 'pedidos_productos.idProducto', 'productos.id')
                ->join('pedidos', 'pedidos_productos.idPedido', 'pedidos.id')
                ->join('roles', 'productos.idRol', 'roles.id')
                ->where('pedidos_productos.idEstadoProducto', '=', '1')
                ->where('roles.cargo', '=', $cargo)
                ->select('pedidos.codigoPedido', 'pedidos.codigoMesa', 'productos.descripcion')
                ->get();
        }
        return $data;
    }

    public function CambiarEstado($codigoPedido, $idRolEncargado, $estadoInicial, $estadoActual)
    {
        $huboCambios = false;
        $pedidosProductos = PedidoProducto::join('pedidos', 'pedidos_productos.idPedido', 'pedidos.id')
            ->where('pedidos_productos.idEstadoProducto', '=', $estadoInicial)
            ->where('pedidos.codigoPedido', '=', $codigoPedido)
            ->select('pedidos_productos.id', 'pedidos_productos.idProducto')
            ->get();

        foreach ($pedidosProductos as $pedidoProducto) {
            $producto = Producto::find($pedidoProducto->idProducto);
            if ($idRolEncargado == 3 || $producto->idRol == $idRolEncargado) { // 3 = socio
                $pedProdDB = PedidoProducto::find($pedidoProducto->id);
                $pedProdDB->idEstadoProducto = $estadoActual;
                $pedProdDB->save();
                $huboCambios = true;
            }
        }
        return true;
    }
}
