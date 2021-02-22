<?php

use App\Http\Controllers\PostController;
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
|
| Las rutas apuntan a metodos y controllers que llaman/crean la visa
| Se llaman a las vistas de la carpeta "views" -> [nombre de la visa].blade.php
| Se llaman a los controladores de la carpeta Http>Controllers y, 
|
*/

//RUTAS DE TESTEO

use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pruebas/nombre/{nombre?}', function ($nombre=null) {
    $text="<p>TEXTO de PRUEBAS $nombre</p>";
    return view('pruebas',array(
        "texto" => $text
    ));
});

Route::get('/pruebas/animales','PruebasController@index');


Route::get('pruebas/testORM','PruebasController@testORM');


//API
    /**
     * Metodos HTTP
     * GET: Conseguir datos o recursos (URL)
     * POST: Guardar datos/recursos, hacer lógica desde un formulario (elementos ocultos)
     * PUT: Actualizar recursos o datos
     * DELETE: Eliminar datos o recursos
     */

    //RUTAS INICIALES DE PRUEBA
    Route::get('/pruebas/usuarios','UserController@pruebas');

    Route::get('/categorias/pruebas','CategoryController@pruebas');

    Route::get('/posts/pruebas','PostController@pruebas');

    //FINALES
    //-USUARIOS
    Route::post('/api/register','UserController@register');
    Route::post('/api/login','UserController@login');
    Route::put('/api/update','UserController@update');
    Route::post('/api/upload','UserController@upload')->middleware(ApiAuthMiddleware::class);
    Route::get('/api/user/avatar/{filename}','UserController@getImage');
    Route::get('/api/user/details/{id}','UserController@details');

    //-CATEGORIAS (php artisan route:list para ver todas las rutas de la interfaz)
    Route::resource('/api/category','CategoryController');

    //-RUTAS DE POST ENTRADA
    Route::resource('api/post','PostController');
    Route::post('/api/post/upload','PostController@upload');
    Route::get('/api/post/image/{filename}','PostController@getImage');
    Route::get('/api/post/category/{id}','PostController@getPostsByCategory');
    Route::get('/api/post/user/{id}','PostController@getPostsByUser');