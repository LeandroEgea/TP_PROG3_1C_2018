<?php
namespace App\Models\ORM;

class pedido_producto extends \Illuminate\Database\Eloquent\Model
{
    protected $idPedido;
    protected $idProducto;
    protected $table = 'pedidos_productos';
}
