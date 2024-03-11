<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Aspirante;
class AspiranteController extends Controller
{
    //
    public function getAspirantesInscritos(Request $request, $id){
        $total = Aspirante::where('idAdmision', $id)->count();
        $inscritos = Aspirante::where('idAdmision', $id)->whereNotNull('idAlumno')->count();

        return response()->json([
            'success' => true,
            'resultados' => ['total' => $total,
                            'inscritos' => $inscritos]
        ]);
    }
    public function getAnioNacAspirantes(Request $request, $id){
        $anio = Aspirante::selectRaw('CONCAT(IF(SUBSTR(aspirantes.curp, 5, 2) > 30, 19, 20), SUBSTR(aspirantes.curp, 5, 2)) as anio, COUNT(*) as total')
        ->groupBy('anio')
        ->where('idAdmision', $id)
        ->get();
        return response()->json([
            'success' => true,
            'resultados' => $anio
        ]);
    }
}
