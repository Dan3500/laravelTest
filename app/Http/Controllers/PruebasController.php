<?php

namespace App\Http\Controllers;

use App\Post;
use App\Category;
use Illuminate\Http\Request;

class PruebasController extends Controller
{
    public function index(){
        $titulo="Estos son los animales";
        $animales = ["perro","gato","tigre"];

        return view('pruebas.index',array(
            'titulo' => $titulo,
            'animales' => $animales
        ));
    }


    public function testORM(){
        $posts= Post::all();
        $categories= Category::all();

        foreach($posts as $post){
            echo "<h1>".$post->tittle."</h1><br>";
            echo "<span>Nombre de usuario: ".$post->user->name."</span><br>";
            echo "<span>Categoría: ".$post->category->name."</span><br>";
            echo "<p>".$post->content."</p><hr><hr>";
        
        }

        foreach($categories as $categoria){
            echo "<h2>".$categoria->name."</h2>";
            foreach ($categoria->posts as $post){
                echo "<h4>".$post->tittle."</h4>";
                echo "<h5>".$post->content."</h5>";
            }
        }
        die();
    }
}
