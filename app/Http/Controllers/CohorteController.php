<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Cohorte;

class CohorteController extends Controller
{
    public function createCohorte(Request $request){

        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
        if ($admin) {
            $newCohorte = new Cohorte();
            $newCohorte->periodo = $request->periodo;
            $newCohorte->anio = $request->anio;
            $newCohorte->plan = $request->plan;
            $newCohorte->idCreador = $admin->id;
            $newCohorte->save();
            $success = true;
            $message = 'Cohorte registrado correctamente';
        }else{
            $success = false;
            $message = "No cuentas con los permisos necesarios";
        }
        
        return response()->json([
            'success'=> $success,
            'message'=>$message
        ]);
    }
    public function editCohorte(Request $request, $id){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
        if ($admin) {
            $cohort = Cohorte::find($id);
            $cohort->periodo = $request->periodo;
            $cohort->anio = $request->anio;
            $cohort->plan = $request->plan;
            $cohort->save();
            $success = true;
            $message = 'Cohorte editado correctamente';
        }else{
            $success = false;
            $message = "No cuentas con los permisos necesarios";
        }
        
        return response()->json([
            'success'=> $success,
            'message'=>$message
        ]);
    }
    public function deleteCohorte(Request $request, $id){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
        if ($admin) {
            $cohort = Cohorte::find($id);
            $cohort->delete();
            $success = true;
            $message = 'Cohorte eliminado correctamente';
        }else{
            $success = false;
            $message = "No cuentas con los permisos necesarios";
        }
        
        return response()->json([
            'success'=> $success,
            'message'=>$message
        ]);
    }
    public function getAllCohortes(Request $request){
        $cohortes = Cohorte::all();
        $success = true;
        $message = 'Cohortes obtenidos correctamente';
        return response()->json([
            'success'=> $success,
            'cohortes'=>$cohortes,
            'message'=>$message
        ]);
    }
    public function getCohorteById(Request $request, $id){
        $cohorte = Cohorte::find($id);
        $success = true;
        $message = 'Cohorte obtenido correctamente';
        return response()->json([
            'success'=> $success,
            'cohorte'=>$cohorte,
            'message'=>$message
        ]);
    }
}
