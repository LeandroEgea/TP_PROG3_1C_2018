<?php
namespace App\Models\ORM;

class PedidoProducto extends \Illuminate\Database\Eloquent\Model
{
    protected $idPedido;
    protected $idProducto;
    protected $table = 'pedidos_productos';
}
