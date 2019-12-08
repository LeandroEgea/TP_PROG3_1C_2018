<?php
namespace App\Models\ORM;

class Pedido extends \Illuminate\Database\Eloquent\Model
{
    protected $idEstadoPedido;
    protected $codigoPedido;
    protected $codigoMesa;
    protected $idEncargado;
    protected $nombreCliente;
    protected $imagen;
    protected $tiempo;
}
