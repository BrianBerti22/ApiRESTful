<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Middleware\ApiAuthMiddleware as Middleware;
use App\Models\Post;
use Illuminate\Http\Response;
use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\Validator;

class postcontroller extends Controller {

    public function pruebas(request $request) {
        return 'accion de pruebas de POST-CONTROLLER';
    }

    public function __construct() {
        $this->middleware(Middleware::class, ['except' => [
            'index', 
            'show', 
            'getImage',
            'getPostsByUser',
            'getPostsByCategory']]);
    }

    public function index() {
        $posts = Post::all()->load('category');

        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'posts' => $posts
                        ], 200);
    }

    public function show($id) {
        $post = Post::find($id)->load('category');

        if (is_object($post)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'posts' => $post
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'entrada no existe'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {
        //recoger datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        $params = json_decode($json);

        if (!empty($params_array)) {

            //conseguir usuario autenticado
            $user = $this->getIdentity($request);
            //validar datos
            $validate = Validator::make($params_array, [
                        'title' => 'required',
                        'content' => 'required',
                        'category_id' => 'required',
                        'image' => 'required'
            ]);
            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'no se ha guardado el post, faltan datos'
                ];
            } else {
                //guardar el post
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;

                $post->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'post' => $post
                ];
            }
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'envia los datos correctamente'
            ];
        }
        //devolver la respuesta

        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request) {




        //recoger datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {

            //validar los datos
            $validate = Validator::make($params_array, [
                        'title' => 'required',
                        'content' => 'required',
                        'category_id' => 'required'
            ]);

            if ($validate->fails()) {
                return response()->json($validate->errors(), 400);
            }
            //eliminar lo que no quremos actualizar
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['user']);
            //conseguir usuario identificado
            $user = $this->getIdentity($request);

            //actualizar el registro
            $where = [
                'id' => $id,
                'user_id' => $user->sub
            ];

            $post = Post::UpdateOrCreate($where, $params_array);

            //devolver algo
            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $post,
                'changes' => $params_array
            ];
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'datos enviados incorrectamente'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request) {
        //conseguir usuario identificado
        $user = $this->getIdentity($request);

        //conseguir el registro
        $post = Post::where('id', $id)->where('user_id', $user->sub)->first();

        if (!empty($post)) {
            //borrarlo
            $post->delete();
            //devolver algo
            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $post
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'post no existe'
            ];
        }
        return response()->json($data, $data['code']);
    }

    private function getIdentity(Request $request) {
        //conseguir usuario identificado
        $jwtAuth = new JwtAuth();
        $token = $request->header('authorization', null);
        $user = $jwtAuth->checkToken($token, true);
        return $user;
    }

    public function upload(Request $request) {
        //recoger la imagen de la peticion
        $image = $request->file('file0');
        //validar imagen
        $validate = Validator::make($request->all(), [
                    'file0' => 'required|image|mimes:jpeg,jpg,png,gif'
        ]);
        //guardar la imagen en un disco storage//app//images
        if (!$image || $validate->fails()) {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'error al subir la imagen'
            ];
        } else {
            $image_name = time() . $image->getClientOriginalName();

            \Storage::disk('imagenes')->put($image_name, \File::get($image));

            $data = [
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            ];
        }
        //devolver datos
        return response()->json($data, $data['code']);
    }

    public function getImage($filename) {
        //comprobar que existe el fichero
        $isset = \Storage::disk('imagenes')->exists($filename);

        if ($isset) {
            //conseguir la imagen
            $file = \Storage::disk('imagenes')->get($filename);

            //devolver la imagen
            return new response($file, 200);
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'la imagen no existe'
            ];
        }
        //mostrar error
        return response()->json($data, $data['code']);
    }

    public function getPostsByCategory($id) {
        $posts = Post::where('category_id', $id)->get();
        return response()->json([
                    'status' => 'success',
                    'post' => $posts
                        ], 200);
    }

    public function getPostsByUser($id) {
        $posts = Post::where('user_id', $id)->get();
        return response()->json([
                    'status' => 'success',
                    'post' => $posts
                        ], 200);
    }

}
