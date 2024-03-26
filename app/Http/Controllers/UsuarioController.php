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
use App\Mail\MailNotificacionNuevoAdmin;
use App\Models\Notificaciones;
use Mockery\Matcher\Not;

class UsuarioController extends Controller
{
    //
    /**
     * Registra un nuevo usuario.
     *
     * Esta función registra un nuevo usuario en el sistema. Verifica los permisos del administrador y valida los datos de entrada.
     * Si el administrador tiene los permisos necesarios, se crea un nuevo usuario con los datos proporcionados y se envían notificaciones por correo electrónico.
     * Si el administrador no tiene los permisos necesarios, se muestra un mensaje de error.
     * Si el tipo de usuario es 0, se crea un nuevo usuario sin verificar los permisos del administrador y se envían notificaciones por correo electrónico a todos los administradores.
     *
     * @param Request $request La solicitud HTTP con los datos del usuario a registrar.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON que indica si el usuario se registró correctamente, el correo electrónico del usuario y un mensaje de éxito o error.
     */
    public function register(Request $request){
        try {
            if($request->tipoUsuario != 0){
                $admin = Usuario::where('token',$request->token)->
                    where('tipoUsuario','>=', 3)->first();
                $this->validate($request, [
                    'email' => 'required|email|unique:usuarios'
                ], [
                    'email.required' => 'El campo de correo electrónico es obligatorio.',
                    'email.email' => 'Por favor, introduce una dirección de correo electrónico válida.',
                    'email.unique' => 'El correo electrónico ya está registrado en nuestra base de datos.'
                ]);                    
                $request->validate([
                    'foto'=>'required|image'
                ], [
                    'foto.required' => 'El campo de foto es obligatorio.',
                    'foto.image' => 'Por favor, introduce una imagen válida.'
                ]);
                $request->validate([
                    'noEmp' => 'required|unique:usuarios|numeric|max_digits:4',
                    'nombre' => 'required',
                    'apP' => 'required',
                    'apM' => 'required',
                    'tipoUsuario' => 'required',
                    'password' => 'required'
                ], [
                    'noEmp.required' => 'El campo de número de empleado es obligatorio.',
                    'nombre.required' => 'El campo de nombre es obligatorio.',
                    'apP.required' => 'El campo de apellido paterno es obligatorio.',
                    'apM.required' => 'El campo de apellido materno es obligatorio.',
                    'tipoUsuario.required' => 'El campo de tipo de usuario es obligatorio.',
                    'password.required' => 'El campo de contraseña es obligatorio.',
                    'noEmp.unique' => 'El número de empleado ya está registrado en nuestra base de datos.',
                    'noEmp.max' => 'El número de empleado no puede tener más de 4 dígitos.'
                ]);
                if ($admin) {
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
                    $admins = Usuario::where('tipoUsuario','>=', 3)->get();
                    foreach($admins as $admin){
                        Mail::to($admin->email)->send(new MailNotificacionNuevoAdmin($request->email, ($admin->nombre." ".$admin->apP." ".$admin->apM)));
                        Notificaciones::create([
                            'id_usuario' => $admin->id,
                            'titulo' => 'Nuevo usuario registrado',
                            'descripcion' => "Nuevo usuario registrado: ".$request->email. " por: ".$admin->nombre." ".$admin->apP." ".$admin->apM
                        ]);
                    }
                }else{
                    $success = false;
                    $message = "No cuentas con los permisos necesarios";
                }
            }else{
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
    /**
     * Obtiene un usuario por su hash.
     *
     * Esta función recibe un objeto Request y un hash como parámetros de entrada.
     * Busca un usuario en la base de datos que coincida con el hash proporcionado.
     * Devuelve una respuesta JSON con un indicador de éxito y el usuario encontrado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $hash
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserByHash(Request $request, $hash){
        $user = Usuario::where('token', $hash)->first();
        return response()->json([
            'success' => true,
            'usuario' => $user
        ]);
    }

    /**
     * Obtiene todos los usuarios.
     *
     * Esta función recibe un objeto Request como parámetro de entrada.
     * Obtiene todos los usuarios de la base de datos.
     * Devuelve una respuesta JSON con un indicador de éxito y la lista de usuarios.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUsers(Request $request){
        $users = Usuario::all();
        return response()->json([
            'success' => true,
            'usuarios' => $users
        ]);
    }

    /**
     * Obtiene un usuario por su ID.
     *
     * Esta función recibe un objeto Request y un ID como parámetros de entrada.
     * Busca un usuario en la base de datos que coincida con el ID proporcionado.
     * Devuelve una respuesta JSON con un indicador de éxito y el usuario encontrado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserById(Request $request, $id){
        $user = Usuario::where('id', $id)->first();
        return response()->json([
            'success' => true,
            'usuario' => $user
        ]);
    }
    /**
     * Edita un usuario en el sistema.
     *
     * Esta función recibe una solicitud HTTP y realiza las siguientes acciones:
     * - Verifica si el usuario que realiza la solicitud tiene los permisos necesarios.
     * - Valida los campos de la solicitud, como el nombre, apellido paterno, apellido materno y tipo de usuario.
     * - Actualiza los datos del usuario en la base de datos, como el nombre, apellido paterno, apellido materno, tipo de usuario, correo electrónico, número de empleado y foto.
     * - Envía una notificación al usuario si se ha cambiado su tipo de usuario.
     * - Envía un correo electrónico al usuario si se ha cambiado su tipo de usuario.
     * - Retorna una respuesta JSON indicando si la edición del usuario fue exitosa, el correo electrónico utilizado en la solicitud y un mensaje de éxito o error.
     *
     * @param Request $request La solicitud HTTP que contiene los datos del usuario a editar.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON que indica si la edición del usuario fue exitosa, el correo electrónico utilizado en la solicitud y un mensaje de éxito o error.
     */
    public function editUser(Request $request){
        try {
            $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->orWhere('email', $request->email)->first();
            
            if($request->has('foto')){
                $request->validate([
                    'foto'=>'required|image'
                ], [
                    'foto.required' => 'El campo de foto es obligatorio.',
                    'foto.image' => 'Por favor, introduce una imagen válida.'
                ]);
            }
            $request->validate([
                'nombre' => 'required',
                'apP' => 'required',
                'apM' => 'required',
                'tipoUsuario' => 'required',
                'noEmp' => 'numeric|max_digits:4'
            ], [
                'nombre.required' => 'El campo de nombre es obligatorio.',
                'apP.required' => 'El campo de apellido paterno es obligatorio.',
                'apM.required' => 'El campo de apellido materno es obligatorio.',
                'tipoUsuario.required' => 'El campo de tipo de usuario es obligatorio.',
                'noEmp.max_digits' => 'El número de empleado no puede tener más de 4 dígitos.',
                'noEmp.numeric' => 'El número de empleado debe ser un número.'
            ]);
            $tiposUsuarios = [
                "Usuario sin acceso",
                "Profesor/Coordinador",
                "Director",
                "Administrador"
            ];
            if ($admin) {
                $user = Usuario::where('id', $request->id)->first();
                $user->nombre = $request->nombre;
                $user->apP = $request->apP;
                $user->apM = $request->apM;
                if($user->tipoUsuario != $request->tipoUsuario){
                    $user->tipoUsuario = $request->tipoUsuario;
                    Notificaciones::create([
                        'id_usuario' => $user->id,
                        'titulo' => 'Cambio de tipo de usuario',
                        'descripcion' => "Tu tipo de usuario ha sido cambiado a: ".($tiposUsuarios[$request->tipoUsuario])
                    ]);
                    try{
                        Mail::to($user->email)->send(new MailNotificacionUsuario($tiposUsuarios[$request->tipoUsuario]));
                    }catch(\Exception $e){
                        $message = $e->getMessage();
                        $success = false;
                    }

                    
                    
                }
                if($request->email != $user->email){
                    $this->validate($request, [
                        'email' => 'required|email|unique:usuarios'
                    ], [
                        'email.required' => 'El campo de correo electrónico es obligatorio.',
                        'email.email' => 'Por favor, introduce una dirección de correo electrónico válida.',
                        'email.unique' => 'El correo electrónico ya está registrado en nuestra base de datos.'
                    ]);  
                    $user->email = $request->email;
                }
                if($request->noEmp != $user->noEmp){
                    $this->validate($request, [
                        'noEmp' => 'required|unique:usuarios'
                    ], [
                        'noEmp.required' => 'El campo de número de empleado es obligatorio.',
                        'noEmp.unique' => 'El número de empleado ya está registrado en nuestra base de datos.'
                    ]);  
                    $user->noEmp = $request->noEmp;
                }
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
    /**
     * Elimina un usuario.
     *
     * Esta función elimina un usuario de la base de datos. Requiere que el usuario que realiza la solicitud sea un administrador con un nivel de permiso igual o superior a 3. Si el usuario cumple con los requisitos, se elimina el usuario especificado por su ID. Si el usuario no cumple con los requisitos, se devuelve un mensaje de error.
     *
     * @param Request $request La solicitud HTTP que contiene el token de autenticación y el ID del usuario a eliminar.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON que indica si se eliminó el usuario correctamente, el correo electrónico asociado a la solicitud y un mensaje de éxito o error.
     */
    public function deleteUser(Request $request){
        try {
            $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 3)->first();
            $request->validate([
                'id' => 'required'
            ],[
                'id.required' => 'El campo de ID es obligatorio.'
            ]);
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
    /**
     * Maneja las imágenes subidas por los usuarios.
     *
     * Esta función recibe un archivo y lo guarda en el sistema de almacenamiento.
     * Genera un nombre único para el archivo y le asigna la extensión original.
     * Luego, guarda el archivo en la carpeta "public" del sistema de almacenamiento.
     * Mueve el archivo de la carpeta de almacenamiento a la carpeta pública.
     * Finalmente, elimina el archivo de la carpeta de almacenamiento y devuelve el nombre del archivo guardado.
     *
     * @param  \Illuminate\Http\UploadedFile  $file  El archivo a manejar.
     * @return string  El nombre del archivo guardado.
     */
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
    /**
     * Desactiva un usuario.
     *
     * Esta función desactiva un usuario en base al ID proporcionado. 
     * Verifica si el usuario que realiza la acción tiene los permisos necesarios.
     * Si el usuario tiene los permisos, se desactiva el usuario y se envía un correo de notificación.
     * Si el usuario no tiene los permisos, se devuelve un mensaje de error.
     *
     * @param Request $request El objeto Request que contiene los datos de la solicitud.
     * @param int $id El ID del usuario que se desea desactivar.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON que indica si la desactivación fue exitosa y un mensaje correspondiente.
     */
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
    /**
     * Activa un usuario.
     *
     * Esta función activa un usuario en el sistema. Para activar un usuario, se requiere un token de administrador y el ID del usuario a activar.
     * Si el token de administrador y el tipo de usuario son válidos, se activa el usuario y se envía una notificación por correo electrónico.
     * Si el token de administrador no es válido o el tipo de usuario no cumple con los requisitos, se devuelve un mensaje de error.
     *
     * @param Request $request El objeto Request que contiene los datos de la solicitud.
     * @param int $id El ID del usuario a activar.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON que indica si el usuario se activó correctamente y un mensaje asociado.
     */
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

