<?php

use App\Http\Controllers\CohorteController;
use App\Http\Controllers\GrupoController; // Import the missing class
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\CalificacionProcesadaController; // Import the missing class
use App\Http\Controllers\BajaController; // Import the missing class

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

Route::post('/login', [LoginController::class, 'login']);
Route::post('/sendMail', [LoginController::class, 'sendRestoreMail']);
Route::post('/restorePassword/{hash}', [LoginController::class, 'restorePassword']);
Route::post('/verifyHash/{hash}', [LoginController::class, 'verifyHash']);

Route::controller(UsuarioController::class)->group(function(){
    Route::get('user/{hash}', 'getUserByHash');
    Route::post('register','register');
    Route::get('usuarios','getAllUsers');
    Route::get('usuario/{id}','getUserById');
    Route::post('usuario/edit/{id}','editUser');
    Route::post('usuario/delete/{id}','deleteUser');
});

Route::controller(CohorteController::class)->group(function(){
    Route::post('cohorte','createCohorte');
    Route::post('cohorte/edit/{id}','editCohorte');
    Route::post('cohorte/delete/{id}','deleteCohorte');
    Route::get('cohortes','getAllCohortes');
    Route::get('cohorte/{id}','getCohorteById');
    Route::post('calificacion/{id}','subirCalificacion');
    Route::post('calificacion/delete/{id}','eliminarCalificaciones');
    Route::get('calificacion/download/{fileName}','download');
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

Route::controller(CalificacionProcesadaController::class)->group(function(){
    Route::post('calificacion/procesar/{id}','importarExcel');
    Route::get('calificacion/aprobados/{id}','getAprobadosReprobados');
    Route::get('calificacion/matriculas/{id}','getAniosInMatriculas');
});

Route::controller(BajaController::class)->group(function(){
    Route::post('baja/{id}','crearBajas');
    Route::post('baja/delete/{id}','eliminarBajas');
    Route::post('baja/edit/{id}','actualizarBajas');
    Route::get('bajas','getBajas');
    Route::get('baja/{id}','getBajaById');
    Route::post('baja/procesar/{id}','procesarBajas');
    Route::get('baja/download/{id}','descargarBajas');
});