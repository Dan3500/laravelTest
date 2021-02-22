<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Category;
use PhpParser\Node\Stmt\Catch_;

class CategoryController extends Controller
{

    public function __construct()
    {
        $this->middleware('api.auth',['except'=>['index','show']]);
    }
     /**
     * Metodo para mostrar todas las categorias
     */
    public function index(){
        $categories=Category::all();

        return response()->json([
            'code'=>200,
            'status'=>'success',
            'categories'=>$categories
        ]);
    }

    /**
     * Metodo para mostrar una categoria
     */
    public function show($id){
        $category=Category::find($id);

        if (is_object($category)){
            $data=[
                'code'=>200,
                'status'=>'success',
                'category'=>$category
            ];
        }else{
            $data=[
                'code'=>400,
                'status'=>'error',
                'message'=>"La categoria no existe"
            ];
        }
        return response()->json($data,$data["code"]);
    }

    public function store(Request $request){
        //RECOGER LOS DATOS POR POST
        $json=$request->input('json',null);
        $params_array=json_decode($json,true);

        if (!empty($params_array)){
            $validate=\Validator::make($params_array,[
                "name"=>"required|unique:categories,name",
            ]);
            //VALIDAR LOS DATOS
            if ($validate->fails()){
                $data=[
                    "code"=>400,
                    "status"=>"error",
                    "message"=>"No son correctos los datos de la categoria o la categoria escogida ya esta guardada"
                ];
            }else{
                //GUARDAR LA CATEGORIA
                $category=new Category();
                $category->name=$params_array["name"];
                $category->save();

                $data=[
                    "code"=>200,
                    "status"=>"success",
                    "message"=>"Se ha guardado la nueva categoria",
                    'category'=>$category
                ];
            }
        }else{
            $data=[
                "code"=>400,
                "status"=>"error",
                "message"=>"No has escrito los datos de la categoria"
            ];
        }
        //DEVOLVER LOS DATOS
        return response()->json($data,$data["code"]);
    }

    /**
     * Metodo para modificar una categoria
     */
    public function update($id,Request $request){
        //RECOGER LOS DATOS DEL POST
        $json=$request->input('json',null);
        $params_array=json_decode($json,true);

        if (!empty($params_array)){
            //VALIDAR LOS DATOS
            $category=Category::find($id);
            if (!empty($category)){
                $validate=\Validator::make($params_array,[
                    "name"=>'required'
                ]);
    
                if ($validate->fails()){
                    $data=[
                        "code"=>400,
                        "status"=>"error",
                        "message"=>"No has escrito ningun nombre para modificar la categoria"
                    ];
                }else{
                    //QUITAR LO QUE NO QUIERO ACTUALIZAR
                    unset($params_array["id"]);
                    unset($params_array["created_at"]);
        
                    //ACTUALIZAR EL REGISTRO (CATEGORIA)
                    $category=Category::where('id',$id)->update($params_array);
    
                    $data=[
                        "code"=>200,
                        "status"=>"success",
                        "category"=>$id,
                        "modificado"=>$params_array
                    ];
                }
            }else{
                $data=[
                    "code"=>400,
                    "status"=>"error",
                    "message"=>"No hay ninguna categoria para modificar"
                ];
            }
        }else{
            $data=[
                "code"=>400,
                "status"=>"error",
                "message"=>"No has enviado ningun dato para modificar la categoria"
            ];
        }
     
        //DEVOLVER LOS DATOS
        return response()->json($data,$data["code"]);
    }
}
