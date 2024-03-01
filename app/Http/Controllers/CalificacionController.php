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
    public function subirCalificacion(Request $request){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
        if ($admin) {
            $newCalificacion = new Calificacion();
            $newCalificacion->archivo = $this->manejarArchivo($request->file('archivo'));
            $newCalificacion->idCreador = $admin->id;
            $newCalificacion->carrera = $request->carrera;
            $newCalificacion->periodo = $request->periodo;
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
    public function editarCalificaciones(Request $request, $id){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
        if ($admin) {
            $calificacion = Calificacion::find($id);
            if($calificacion){
                if(request()->hasFile('archivo')){
                    $this->deleteFile($calificacion->archivo);
                    $calificacion->archivo = $this->manejarArchivo($request->file('archivo'));
                }
                $calificacion->carrera = $request->carrera;
                $calificacion->periodo = $request->periodo;
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
    public function eliminarCalificaciones(Request $request, $id){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
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
    public function download(Request $request, $filename)
    {
        // Define la ruta al archivo dentro de la carpeta de almacenamiento (por ejemplo, en la carpeta "public")
        $rutaArchivo = public_path('excel/'.$filename);

        // Obtén la archivo como una respuesta
        return response()->file($rutaArchivo, ['Content-Disposition' => 'attachment; filename="' . $filename . '"']);
    }

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
    public function getCalificaciones(Request $request){
        $calificaciones = Calificacion::all();
        return response()->json([
            'success' => true,
            'calificaciones' => $calificaciones
        ]);
    }
}
