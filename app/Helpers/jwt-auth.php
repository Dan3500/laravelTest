<?php

namespace App\Helpers;

use Firebase\JWT\JWT;

use Illuminate\Support\Facades\DB;

use App\User;

/**
 * JWT-> JSON Web Token 
 * Clase para crear un token al iniciar sesión
 */
class JwtAuth{
   
    public $key;//Clave con la que se creara el token

    public function __construct()
    {
        $this->key="mi_clave_secreta-693865413576291";
    }

    /**
     * Metodo para crear el token o devolver los datos del usuario si se encuentra un usuario con los datos
     * Se devuelven los datos al método login de UserController.php
     * @param String $email: Email del usuario
     * @param String $password: Contraseña del usuario. Esta cifrada
     * @param Boolean $getToken: Indica si se van a obtener los datos del token (true) o si se va a obtener el token cifrado (null -> Por defecto)
     * @return $data: Resultado de la creacion del token-> String con el token cifrado|Array con los datos de error|Array con los datos del usuario procedente del token
     */
    public function signUp($email,$password,$getToken=null){
        //Comprobar datos del login del usuario y buscar si existe un usuario registrado con esos datos

        $user=User::where([//Hace una consulta a la BBDD
            'email' =>  $email,
            'password'  => $password
        ])->first();

        $login=false;
        if (is_object($user)){
            $login=true;
        }

        //Generar el token con los datos

        if ($login){//Si se comprueba correctamente el usuario
            $token=array(//Se obtienen los datos del usuario
                'sub' => $user->id,
                'email' => $user->email,
                'name'=> $user->name,
                'surname'=> $user->surname,
                'image'=>$user->image,
                'description'=>$user->description,
                'iat'=>time(),
                'exp'=>time()+(7*24*3600)
            );

            //Devolver los datos decodificados o el token, en funcion del parametro


            $jwt=JWT::encode($token, $this->key,'HS256');

            if ($getToken==null){
                $data=$jwt;//Token codificado
            }else{
                $data=JWT::decode($jwt, $this->key,['HS256']); //Datos del token
            }

        }else{//Si no se comprueba correctamente el usuario
            $data=array(
                "status"    =>"error",
                "message"   =>"Los datos enviados no son correctos",
            );
        }
        
        return $data;
    }


    /**
     * Metodo para comprobar el token 
     */
    public function checkToken($jwt,$getIdentity=false){
        $auth=false;
        try{
            $jwt=str_replace('"',"",$jwt);
            $decoded=JWT::decode($jwt,$this->key,['HS256']);
        }catch(\UnexpectedValueException $e){
            $auth=false;
        }catch(\DomainException $e){
            $auth=false;
        }

        if (isset($decoded)&&is_object($decoded)&&isset($decoded->sub)){
            $auth=true;
        }else{
            $auth=false;
        }

        if ($getIdentity){
            return $decoded;
        }
        
        return $auth;



    }

}