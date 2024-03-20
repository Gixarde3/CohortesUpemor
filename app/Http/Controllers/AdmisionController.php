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
            if($admision && !$admision->procesado){
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
    public function getAprobadosCeneval(Request $request, $idCohorte){
        $cohorte = Cohorte::find($idCohorte);
        $aprobados = Aspirante::join('admisions', 'admisions.id', '=', 'aspirantes.idAdmision')
                        ->where('admisions.anio', $cohorte->anio)
                        ->whereRaw('SUBSTR(aspirantes.carrera, 1, 3) = ?', substr($cohorte->plan, 0, 3))
                        ->count();

        return response()->json([
            'success' => true,
            'resultados'=>$aprobados
        ]);
    }
    public function getAspirantesCurso(Request $request, $idCohorte){
        $cohorte = Cohorte::find($idCohorte);
        $aprobados = Aspirante::join('admisions', 'admisions.id', '=', 'aspirantes.idAdmision')
                        ->where('admisions.anio', $cohorte->anio)
                        ->whereRaw('SUBSTR(aspirantes.carrera, 1, 3) = ?', substr($cohorte->plan, 0, 3))
                        ->where('aspirantes.pago_curso', true)
                        ->count();

        return response()->json([
            'success' => true,
            'resultados'=>$aprobados
        ]);
    }
    public function getAprobadosCurso(Request $request, $idCohorte){
        $cohorte = Cohorte::find($idCohorte);
        $aprobados = Aspirante::join('admisions', 'admisions.id', '=', 'aspirantes.idAdmision')
                        ->where('admisions.anio', $cohorte->anio)
                        ->whereRaw('SUBSTR(aspirantes.carrera, 1, 3) = ?', substr($cohorte->plan, 0, 3))
                        ->where('aspirantes.aprobo_curso', true)
                        ->count();

        return response()->json([
            'success' => true,
            'resultados'=>$aprobados
        ]);
    }
}
