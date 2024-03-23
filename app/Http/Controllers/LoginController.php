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
    /**
     * Realiza el proceso de inicio de sesión.
     *
     * Esta función recibe una solicitud HTTP con los datos de inicio de sesión del usuario.
     * Verifica si el usuario existe en la base de datos y si la contraseña proporcionada es correcta.
     * Si el usuario no existe, se devuelve un error de correo electrónico.
     * Si la contraseña es incorrecta, se devuelve un error de contraseña.
     * Si el usuario no tiene un nivel de acceso suficiente, se devuelve un error de acceso denegado.
     * Si la cuenta del usuario está desactivada, se devuelve un error de cuenta desactivada.
     * Si todo es correcto, se genera un token aleatorio, se asigna al usuario y se guarda en la base de datos.
     * Finalmente, se devuelve una respuesta JSON con el token, el correo electrónico del usuario, el tipo de usuario y un indicador de éxito.
     *
     * @param Request $request La solicitud HTTP con los datos de inicio de sesión del usuario.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con los datos solicitados.
     */
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

    /**
     * Envía un correo electrónico de restauración al usuario.
     *
     * Esta función recibe una solicitud HTTP y busca un usuario en la base de datos
     * con el correo electrónico proporcionado en la solicitud. Si el usuario no existe,
     * se devuelve una respuesta JSON con un mensaje de error. Si el usuario existe,
     * se genera un token aleatorio de 6 números y se asigna al usuario actual en la
     * base de datos. Luego, se envía un correo electrónico de restauración al usuario
     * con el token generado. Finalmente, se devuelve una respuesta JSON con un mensaje
     * de éxito.
     *
     * @param Request $request La solicitud HTTP que contiene el correo electrónico del usuario.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con el mensaje de éxito o error.
     */
    public function sendRestoreMail(Request $request)
    {
        $usuario = Usuario::where('email', $request->input('email'))->first();

        if (!$usuario) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        // Generar un token aleatorio de 6 numeros
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

    /**
     * Restaura la contraseña de un usuario.
     *
     * Esta función recibe una solicitud HTTP y un hash de token como parámetros de entrada.
     * Busca un usuario en la base de datos que tenga el token proporcionado.
     * Si no se encuentra ningún usuario con el token, devuelve un error en formato JSON.
     * Si se encuentra el usuario, actualiza su contraseña con el valor proporcionado en la solicitud.
     * Genera un nuevo token aleatorio y lo asigna al usuario actual.
     * Guarda los cambios en la base de datos.
     * Devuelve una respuesta en formato JSON indicando el éxito de la operación.
     *
     * @param Request $request La solicitud HTTP que contiene la nueva contraseña.
     * @param string $hash El hash de token utilizado para buscar al usuario.
     * @return \Illuminate\Http\JsonResponse Una respuesta en formato JSON indicando el éxito de la operación.
     */
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

    /**
     * Verifica el hash de un usuario.
     *
     * Esta función recibe una solicitud HTTP y un hash como parámetros de entrada.
     * Busca un usuario en la base de datos que tenga el token igual al hash proporcionado.
     * Si no se encuentra ningún usuario con el token dado, se devuelve una respuesta JSON con un error y un código de estado 401.
     * Si se encuentra un usuario con el token dado, se devuelve una respuesta JSON con un indicador de éxito y un código de estado 200.
     *
     * @param  \Illuminate\Http\Request  $request  La solicitud HTTP recibida.
     * @param  string  $hash  El hash a verificar.
     * @return \Illuminate\Http\JsonResponse  La respuesta JSON con el resultado de la verificación.
     */
    public function verifyHash(Request $request, $hash){
        $usuario = Usuario::where('token', $hash)->first();

        if (!$usuario) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Verifica el código de un usuario.
     *
     * Esta función recibe una solicitud HTTP y un código como parámetros de entrada.
     * Busca un usuario en la base de datos que tenga el correo electrónico igual al proporcionado en la solicitud.
     * Si no se encuentra ningún usuario con el correo electrónico dado, se devuelve una respuesta JSON con un error y un código de estado 401.
     * Si se encuentra un usuario con el correo electrónico dado, se genera un token aleatorio y se actualiza el token del usuario en la base de datos.
     * Si el código de recuperación del usuario coincide con el código proporcionado, se devuelve una respuesta JSON con un indicador de éxito, el token generado y un código de estado 200.
     * Si el código de recuperación del usuario no coincide con el código proporcionado, se devuelve una respuesta JSON con un error y un código de estado 200.
     *
     * @param  \Illuminate\Http\Request  $request  La solicitud HTTP recibida.
     * @param  string  $code  El código a verificar.
     * @return \Illuminate\Http\JsonResponse  La respuesta JSON con el resultado de la verificación.
     */
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
