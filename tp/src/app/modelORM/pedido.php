<?php
namespace App\Models\ORM;

class Pedido extends \Illuminate\Database\Eloquent\Model
{
    protected $idEstadoPedido;
    protected $codigoMesa;
    protected $productos;
    protected $idEncargado;
    protected $nombreCliente;
    protected $imagen;
    protected $tiempo;
}
