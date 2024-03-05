<?php

namespace App\Http\Controllers;

use App\Imports\AdminisionesMultiImport;
use Illuminate\Http\Request;
use App\Models\Admision;
use App\Models\Usuario;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
class AdmisionController extends Controller
{
    //
    public function crearAdmision(Request $request){
        $admin = Usuario::where('token', $request->token)->where('tipoUsuario', '>=', 3)->first();
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
    public function editarAdmision(Request $request, $id){
        $admin = Usuario::where('token', $request->token)->where('tipoUsuario', '>=', 3)->first();
        $admision = Admision::find($id);
        if($admin){
            if($admision){
                if(request()->hasFile('archivo')){
                    $this->deleteFile($admision->archivo);
                    $admision->archivo = $this->manejarArchivo($request->file('archivo'));
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
    public function eliminarAdmision(Request $request, $id){
        $admin = Usuario::where('token', $request->token)->where('tipoUsuario', '>=', 3)->first();
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
    public function getAdmisiones(Request $request)
    {
        $admisiones = Admision::all();
        return response()->json([
            'success' => true,
            'admisiones' => $admisiones
        ]);
    }
    public function descargarAdmision(Request $request, $id){
        $admision = Admision::find($id);
        if($admision){
            return $this->download($request, $admision->archivo);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la admisión'
            ]);
        }
    }
    public function procesarAdmision(Request $request, $id){
        $admin = Usuario::where('token', $request->token)->where('tipoUsuario', '>=', 3)->first();
        $admision = Admision::find($id);
        if($admin){
            if($admision){
                $archivo = $admision->archivo;
                $archivo = public_path('excel/'.$archivo);
                Excel::import(new AdminisionesMultiImport($admision->id, $admin->id), $archivo); // Fix the undefined type error
                $admision->procesado = true;
                $admision->save();
                return response()->json([
                    'success' => true,
                    'message' => 'Admisión procesada correctamente'
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
}
