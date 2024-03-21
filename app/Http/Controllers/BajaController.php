<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Models\Usuario;
use App\Models\Baja;
use App\Imports\BajaImport;
use Maatwebsite\Excel\Facades\Excel; // Import the Excel class

class BajaController extends Controller
{
    //
    public function crearBajas(Request $request)
    {
        $admin = Usuario::where('token', $request->token)->where('tipoUsuario', '>=', 2)->first();
        if ($admin) {
            $file = $request->file('archivo');
            $fileName = $this->manejarArchivo($file);
            $newBaja = new Baja();
            $newBaja->archivo = $fileName;
            $newBaja->idUsuario = $admin->id;
            $newBaja->periodo = $request->periodo;
            $newBaja->procesado = false;
            $newBaja->save();
            $success = true;
            $message = 'Bajas registradas correctamente';
        } else {
            $success = false;
            $message = "No cuentas con los permisos necesarios";
        }

        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }
    public function eliminarBajas(Request $request, $id)
    {
        $admin = Usuario::where('token', $request->token)->where('tipoUsuario', '>=', 2)->first();
        if ($admin) {
            $baja = Baja::find($id);
            $this->deleteFile($baja->archivo);
            $baja->delete();
            $success = true;
            $message = 'Bajas eliminadas correctamente';
        } else {
            $success = false;
            $message = "No cuentas con los permisos necesarios";
        }

        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }
    public function actualizarBajas(Request $request, $id)
    {
        $admin = Usuario::where('token', $request->token)->where('tipoUsuario', '>=', 2)->first();
        if ($admin) {
            $baja = Baja::find($id);
            if($request->hasFile('archivo')){
                $this->deleteFile($baja->archivo);
                $file = $request->file('archivo');
                $fileName = $this->manejarArchivo($file);
                $baja->archivo = $fileName;
            };
            $baja->periodo = $request->periodo;
            $baja->save();
            $success = true;
            $message = 'Bajas actualizadas correctamente';
        } else {
            $success = false;
            $message = "No cuentas con los permisos necesarios";
        }

        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }
    public function descargarBajas(Request $request, $id)
    {
        $baja = Baja::find($id);
        if ($baja) {
            return $this->download($request, $baja->archivo);
        } else {
            return response()->json([
                'success' => false,
                'message' => "No se encontró la baja con ese ID"
            ]);
        }
    }

    public function getBajas(Request $request)
    {
        $bajas = Baja::all();
        $success = true;
        $message = 'Bajas obtenidas correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'bajas' => $bajas
        ]);
    }
    public function getBajaById(Request $request, $id){
        $baja = Baja::find($id);
        if($baja){
            $success = true;
            $message = 'Baja obtenida correctamente';
        }else{
            $success = false;
            $message = "No se encontró la baja con ese ID";
        }
        return response()->json([
            'success' => $success,
            'message' => $message,
            'baja' => $baja
        ]);
    }
    public function procesarBajas(Request $request, $id){
        $admin = Usuario::where('token', $request->token)->where('tipoUsuario', '>=', 2)->first();
        if ($admin) {
            $baja = Baja::find($id);
            if($baja && $baja->procesado == false){
                
                $archivo = $baja->archivo;
                $archivo = public_path('excel/'.$archivo);
                Excel::import(new BajaImport($id, $baja->periodo, $baja->idUsuario), $archivo); // Fix the undefined type error
                $baja->procesado = true;
                $baja->save();
                return response()->json([
                    'success' => true,
                    'message' => 'Calificaciones procesadas correctamente'
                ]);
            }else{
                $success = false;
                $message = "No se encontró una baja no procesada con ese ID";
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
