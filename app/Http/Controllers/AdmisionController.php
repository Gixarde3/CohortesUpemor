<?php

namespace App\Http\Controllers;

use App\Imports\AdminisionesMultiImport;
use Illuminate\Http\Request;
use App\Models\Admision;
use App\Models\Usuario;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\AdmisionData;
use App\Models\Aspirante;
use App\Models\Cohorte;

class AdmisionController extends Controller
{
    /**
     * Crea una nueva admisión en el sistema.
     *
     * @param Request $request Los datos de la solicitud HTTP.
     *                        Se espera que contenga los siguientes parámetros:
     *                        - token: El token de autenticación del usuario administrador.
     *                        - archivo: El archivo adjunto de la admisión.
     *                        - periodo: El periodo de la admisión.
     *                        - anio: El año de la admisión.
     *
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON que indica si la admisión se creó correctamente o no.
     *                                      Si la admisión se creó correctamente, el JSON contendrá:
     *                                      - success: true
     *                                      - message: 'Admisión creada correctamente'
     *                                      Si no se creó la admisión debido a permisos insuficientes, el JSON contendrá:
     *                                      - success: false
     *                                      - message: 'No cuentas con los permisos necesarios'
     */
    public function crearAdmision(Request $request){
        $admin = Usuario::where('token', $request->token)->where('tipoUsuario', '>=', 1)->first();
        if($admin){
            $newAdmision = new Admision();
            $newAdmision->archivo = $this->manejarArchivo($request->file('archivo'));
            $newAdmision->procesado = false;
            $newAdmision->periodo = $request->periodo;
            $newAdmision->anio = $request->anio;
            $newAdmision->idCreador = $admin->id;
            $newAdmision->save();
            return response()->json([
                'success' => true,
                'message' => 'Admisión creada correctamente'
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'No cuentas con los permisos necesarios'
            ]);
        }
    }
    /**
     * Edita una admisión en el controlador de Admisiones.
     *
     * @param Request $request El objeto Request que contiene los datos de la solicitud.
     * @param int $id El ID de la admisión que se va a editar.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON que indica si la admisión se editó correctamente o si ocurrió un error.
     */
    public function editarAdmision(Request $request, $id){
        $admin = Usuario::where('token', $request->token)->where('tipoUsuario', '>=', 1)->first();
        $admision = Admision::find($id);
        if($admin){
            if($admision){
                if(request()->hasFile('archivo')){
                    $this->deleteFile($admision->archivo);
                    $admision->archivo = $this->manejarArchivo($request->file('archivo'));
                    $admision->procesado = false;
                }
                $admision->periodo = $request->periodo;
                $admision->anio = $request->anio;
                $admision->save();
                return response()->json([
                    'success' => true,
                    'message' => 'Admisión editada correctamente'
                ]);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la admisión'
                ]);
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => 'No cuentas con los permisos necesarios'
            ]);
        }
    }
    /**
     * Elimina una admisión.
     *
     * Esta función elimina una admisión específica en base al ID proporcionado.
     *
     * @param Request $request El objeto Request que contiene los datos de la solicitud.
     * @param int $id El ID de la admisión que se desea eliminar.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON que indica si la eliminación fue exitosa o no.
     */
    public function eliminarAdmision(Request $request, $id){
        $admin = Usuario::where('token', $request->token)->where('tipoUsuario', '>=', 1)->first();
        $admision = Admision::find($id);
        if($admin){
            if($admision){
                $this->deleteFile($admision->archivo);
                $admision->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Admisión eliminada correctamente'
                ]);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la admisión'
                ]);
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => 'No cuentas con los permisos necesarios'
            ]);
        }
    }
    /**
     * Obtiene todas las admisiones.
     *
     * @param Request $request La solicitud HTTP recibida.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con el resultado de la operación.
     */
    public function getAdmisiones(Request $request)
    {
        $admisiones = Admision::all();
        return response()->json([
            'success' => true,
            'admisiones' => $admisiones
        ]);
    }

    /**
     * Descarga una admisión específica.
     *
     * @param Request $request La solicitud HTTP recibida.
     * @param int $id El ID de la admisión a descargar.
     * @return mixed La respuesta de descarga del archivo o una respuesta JSON con un mensaje de error.
     */
    public function descargarAdmision(Request $request, $id)
    {
        $admision = Admision::find($id);
        if ($admision) {
            return $this->download($request, $admision->archivo);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la admisión'
            ]);
        }
    }

    /**
     * Procesa una admisión específica.
     *
     * @param Request $request La solicitud HTTP recibida.
     * @param int $id El ID de la admisión a procesar.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con el resultado de la operación.
     */
    public function procesarAdmision(Request $request, $id)
    {
        $admin = Usuario::where('token', $request->token)->where('tipoUsuario', '>=', 1)->first();
        $admision = Admision::find($id);
        if ($admin) {
            if ($admision && !$admision->procesado) {
                $archivo = $admision->archivo;
                $archivo = public_path('excel/'.$archivo);
                Excel::import(new AdminisionesMultiImport($admision->id, $admin->id), $archivo); // Fix the undefined type error
                $admision->procesado = true;
                $admision->save();
                return response()->json([
                    'success' => true,
                    'message' => 'Admisión procesada correctamente'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la admisión'
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No cuentas con los permisos necesarios'
            ]);
        }
    }
    /**
     * Obtiene una admisión por su ID.
     *
     * @param Request $request La solicitud HTTP recibida.
     * @param int $id El ID de la admisión a buscar.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con los detalles de la admisión encontrada o un mensaje de error si no se encuentra.
     */
    public function getAdmisionById(Request $request, $id)
    {
        $admision = Admision::find($id);
        if($admision){
            return response()->json([
                'success' => true,
                'admision' => $admision
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la admisión'
            ]);
        }
    }

    /**
     * Maneja un archivo subido.
     *
     * @param \Illuminate\Http\UploadedFile $file El archivo subido.
     * @return string El nombre del archivo generado.
     */
    public function manejarArchivo($file)
    {
        $nameFile = uniqid();
        $extensionFile = '.' . $file->getClientOriginalExtension();
        $file->storeAs('public/', $nameFile . $extensionFile);
        $storageRoute = storage_path('app/public/' . $nameFile . $extensionFile);
        $publicRoute = public_path('excel/' . $nameFile . $extensionFile);
        File::move($storageRoute, $publicRoute);
        Storage::delete($storageRoute);
        return $nameFile . $extensionFile;
    }

    /**
     * Descarga un archivo.
     *
     * @param Request $request La solicitud HTTP recibida.
     * @param string $filename El nombre del archivo a descargar.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse La respuesta con el archivo descargado.
     */
    public function download(Request $request, $filename)
    {
        // Define la ruta al archivo dentro de la carpeta de almacenamiento (por ejemplo, en la carpeta "public")
        $rutaArchivo = public_path('excel/'.$filename);

        // Obtén el archivo como una respuesta
        return response()->file($rutaArchivo, ['Content-Disposition' => 'attachment; filename="' . $filename . '"']);
    }

    /**
     * Elimina un archivo.
     *
     * @param string $fileName El nombre del archivo a eliminar.
     * @return bool True si el archivo se eliminó correctamente, False si no se encontró el archivo.
     */
    public function deleteFile($fileName)
    {
        $filePath = public_path('excel/' . $fileName);
        if (file_exists($filePath)) {
            unlink($filePath);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Obtiene el número de fichas vendidas en un rango de años y una carrera específica.
     *
     * @param Request $request El objeto de solicitud HTTP.
     * @param int $anio1 El año inicial del rango.
     * @param int $anio2 El año final del rango.
     * @param string $carrera La carrera específica.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con el resultado de la consulta.
     */
    public function getFichasVendidas(Request $request, $anio1, $anio2, $carrera){
        if($anio1 > $anio2) 
            return response()->json(['success' => false, 'message' => 'El año 1 debe ser menor al año 2']);

        $admisiones = AdmisionData::join('admisions', 'admisions.id', '=', 'admision_data.idAdmision')
                            ->whereBetween('admisions.anio', [$anio1, $anio2])
                            ->where('admision_data.carrera', $carrera)
                            ->select('admision_data.solicitudes as total', 'admisions.anio')
                            ->groupBy('admisions.anio', 'admision_data.solicitudes')
                            ->get();
        return response()->json([
            'success' => true,
            'resultados' => $admisiones
        ]);
    }

    /**
     * Obtiene el número de exámenes presentados en un rango de años y una carrera específica.
     *
     * @param Request $request El objeto de solicitud HTTP.
     * @param int $anio1 El año inicial del rango.
     * @param int $anio2 El año final del rango.
     * @param string $carrera La carrera específica.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con el resultado de la consulta.
     */
    public function getExamenesPresentados(Request $request, $anio1, $anio2, $carrera){
        if($anio1 > $anio2) 
            return response()->json(['success' => false, 'message' => 'El año 1 debe ser menor al año 2']);

        $admisiones = AdmisionData::join('admisions', 'admisions.id', '=', 'admision_data.idAdmision')
                            ->whereBetween('admisions.anio', [$anio1, $anio2])
                            ->where('admision_data.carrera', $carrera)
                            ->select('admision_data.examenes_presentados as total', 'admisions.anio')
                            ->groupBy('admisions.anio', 'admision_data.examenes_presentados')
                            ->get();
        return response()->json([
            'success' => true,
            'resultados' => $admisiones
        ]);
    }

    /**
     * Obtiene el número de fichas vendidas en una cohorte específica.
     *
     * @param Request $request El objeto de solicitud HTTP.
     * @param int $idCohorte El ID de la cohorte específica.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con el resultado de la consulta.
     */
    public function getFichasVendidasByCohorte(Request $request, $idCohorte){
        $cohorte = Cohorte::find($idCohorte);
        $admisiones = AdmisionData::join('admisions', 'admisions.id', '=', 'admision_data.idAdmision')
                            ->where('admisions.anio', $cohorte->anio)
                            ->where('admision_data.carrera', substr($cohorte->plan, 0, 3))
                            ->select('admision_data.solicitudes as total')
                            ->first();
        return response()->json([
            'success' => true,
            'resultados' => $admisiones->total
        ]);
    }

    /**
     * Obtiene el número de exámenes presentados en una cohorte específica.
     *
     * @param Request $request El objeto de solicitud HTTP.
     * @param int $idCohorte El ID de la cohorte específica.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con el resultado de la consulta.
     */
    public function getExamenesPresentadosByCohorte(Request $request, $idCohorte){
        $cohorte = Cohorte::find($idCohorte);
        $admisiones = AdmisionData::join('admisions', 'admisions.id', '=', 'admision_data.idAdmision')
                            ->where('admisions.anio', $cohorte->anio)
                            ->where('admision_data.carrera', substr($cohorte->plan, 0, 3))
                            ->select('admision_data.examenes_presentados as total')
                            ->first();
        return response()->json([
            'success' => true,
            'resultados' => $admisiones->total
        ]);
    }
    /**
     * Obtiene el número de aspirantes aprobados en el Ceneval para una cohorte específica.
     *
     * @param Request $request El objeto de solicitud HTTP.
     * @param int $idCohorte El ID de la cohorte.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con el número de aspirantes aprobados.
     */
    public function getAprobadosCeneval(Request $request, $idCohorte){
        // Obtiene la cohorte correspondiente al ID proporcionado.
        $cohorte = Cohorte::find($idCohorte);

        // Cuenta el número de aspirantes aprobados en el Ceneval para la cohorte y carrera específicas.
        $aprobados = Aspirante::join('admisions', 'admisions.id', '=', 'aspirantes.idAdmision')
                        ->where('admisions.anio', $cohorte->anio)
                        ->whereRaw('SUBSTR(aspirantes.carrera, 1, 3) = ?', substr($cohorte->plan, 0, 3))
                        ->count();

        // Devuelve la respuesta JSON con el número de aspirantes aprobados.
        return response()->json([
            'success' => true,
            'resultados'=>$aprobados
        ]);
    }

    /**
     * Obtiene el número de aspirantes que han pagado el curso para una cohorte específica.
     *
     * @param Request $request El objeto de solicitud HTTP.
     * @param int $idCohorte El ID de la cohorte.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con el número de aspirantes que han pagado el curso.
     */
    public function getAspirantesCurso(Request $request, $idCohorte){
        // Obtiene la cohorte correspondiente al ID proporcionado.
        $cohorte = Cohorte::find($idCohorte);

        // Cuenta el número de aspirantes que han pagado el curso para la cohorte y carrera específicas.
        $aprobados = Aspirante::join('admisions', 'admisions.id', '=', 'aspirantes.idAdmision')
                        ->where('admisions.anio', $cohorte->anio)
                        ->whereRaw('SUBSTR(aspirantes.carrera, 1, 3) = ?', substr($cohorte->plan, 0, 3))
                        ->where('aspirantes.pago_curso', true)
                        ->count();

        // Devuelve la respuesta JSON con el número de aspirantes que han pagado el curso.
        return response()->json([
            'success' => true,
            'resultados'=>$aprobados
        ]);
    }

    /**
     * Obtiene el número de aspirantes que han aprobado el curso para una cohorte específica.
     *
     * @param Request $request El objeto de solicitud HTTP.
     * @param int $idCohorte El ID de la cohorte.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con el número de aspirantes que han aprobado el curso.
     */
    public function getAprobadosCurso(Request $request, $idCohorte){
        // Obtiene la cohorte correspondiente al ID proporcionado.
        $cohorte = Cohorte::find($idCohorte);

        // Cuenta el número de aspirantes que han aprobado el curso para la cohorte y carrera específicas.
        $aprobados = Aspirante::join('admisions', 'admisions.id', '=', 'aspirantes.idAdmision')
                        ->where('admisions.anio', $cohorte->anio)
                        ->whereRaw('SUBSTR(aspirantes.carrera, 1, 3) = ?', substr($cohorte->plan, 0, 3))
                        ->where('aspirantes.aprobo_curso', true)
                        ->count();

        // Devuelve la respuesta JSON con el número de aspirantes que han aprobado el curso.
        return response()->json([
            'success' => true,
            'resultados'=>$aprobados
        ]);
    }
}
