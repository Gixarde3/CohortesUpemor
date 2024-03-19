<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notificaciones;
use App\Models\Usuario;
class NotificacionesController extends Controller
{
    //
    public function getNotificaciones(Request $request, $token){
        $usuario = Usuario::where('token',$token)->first();
        $notificaciones = Notificaciones::where('id_usuario',$usuario->id)->get();
        return response()->json([
            'success' => true,
            'notificaciones' => $notificaciones,
            'message' => 'Notificaciones obtenidas correctamente'
        ]);
    }
}
