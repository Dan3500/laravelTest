<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table='categories';

    //Relacion entre las tablas de uno a muchos (posts pertenecientes a una categoria)
    public function posts(){
        return $this->hasMany('App\Post');
    }
}
