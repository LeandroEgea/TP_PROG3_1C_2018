<?php
namespace App\Models\ORM;

class Producto extends \Illuminate\Database\Eloquent\Model
{
    protected $id;
    protected $descripcion;
    protected $precio;
    protected $idRol;
    protected $tiempoPreparacion;
    protected $primaryKey = 'id';
}
