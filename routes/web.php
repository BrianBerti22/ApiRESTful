<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Middleware\ApiAuthMiddleware;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//rutas de prueva
Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-orm', [\App\Http\Controllers\pruebasController::class, 'testOrm']);

Route::get('/test-db-connection', function () {
    $users = DB::table('users')->get();

    foreach ($users as $user) {
        echo $user->name;
    }
});

//rutas del api

//rutas de pruevas

Route::get('/category/pruebas', [\App\Http\Controllers\categoryController::class, 'pruebas']);
Route::get('/post/pruebas', [\App\Http\Controllers\postController::class, 'pruebas']);
Route::get('/user/pruebas', [\App\Http\Controllers\userController::class, 'pruebas']);

//rutas del controlador de usuarios

Route::post('/user/register', [\App\Http\Controllers\userController::class, 'register']);
Route::post('/user/login', [\App\Http\Controllers\userController::class, 'login']);
Route::put('/user/update', [\App\Http\Controllers\userController::class, 'update']);
Route::post('/user/upload', [\App\Http\Controllers\userController::class, 'upload'])->middleware(ApiAuthMiddleware::class);
Route::get('/user/avatar/{filename}', [\App\Http\Controllers\userController::class, 'getImage']);
Route::get('/user/detail/{id}', [\App\Http\Controllers\userController::class, 'detail']);

//rutas del controlador de categorias

Route::resource('/category', '\App\Http\Controllers\CategoryController');


//rutas del controlador de posts

Route::resource('/post', '\App\Http\Controllers\PostController');
Route::post('/post/upload', [\App\Http\Controllers\PostController::class, 'upload']);
Route::get('/post/image/{filename}', [\App\Http\Controllers\PostController::class, 'getImage']);
Route::get('/post/category/{id}', [\App\Http\Controllers\PostController::class, 'getPostsByCategory']);
Route::get('/post/user/{id}', [\App\Http\Controllers\PostController::class, 'getPostsByUser']);





