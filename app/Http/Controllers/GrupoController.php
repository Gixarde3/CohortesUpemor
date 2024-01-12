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
        $newGrupo->grupo = $request->grupo;
        $newGrupo->grado = $request->grado;
        $newGrupo->periodo = $request->periodo;
        $newGrupo->fecha = $request->fecha;
        $newGrupo->idCreador = $admin->id;
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
            $grupo->grupo = $request->grupo;
            $grupo->grado = $request->grado;
            $grupo->periodo = $request->periodo;
            $grupo->fecha = $request->fecha;
            $grupo->idCreador = $admin->id;
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
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
        if (!$admin) {
            $success = false;
            $message = "No cuentas con los permisos necesarios";
            return response()->json([
                'success' => $success,
                'message' => $message
            ]);
        }
        $grupos = Grupo::all();
        $success = true;
        $message = 'Grupos obtenidos correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'grupos' => $grupos
        ]);
    }
    public function getGrupoById(Request $request, $id){
        $grupo = Grupo::find($id);
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
        $grupos = Grupo::where('grado',$grado)->get();
        $success = true;
        $message = 'Grupos obtenidos correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'grupos' => $grupos
        ]);
    }
    public function getGruposByPeriodo(Request $request, $periodo){
        $grupos = Grupo::where('periodo',$periodo)->get();
        $success = true;
        $message = 'Grupos obtenidos correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'grupos' => $grupos
        ]);
    }
    public function getGruposByGrupo(Request $request, $grupo){
        $grupos = Grupo::where('grupo',$grupo)->get();
        $success = true;
        $message = 'Grupos obtenidos correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'grupos' => $grupos
        ]);
    }
    public function getGruposByFecha(Request $request, $fecha){
        $grupos = Grupo::where('fecha',$fecha)->get();
        $success = true;
        $message = 'Grupos obtenidos correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'grupos' => $grupos
        ]);
    }
}
