<?php

namespace App\Models\ORM;

use App\Models\AutentificadorJWT;

include_once __DIR__ . '../../modelAPI/AutentificadorJWT.php';

class Middleware
{
    public function validarToken($request, $response, $next)
    {

        $parametros = $request->getParsedBody();
        $token = "token";

        if (array_key_exists("token", $parametros) || count((array) $token) > 5) {
            try {

                $token = $parametros["token"];
                if (AutentificadorJWT::VerificarToken($token)) {
                    $newResponse = $next($request, $response);
                }
            } catch (Exception $e) {
                $newResponse = $response->withJson("Token invalido", 200);
            }
        } else {
            $newResponse = $response->withJson("No se ha recibido un token. Verificar", 200);
        }
        return $newResponse;
    }

    public function EsSocio($request, $response, $next)
    {

        $parametros = $request->getParsedBody();
        $token = $parametros["token"];

        if (count((array) $token) > 0) {
            try {

                $data = AutentificadorJWT::ObtenerData($token);
                if ($data->cargo == "socio") {
                    $newResponse = $next($request, $response);
                }
            } catch (Exception $e) {

                $newResponse = $response->withJson("Esta accion solo la puede cumplir un socio", 200);
            }
        } else {
            $newResponse = $response->withJson("No se ha recibido un token. Verificar", 200);
        }
        return $newResponse;
    }

}
