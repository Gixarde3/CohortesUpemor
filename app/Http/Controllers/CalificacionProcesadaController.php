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
     * Importa un archivo de Excel y procesa las calificaciones.
     *
     * Esta función recibe una solicitud HTTP, un identificador de calificación y un token de administrador.
     * Verifica si el administrador tiene los permisos necesarios y si la calificación existe y no ha sido procesada.
     * Luego, importa el archivo de Excel utilizando la clase CalificacionesImportMulti y lo guarda en la ubicación especificada.
     * Marca la calificación como procesada y guarda los cambios en la base de datos.
     * Finalmente, devuelve una respuesta JSON indicando si las calificaciones se procesaron correctamente o si ocurrió un error.
     *
     * @param Request $request La solicitud HTTP que contiene el token de autenticación.
     * @param int $id El identificador de la calificación a procesar.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON que indica si las calificaciones se procesaron correctamente o si ocurrió un error.
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
    /**
     * Obtiene la cantidad de aprobados y reprobados para una calificación procesada.
     *
     * Esta función recibe un objeto de tipo Request y un identificador de calificación.
     * Busca la calificación correspondiente al identificador proporcionado.
     * Si la calificación existe y no ha sido procesada, devuelve un mensaje de error.
     * Si la calificación ha sido procesada, cuenta la cantidad de aprobados y reprobados.
     * Retorna un objeto JSON con un indicador de éxito, la cantidad de aprobados y la cantidad de reprobados.
     * Si no se encuentra la calificación, devuelve un mensaje de error.
     *
     * @param Request $request El objeto Request que contiene los datos de la solicitud.
     * @param int $id El identificador de la calificación.
     * @return \Illuminate\Http\JsonResponse El objeto JSON con el resultado de la operación.
     */
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
    /**
     * Obtiene los años presentes en las matrículas de los alumnos asociados a una calificación.
     *
     * @param Request $request El objeto de solicitud HTTP.
     * @param int $id El ID de la calificación.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con los años presentes en las matrículas de los alumnos.
     */
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
    /**
     * Obtiene las materias más reprobadas por cohorte.
     *
     * Esta función realiza una consulta a la base de datos para obtener las materias que tienen más alumnos reprobados
     * en un cohorte específico. Se realiza un join con las tablas 'materias' y 'alumnos' para obtener el nombre de la
     * materia y el número de alumnos reprobados. Se filtra por una calificación menor a 7 y por el ID del cohorte.
     * Finalmente, se agrupa por el nombre de la materia y se devuelve un JSON con los resultados.
     *
     * @param Request $request El objeto de solicitud HTTP.
     * @param int $idCohorte El ID del cohorte.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con los resultados de las materias más reprobadas.
     */
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
    /**
     * Obtiene las cohortes por cuatrimestre para una calificación específica.
     *
     * Esta función recibe un objeto de tipo Request y un ID de calificación como parámetros de entrada.
     * Busca la calificación correspondiente al ID proporcionado y realiza una consulta para obtener las cohortes, grados y cantidad de alumnos asociados a esa calificación.
     * Los resultados se agrupan por grado y cohorte.
     * Luego, los resultados se organizan en un arreglo asociativo donde la clave es el grado y el valor es un arreglo con la cohorte y la cantidad de alumnos.
     * Finalmente, se devuelve una respuesta JSON con los resultados organizados si se encontró la calificación, o un mensaje de error si no se encontró.
     *
     * @param Request $request El objeto de tipo Request que contiene los datos de la solicitud.
     * @param int $id El ID de la calificación.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con los resultados organizados si se encontró la calificación, o un mensaje de error si no se encontró.
     */
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
