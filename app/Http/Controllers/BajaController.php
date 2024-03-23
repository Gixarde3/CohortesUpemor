<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Models\Usuario;
use App\Models\Baja;
use App\Imports\BajaImport;
use Maatwebsite\Excel\Facades\Excel; // Import the Excel class

class BajaController extends Controller
{
    //
    /**
     * Crea bajas.
     *
     * Esta función se utiliza para crear registros de bajas en el sistema. Verifica si el usuario que realiza la solicitud tiene los permisos necesarios. Si es así, guarda el archivo adjunto y crea un nuevo registro de baja en la base de datos. Devuelve una respuesta JSON indicando si la operación fue exitosa y un mensaje descriptivo.
     *
     * @param Request $request El objeto Request que contiene los datos de la solicitud.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON que indica si la operación fue exitosa y un mensaje descriptivo.
     */
    public function crearBajas(Request $request)
    {
        $admin = Usuario::where('token', $request->token)->where('tipoUsuario', '>=', 2)->first();
        if ($admin) {
            $file = $request->file('archivo');
            $fileName = $this->manejarArchivo($file);
            $newBaja = new Baja();
            $newBaja->archivo = $fileName;
            $newBaja->idUsuario = $admin->id;
            $newBaja->periodo = $request->periodo;
            $newBaja->procesado = false;
            $newBaja->save();
            $success = true;
            $message = 'Bajas registradas correctamente';
        } else {
            $success = false;
            $message = "No cuentas con los permisos necesarios";
        }

        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }
    /**
     * Elimina las bajas de acuerdo al ID proporcionado.
     *
     * @param Request $request La solicitud HTTP que contiene el token de autenticación.
     * @param int $id El ID de la baja a eliminar.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON que indica si se eliminaron las bajas correctamente.
     */
    public function eliminarBajas(Request $request, $id)
    {
        $admin = Usuario::where('token', $request->token)->where('tipoUsuario', '>=', 2)->first();
        if ($admin) {
            $baja = Baja::find($id);
            $this->deleteFile($baja->archivo);
            $baja->delete();
            $success = true;
            $message = 'Bajas eliminadas correctamente';
        } else {
            $success = false;
            $message = "No cuentas con los permisos necesarios";
        }

        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }
    /**
     * Actualiza las bajas.
     *
     * Esta función actualiza las bajas en el sistema. Verifica si el usuario que realiza la solicitud es un administrador con permisos suficientes.
     * Si el usuario es un administrador válido, se actualiza la baja correspondiente al ID proporcionado.
     * Se puede adjuntar un archivo a la solicitud para actualizar el archivo adjunto de la baja.
     * La función devuelve una respuesta JSON con un indicador de éxito y un mensaje.
     *
     * @param Request $request El objeto Request que contiene los datos de la solicitud.
     * @param int $id El ID de la baja que se va a actualizar.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con el indicador de éxito y el mensaje.
     */
    public function actualizarBajas(Request $request, $id)
    {
        $admin = Usuario::where('token', $request->token)->where('tipoUsuario', '>=', 2)->first();
        if ($admin) {
            $baja = Baja::find($id);
            if($request->hasFile('archivo')){
                $this->deleteFile($baja->archivo);
                $file = $request->file('archivo');
                $fileName = $this->manejarArchivo($file);
                $baja->archivo = $fileName;
            };
            $baja->periodo = $request->periodo;
            $baja->save();
            $success = true;
            $message = 'Bajas actualizadas correctamente';
        } else {
            $success = false;
            $message = "No cuentas con los permisos necesarios";
        }

        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }
    /**
     * Descarga una baja específica.
     *
     * @param Request $request La solicitud HTTP recibida.
     * @param int $id El ID de la baja a descargar.
     * @return mixed La respuesta HTTP con la descarga de la baja si se encuentra, o un JSON con un mensaje de error si no se encuentra.
     */
    public function descargarBajas(Request $request, $id)
    {
        $baja = Baja::find($id);
        if ($baja) {
            return $this->download($request, $baja->archivo);
        } else {
            return response()->json([
                'success' => false,
                'message' => "No se encontró la baja con ese ID"
            ]);
        }
    }

    /**
     * Obtiene todas las bajas.
     *
     * @param Request $request El objeto de solicitud HTTP.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con las bajas obtenidas.
     */
    public function getBajas(Request $request)
    {
        $bajas = Baja::all();
        $success = true;
        $message = 'Bajas obtenidas correctamente';
        return response()->json([
            'success' => $success,
            'message' => $message,
            'bajas' => $bajas
        ]);
    }

