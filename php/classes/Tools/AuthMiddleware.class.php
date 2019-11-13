<?php


namespace Attack\Tools;


use \Firebase\JWT\JWT;

use Attack\Exceptions\DatabaseException;
use Attack\Exceptions\NullPointerException;
use Attack\Model\User\ModelUser;
use Slim\Middleware;

class AuthMiddleware extends Middleware
{
    /**
     * @throws NullPointerException
     * @throws DatabaseException
     */
    public function call()
    {
        $token = static::getBearerToken();
        if (empty($token)) {
            $this->next->call();
            return;
        }
        $publicKey = file_get_contents(getcwd() . '/public.key');
        try {
            $decoded = JWT::decode($token, $publicKey, ['RS256']);
        } catch (\Exception $exception) {
            $this->next->call();
            return;
        }
        if (!empty($decoded->sub)) {
            ModelUser::setCurrentUserByName($decoded->sub);
        }
        $this->next->call();
    }

    private static function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    private static function getBearerToken()
    {
        $headers = static::getAuthorizationHeader();
        if (!empty($headers)) {
            if (preg_match('/Bearer\s((.*)\.(.*)\.(.*))/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}
