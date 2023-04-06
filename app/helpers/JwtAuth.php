<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\models\User;
use Illuminate\Http\Request;

class JwtAuth {
    private $key;

    public function __construct() {
        $this->key = 'mi_clave_secreta';
    }

    public function signup($email, $password) {
        $user = User::where('email', $email)->first();

        if (!$user) {
            // El usuario no existe
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        if (!Hash::check($password, $user->password)) {
            // La contraseña no es válida
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        $payload = array(
            'sub' => $user->id,
            'email' => $user->email,
            'iat' => time(),
            'exp' => time() + (7 * 24 * 60 * 60) // El token expira en 7 días
        );

        $token = JWT::encode($payload, $this->key);

        return response()->json(compact('token'));
    }

    public function checkToken($Jwt, $getIdentity = false) {
        $auth = false;

        try {
            $decoded = JWT::decode($Jwt, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        }

        if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }

        if ($getIdentity) {
            return $decoded;
        }

        return $auth;
    }

    

        
    }


   
    
    

