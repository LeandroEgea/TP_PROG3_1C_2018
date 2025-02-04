<?php

use App\Models\ORM\EncargadoController;
use App\Models\ORM\MesaController;
use App\Models\ORM\PedidoController;
use App\Models\ORM\ProductoController;
use Slim\App;

include_once __DIR__ . '/../../src/app/modelORM/pedidoController.php';
include_once __DIR__ . '/../../src/app/modelORM/encargadoController.php';
include_once __DIR__ . '/../../src/app/modelORM/mesaController.php';
include_once __DIR__ . '/../../src/app/modelORM/productoController.php';

return function (App $app) {
    $container = $app->getContainer();

    $app->group('/mesas', function () {
        $this->get('[/]', MesaController::class . ':TraerTodos');
        $this->get('/get/{codigo}[/]', MesaController::class . ':TraerUno');
        $this->post('[/]', MesaController::class . ':CargarUno')
            ->add(Middleware::class . ":ValidarToken")
            ->add(Middleware::class . ":EsSocio");
        $this->put('/{codigo}[/]', MesaController::class . ':ModificarUno')
            ->add(Middleware::class . ":ValidarToken")
            ->add(Middleware::class . ":EsSocio");
        $this->delete('/{codigo}[/]', MesaController::class . ':BorrarUno')
            ->add(Middleware::class . ":ValidarToken")
            ->add(Middleware::class . ":EsSocio");
        $this->get('/libre[/]', MesaController::class . ':ObtenerMesaLibreResponse');
    });

    $app->group('/encargados', function () {
        $this->get('[/]', EncargadoController::class . ':TraerTodos')
            ->add(Middleware::class . ":ValidarToken");
        $this->get('/get/{id}[/]', EncargadoController::class . ':TraerUno')
            ->add(Middleware::class . ":ValidarToken");
        $this->post('[/]', EncargadoController::class . ':CargarUno')
            ->add(Middleware::class . ":ValidarToken")
            ->add(Middleware::class . ":EsSocio");
        $this->post('/put/{id}[/]', EncargadoController::class . ':ModificarUno')
            ->add(Middleware::class . ":ValidarToken")
            ->add(Middleware::class . ":EsSocio");
        $this->delete('/{id}[/]', EncargadoController::class . ':BorrarUno')
            ->add(Middleware::class . ":ValidarToken")
            ->add(Middleware::class . ":EsSocio");
        $this->post('/login[/]', EncargadoController::class . ':IniciarSesion');
    });

    $app->group('/productos', function () {
        $this->get('[/]', ProductoController::class . ':TraerTodos');
        $this->get('/get/{id}[/]', ProductoController::class . ':TraerUno');
        $this->post('[/]', ProductoController::class . ':CargarUno')
            ->add(Middleware::class . ":ValidarToken");
        $this->post('/put/{id}[/]', ProductoController::class . ':ModificarUno')
            ->add(Middleware::class . ":ValidarToken");
        $this->delete('/{id}[/]', ProductoController::class . ':BorrarUno')
            ->add(Middleware::class . ":ValidarToken");
        $this->get('/pendientes[/]', ProductoController::class . ':VerPendientes')
            ->add(Middleware::class . ":ValidarToken");
    });

    //En EsMozo, los socios tambien pueden realizar esas acciones
    $app->group('/pedidos', function () {
        //ABM
        $this->get('[/]', PedidoController::class . ':TraerTodos');
        $this->get('/get/{codigo}[/]', PedidoController::class . ':TraerUno');
        $this->post('[/]', PedidoController::class . ':CargarUno')
            ->add(Middleware::class . ":ValidarToken")
            ->add(Middleware::class . ":EsMozo");
        $this->post('/put/{codigo}[/]', PedidoController::class . ':ModificarUno')
            ->add(Middleware::class . ":ValidarToken")
            ->add(Middleware::class . ":EsMozo");
        $this->delete('/{codigo}[/]', PedidoController::class . ':BorrarUno')
            ->add(Middleware::class . ":ValidarToken")
            ->add(Middleware::class . ":EsMozo");
        //Negocio
        $this->put('/preparar/{codigo}[/]', PedidoController::class . ':PrepararPedido')
            ->add(Middleware::class . ":ValidarToken");
        $this->put('/terminar/{codigo}[/]', PedidoController::class . ':TerminarPedido')
            ->add(Middleware::class . ":ValidarToken");
        $this->put('/servir/{codigo}[/]', PedidoController::class . ':ServirPedido')
            ->add(Middleware::class . ":ValidarToken")
            ->add(Middleware::class . ":EsMozo");
        $this->put('/cuenta/{codigo}[/]', PedidoController::class . ':PedirCuenta')
            ->add(Middleware::class . ":ValidarToken")
            ->add(Middleware::class . ":EsMozo");
        $this->put('/cobrar/{codigo}[/]', PedidoController::class . ':Cobrar')
            ->add(Middleware::class . ":ValidarToken")
            ->add(Middleware::class . ":EsMozo");
    });
};
