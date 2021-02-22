<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;

class UserController extends Controller
{

    /**
     * Metodo de prueba
     */
    public function pruebas(Request $request){
        return "Hola mundo, funciona (pruebas->UserController)";
    }


    /**
     * Metodo para registrar los datos de un usuario
     * @param Request $request: Datos del usuario obtenidos de un formulario
     * @return Array->JSON $data: Resultado del registro de usuarios. Se devuelve como JSON
     */
    public function register(Request $request){

        //Recoger los datos del usuario por post
        $json=$request->input('json',null);

        $params=json_decode($json);//Guardar los datos como Objeto
        $params_array=json_decode($json,true);//Guardar los datos como Array


        //Validar los datos del usuario

        if (!empty($params_array)&&!empty($params)){//Si hay datos almacenados
            
            $params_array=array_map('trim',$params_array);//Limpiar datos

            //Se crea un objeto Validator para validar los datos del formulario
            $validate=\Validator::make($params_array,[
                'name'  => 'required|alpha', //Variable que quieres comprobar -> Validaciones que se hacen (documentacion)
                'surname'  => 'required|alpha',
                'email' => 'required|email|unique:users',//Comprobar si el usuario ya esta creado (duplicado), si no esta registrado lo registra
                'password'=>'required'
            ]);
    
            if ($validate->fails()){//VALIDACION FALLADA

                $data=array(//DEVUELVE ERROR DE VALIDACIÓN DE DATOS
                    "status"    =>"error",
                    "code"      =>404,
                    "message"   =>"Los datos introducidos no son correctos",
                    "errors"    => $validate->errors()
                );

            }else{//VALIDACION CORRECTA

                //Cifrar contraseña
                $pwd=hash('sha256',$params->password);

                //Crear el usuario

                $user=new User();
                $user->name=$params_array["name"];
                $user->surname=$params_array["surname"];
                $user->email=$params_array["email"];
                $user->password=$pwd;
                $user->role='ROLE_USER';

                //Guardar el user
                if($user->save()){
                    //DEVUELVE EL MENSAJE CORRECTO
                    $data=array(
                        "status"    =>"success",
                        "code"      =>200,
                        "message"   =>"El usuario se ha creado correctamente",
                    );

                }else{
                    //DEVUELVE ERROR SI NO SE GUARDA EL USUARIO
                    $data=array(
                        "status"    =>"error",
                        "code"      =>404,
                        "message"   =>"Error al insertar el usuario en la BD",
                    );
                }
            }
        }else{
            $data=array(
                "status"    =>"error",
                "code"      =>404,
                "message"   =>"No se han recogido los datos del usuario correctamente",
            );
        }
        
        return response()->json($data,$data["code"]);
    }

    /**
     * Metodo para loguear al usuario
     * @param Request $request: Datos enviados del usuario
     * @return $data: Resultado del login -> Array con mensaje de error | String con el token cifrado | Array con los datos del usuario conectado
     */
    public function login(Request $request){

        $jwtAuth=new \JwtAuth();

        //Recibir datos por post

        $json=$request->input('json',null);
        $params=json_decode($json);
        $params_array=json_decode($json,true);

        //Validar esos datos

        $validate=\Validator::make($params_array,[
            'email' => 'required|email',//Comprobar si el usuario ya esta creado (duplicado), si no esta registrado lo registra
            'password'=>'required'
        ]);

        if ($validate->fails()){//VALIDACION FALLADA
            $data=array(
                "status"    =>"error",
                "code"      =>404,
                "message"   =>"Error al validar los datos de inicio de sesión",
                "errors"    => $validate->errors()
            );
        }else{
            //Cifrar la password
            $password=hash('sha256',$params->password);

           
            if (isset($params->getToken)){
                $data=$jwtAuth->signUp($params->email,$password,true);
            }else{
                $data=$jwtAuth->signUp($params->email,$password);
            }
        }
        //Devolver token o datos
        return response()->json($data,200);
    }


    /**
     * Metodo para actualizar el usuario
     */
    public function update(Request $request){

        //Comprobar si el usuario esta identificado

        $token=$request->header("Authorization");
        $jwt=new \JwtAuth();
        $checkToken=$jwt->checkToken($token);

        //Recoger los datos por post
        $json=$request->input('json',null);
        $params_array=json_decode($json,true);

        if($checkToken&&!empty($params_array)){
            //Actualizar el usuario

            //Sacar usuario identificado
            $userConectado=$jwt->checkToken($token,true);
            
            //Validar los datos
            $validate=\Validator::make($params_array,[
                'name'  => 'required|alpha', //Variable que quieres comprobar -> Validaciones que se hacen (documentacion)
                'surname'  => 'required|alpha',
                'email' => 'required|email|unique:users,'.$userConectado->sub
            ]);

            //Quitar los campos que no quiero actualizar

            unset($params_array["id"]);
            unset($params_array["role"]);
            unset($params_array["password"]);
            unset($params_array["created_at"]);
            unset($params_array["remember_token"]);

            //Actualizar el user en la BD
            $user_update=User::where('id',$userConectado->sub)->update($params_array);
            //Devolver array con el resultado

            $data=array(
                "code"=>200,
                "status"=>"success",
                "user"=>$userConectado,
                "changes"=>$params_array
            );

        }else{
           
            $data=array(
                "code"=>400,
                "status"=>"error",
                "message"=>"El usuario no esta identificado"
            );
        }

       return response()->json($data,$data["code"]);

    }

    /**
     * Metodo para subir una imagen
     */
    public function upload(Request $request){
        //RECOGER LOS DATOS DE LA SUBIDA
        $img=$request->file('file0');

        //VALIDAR QUE SEA UNA IMAGEN
        $validate=\Validator::make($request->all(),[
            'file0'=>'required|mimes:jpg,png,gif'
        ]);

        //SUBIR Y GUARDAR LA IMAGEN
        if (!$img||$validate->fails()){
            $data=array(
                "code"=>400,
                "status"=>"error",
                "message"=>"Error al subir imagen"
            );
        }else{
            $img_name=time().$img->getClientOriginalName();//Obtener el nombre real de la imagen perro.jpg
            \Storage::disk('users')->put($img_name,\File::get($img));

            //DEVOLVER EL RESULTADO
            $data=[
                'image'=>$img_name,
                'status'=>'succes',
                'code'=>200
            ];
        }

        return response()->json($data,$data["code"]);
    }

    /**
     * Obtener la imagen de usuario
     */
    public function getImage($filename){
        if (\Storage::disk("users")->exists($filename)){
            $file=\Storage::disk("users")->get($filename);
            return new Response($file,200);
        }else{
            $data=array(
                "code"=>400,
                "status"=>"error",
                "message"=>"La imagen no existe"
            );
            return response()->json($data,$data["code"]);
        }
    }

    public function details($id){
        $user=User::find($id);

        if (is_object($user)){
            $data=[
                "code" => 200,
                "status" => 'success',
                "user" =>$user
            ];
        }else{
            $data=array(
                "code"=>400,
                "status"=>"error",
                "message"=>"No existe este usuario"
            );
        }
        return response()->json($data,$data["code"]);
    }
}
