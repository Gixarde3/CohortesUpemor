<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Aspirante;
class AspiranteController extends Controller
{
    //
    public function getAspirantesInscritos(Request $request, $anio1, $anio2, $carrera){
        if($anio1 > $anio2) 
            return response()->json(['success' => false, 'message' => 'El año 1 debe ser menor al año 2']);

        $aspirantes = Aspirante::join('admisions', 'admisions.id', '=', 'aspirantes.idAdmision')
                                    ->join('alumnos', 'alumnos.id', '=', 'aspirantes.idAlumno')
                                    ->join('cohortes', 'cohortes.id', '=', 'alumnos.idCohorte')
                                    ->whereNotNull('aspirantes.idAlumno') 
                                    ->whereBetween('admisions.anio', [$anio1, $anio2])
                                    ->whereRaw('SUBSTR(cohortes.plan, 1, 3) = ?', [$carrera])
                                    ->selectRaw('COUNT(*) as total, admisions.anio')
                                    ->groupBy('admisions.anio')
                                    ->get();
    
        return response()->json([
            'success' => true,
            'resultados' => $aspirantes
        ]);
        
    }
    public function getAprobadosCeneval(Request $request, $anio1, $anio2, $carrera){
        if($anio1 > $anio2) 
            return response()->json(['success' => false, 'message' => 'El año 1 debe ser menor al año 2']);

        $aspirantes = Aspirante::join('admisions', 'admisions.id', '=', 'aspirantes.idAdmision')
                            ->whereBetween('admisions.anio', [$anio1, $anio2])
                            ->whereRaw('SUBSTR(aspirantes.carrera, 1, 3) = ?', [$carrera])
                            ->selectRaw('COUNT(*) as total, admisions.anio')
                            ->groupBy('admisions.anio')
                            ->get();
        return response()->json([
            'success' => true,
            'resultados' => $aspirantes
        ]);
    }
}
