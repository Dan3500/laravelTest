<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table='posts';

    protected $fillable=['tittle','content','category_id','user_id','image'];

    //Relacion de uno a muchos inversas (De muchos a uno => Muchos posts pertenecen a un usuario o a una categoria)
    public function user(){
        return $this->belongsTo('App\User','user_id');
    }

    public function category(){
        return $this->belongsTo('App\Category','category_id');
    }
}
