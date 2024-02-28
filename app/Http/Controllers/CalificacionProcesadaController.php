<?php

namespace App\Http\Controllers;

use App\Imports\CalificacionesImport;
use Illuminate\Http\Request;
use App\Models\CalificacionCuatrimestral;
use App\Models\Excels;
use App\Models\Usuario;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\CalificacionProcesada; // Import the missing class
use App\Imports\CalificacionesImportMulti; // Import the missing class
use App\Models\Cohorte; // Import the missing class
class CalificacionProcesadaController extends Controller
{
    /**
     * Importa un archivo de excel con las calificaciones de un grupo.
     * Para esto hace uso del paquete Laravel Excel.
     */
    public function importarExcel(Request $request, $id){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
        $cohorte = Cohorte::find($id);
        if($admin){
            if($cohorte){
                $archivo = $cohorte->archivo;
                $archivo = public_path('excel/'.$archivo);
                Excel::import(new CalificacionesImportMulti($id), $archivo); // Fix the undefined type error
                $cohorte->procesado = true;
                $cohorte -> save();
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
    public function getAprobadosReprobados(Request $request, $id){
        $cohorte = Cohorte::find($id);
        if($cohorte){
            if($cohorte->procesado == false){
                return response()->json([
                    'success' => false,
                    'message' => 'Las calificaciones no han sido procesadas'
                ]);
            }

            $aprobados = CalificacionProcesada::where('calificacion', '>=', 7)
                ->where('idCohorte', $id)
                ->count();
            $reprobados = CalificacionProcesada::where('calificacion', '<', 7)
                ->where('idCohorte', $id)
                ->count();
            return response()->json([
                'success' => true,
                'aprobados' => $aprobados,
                'reprobados' => $reprobados
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron calificaciones'
            ]);
        }
    }
    public function getAniosInMatriculas(Request $request, $id){
        $cohorte = Cohorte::find($id);
        if($cohorte){
            $resultados = CalificacionProcesada::selectRaw('count(*) as cantidad, SUBSTRING(alumnos.matricula, 5, 2) as anio')
                ->join('alumnos', 'alumnos.id', '=', 'calificacion_procesadas.idAlumno')
                ->where('calificacion_procesadas.idCohorte', 1)
                ->groupByRaw('SUBSTRING(alumnos.matricula, 5, 2)')
                ->get();
            return response()->json([
                'success' => true,
                'anios' => $resultados
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron calificaciones'
            ]);
        }
    }
}
