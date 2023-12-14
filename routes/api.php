<?php

use App\Http\Controllers\CohorteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\LoginController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
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
});

