<?php

namespace App\Http\Controllers;

use App\Imports\CalificacionesImport;
use Illuminate\Http\Request;
use App\Models\CalificacionCuatrimestral;
use App\Models\Usuario;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\CalificacionProcesada; // Import the missing class
use App\Imports\CalificacionesImportMulti; // Import the missing class

class CalificacionCuatrimestralProcesadaController extends Controller
{
    //
    public function importarExcel(Request $request, $id){
        $calificaciones = CalificacionCuatrimestral::find($id);
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
        if($admin){
            if($calificaciones){
                $archivo = $calificaciones->archivo;
                $archivo = public_path('excel/'.$archivo);
                Excel::import(new CalificacionesImportMulti($id), $archivo); // Fix the undefined type error
                $calificaciones -> procesado = true;
                $calificaciones -> save();
                return response()->json([
                    'success' => true,
                    'message' => 'Calificaciones procesadas correctamente'
                ]);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron calificaciones'
                ]);
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => 'No cuentas con los permisos necesarios'
            ]);
        }
    }
}
