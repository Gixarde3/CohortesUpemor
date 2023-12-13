<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class UsuarioController extends Controller
{
    //
    public function register(Request $request){
        try {
            $this->validate($request, [
                'email' => 'required|email|unique:users'
            ]);
            $request->validate([
                'foto'=>'required|image'
            ]);
            $newUser = new Usuario();
            $newUser->noEmp = $request->noEmp;
            $newUser->nombre = $request->nombre;
            $newUser->apP = $request->apP;
            $newUser->apM = $request->apM;
            $newUser->tipoUsuario = 3;
            $newUser->email = $request->email;
            $newUser->password = $request->password;
            $newUser->foto = $this->manejarImagenes($request->file('foto'));
            $newUser->recuperacion = null;
            $cookie = Str::random(60);
            $newUser->token = $cookie;
            $newUser->save();
            $success = true;
            $message = 'Usuario registrado correctamente';
        } catch (ValidationException $e) {
            $message = $e->getMessage();
            $success = false;
            $cookie = '';
        }
        return response()->json([
            'success'=> $success,
            'token'=>$cookie,
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
}

