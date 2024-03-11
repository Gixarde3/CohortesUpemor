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

        if(!$usuario){
            return response()->json(['error' => 'mail'], 401);
        }

        if(!password_verify($request->input('password'), $usuario->password)){
            return response()->json(['error' => 'password'], 401);
        }

        if(!$usuario->tipoUsuario >= 1){
            return response()->json(['error' => 'Espera a que un administrador te verifique'], 403);
        }

        if(!$usuario->activo){
            return response()->json(['error' => 'Tu cuenta ha sido desactivada'], 403);
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

        // Generar un token aleatorio de 4 numeros
        $token = rand(100000, 999999);


        // Asignar el token generado al usuario actual y guardarlo en la base de datos
        $usuario->recuperacion = $token;
        $usuario->save();

        $url = "localhost:5173/restaurar";
        Mail::to($request->email)->send(new MailRestauracion($url, $token));
        // Devolver los datos solicitados en formato JSON
        return response()->json([
            'message' => 'Correo enviado',
            'success' => true
        ]);
    }

    public function restorePassword(Request $request, $hash)
    {
        $usuario = Usuario::where('token', $hash)->first();

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
        $usuario = Usuario::where('token', $hash)->first();

        if (!$usuario) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        return response()->json([
            'success' => true
        ]);
    }

    public function verifyCode(Request $request, $code){
        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        $token = Str::random(60);

        $usuario->token = $token;
        $usuario->save();
        if($usuario->recuperacion == $code){
            return response()->json([
                'success' => true,
                'token' => $token
            ]);
        }else{
            return response()->json([
                'error' => 'Credenciales inválidas'
            ]);
        }
    }
}
