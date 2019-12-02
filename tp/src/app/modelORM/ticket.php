<?php
namespace App\Models\ORM;

class Ticket extends \Illuminate\Database\Eloquent\Model
{
    protected $id;
    protected $precioTotal;
    protected $codigoPedido;
    protected $mesa;
    protected $encargado;
}
