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
    /**
     * Obtiene las notificaciones de un usuario y las marca como vistas.
     *
     * @param Request $request El objeto de solicitud HTTP.
     * @param string $token El token de autenticación del usuario.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con las notificaciones obtenidas.
     */
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
    /**
     * Obtiene la cantidad de notificaciones pendientes para un usuario específico.
     *
     * @param Request $request El objeto de solicitud HTTP.
     * @param string $token El token de autenticación del usuario.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con la cantidad de notificaciones pendientes y un mensaje de éxito.
     */
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