    /**
     * Obtiene una baja por su ID.
     *
     * @param Request $request El objeto de solicitud HTTP.
     * @param int $id El ID de la baja a obtener.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con la baja obtenida.
     */
    public function getBajaById(Request $request, $id)
    {
        $baja = Baja::find($id);
        if ($baja) {
            $success = true;
            $message = 'Baja obtenida correctamente';
        } else {
            $success = false;
            $message = "No se encontró la baja con ese ID";
        }
        return response()->json([
            'success' => $success,
            'message' => $message,
            'baja' => $baja
        ]);
    }
    /**
     * Procesa las bajas de calificaciones.
     *
     * Esta función procesa las bajas de calificaciones de los usuarios administradores.
     * Verifica los permisos del administrador y busca la baja de calificaciones correspondiente al ID proporcionado.
     * Si la baja existe y no ha sido procesada previamente, importa un archivo Excel utilizando la clase BajaImport.
     * Marca la baja como procesada y guarda los cambios en la base de datos.
     * Finalmente, devuelve una respuesta JSON indicando el éxito del procesamiento y un mensaje descriptivo.
     *
     * @param Request $request El objeto Request que contiene los datos de la solicitud.
     * @param int $id El ID de la baja de calificaciones a procesar.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con el resultado del procesamiento.
     */
    public function procesarBajas(Request $request, $id){
        $admin = Usuario::where('token', $request->token)->where('tipoUsuario', '>=', 2)->first();
        if ($admin) {
            $baja = Baja::find($id);
            if($baja && $baja->procesado == false){
                
                $archivo = $baja->archivo;
                $archivo = public_path('excel/'.$archivo);
                try{
                    Excel::import(new BajaImport($id, $baja->periodo, $baja->idUsuario), $archivo); // Fix the undefined type error
                }catch(\Exception $e){
                    if(strstr($e->getMessage(), "Undefined array key")){
                        return response()->json([
                            'success' => false,
                            'message' => 'El periodo ingresado no corresponde con el periodo de cierre del archivo cargado'
                        ]);
                    
                    }
                }
                
                $baja->procesado = true;
                $baja->save();
                return response()->json([
                    'success' => true,
                    'message' => 'Calificaciones procesadas correctamente'
                ]);
            }else{
                $success = false;
                $message = "No se encontró una baja no procesada con ese ID";
            }
        } else {
            $success = false;
            $message = "No cuentas con los permisos necesarios";
        }
        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }
    /**
     * Maneja el archivo recibido y lo guarda en una ubicación pública.
     *
     * @param  \Illuminate\Http\UploadedFile  $file  El archivo recibido.
     * @return string  El nombre del archivo generado.
     */
    public function manejarArchivo($file)
    {
        $nameFile = uniqid();
        $extensionFile = '.' . $file->getClientOriginalExtension();
        $file->storeAs('public/', $nameFile . $extensionFile);
        $storageRoute = storage_path('app/public/' . $nameFile . $extensionFile);
        $publicRoute = public_path('excel/' . $nameFile . $extensionFile);
        File::move($storageRoute, $publicRoute);
        Storage::delete($storageRoute);
        return $nameFile . $extensionFile;
    }
    /**
     * Descarga un archivo desde la carpeta de almacenamiento.
     *
     * @param Request $request La solicitud HTTP.
     * @param string $filename El nombre del archivo a descargar.
     * @return \Illuminate\Http\Response La respuesta HTTP con el archivo adjunto.
     */
    public function download(Request $request, $filename)
    {
        // Define la ruta al archivo dentro de la carpeta de almacenamiento (por ejemplo, en la carpeta "public")
        $rutaArchivo = public_path('excel/'.$filename);

        // Obtén el archivo como una respuesta
        return response()->file($rutaArchivo, ['Content-Disposition' => 'attachment; filename="' . $filename . '"']);
    }

    /**
     * Elimina un archivo de la carpeta de almacenamiento.
     *
     * @param string $fileName El nombre del archivo a eliminar.
     * @return bool Devuelve true si el archivo se eliminó correctamente, de lo contrario devuelve false.
     */
    public function deleteFile($fileName)
    {
        $filePath = public_path('excel/' . $fileName);
        if (file_exists($filePath)) {
            unlink($filePath);
            return true;
        } else {
            return false;
        }
    }
}
