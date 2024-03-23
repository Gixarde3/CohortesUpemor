<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Calificacion;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
class CalificacionController extends Controller
{
    /**
     * Obtiene una calificación por su ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCalificacionById(Request $request, $id){
        $calificacion = Calificacion::find($id);
        if($calificacion){
            return response()->json([
                'success' => true,
                'calificacion' => $calificacion
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => "No se encontró la calificacion con ese ID"
            ]);
        }
    }
    /**
     * Sube una calificación al sistema.
     *
     * Esta función recibe una solicitud HTTP y realiza las siguientes acciones:
     * - Verifica si el usuario que realiza la solicitud es un administrador.
     * - Valida los campos de la solicitud.
     * - Crea una nueva instancia de la clase Calificacion y asigna los valores de los campos.
     * - Guarda la nueva calificación en la base de datos.
     * - Retorna una respuesta JSON indicando si la calificación se subió correctamente o si el usuario no tiene los permisos necesarios.
     *
     * @param Request $request La solicitud HTTP que contiene los datos de la calificación a subir.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON con la información sobre el éxito de la operación y un mensaje descriptivo.
     */
    public function subirCalificacion(Request $request){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 1)->first();
        $request->validate([
            'archivo' => 'required',
            'carrera' => 'required',
            'periodo' => 'required',
            'anio' => 'required',
            'anioperiodocarrera' => 'unique_concat:calificaciones,anio,periodo,carrera'
        ],[
            'anioperiodo.unique_concat' => 'El conjunto de año y el periodo ya han sido registrados'
        ]);
        if ($admin) {
            $newCalificacion = new Calificacion();
            $newCalificacion->archivo = $this->manejarArchivo($request->file('archivo'));
            $newCalificacion->idCreador = $admin->id;
            $newCalificacion->carrera = $request->carrera;
            $newCalificacion->periodo = $request->periodo;
            $newCalificacion->programa = $request->programa;
            $newCalificacion->anio = $request->anio;
            $success = true;
            $message = "Calificación subida correctamente";
            $newCalificacion->save();
        } else {
            $success = false;
            $message = "No cuentas con los permisos necesarios";
        }
        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }
    /**
     * Edita las calificaciones.
     *
     * Esta función recibe una solicitud HTTP y un ID de calificación como parámetros de entrada.
     * Verifica si el usuario que realiza la solicitud es un administrador válido.
     * Si el usuario es un administrador válido, busca la calificación correspondiente al ID proporcionado.
     * Si la calificación existe, actualiza los campos de la calificación con los valores proporcionados en la solicitud.
     * Si se adjunta un archivo en la solicitud, elimina el archivo anterior asociado a la calificación y guarda el nuevo archivo adjunto.
     * Finalmente, guarda los cambios realizados en la calificación y devuelve una respuesta JSON indicando si la edición fue exitosa y un mensaje correspondiente.
     *
     * @param Request $request La solicitud HTTP que contiene los datos de la calificación a editar.
     * @param int $id El ID de la calificación a editar.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON que indica si la edición fue exitosa y un mensaje correspondiente.
     */
    public function editarCalificaciones(Request $request, $id){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 1)->first();
        if ($admin) {
            $calificacion = Calificacion::find($id);
            if($calificacion){
                if(request()->hasFile('archivo')){
                    $this->deleteFile($calificacion->archivo);
                    $calificacion->archivo = $this->manejarArchivo($request->file('archivo'));
                }
                $calificacion->carrera = $request->carrera;
                $calificacion->anio = $request->anio;
                $calificacion->periodo = $request->periodo;
                $calificacion->programa = $request->programa;
                $calificacion->save();
                $success = true;
                $message = "Calificación editada correctamente";
            } else {
                $success = false;
                $message = "No se encontró la calificación";
            }
        } else {
            $success = false;
            $message = "No cuentas con los permisos necesarios";
        }
        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }
    /**
     * Elimina una calificación según su ID.
     *
     * @param Request $request El objeto Request que contiene los datos de la solicitud.
     * @param int $id El ID de la calificación a eliminar.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON que indica si la calificación se eliminó correctamente o no.
     */
    public function eliminarCalificaciones(Request $request, $id){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 1)->first();
        if ($admin) {
            $calificacion = Calificacion::find($id);
            if($calificacion){
                $this->deleteFile($calificacion->archivo);
                $calificacion->delete();
                $success = true;
                $message = "Calificación eliminada correctamente";
            } else {
                $success = false;
                $message = "No se encontró la calificación";
            }
        } else {
            $success = false;
            $message = "No cuentas con los permisos necesarios";
        }
        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }
    /**
     * Descarga una calificación específica.
     *
     * @param Request $request El objeto de solicitud HTTP.
     * @param int $id El ID de la calificación a descargar.
     * @return mixed La respuesta de descarga del archivo de la calificación si se encuentra, o una respuesta JSON con un mensaje de error si no se encuentra la calificación.
     */
    public function downloadCalificacion(Request $request, $id){
        $calificacion = Calificacion::find($id);
        if($calificacion){
            return $this->download($request, $calificacion->archivo);
        }else{
            return response()->json([
                'success' => false,
                'message' => "No se encontró la calificacion con ese ID"
            ]);
        }
    }
    /**
     * Maneja el archivo recibido y lo guarda en una ubicación específica.
     *
     * @param  mixed  $file  El archivo a manejar.
     * @return string  El nombre del archivo guardado.
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
     * Descarga el archivo con el nombre de archivo especificado.
     *
     * @param  \Illuminate\Http\Request  $request  La solicitud HTTP actual.
     * @param  string  $filename  El nombre del archivo a descargar.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse  La respuesta HTTP con el archivo adjunto.
     */
    public function download(Request $request, $filename)
    {
        // Define la ruta al archivo dentro de la carpeta de almacenamiento (por ejemplo, en la carpeta "public")
        $rutaArchivo = public_path('excel/'.$filename);

        // Obtén el archivo como una respuesta
        return response()->file($rutaArchivo, ['Content-Disposition' => 'attachment; filename="' . $filename . '"']);
    }

    /**
     * Elimina el archivo con el nombre de archivo especificado.
     *
     * @param  string  $fileName  El nombre del archivo a eliminar.
     * @return bool  Verdadero si el archivo se eliminó correctamente, falso de lo contrario.
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
     * Obtiene todas las calificaciones.
     *
     * @param  \Illuminate\Http\Request  $request  La solicitud HTTP actual.
     * @return \Illuminate\Http\JsonResponse  La respuesta HTTP con las calificaciones en formato JSON.
     */
    public function getCalificaciones(Request $request)
    {
        $calificaciones = Calificacion::all();
        return response()->json([
            'success' => true,
            'calificaciones' => $calificaciones
        ]);
    }
}
