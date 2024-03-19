<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailNotificacion;
use App\Mail\MailNotificacionNuevo;
use App\Mail\MailNotificacionUsuario;
use App\Mail\NotificacionDesactivar;
use App\Mail\NotificacionReactivar;
use App\Models\Notificaciones;
use Mockery\Matcher\Not;

class UsuarioController extends Controller
{
    //
    public function register(Request $request){
        try {
            if($request->tipoUsuario != 0){
                $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
                if ($admin) {
                    $this->validate($request, [
                        'email' => 'required|email|unique:usuarios'
                    ]);
                    $request->validate([
                        'foto'=>'required|image'
                    ]);
                    $newUser = new Usuario();
                    $newUser->noEmp = $request->noEmp;
                    $newUser->nombre = $request->nombre;
                    $newUser->apP = $request->apP;
                    $newUser->apM = $request->apM;
                    $newUser->tipoUsuario = $request->tipoUsuario;
                    $newUser->email = $request->email;
                    $newUser->password = $request->password;
                    $newUser->foto = $this->manejarImagenes($request->file('foto'));
                    $newUser->recuperacion = null;
                    $cookie = Str::random(60);
                    $newUser->token = $cookie;
                    $newUser->save();
                    $success = true;
                    $message = 'Usuario registrado correctamente';
                    Mail::to($admin->email)->send(new MailNotificacion($request->email));
                    Mail::to($request->email)->send(new MailNotificacionNuevo($request->email));
                }else{
                    $success = false;
                    $message = "No cuentas con los permisos necesarios";
                }
            }else{
                $this->validate($request, [
                    'email' => 'required|email|unique:usuarios'
                ]);
                $request->validate([
                    'foto'=>'required|image'
                ]);
                $newUser = new Usuario();
                $newUser->noEmp = $request->noEmp;
                $newUser->nombre = $request->nombre;
                $newUser->apP = $request->apP;
                $newUser->apM = $request->apM;
                $newUser->tipoUsuario = $request->tipoUsuario;
                $newUser->email = $request->email;
                $newUser->password = $request->password;
                $newUser->foto = $this->manejarImagenes($request->file('foto'));
                $newUser->recuperacion = null;
                $cookie = Str::random(60);
                $newUser->token = $cookie;
                $newUser->save();
                $success = true;
                $message = 'Usuario registrado correctamente';
                $admins = Usuario::where('tipoUsuario','>=', 3)->get();
                foreach($admins as $admin){
                    Mail::to($admin->email)->send(new MailNotificacionNuevo($request->email));
                    Notificaciones::create([
                        'id_usuario' => $admin->id,
                        'titulo' => 'Nuevo usuario registrado',
                        'descripcion' => "Nuevo usuario registrado: ".$request->email
                    ]);
                }
            }
        } catch (ValidationException $e) {
            $message = $e->getMessage();
            $success = false;
        }
        return response()->json([
            'success'=> $success,
            'email'=>$request->email,
            'message'=>$message
        ]);
    }
    public function getUserByHash(Request $request, $hash){
        $user = Usuario::where('token', $hash)->first();
        return response()->json([
            'success' => true,
            'usuario' => $user
        ]);
    }
    public function getAllUsers(Request $request){
        $users = Usuario::all();
        return response()->json([
            'success' => true,
            'usuarios' => $users
        ]);
    }
    public function getUserById(Request $request, $id){
        $user = Usuario::where('id', $id)->first();
        return response()->json([
            'success' => true,
            'usuario' => $user
        ]);
    }
    public function editUser(Request $request){
        try {
            $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
            if ($admin) {
                $user = Usuario::where('id', $request->id)->first();
                $user->noEmp = $request->noEmp;
                $user->nombre = $request->nombre;
                $user->apP = $request->apP;
                $user->apM = $request->apM;
                if($user->tipoUsuario != $request->tipoUsuario){
                    Mail::to($user->email)->send(new MailNotificacionUsuario($request->tipoUsuario === 1 ? "Director" : ($request->tipoUsuario === 2 ? "Coordinador/Profesor" : "Administrador")));
                    Notificaciones::create([
                        'id_usuario' => $user->id,
                        'titulo' => 'Cambio de tipo de usuario',
                        'descripcion' => "Tu tipo de usuario ha sido cambiado a: ".($request->tipoUsuario === 1 ? "Director" : ($request->tipoUsuario === 2 ? "Coordinador/Profesor" : "Administrador"))
                    ]);
                }
                $user->tipoUsuario = $request->tipoUsuario;
                $user->email = $request->email;
                if($request->has('password')){
                    $user->password = $request->password;
                }
                if($request->has('foto')){
                    $request->validate([
                        'foto'=>'required|image'
                    ]);
                    $user->foto = $this->manejarImagenes($request->file('foto'));
                }
                $user->save();
                $success = true;
                $message = 'Usuario editado correctamente';
            }else{
                $success = false;
                $message = "No cuentas con los permisos necesarios";
            }
        } catch (ValidationException $e) {
            $message = $e->getMessage();
            $success = false;
        }
        return response()->json([
            'success'=> $success,
            'email'=>$request->email,
            'message'=>$message
        ]);
    }
    public function deleteUser(Request $request){
        try {
            $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
            if ($admin) {
                $user = Usuario::where('id', $request->id)->first();
                $user->delete();
                $success = true;
                $message = 'Usuario eliminado correctamente';
            }else{
                $success = false;
                $message = "No cuentas con los permisos necesarios";
            }
        } catch (ValidationException $e) {
            $message = $e->getMessage();
            $success = false;
        }
        return response()->json([
            'success'=> $success,
            'email'=>$request->email,
            'message'=>$message
        ]);
    }
    public function manejarImagenes($file){
        $nameFile = uniqid();
        $extensionFile = '.' . $file->getClientOriginalExtension();
        $file->storeAs('public/',$nameFile.$extensionFile);
        $storageRoute = storage_path('app/public/'.$nameFile.$extensionFile);
        $publicRoute = public_path('perfiles/'.$nameFile.$extensionFile);
        File::move($storageRoute,$publicRoute);
        Storage::delete($storageRoute);
        return $nameFile.$extensionFile;
    }
    public function desactivar(Request $request, $id){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
        if ($admin) {
            $user = Usuario::where('id', $id)->first();
            $user->activo = false;
            $user->save();
            $success = true;
            $message = 'Usuario desactivado correctamente';
            Mail::to($user->email)->send(new NotificacionDesactivar());
        }else{
            $success = false;
            $message = "No cuentas con los permisos necesarios";
        }
        return response()->json([
            'success'=> $success,
            'message'=>$message
        ]);
    }
    public function activar(Request $request, $id){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
        if ($admin) {
            $user = Usuario::where('id', $id)->first();
            $user->activo = true;
            $user->save();
            $success = true;
            $message = 'Usuario reactivado correctamente';
            Mail::to($user->email)->send(new NotificacionReactivar());
        }else{
            $success = false;
            $message = "No cuentas con los permisos necesarios";
        }
        return response()->json([
            'success'=> $success,
            'message'=>$message
        ]);
    }
}

