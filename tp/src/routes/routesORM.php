<?php

use App\Models\ORM\encargadoController;
use App\Models\ORM\mesaController;
use App\Models\ORM\pedidoController;
use App\Models\ORM\productoController;
use Slim\App;

include_once __DIR__ . '/../../src/app/modelORM/pedidoController.php';
include_once __DIR__ . '/../../src/app/modelORM/encargadoController.php';
include_once __DIR__ . '/../../src/app/modelORM/mesaController.php';
include_once __DIR__ . '/../../src/app/modelORM/productoController.php';

return function (App $app) {
    $container = $app->getContainer();

    $app->group('/mesas', function () {
        $this->post('/', mesaController::class . ':CargarUno');
        $this->post('/baja', mesaController::class . ':BorrarUno');
        $this->post('/modificar', mesaController::class . ':ModificarUno');
        $this->get('/', mesaController::class . ':TraerTodos');
        $this->get('/obtenerMesaLibre', mesaController::class . ':obtenerMesaLibre');
        $this->get('/{id}', mesaController::class . ':TraerUno');
    });

    $app->group('/encargados', function () {
        $this->post('/', encargadoController::class . ':CargarUno')->add(Middleware::class . ":EsSocio")
            ->add(Middleware::class . ":validarToken");
        $this->post('/logIn', encargadoController::class . ':IniciarSesion');
        $this->post('/baja', encargadoController::class . ':BorrarUno');
        $this->post('/modificar', encargadoController::class . ':ModificarUno');
        $this->get('/', encargadoController::class . ':TraerTodos');
        $this->get('/{id}', encargadoController::class . ':TraerUno');
    });

    $app->group('/productos', function () {
        $this->get('/verPendientes', productoController::class . ':verPendientes')->add(Middleware::class . ":validarToken");
        $this->post('/', productoController::class . ':CargarUno');
        $this->post('/baja', productoController::class . ':BorrarUno');
        $this->post('/modificar', productoController::class . ':ModificarUno');
        $this->get('/', productoController::class . ':TraerTodos');
        $this->get('/{id}', productoController::class . ':TraerUno');
    });

    $app->group('/pedidos', function () {
        $this->post('/', pedidoController::class . ':CargarUno')->add(Middleware::class . ":EsMozo")
            ->add(Middleware::class . ":validarToken");
        $this->post('/baja', pedidoController::class . ':BorrarUno');
        $this->post('/modificar', pedidoController::class . ':ModificarUno');
        $this->get('/', pedidoController::class . ':TraerTodos');
        $this->get('/traerUno', pedidoController::class . ':TraerUno');
        $this->post('/prepararPedido', pedidoController::class . ':prepararPedido')->add(Middleware::class . ":validarToken");
        $this->post('/terminarPedido', pedidoController::class . ':terminarPedido')->add(Middleware::class . ":validarToken");
        $this->post('/servirPedido', pedidoController::class . ':servirPedido')->add(Middleware::class . ":EsMozo")
            ->add(Middleware::class . ":validarToken");;
        $this->get('/pedirCuenta', pedidoController::class . ':pedirCuenta')->add(Middleware::class . ":EsMozo")
            ->add(Middleware::class . ":validarToken");;
        $this->get('/cobrar', pedidoController::class . ':cobrar')->add(Middleware::class . ":EsMozo")
            ->add(Middleware::class . ":validarToken");;
    });
};
