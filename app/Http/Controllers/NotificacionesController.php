<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notificaciones;
use App\Models\Usuario;
use Illuminate\Notifications\Notification;

class NotificacionesController extends Controller
{
    //
    public function getNotificaciones(Request $request, $token){
        $usuario = Usuario::where('token',$token)->first();
        $notificaciones = Notificaciones::where('id_usuario',$usuario->id)->get();
        foreach ($notificaciones as $notificacion) {
            $notificacion->vista = true;
            $notificacion->save();
        }
        return response()->json([
            'success' => true,
            'notificaciones' => $notificaciones,
            'message' => 'Notificaciones obtenidas correctamente'
        ]);
    }
    public function getNotificacionesPendientes(Request $request, $token){
        $usuario = Usuario::where('token',$token)->first();
        $cantidad = Notificaciones::where('id_usuario', $usuario->id)->where('vista', false)->count();
        return response()->json([
            'success' => true,
            'cantidad' => $cantidad,
            'message' => 'Notificaciones no vistas obtenidas correctamente'
        ]);
    }
}
