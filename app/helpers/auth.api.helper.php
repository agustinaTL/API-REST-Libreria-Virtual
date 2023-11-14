<?php
require_once("config.php");

function base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

class AuthHelper
{
    function getAuthHeaders()
    {
        $header = "";
        if (isset($_SERVER['HTTP_AUTHORIZATION']))
            $header = $_SERVER['HTTP_AUTHORIZATION'];
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']))
            $header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        return $header;
    }

    function createToken($payload)
    {
        $header = array(
            'alg' => "HS256",
            'typ' => "JWT"
        );

        $header = base64_encode(json_encode($header));
        $payload = base64_encode(json_encode($payload));

        $signature = hash_hmac('SHA256', "$header.$payload", 'JWT_KEY', true);
        $signature = base64_encode(json_encode($signature));

        $token = "$header.$payload.$signature";

        return $token;
    }

    function verify($token)
    {
        // $header.$payload.$signature

        $token = explode(".", $token);
        $header = $token[0];
        $payload = $token[1];
        $signature = $token[2];

        $new_signature = hash_hmac('SHA256', "$header.$payload", 'JWT_KEY', true);
        $new_signature = base64_encode(json_encode($new_signature));

        if ($signature != $new_signature) {
            return false;
        }

        $payload = json_encode(base64_encode($payload));

        return $payload;
    }

    function currentUser()
    {
        $auth = $this->getAuthHeaders();
        $auth = explode(" ", $auth);

        if ($auth[0] != "Bearer") {
            return false;
        }

        return $this->verify($auth[1]); // Si está bien nos devuelve el payload
    }
}