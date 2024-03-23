<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DatabaseLog;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BackupController extends Controller
{
    /**
     * Sube un respaldo de base de datos y lo restaura en el servidor.
     *
     * @param Request $request La solicitud HTTP que contiene el archivo de respaldo y el token de autenticación.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON que indica si la restauración fue exitosa y un mensaje descriptivo.
     */
    public function uploadBackup(Request $request){
        $admin = Usuario::where('token', $request->token)->where('tipoUsuario', 3)->first();
        if ($admin) {
            if ($request->hasFile('sql')) {
                if ($request->file('sql')->getClientOriginalExtension() == 'sql') {
                    $request->file('sql')->storeAs('backupCohortes.sql');
                    $databaseName = env('DB_DATABASE');
                    $username = env('DB_USERNAME');
                    $outputFile = storage_path('app/backupCohortes.sql');
                    $command = 'C:\xampp\mysql\bin\mysql -u '.$username.' '.$databaseName.' < '. $outputFile;
                    exec($command, $output, $exitCode);
                    if ($exitCode === 0) {
                        $databaseLog = new DatabaseLog();
                        $databaseLog->idUsuario = $admin->id;
                        $databaseLog->operation = 'Restauración';
                        $databaseLog->date = Carbon::now();
                        $databaseLog->save();
                        $success = true;
                        $message = "Base de datos restaurada correctamente";
                    } else {
                        $success = false;
                        $message = 'Error al restaurar la base de datos';
                    }
                } else {
                    $success = false;
                    $message = 'El archivo no es un respaldo de base de datos';
                }
            }else{
                $success = false;
                $message = 'No se ha seleccionado un archivo';
            }
        } else {
            $success = false;
            $message = 'Acceso prohibido';
        }
        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }

    /**
     * Busca usuarios por nombre.
     *
     * Esta función busca usuarios en la base de datos por su nombre completo. 
     * Recibe como parámetros una solicitud HTTP y el nombre de usuario a buscar.
     * Devuelve un objeto JSON con el resultado de la búsqueda.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $username
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchByName(Request $request, $username){

        $search = Usuario::join('database_logs', 'database_logs.idUsuario', '=', 'usuarios.id')
        ->selectRaw('CONCAT(usuarios.nombre, " ", usuarios.apP, " ", usuarios.apM) as nombre, database_logs.operation as operacion, database_logs.date as fecha')
        ->whereRaw('CONCAT(usuarios.nombre, " ", usuarios.apP, " ", usuarios.apM) LIKE ?', ['%Marco Antonio Chavez Rodriguez%'])
        ->get();
        return response()->json([
            'success'=>true,    
            'resultados'=>$search
        ]);
        
        
    }

    /**
     * Busca registros en la base de datos por fecha.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $date
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchByDate(Request $request, $date){
        $search = Usuario::join('database_logs', 'database_logs.idUsuario', '=', 'usuarios.id')
        ->selectRaw('CONCAT(usuarios.nombre, " ", usuarios.apP, " ", usuarios.apM) as nombre, database_logs.operation as operacion, database_logs.date as fecha, database_logs.id as id')
        ->where('database_logs.date', $date)->get();
        return response()->json([
            'success'=>true,    
            'resultados'=>$search
        ]);
    }

    /**
     * Realiza una búsqueda general en la base de datos.
     *
     * @param Request $request El objeto de solicitud HTTP.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con los resultados de la búsqueda.
     */
    public function searchGeneral(Request $request){
        $search = Usuario::join('database_logs', 'database_logs.idUsuario', '=', 'usuarios.id')
        ->selectRaw('CONCAT(usuarios.nombre, " ", usuarios.apP, " ", usuarios.apM) as nombre, database_logs.operation as operacion, database_logs.date as fecha, database_logs.id as id')
        ->get();
        return response()->json([
            'success'=>true,    
            'resultados'=>$search
        ]);
    }
    /**
     * Descarga una copia de seguridad de la base de datos y registra la operación en un log.
     *
     * @param Request $request El objeto de solicitud HTTP.
     * @param string $token El token de autenticación del administrador.
     * @return \Illuminate\Http\Response La respuesta HTTP que contiene el archivo de copia de seguridad descargado.
     */
    public function backupDownload(Request $request, $token){
        // Verificar si el administrador existe y tiene el tipo de usuario correcto
        $admin = Usuario::where('token', $token)->where('tipoUsuario', 3)->first();
        if ($admin) {
            // Obtener el nombre de la base de datos y el nombre de usuario desde el archivo de configuración
            $databaseName = env('DB_DATABASE');
            $username = env('DB_USERNAME');
            
            // Establecer la ruta y el nombre del archivo de salida de la copia de seguridad
            $outputFile = storage_path('app/backupCohortes.sql');
            
            // Ejecutar el comando de mysqldump para crear la copia de seguridad en el archivo
            $command = 'C:\xampp\mysql\bin\mysqldump -u '.$username.' '.$databaseName.' > '. $outputFile;
            exec($command, $output, $exitCode);
            
            // Mover la copia de seguridad al directorio público
            $nameFile = $this->moveToPublic();
            $publicRoute = public_path('database/'.$nameFile);
            
            // Registrar la operación de respaldo en el log de la base de datos
            $databaseLog = new DatabaseLog();
            $databaseLog->idUsuario = $admin->id;
            $databaseLog->operation = 'Respaldo';
            $databaseLog->date = Carbon::now();
            $databaseLog->save();
            
            // Devolver la respuesta HTTP que contiene el archivo de copia de seguridad descargado
            return response()->file($publicRoute, ['Content-Disposition' => 'attachment; filename="' . $nameFile . '"']);
        } else {
            // Devolver una respuesta de acceso prohibido si el administrador no existe o no tiene el tipo de usuario correcto
            return response('Acceso prohibido.', 403);
        }
    }

    /**
     * Mueve la copia de seguridad al directorio público.
     *
     * @return string El nombre del archivo de copia de seguridad movido.
     */
    public function moveToPublic(){
        // Establecer la ruta de almacenamiento y la ruta pública del archivo de copia de seguridad
        $storageRoute = storage_path('app/backupCohortes.sql');
        $publicRoute = public_path('database/backupCohortes.sql');
        
        // Copiar el archivo de copia de seguridad al directorio público
        File::copy($storageRoute,$publicRoute);
        
        // Devolver el nombre del archivo de copia de seguridad movido
        return 'backupCohortes.sql';
    }
}
