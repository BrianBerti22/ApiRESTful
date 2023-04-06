<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Category;
use App\Http\Middleware\ApiAuthMiddleware as Middleware;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller {

    public function __construct() {
        $this->middleware(Middleware::class, ['except' => ['register', 'login', 'detail']]);
    }

    public function pruebas(Request $request) {
        return 'accion de pruebas de CATEGORY-CONTROLLER';
    }

    public function index() {
        $categories = Category::all();

        return response()->json([
                    'categories' => $categories,
                    'code' => 200,
                    'status' => 'success'
        ]);
    }

    public function show($id) {
        $category = Category::find($id);

        if (is_object($category)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'category' => $category
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'la categoria no existe'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {
        //recoger datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            //validar los datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required'
            ]);

            //guardar categoria
            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'no se ha guardado la categoria'
                ];
            } else {
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'category' => $category
                ];
            }
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'no has enviado ninguna categoria'
            ];
        }

        //devolver resultado
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request) {
        //recoger los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {

            //validar los datos
            $validate = Validator::make($params_array, [
                        'name' => 'required'
            ]);

            //quitar lo que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['created_at']);

            //actualozar el registro(categoria)
            $category = Category::where('id', $id)->update($params_array);

            $data = [
                'code' => 200,
                'status' => 'success',
                'category' => $params_array
            ];
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'no has enviado ninguna categoria'
            ];
        }
        //devolver los datos
        return response()->json($data, $data['code']);
    }

}
