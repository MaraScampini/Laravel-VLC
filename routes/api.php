<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MultitaskController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

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

// Route::get('/user/{id}', function($id) {
    
//     // Con esta query sí aparecen los campos password y token
//     // $user = DB::table('users')->where('id', '=', $id)->get();

//     // Con esta query NO aparecen, porque la hago a través del modelo, donde he especificado que quiero que se oculten
//     $user = User::where('id', $id)->get();


//     return $user;
// });

Route::post('/task/create', [TaskController::class, 'createTask'])->middleware('auth:sanctum');
Route::get( '/tasks', [TaskController::class, 'getAllTasks']);
Route::get('/mytasks', [TaskController::class, 'getMyTasks'])->middleware('auth:sanctum');
Route::put('/task/update', [TaskController::class, 'updateTask']);
Route::delete('/task/delete/{id}', [TaskController::class, 'deleteTask']);

Route::post('/multitask/create', [MultitaskController::class, 'createMultitask']);
Route::post('/multitask/join', [MultitaskController::class, 'joinTask']);
Route::post('/multitask/leave', [MultitaskController::class, 'leaveTask']);

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [ AuthController::class, 'login']);
Route::get('/auth/profile', [AuthController::class, 'profile'])->middleware('auth:sanctum');

Route::delete('/user', [UserController::class, 'deleteUser'])->middleware( 'auth:sanctum');
Route::post('/user/{id}', [UserController::class, 'restoreUser']);


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
