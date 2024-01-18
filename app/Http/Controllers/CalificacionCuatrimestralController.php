<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Excels;
use App\Models\CalificacionCuatrimestral; // Add this line
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
class CalificacionCuatrimestralController extends Controller
{
    public function calificaciones(Request $request){
        $calificaciones = CalificacionCuatrimestral::select('calificacion_cuatrimestrals.*', 'excels.archivo', 'excels.procesado')->join('excels','calificacion_cuatrimestrals.idArchivo', '=', 'excels.id')->get();
        $success = true;
        $message = 'Calificaciones obtenidas correctamente';
        return response()->json([
            'success' => $success,
            'calificaciones' => $calificaciones,
            'message' => $message
        ]);
    }
    //
    public function subirCalificacion(Request $request){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
        if ($admin) {
            $newCalificaciones = new CalificacionCuatrimestral();
            $newFile = new Excels();
            $newCalificaciones->periodo = $request->periodo;
            $newCalificaciones->carrera = $request->carrera;
            $newCalificaciones->anio = $request->anio;
            $newCalificaciones->programaEducativo = $request->programaEducativo;
            $file = $request->file('archivo');
            $extension = $file->getClientOriginalExtension();
            $allowedExtensions = ['xls', 'xlsx'];
            if (in_array($extension, $allowedExtensions)) {
                $newFile->archivo = $this->manejarArchivo($request->file('archivo'));
                $newFile->tipo = "Calificaciones Cuatrimestrales";
                $newFile->save();
                $newCalificaciones->idArchivo = $newFile->id;
            } else {
                $success = false;
                $message = "El archivo debe ser un Excel válido";
                return response()->json([
                    'success' => $success,
                    'message' => $message
                ]);
            }
            $newCalificaciones->idCreador = $admin->id;
            $newCalificaciones->save();
            $success = true;
            $message = 'Calificaciones subidas correctamente';
        } else {
            $success = false;
            $message = "No cuentas con los permisos necesarios";
        }
        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }
    public function getCalificacionById(Request $request, $id){
        $calificaciones = CalificacionCuatrimestral::select('calificacion_cuatrimestrals.*', 'excels.archivo', 'excels.procesado')->join('excels','calificacion_cuatrimestrals.idArchivo', '=', 'excels.id')->where('calificacion_cuatrimestrals.id', '=', $id)->first();

        if($calificaciones){
            $success = true;
            $message = 'Calificaciones obtenidas correctamente';
        }else{
            $success = false;
            $message = 'No se encontraron las calificaciones';
        }
        return response()->json([
            'success' => $success,
            'calificaciones' => $calificaciones,
            'message' => $message
        ]);
    }
    public function actualizarCalificaciones(Request $request, $id)
    {
        $admin = Usuario::where('token', $request->token)->where('tipoUsuario', '>=', 3)->first();
        if ($admin) {
            $calificaciones = CalificacionCuatrimestral::find($id);
            $archivo = Excels::find($calificaciones->idArchivo);
            $calificaciones->periodo = $request->periodo;
            $calificaciones->carrera = $request->carrera;
            $calificaciones->anio = $request->anio;
            $calificaciones->programaEducativo = $request->programaEducativo;
            if ($request->has(('archivo'))) {
                $file = $request->file('archivo');
                $extension = $file->getClientOriginalExtension();
                $allowedExtensions = ['xls', 'xlsx'];
                if (in_array($extension, $allowedExtensions)) {
                    $this->deleteFile($archivo->archivo);
                    $archivo->archivo = $this->manejarArchivo($file);
                    $archivo->save();
                } else {
                    $success = false;
                    $message = "El archivo debe ser un Excel válido";
                    return response()->json([
                        'success' => $success,
                        'message' => $message
                    ]);
                }
            }
            $calificaciones->idCreador = $admin->id;
            $calificaciones->save();
            $success = true;
            $message = 'Calificaciones actualizadas correctamente';
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
            $calificaciones = CalificacionCuatrimestral::select('calificacion_cuatrimestrals.*', 'excels.archivo', 'excels.procesado')->join('excels','calificacion_cuatrimestrals.idArchivo', '=', 'excels.id')->where('calificacion_cuatrimestrals.id', '=', $id)->first();
            $excel = Excels::find($calificaciones->idArchivo);
            if($calificaciones){
                $this->deleteFile($calificaciones->archivo);
                $calificaciones->delete();
                $excel->delete();
                $success = true;
                $message = 'Calificaciones eliminadas correctamente';
            }else{
                $success = false;
                $message = 'No se encontraron las calificaciones';
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

        // Obtén el archivo como una respuesta
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
}
