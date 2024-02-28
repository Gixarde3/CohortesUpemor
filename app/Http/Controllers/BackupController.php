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

    public function searchByDate(Request $request, $date){
        $search = Usuario::join('database_logs', 'database_logs.idUsuario', '=', 'usuarios.id')
        ->selectRaw('CONCAT(usuarios.nombre, " ", usuarios.apP, " ", usuarios.apM) as nombre, database_logs.operation as operacion, database_logs.date as fecha, database_logs.id as id')
        ->where('database_logs.date', $date)->get();
        return response()->json([
            'success'=>true,    
            'resultados'=>$search
        ]);
    }

    public function searchGeneral(Request $request){
        $search = Usuario::join('database_logs', 'database_logs.idUsuario', '=', 'usuarios.id')
        ->selectRaw('CONCAT(usuarios.nombre, " ", usuarios.apP, " ", usuarios.apM) as nombre, database_logs.operation as operacion, database_logs.date as fecha, database_logs.id as id')
        ->get();
        return response()->json([
            'success'=>true,    
            'resultados'=>$search
        ]);
    }
    public function backupDownload(Request $request, $token){
        $admin = Usuario::where('token', $token)->where('tipoUsuario', 3)->first();
        if ($admin) {
            $databaseName = env('DB_DATABASE');
            $username = env('DB_USERNAME');
            $outputFile = storage_path('app/backupCohortes.sql');
            $command = 'C:\xampp\mysql\bin\mysqldump -u '.$username.' '.$databaseName.' > '. $outputFile;
            exec($command, $output, $exitCode);
            $nameFile = $this->moveToPublic();
            $publicRoute = public_path('database/'.$nameFile);
            $databaseLog = new DatabaseLog();
            $databaseLog->idUsuario = $admin->id;
            $databaseLog->operation = 'Respaldo';
            $databaseLog->date = Carbon::now();
            $databaseLog->save();
            return response()->file($publicRoute, ['Content-Disposition' => 'attachment; filename="' . $nameFile . '"']);
        } else {
            return response('Acceso prohibido.', 403);
        }
        
    }
    public function moveToPublic(){
        $storageRoute = storage_path('app/backupCohortes.sql');
        $publicRoute = public_path('database/backupCohortes.sql');
        File::copy($storageRoute,$publicRoute);
        return 'backupCohortes.sql';
    }
}
