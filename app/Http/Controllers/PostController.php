<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth',
        ['except'=>['index',
        'show',
        'getImage',
        'getPostsByCategory',
        'getPostsByUser']]);
    }

    public function pruebas(Request $request){
        return "Hola mundo, funciona (pruebas->PostController)";
    }

    /**
     * Listar todos los posts
     */
    public function index(){
        $posts=Post::all()->load('category');

        return response()->json([
            "code"=>200,
            "status"=>"success",
            "posts"=>$posts],200);
    }

    /**
     * Listar un solo post
     */
    public function show($id){
        $post=Post::find($id)->load("category")->load("user");

        if (is_object($post)){
            $data=[
                "code"=>200,
                "status"=>"success",
                "post"=>$post
            ];
        }else{
            $data=[
                "code"=>400,
                "status"=>"error",
                "message"=>"No existe este post"
            ];
        }
        return response()->json($data,$data["code"]);
    }

    /**
     * Guardar un nuevo post
     */
    public function store(Request $request){
        //RECOGER LOS DATOS POR POST
        $json=$request->input('json',null);
        $params_array=json_decode($json,true);
        $params=json_decode($json);

        //CONSEGUIR EL USUARIO LOGUEADO
        if (!empty($params_array)){
            $jwt=new JwtAuth();
            $token=$request->header('Authorization',null);
            $user=$jwt->checkToken($token,true);
            //VALIDAR LOS DATOS
            $validate=\Validator::make($params_array,[
                'tittle'=>'required',
                'content'=>'required',
                'category_id'=>'required',
                'image'=>'required'
            ]);
            if ($validate->fails()){
                $data=[
                    "code"=>400,
                    "status"=>"error",
                    "message"=>"Faltan datos para crear el post"
                ];
            }else{
                //GUARDAR LA EL POST
                $post=new Post();
                $post->user_id=$user->sub;
                $post->category_id=$params->category_id;
                $post->tittle=$params->tittle;
                $post->content=$params->content;
                $post->image=$params->image;
                $post->save();

                $data=[
                    "code"=>200,
                    "status"=>"success",
                    "posts"=>$post
                ];
            }
        }
        //DEVOLVER LA RESPUESTA
        return response()->json($data,$data["code"]);
    }

    /**
     * Actualizar un nuevo post
     */
    public function update($id,Request $request){
        //RECOGER LOS DATOS POR POST
        $json=$request->input('json',null);
        $params_array=json_decode($json,true);
        $params=json_decode($json);

        if(!empty($params_array)){
            //VALIDAR LOS DATOS
            $validate=\Validator::make($params_array,[
                'tittle'=>'required',
                'content'=>'required',
                'category_id'=>'required'
            ]);
            if (!$validate->fails()){
                //ELIMINAR LO QUE NO QUEREMOS ACTUALIZAR
                unset($params_array["user_id"]);
                unset($params_array["id"]);
                unset($params_array["created_at"]);
                unset($params_array["user"]);

                //ACTUALIZAR EL REGISTRO
                $user=$this->getIdentity($request);
                $where=["id"=>$id,'user_id'=>$user->sub];
                $post=Post::updateOrCreate($where,$params_array);

                //DEVOLVER EL RESULTADO
                $data=[
                    "code"=>200,
                    "status"=>'success',
                    "post"=>$post,
                    "datos"=>$params_array
                ];
            }else{
                $data=[
                    "code"=>400,
                    "status"=>'error',
                    "message"=>'Los datos para actualizar son incorrectos'
                ]; 
            }
        }else{
            $data=[
                "code"=>400,
                "status"=>'error',
                "message"=>'Datos enviados incorrectamente'
            ]; 
        }
        

        return response()->json($data,$data["code"]);
    }

    /**
     * Eliminar un post
     */
    public function destroy($id,Request $request){
        //conseguir usuario identificado
        $user=$this->getIdentity($request);
        //COMPROBAR SI EXISTE EL REGISTRO
        $post=Post::where('id',$id)->where("user_id",$user->sub)->first();
        if (!empty($post)){
            //BORRAR EL REGISTRO SI EXISTE
            $post->delete();
            //DEVOLVER EL RESULTADO
            $data=[
                "code"=>200,
                "status"=>"success",
                "post"=>$post
            ];
        }else{
            $data=[
                "code"=>400,
                "status"=>"error",
                "message"=>"El post no existe"
            ];
        }
        
        return response()->json($data,$data["code"]);
    }

    private function getIdentity($request){
        $jwt=new JwtAuth();
        $token=$request->header("Authorization",null);
        $user=$jwt->checkToken($token,true);

        return $user;
    }

    public function upload(Request $request){
        //RECOGER LA IMAGEN DE LA PETICION
        $img=$request->file('file0');
        $validate=\Validator::make($request->all(),[
            'file0'=>'required|mimes:jpg,png,jpeg,gif'
        ]);

        //VALIDAR LA IMAGEN
        if ($validate->fails()){
            $data=[
                "code"=>400,
                "status"=>"error",
                "message"=>"Error al subir la imagen"
            ];
        }else{
             //GUARDAR LA IMAGEN EN UN CURSO
            $img_name=time().$img->getClientOriginalName();

            \Storage::disk('images')->put($img_name,\File::get($img));

            $data=[
                "code"=>200,
                "status"=>"success",
                "image"=>$img_name
            ];
        }
        
        //DEVOLVER DATOS
        return response()->json($data,$data["code"]);
    }

    public function getImage($filename){
        //COMPROBAR SI EXISTE EL FICHERO
        $isset=\Storage::disk('images')->exists($filename);
        
        if ($isset){
            //CONSEGUIR LA IMAGEN
            $file=\Storage::disk('images')->get($filename);
            return new Response($file,200);
        }else{
            $data=[
                "code"=>400,
                "status"=>"error",
                "message"=>"No existe la imagen"
            ];

            return response()->json($data,$data["code"]);
        }
        
        //DEVOLVER EL RESULTADO
    }

    public function getPostsByCategory($id){
        $posts=Post::where('category_id',$id)->get();

        return response()->json(['status'=>'success',"posts"=>$posts],200);
    }

    public function getPostsByUser($id){
        $posts=Post::where('user_id',$id)->get();

        return response()->json(['status'=>'success',"posts"=>$posts],200);
    }
}
