<?php

namespace App\Http\Controllers;

use App\Mail\MailRestauracion;
use App\Models\Usuario; // Asegúrate de importar el modelo Usuario
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $usuario = Usuario::where('email', $request->input('email'))->first();

        if (!$usuario || !password_verify($request->input('password'), $usuario->password)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        // Generar un token aleatorio
        $token = Str::random(60);

        // Asignar el token generado al usuario actual y guardarlo en la base de datos
        $usuario->token = $token;
        $usuario->save();

        // Devolver los datos solicitados en formato JSON
        return response()->json([
            'token' => $token,
            'email' => $usuario->email,
            'tipoUsuario' => $usuario->tipoUsuario,
            'success'=>true
        ]);
    }

    public function sendRestoreMail(Request $request)
    {
        $usuario = Usuario::where('email', $request->input('email'))->first();

        if (!$usuario) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        // Generar un token aleatorio
        $token = Str::random(60);


        // Asignar el token generado al usuario actual y guardarlo en la base de datos
        $usuario->recuperacion = $token;
        $usuario->save();

        $url = "localhost:5173/restaurar/$token";
        Mail::to($request->email)->send(new MailRestauracion($url));
        // Devolver los datos solicitados en formato JSON
        return response()->json([
            'message' => 'Correo enviado',
            'success' => true
        ]);
    }

    public function restorePassword(Request $request, $hash)
    {
        $usuario = Usuario::where('recuperacion', $hash)->first();

        if (!$usuario) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        $usuario->password = $request->input('password');

        // Generar un token aleatorio
        $token = Str::random(60);

        // Asignar el token generado al usuario actual y guardarlo en la base de datos
        $usuario->token = $token;
        $usuario->save();

        // Devolver los datos solicitados en formato JSON
        return response()->json([
            'success' => true
        ]);
    }

    public function verifyHash(Request $request, $hash){
        $usuario = Usuario::where('recuperacion', $hash)->first();

        if (!$usuario) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        return response()->json([
            'success' => true
        ]);
    }
}
