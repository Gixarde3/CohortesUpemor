<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BajaProcesada; // Import the missing class
use App\Models\Alumno;
class BajaProcesadaController extends Controller
{
    //
    public function getBajasByPeriodo(Request $request, $idCohorte){
        $bajas = BajaProcesada::join('alumnos', 'alumnos.id', '=', 'baja_procesadas.idAlumno')
        ->selectRaw('baja_procesadas.periodo, count(*) as total')
        ->where('alumnos.idCohorte', $idCohorte)
        ->groupBy('baja_procesadas.periodo')
        ->get();
        return response()->json([
            'success' => true,
            'resultados' => $bajas
        ]);
    }
    public function getBajas(Request $request, $idCohorte){
        $bajas = Alumno::join('baja_procesadas', 'baja_procesadas.idAlumno', '=', 'alumnos.id')
        ->selectRaw('SUM(IF(alumnos.activo, 1, 0)) as activos, 
            SUM(IF(alumnos.activo, 0, IF(baja_procesadas.bajaDefinitiva, 0, 1))) as temporal, 
            SUM(IF(alumnos.activo, 0, IF(baja_procesadas.bajaDefinitiva, 1, 0))) as definitiva')
        ->where('alumnos.idCohorte', $idCohorte)
        ->first();
        return response()->json([
            'success' => true,
            'resultados' => $bajas
        ]);
    }
}
