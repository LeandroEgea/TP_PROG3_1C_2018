<?php

namespace App\Models\ORM;

use App\Models\ORM\PedidoProducto;
use App\Models\ORM\Producto;
use App\Models\ORM\Roles;

include_once __DIR__ . '/pedido_producto.php';
include_once __DIR__ . '/producto.php';
include_once __DIR__ . '/roles.php';

class PedidoProductoController
{
    public function VerPendientes($codigo, $encargado)
    {
        $rol = Roles::select('cargo')->where('roles.id', '=', $encargado)->get()->toArray();
        $rol = $rol[0]["cargo"];

        if ($rol == "socio") {
            $data = PedidoProducto::join('productos', 'productos_pedidos.idProducto', 'productos.id')
                ->join('roles', 'roles.id', 'productos.idRol')
                ->where('productos_pedidos.idEstadoProducto', '=', '1')
                ->where('codigoPedido', '=', $codigo)
                ->select('codigoPedido', 'productos.descripcion', 'cargo')
                ->get();
        } else {
            $data = PedidoProducto::join('productos', 'productos_pedidos.idProducto', 'productos.id')
                ->join('roles', 'roles.id', 'productos.idRol')
                ->where('productos_pedidos.idEstadoProducto', '=', '1')
                ->where('productos.idRol', '=', $encargado)
                ->where('codigoPedido', '=', $codigo)
                ->select('codigoPedido', 'productos.descripcion', 'cargo')
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
