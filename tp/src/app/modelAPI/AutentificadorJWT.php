<?php
namespace App\Models;

use Firebase\JWT\JWT;

class AutentificadorJWT
{
    private static $claveSecreta = 'ClaveSuperSecreta@';
    private static $tipoEncriptacion = ['HS256'];
    private static $aud = null;

    public static function CrearToken($datos)
    {
        $ahora = time();
        /*
        parametros del payload
        https://tools.ietf.org/html/rfc7519#section-4.1
        + los que quieras ej="'app'=> "API REST CD 2019"
         */
        $payload = array(
            'iat' => $ahora,
            'exp' => $ahora + (12000),
            'aud' => self::Aud(),
            'data' => $datos,
            'app' => "API REST CD UTN FRA",
        );
        return JWT::encode($payload, self::$claveSecreta);
    }

    public static function VerificarToken($token)
    {
        if (empty($token)) {
            throw new Exception("El token esta vacio.");
        }
        // las siguientes lineas lanzan una excepcion, de no ser correcto o de haberse terminado el tiempo

        try {
            $decodificado = JWT::decode(
                $token,
                self::$claveSecreta,
                self::$tipoEncriptacion
            );
        } catch (Exception $e) {
            throw $e;
        }

        return true;
    }

    public static function ObtenerPayLoad($token)
    {
        return JWT::decode(
            $token,
            self::$claveSecreta,
            self::$tipoEncriptacion
        );
    }
    public static function ObtenerData($token)
    {
        return JWT::decode(
            $token,
            self::$claveSecreta,
            self::$tipoEncriptacion
        )->data;
    }
    private static function Aud()
    {
        $aud = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $aud = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $aud = $_SERVER['REMOTE_ADDR'];
        }

        $aud .= @$_SERVER['HTTP_USER_AGENT'];
        $aud .= gethostname();

        return sha1($aud);
    }
}
