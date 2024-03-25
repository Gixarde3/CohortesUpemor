<?php

use App\Http\Controllers\AdmisionController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CohorteController;
use App\Http\Controllers\GrupoController; // Import the missing class
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\CalificacionProcesadaController; // Import the missing class
use App\Http\Controllers\BajaController; // Import the missing class
use App\Http\Controllers\BajaProcesadaController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\AspiranteController;
use App\Http\Controllers\NotificacionesController;
use App\Models\Calificacion;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí es donde puedes registrar las rutas de API para tu aplicación. Estas
| rutas son cargadas por RouteServiceProvider y todas ellas serán
| asignadas al grupo de middleware "api". 
|
*/

Route::group(['controller' => NotificacionesController::class], function () {
    Route::get('notificaciones/{token}', 'getNotificaciones');
    Route::get('notificaciones/pendientes/{token}', 'getNotificacionesPendientes');
});

Route::group(['controller' => LoginController::class], function () {
    Route::post('login', 'login');
    Route::post('sendMail', 'sendRestoreMail');
    Route::post('restorePassword/{hash}', 'restorePassword');
    Route::post('verifyHash/{hash}', 'verifyHash');
    Route::post('verifyCode/{code}', 'verifyCode');
});

Route::controller(UsuarioController::class)->group(function(){
    Route::get('user/{hash}', 'getUserByHash');
    Route::post('register','register');
    Route::get('usuarios','getAllUsers');
    Route::get('usuario/{id}','getUserById');
    Route::post('usuario/edit/{id}','editUser');
    Route::post('usuario/delete/{id}','deleteUser');
    Route::post('usuario/desactivar/{id}','desactivar');
    Route::post('usuario/activar/{id}','activar');
});

Route::controller(CohorteController::class)->group(function(){
    Route::post('cohorte','createCohorte');
    Route::post('cohorte/edit/{id}','editCohorte');
    Route::post('cohorte/delete/{id}','deleteCohorte');
    Route::get('cohortes','getAllCohortes');
    Route::get('cohorte/{id}','getCohorteById');
});


Route::controller(GrupoController::class)->group(function(){ // The undefined type 'GrupoController' is now defined
    Route::post('grupo','crearGrupo');
    Route::post('grupo/edit/{id}','editarGrupo');
    Route::post('grupo/delete/{id}','eliminarGrupo');
    Route::get('grupos','getGrupos');
    Route::get('grupo/{id}','getGrupoById');
    Route::get('grupo/grado/{grado}','getGruposByGrado');
    Route::get('grupo/letra/{letra}','getGruposByLetra');
    Route::get('grupo/cohorte/{cohorte}','getGruposByCohorte');
    Route::get('grupo/nombre/{nombre}','getGruposByNombre');
    Route::get('grupo/clave/{clave}','getGruposByClave');
});

Route::controller(CalificacionController::class)->group(function(){
    Route::post('calificacion','subirCalificacion');
    Route::get('calificaciones','getCalificaciones');
    Route::post('calificacion/delete/{id}','eliminarCalificaciones');
    Route::post('calificacion/edit/{id}','editarCalificaciones');
    Route::get('calificacion/download/{id}','download');
    Route::get('calificacion/{id}', 'getCalificacionById');
});

Route::controller(CalificacionProcesadaController::class)->group(function(){
    Route::post('calificacion/procesar/{id}','importarExcel');
    Route::get('calificacion/aprobados/{id}','getAprobadosReprobados');
    Route::get('calificacion/matriculas/{id}','getAniosInMatriculas');
    Route::get('califcaciones','getCalificaciones');
    Route::get('cohorte/calificaciones/{idCohorte}','getMateriasMasReprobadasByCohorte');
    Route::get('calificacion/matriculasPorCuatrimestre/{id}','getCohortesByCuatrimestre');
    
});

Route::controller(BajaController::class)->group(function(){
    Route::post('baja','crearBajas');
    Route::post('baja/delete/{id}','eliminarBajas');
    Route::post('baja/edit/{id}','actualizarBajas');
    Route::get('bajas','getBajas');
    Route::get('baja/{id}','getBajaById');
    Route::post('baja/procesar/{id}','procesarBajas');
    Route::get('baja/download/{id}','descargarBajas');
    
});

Route::controller(BackupController::class)->group(function(){
    Route::post('backup','uploadBackup');
    Route::get('backups','searchGeneral');
    Route::get('backup/nombre/{username}','searchByName');
    Route::get('backup/fecha/{date}','searchByDate');
    Route::get('backup/download/{token}','backupDownload');
});
Route::controller(BajaProcesadaController::class)->group(function(){
    Route::get('cohorte/bajas/periodos/{idCohorte}','getBajasByPeriodo');
    Route::get('cohorte/bajas/{idCohorte}','getBajas');
    Route::get('bajas/rango/{anio1}/{anio2}/{carrera}','getBajasByRango');
    Route::get('bajas/alumnos/{idCohorte}', 'getAlumnos');
});

Route::controller(AdmisionController::class)->group(function(){
    Route::post('admision','crearAdmision');
    Route::post('admision/edit/{id}','editarAdmision');
    Route::post('admision/delete/{id}','eliminarAdmision');
    Route::get('admisiones','getAdmisiones');
    Route::get('admision/{id}','getAdmisionById');
    Route::post('admision/procesar/{id}','procesarAdmision');
    Route::get('admision/download/{id}','descargarAdmision');
    Route::get('aspirantes/fichas/{anio1}/{anio2}/{carrera}','getFichasVendidas');
    Route::get('aspirantes/examenes/{anio1}/{anio2}/{carrera}','getExamenesPresentados');
    Route::get('aspirantes/fichas/{idCohorte}', 'getFichasVendidasByCohorte');
    Route::get('aspirantes/examen/{idCohorte}', 'getExamenesPresentadosByCohorte');
    Route::get('aspirantes/examen/aprobados/{idCohorte}', 'getAprobadosCeneval');
    Route::get('aspirantes/curso/{idCohorte}', 'getAspirantesCurso');
    Route::get('aspirantes/curso/aprobados/{idCohorte}', 'getAprobadosCurso');
});

Route::controller(AspiranteController::class)->group(function(){
    Route::get('aspirantes/inscritos/{anio1}/{anio2}/{carrera}', 'getAspirantesInscritos');
    Route::get('aspirantes/aprobados/{anio1}/{anio2}/{carrera}', 'getAprobadosCeneval');
});