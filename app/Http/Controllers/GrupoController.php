<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grupo; // Import the 'Grupo' class
use App\Models\Usuario; // Import the 'Usuario' class

class GrupoController extends Controller
{
    //
    public function crearGrupo(Request $request){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 1)->first();
        $request->validate([
            'clave' => 'required|unique:grupos,clave',
            'nombre' => 'required',
            'letra' => 'required',
            'grado' => 'required',
            'periodo' => 'required'
        ],[
            'clave.unique' => 'La clave ya ha sido registrada'
        ]);
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
        $newGrupo->periodo = $request->periodo;
        $newGrupo->save();
        $success = true;
        $message = 'Grupo creado correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }
    public function editarGrupo(Request $request, $id){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 1)->first();
        $request->validate([
            'clave' => 'required|unique:grupos,clave',
            'nombre' => 'required',
            'letra' => 'required',
            'grado' => 'required',
            'periodo' => 'required'
        ],[
            'clave.unique' => 'La clave ya ha sido registrada'
        ]);
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
            $grupo->periodo = $request->periodo;
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
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 1)->first();
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
        $grupos = Grupo::all();
        $success = true;
        $message = 'Grupos obtenidos correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'resultados' => $grupos
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
            'resultados' => $grupo
        ]);
    }
    public function getGruposByGrado(Request $request, $grado){
        $grupos = Grupo::where('grado',$grado)->get();
        $success = true;
        $message = 'Grupos obtenidos correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'resultados' => $grupos
        ]);
    }
    public function getGruposByLetra(Request $request, $letra){
        $grupos = Grupo::where('letra',$letra)->get();
        $success = true;
        $message = 'Grupos obtenidos correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'resultados' => $grupos
        ]);
    }
    public function getGruposByClave(Request $request, $clave){
        $grupos = Grupo::where('clave',$clave)->get();
        $success = true;
        $message = 'Grupos obtenidos correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'resultados' => $grupos
        ]);
    }
    public function getGruposByNombre(Request $request, $nombre){
        $grupos = Grupo::where('nombre',$nombre)->get();
        $success = true;
        $message = 'Grupos obtenidos correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'resultados' => $grupos
        ]);
    }
}
