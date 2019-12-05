<?php

namespace App\Models\ORM;

use App\Models\ORM\PedidoProducto;
use App\Models\ORM\Producto;

include_once __DIR__ . '/pedido_producto.php';
include_once __DIR__ . '/producto.php';
include_once __DIR__ . '/roles.php';

class PedidoProductoController
{
    public function VerPendientes($cargo)
    {
        if ($cargo == "socio") {
            $data = PedidoProducto::join('productos', 'productos_pedidos.idProducto', 'productos.id')
                ->join('pedidos', 'productos_pedidos.idPedido', 'pedidos.id')
                ->join('roles', 'productos.idRol', 'roles.id')
                ->where('productos_pedidos.idEstadoProducto', '=', '1')
                ->select('pedidos.codigoPedido', 'pedidos.codigoMesa', 'productos.descripcion', 'roles.cargo')
                ->get();
        } else {
            $data = PedidoProducto::join('productos', 'productos_pedidos.idProducto', 'productos.id')
                ->join('pedidos', 'productos_pedidos.idPedido', 'pedidos.id')
                ->join('roles', 'productos.idRol', 'roles.id')
                ->where('productos_pedidos.idEstadoProducto', '=', '1')
                ->where('roles.cargo', '=', $cargo)
                ->select('codigoPedido', 'codigoMesa', 'productos.descripcion')
                ->get();
        }
        return $data;
    }

    public function CambiarEstado($codigo, $encargadoID, $estadoInicial, $estadoactual)
    {
        $ret = false;
        $data = PedidoProducto::where('idEstadoProducto', '=', $estadoInicial)
            ->where('codigoPedido', '=', $codigo)
            ->get();

        foreach ($data as $value) {
            $prod = Producto::where('id', '=', $value->idProducto)->first();

            if ($encargadoID == 3) {
                $value->idEstadoProducto = $estadoactual;
                $value->save();
                $ret = true;
            } else if ($prod->idRol == $encargadoID) {
                $value->idEstadoProducto = $estadoactual;
                $value->save();
                $ret = true;
            }
        }
        return $ret;
    }
}
