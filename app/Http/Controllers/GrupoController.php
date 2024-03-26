<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grupo; // Import the 'Grupo' class
use App\Models\Usuario; // Import the 'Usuario' class

class GrupoController extends Controller
{
    //
    /**
     * Crea un nuevo grupo.
     *
     * Esta función recibe una solicitud HTTP y crea un nuevo grupo en la base de datos.
     * Verifica si el usuario que realiza la solicitud tiene los permisos necesarios.
     * Los parámetros de entrada son los siguientes:
     * - $request: La solicitud HTTP que contiene los datos del grupo a crear.
     * 
     * La función realiza las siguientes validaciones en los datos del grupo:
     * - 'clave': Debe ser único en la tabla 'grupos'.
     * - 'nombre': Requerido.
     * - 'letra': Requerido.
     * - 'grado': Requerido.
     * - 'periodo': Requerido.
     * 
     * Si el usuario no tiene los permisos necesarios, se devuelve una respuesta JSON con un mensaje de error.
     * Si se crea el grupo correctamente, se guarda en la base de datos y se devuelve una respuesta JSON con un mensaje de éxito.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function crearGrupo(Request $request){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 1)->first();
        try{
            $request->validate([
                'clave' => 'required|unique:grupos,clave',
                'nombre' => 'required',
                'letra' => 'required',
                'grado' => 'required',
                'periodo' => 'required'
            ],[
                'clave.unique' => 'La clave ya ha sido registrada'
            ]);
            if (!$admin) {
                $success = false;
                $message = "No cuentas con los permisos necesarios";
                return response()->json([
                    'success' => $success,
                    'message' => $message
                ]);
            }
            $newGrupo = new Grupo();
            $newGrupo->clave = $request->clave;
            $newGrupo->nombre = $request->nombre;
            $newGrupo->letra = $request->letra;
            $newGrupo->grado = $request->grado;
            $newGrupo->periodo = $request->periodo;
            $newGrupo->save();
            $success = true;
            $message = 'Grupo creado correctamente';
            return response()->json([
                'success' => $success,
                'message' => $message
            ]);
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    /**
     * Edita un grupo existente.
     *
     * Esta función recibe una solicitud HTTP y el ID del grupo a editar. Verifica si el usuario que realiza la solicitud es un administrador válido. Luego, valida los datos de entrada de la solicitud y actualiza los campos del grupo correspondiente en la base de datos. Finalmente, devuelve una respuesta JSON indicando si la edición del grupo fue exitosa y un mensaje descriptivo.
     *
     * @param \Illuminate\Http\Request $request La solicitud HTTP que contiene los datos de edición del grupo.
     * @param int $id El ID del grupo a editar.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON que indica si la edición del grupo fue exitosa y un mensaje descriptivo.
     */
    public function editarGrupo(Request $request, $id){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 1)->first();
        try{
            $request->validate([
                'clave' => 'required',
                'nombre' => 'required',
                'letra' => 'required',
                'grado' => 'required',
                'periodo' => 'required'
            ]);
            if (!$admin) {
                $success = false;
                $message = "No cuentas con los permisos necesarios";
                return response()->json([
                    'success' => $success,
                    'message' => $message
                ]);
            }
            $grupo = Grupo::find($id);
            if($grupo){
                if($grupo->clave != $request->clave){
                    $grupoRepetido = Grupo::where('clave',$request->clave)->first();
                    if ($grupoRepetido) {
                        return response()->json([
                            'success' => false,
                            'message' => 'La clave ya ha sido registrada'
                        ]);
                    }
                }
                $grupo->clave = $request->clave;
                $grupo->nombre = $request->nombre;
                $grupo->letra = $request->letra;
                $grupo->grado = $request->grado;
                $grupo->periodo = $request->periodo;
                $grupo->save();
                $success = true;
                $message = 'Grupo editado correctamente';
            } else {
                $success = false;
                $message = 'No se encontró el grupo';
            }
            return response()->json([
                'success' => $success,
                'message' => $message
            ]);
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    /**
     * Elimina un grupo.
     *
     * Esta función elimina un grupo específico de acuerdo al ID proporcionado.
     * Verifica si el usuario que realiza la solicitud tiene los permisos necesarios.
     *
     * @param Request $request El objeto de solicitud HTTP.
     * @param int $id El ID del grupo a eliminar.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON que indica si se eliminó el grupo correctamente y un mensaje descriptivo.
     */
    public function eliminarGrupo(Request $request, $id){
        $admin = Usuario::where('token',$request->token)->where('tipoUsuario','>=', 1)->first();
        if (!$admin) {
            $success = false;
            $message = "No cuentas con los permisos necesarios";
            return response()->json([
                'success' => $success,
                'message' => $message
            ]);
        }
        $grupo = Grupo::find($id);
        if($grupo){
            $grupo->delete();
            $success = true;
            $message = 'Grupo eliminado correctamente';
        } else {
            $success = false;
            $message = 'No se encontró el grupo';
        }
        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }
    /**
     * Obtiene todos los grupos.
     *
     * Esta función obtiene todos los grupos de la base de datos y devuelve una respuesta JSON con los resultados.
     *
     * @param Request $request El objeto de solicitud HTTP.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con los grupos obtenidos.
     */
    public function getGrupos(Request $request){
        $grupos = Grupo::all();
        $success = true;
        $message = 'Grupos obtenidos correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'resultados' => $grupos
        ]);
    }

    /**
     * Obtiene un grupo por su ID.
     *
     * Esta función busca un grupo en la base de datos por su ID y devuelve una respuesta JSON con el resultado.
     * Si no se encuentra el grupo, se devuelve un mensaje de error.
     *
     * @param Request $request El objeto de solicitud HTTP.
     * @param int $id El ID del grupo a buscar.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con el grupo obtenido o el mensaje de error.
     */
    public function getGrupoById(Request $request, $id){
        $grupo = Grupo::find($id);
        if($grupo){
            $success = true;
            $message = 'Grupo obtenido correctamente';
        } else {
            $success = false;
            $message = 'No se encontró el grupo';
        }
        return response()->json([
            'success' => $success,
            'message' => $message,
            'resultados' => $grupo
        ]);
    }
    /**
     * Obtiene los grupos por grado.
     *
     * Esta función recibe un objeto de tipo Request y un parámetro de grado.
     * Busca y devuelve los grupos que coinciden con el grado especificado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $grado
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGruposByGrado(Request $request, $grado){
        $grupos = Grupo::where('grado',$grado)->get();
        $success = true;
        $message = 'Grupos obtenidos correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'resultados' => $grupos
        ]);
    }

    /**
     * Obtiene los grupos por letra.
     *
     * Esta función recibe un objeto de tipo Request y un parámetro de letra.
     * Busca y devuelve los grupos que coinciden con la letra especificada.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $letra
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGruposByLetra(Request $request, $letra){
        $grupos = Grupo::where('letra',$letra)->get();
        $success = true;
        $message = 'Grupos obtenidos correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'resultados' => $grupos
        ]);
    }
    /**
     * Obtiene los grupos por clave.
     *
     * Esta función recibe una solicitud HTTP y una clave como parámetros de entrada.
     * Busca los grupos en la base de datos que coincidan con la clave proporcionada.
     * Devuelve una respuesta JSON con un indicador de éxito, un mensaje y los grupos encontrados.
     *
     * @param Request $request La solicitud HTTP recibida.
     * @param string $clave La clave para buscar los grupos.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con los grupos encontrados.
     */
    public function getGruposByClave(Request $request, $clave){
        $grupos = Grupo::where('clave',$clave)->get();
        $success = true;
        $message = 'Grupos obtenidos correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'resultados' => $grupos
        ]);
    }

    /**
     * Obtiene los grupos por nombre.
     *
     * Esta función recibe una solicitud HTTP y un nombre como parámetros de entrada.
     * Busca los grupos en la base de datos que coincidan con el nombre proporcionado.
     * Devuelve una respuesta JSON con un indicador de éxito, un mensaje y los grupos encontrados.
     *
     * @param Request $request La solicitud HTTP recibida.
     * @param string $nombre El nombre para buscar los grupos.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con los grupos encontrados.
     */
    public function getGruposByNombre(Request $request, $nombre){
        $grupos = Grupo::where('nombre',$nombre)->get();
        $success = true;
        $message = 'Grupos obtenidos correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'resultados' => $grupos
        ]);
    }
}
