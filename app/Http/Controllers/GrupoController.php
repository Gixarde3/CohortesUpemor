<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grupo; // Import the 'Grupo' class
use App\Models\Usuario; // Import the 'Usuario' class

class GrupoController extends Controller
{
    //
    public function crearGrupo(Request $request){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
        if (!$admin) {
            $success = false;
            $message = "No cuentas con los permisos necesarios";
            return response()->json([
                'success' => $success,
                'message' => $message
            ]);
        }
        $newGrupo = new Grupo();
        $newGrupo->clave = $request->clave;
        $newGrupo->nombre = $request->nombre;
        $newGrupo->letra = $request->letra;
        $newGrupo->grado = $request->grado;
        $newGrupo->idCohorte = $request->cohorte;
        $newGrupo->save();
        $success = true;
        $message = 'Grupo creado correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }
    public function editarGrupo(Request $request, $id){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
        if (!$admin) {
            $success = false;
            $message = "No cuentas con los permisos necesarios";
            return response()->json([
                'success' => $success,
                'message' => $message
            ]);
        }
        $grupo = Grupo::find($id);
        if($grupo){
            $grupo->clave = $request->clave;
            $grupo->nombre = $request->nombre;
            $grupo->letra = $request->letra;
            $grupo->grado = $request->grado;
            $grupo->idCohorte = $request->cohorte;
            $grupo->save();
            $success = true;
            $message = 'Grupo editado correctamente';
        } else {
            $success = false;
            $message = 'No se encontró el grupo';
        }
        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }
    public function eliminarGrupo(Request $request, $id){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
        if (!$admin) {
            $success = false;
            $message = "No cuentas con los permisos necesarios";
            return response()->json([
                'success' => $success,
                'message' => $message
            ]);
        }
        $grupo = Grupo::find($id);
        if($grupo){
            $grupo->delete();
            $success = true;
            $message = 'Grupo eliminado correctamente';
        } else {
            $success = false;
            $message = 'No se encontró el grupo';
        }
        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }
    public function getGrupos(Request $request){
        $grupos = Grupo::join('cohortes','grupos.idCohorte','=','cohortes.id')->select('grupos.*','cohortes.plan as cohorte')->get();
        $success = true;
        $message = 'Grupos obtenidos correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'grupos' => $grupos
        ]);
    }
    public function getGrupoById(Request $request, $id){
        $grupo = Grupo::join('cohortes','grupos.idCohorte','=','cohortes.id')->select('grupos.*','cohortes.plan as cohorte')->where('grupos.id',$id)->first();
        if($grupo){
            $success = true;
            $message = 'Grupo obtenido correctamente';
        } else {
            $success = false;
            $message = 'No se encontró el grupo';
        }
        return response()->json([
            'success' => $success,
            'message' => $message,
            'grupo' => $grupo
        ]);
    }
    public function getGruposByGrado(Request $request, $grado){
        $grupos = Grupo::join('cohortes','grupos.idCohorte','=','cohortes.id')->select('grupos.*','cohortes.plan as cohorte')->where('grupos.grado',$grado)->get();
        $success = true;
        $message = 'Grupos obtenidos correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'grupos' => $grupos
        ]);
    }
    public function getGruposByLetra(Request $request, $letra){
        $grupos = Grupo::join('cohortes','grupos.idCohorte','=','cohortes.id')->select('grupos.*','cohortes.plan as cohorte')->where('grupos.letra',$letra)->get();
        $success = true;
        $message = 'Grupos obtenidos correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'grupos' => $grupos
        ]);
    }
    public function getGruposByCohorte(Request $request, $cohorte){
        $grupos = Grupo::join('cohortes','grupos.idCohorte','=','cohortes.id')->select('grupos.*','cohortes.plan as cohorte')->where('cohortes.plan',$cohorte)->get();
        $success = true;
        $message = 'Grupos obtenidos correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'grupos' => $grupos
        ]);
    }
    public function getGruposByClave(Request $request, $clave){
        $grupos = Grupo::join('cohortes','grupos.idCohorte','=','cohortes.id')->select('grupos.*','cohortes.plan as cohorte')->where('grupos.clave',$clave)->get();
        $success = true;
        $message = 'Grupos obtenidos correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'grupos' => $grupos
        ]);
    }
    public function getGruposByNombre(Request $request, $nombre){
        $grupos = Grupo::join('cohortes','grupos.idCohorte','=','cohortes.id')->select('grupos.*','cohortes.plan as cohorte')->where('grupos.nombre',$nombre)->get();
        $success = true;
        $message = 'Grupos obtenidos correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'grupos' => $grupos
        ]);
    }
}
