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
use App\Models\Calificacion; // Import the missing class
class CalificacionProcesadaController extends Controller
{
    /**
     * Importa un archivo de excel con las calificaciones de un grupo.
     * Para esto hace uso del paquete Laravel Excel.
     */
    public function importarExcel(Request $request, $id){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 1)->first();
        $calificacion = Calificacion::find($id);
        try{
            if($admin){
                if($calificacion && $calificacion->procesado == false){
                    $archivo = $calificacion->archivo;
                    $archivo = public_path('excel/'.$archivo);
                    Excel::import(new CalificacionesImportMulti($admin->id, $calificacion->id), $archivo); // Fix the undefined type error
                    $calificacion->procesado = true;
                    $calificacion -> save();
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
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar las calificaciones, el formato del archivo puede no ser el correcto.'
            ]);
        }
    }
    public function getAprobadosReprobados(Request $request, $id){
        $calificacion = Calificacion::find($id);
        if($calificacion){
            if($calificacion->procesado == false){
                return response()->json([
                    'success' => false,
                    'message' => 'Las calificaciones no han sido procesadas'
                ]);
            }

            $aprobados = CalificacionProcesada::where('calificacion', '>=', 7)
                ->where('idCalificacion', $id)
                ->count();
            $reprobados = CalificacionProcesada::where('calificacion', '<', 7)
                ->where('idCalificacion', $id)
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
        $calificacion = Calificacion::find($id);
        if($calificacion){
            $resultados = CalificacionProcesada::selectRaw('count(*) as cantidad, SUBSTRING(alumnos.matricula, 5, 2) as anio')
                ->join('alumnos', 'alumnos.id', '=', 'calificacion_procesadas.idAlumno')
                ->where('calificacion_procesadas.idCalificacion', $id)
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
    public function getMateriasMasReprobadasByCohorte(Request $request, $idCohorte){
        $reprobados = CalificacionProcesada::join('materias', 'materias.id', '=', 'calificacion_procesadas.idMateria')
            ->join('alumnos', 'alumnos.id', '=', 'calificacion_procesadas.idAlumno')
            ->selectRaw('materias.nombre as materia, count(*) as reprobados')
            ->where('calificacion_procesadas.Calificacion', '<', 7)
            ->where('alumnos.idCohorte', $idCohorte)
            ->groupBy('materias.nombre')
            ->get();
        return response()->json([
            'success' => true,
            'resultados' => $reprobados
        ]);

    }
    public function getCohortesByCuatrimestre(Request $request, $id){
        $calificacion = Calificacion::find($id);
        if($calificacion){
            $resultados = CalificacionProcesada::join('alumnos', 'alumnos.id', '=', 'calificacion_procesadas.idAlumno')
                            ->join('grupos', 'grupos.id', '=', 'calificacion_procesadas.idGrupo')
                            ->join('cohortes', 'cohortes.id', '=', 'alumnos.idCohorte')
                            ->selectRaw('CONCAT(cohortes.periodo, cohortes.anio) as cohorte, grupos.grado, COUNT(alumnos.id) as cantidad_alumnos')
                            ->groupByRaw('CONCAT(grupos.grado, "-", cohortes.periodo, cohortes.anio), cohortes.periodo, cohortes.anio, grupos.grado')
                            ->where('calificacion_procesadas.idCalificacion', $id)
                            ->get();

            $organizado = [];
            foreach ($resultados as $resultado) {
                $grado = $resultado["grado"] === null ? "Recursamiento" : $resultado["grado"];
                $organizado[$grado][] = [
                    "cohorte" => $resultado["cohorte"],
                    "cantidad" => $resultado["cantidad_alumnos"]
                ];
            }
            return response()->json([
                'success' => true,
                'resultados' => $organizado
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron calificaciones'
            ]);
        }
    }
}
