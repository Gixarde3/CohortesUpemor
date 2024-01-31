<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Cohorte;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CohorteController extends Controller
{
    
    /**
         * Crea un nuevo Cohorte.
         *
         * @param Request $request El objeto de solicitud HTTP.
         * @return \Illuminate\Http\JsonResponse La respuesta JSON que contiene el estado de éxito y el mensaje.
         */

    public function createCohorte(Request $request){

        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
        if ($admin) {
            $newCohorte = new Cohorte();
            $newCohorte->periodo = $request->periodo;
            $newCohorte->anio = $request->anio;
            $newCohorte->plan = $request->plan;
            $newCohorte->idCreador = $admin->id;
            $newCohorte->save();
            $success = true;
            $message = 'Cohorte registrado correctamente';
        }else{
            $success = false;
            $message = "No cuentas con los permisos necesarios";
        }
        
        return response()->json([
            'success'=> $success,
            'message'=>$message
        ]);
    }
    
    /**
     * Edita un cohorte existente.
     *
     * @param Request $request La solicitud HTTP recibida.
     * @param int $id El ID del cohorte a editar.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con el resultado de la operación.
     */
    public function editCohorte(Request $request, $id){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
        if ($admin) {
            $cohort = Cohorte::find($id);
            $cohort->periodo = $request->periodo;
            $cohort->anio = $request->anio;
            $cohort->plan = $request->plan;
            $cohort->save();
            $success = true;
            $message = 'Cohorte editado correctamente';
        }else{
            $success = false;
            $message = "No cuentas con los permisos necesarios";
        }
        
        return response()->json([
            'success'=> $success,
            'message'=>$message
        ]);
    }
    /**
     * Elimina un cohorte.
     *
     * @param Request $request La solicitud HTTP recibida.
     * @param int $id El ID del cohorte a eliminar.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON que indica si se eliminó correctamente el cohorte.
     */
    public function deleteCohorte(Request $request, $id){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
        if ($admin) {
            $cohort = Cohorte::find($id);
            $cohort->delete();
            $success = true;
            $message = 'Cohorte eliminado correctamente';
        }else{
            $success = false;
            $message = "No cuentas con los permisos necesarios";
        }
        
        return response()->json([
            'success'=> $success,
            'message'=>$message
        ]);
    }

    /**
     * Obtiene todos los cohortes.
     *
     * @param Request $request La solicitud HTTP recibida.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON que contiene todos los cohortes obtenidos.
     */
    public function getAllCohortes(Request $request){
        $cohortes = Cohorte::all();
        $success = true;
        $message = 'Cohortes obtenidos correctamente';
        return response()->json([
            'success'=> $success,
            'cohortes'=>$cohortes,
            'message'=>$message
        ]);
    }

    /**
     * Obtiene un cohorte por su ID.
     *
     * @param Request $request La solicitud HTTP recibida.
     * @param int $id El ID del cohorte a obtener.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON que contiene el cohorte obtenido.
     */
    public function getCohorteById(Request $request, $id){
        $cohorte = Cohorte::find($id);
        $success = true;
        $message = 'Cohorte obtenido correctamente';
        return response()->json([
            'success'=> $success,
            'cohorte'=>$cohorte,
            'message'=>$message
        ]);
    }
    public function subirCalificacion(Request $request, $id){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
        if ($admin) {
            $cohorte = Cohorte::find($id);
            if($cohorte){
                $cohorte->archivo = $this->manejarArchivo($request->archivo);
                $cohorte->save();
            }else{
                $success = false;
                $message = "No se encontró el cohorte con ese ID";
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
    public function eliminarCalificacion(Request $request, $id){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
        if ($admin) {
            $cohorte = Cohorte::find($id);
            if($cohorte){
                $cohorte->archivo = null;
                $cohorte->save();
                $this->deleteFile($request->archivo);
            }else{
                $success = false;
                $message = "No se encontró el cohorte con ese ID";
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
        $cohorte = Cohorte::find($id);
        if($cohorte){
            return $this->download($request, $cohorte->archivo);
        }else{
            return response()->json([
                'success' => false,
                'message' => "No se encontró el cohorte con ese ID"
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
