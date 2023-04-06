<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class userController extends Controller {

    public function pruebas(request $request) {
        return 'accion de pruebas de USER-CONTROLLER';
    }

    public function register(Request $request) {
//recoger datos de usuario

        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        //agregar esta linea para asegurarse que $params_array sea un array
        $params_array = is_array($params_array) ? $params_array : [];

        //limpiar datos
        $params_array = array_map('trim', $params_array);

//validacion de datos
        $validator = Validator::make($params_array, [
                    'name' => 'required',
                    'surname' => 'required|alpha',
                    'email' => 'required|email|unique:users',
                    'password' => 'required',
        ]);

        if ($validator->fails()) {
            $data = [
                'status' => 'error',
                'code' => 422,
                'message' => $validator->errors()
            ];
        } else {
            // validacion correcta
            //cifrar contraseña
            $pwd = password_hash($request->input('password'), PASSWORD_BCRYPT, ['cost' => 4]);

            //crear nuevo usuario

            $user = new User();
            $user->name = $params_array['name'];
            $user->surname = $params_array['surname'];
            $user->email = $params_array['email'];
            $user->password = $pwd;
            $user->role = 'ROLE_USER';

            //guardar el usuario

            $user->save();

            $data = [
                'status' => 'success',
                'code' => 200,
                'message' => 'User created successfully'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function login(Request $request) {
        $jwtAuth = new JwtAuth();

        //recibir los datos por post
        //validacion de datos
        $validator = Validator::make($request->all(), [
                    'email' => 'required|email',
                    'password' => 'required',
        ]);

        if ($validator->fails()) {
            $signup = [
                'status' => 'error',
                'code' => 422,
                'message' => 'El usuario no se ha podido logear',
                'errors' => $validator->errors()
            ];
        } else {
            // validacion correcta

            $signup = $jwtAuth->signup($request->input('email', 'password'), !empty($request->input('gettoken')));
        }

        return response()->json($signup, 200);
    }

    public function update(Request $request) {

        //verificar si el usuario esat identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if ($checkToken) {
            //recoger los datos por post

            $json = $request->input('json', null);
            $params_array = json_decode($json, true);

            //agregar esta linea para asegurarse que $params_array sea un array
            $params_array = is_array($params_array) ? $params_array : [];

            //sacar usuario identificado

            $user = $jwtAuth->checkToken($token, true);

            //validar datos

            $validator = Validator::make($params_array, [
                        'name' => 'required|alpha',
                        'surname' => 'required|alpha',
                        'email' => 'required|email|unique:users,' . $user->sub
            ]);
            //quitarlos campos que no quiero actualizar 

            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            //actualizar usuario en base de usuarios

            $user_update = user::Where('id', $user->sub)->update($params_array);

            //devolver array con resultado

            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user,
                'changes' => $params_array
            );
            return response()->json($data, $data['code']);
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no esta identificado'
            );
            return response()->json($data, $data['code']);
        }
    }

    public function upload(request $request) {

        //recoger datos de la peticion
        $image = $request->file('file0');

        //validar imagen
        $validate = \Validator::make($request->all(), [
                    'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        //guardar imagen
        if (!$image || $validate->fails()) {
            //devolver resultado negativo
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen'
            );
            return response()->json($data, $data['code']);
        } else {

            $image_name = time() . $image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));

            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );
            return response()->json($data, $data['code']);
        }
    }

    public function getImage($filename) {

        $isset = \Storage::disk('users')->exists($filename);

        if ($isset) {
            $file = \Storage::disk('users')->get($filename);
            return new Response($file, 200);
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'la imagen no existe'
            );
            return response()->json($data, $data['code']);
        }
    }

   public function detail($id) {
    $user = User::find($id);

    if (is_object($user)) {
        $data = array(
            'code' => 200,
            'status' => 'success',
            'user' => $user
        );
        return response()->json($data, $data['code']);
    } else {
         $data = array(
            'code' => 404,
            'status' => 'error',
            'message' => 'el usuario no existe'
        );
         return response()->json($data, $data['code']);
    }
}

}
