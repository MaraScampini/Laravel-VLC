<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{

    public function getAllTasks()
    {
        try {
            $tasks = Task::with('user')->get();

            return response()->json([
                'message' => 'These are all the tasks',
                'data' => $tasks
            ]);
        } catch (\Throwable $th) {
            Log::error('Get tasks error: ' . $th->getMessage());

            return response()->json(
                [
                    "success" => false,
                    "message" => "Could not get tasks"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function getMyTasks()
    {
        try {
            $user = auth()->user();
            $tasks = Task::where('user_id', $user->id)->get();

            return response()->json([
                'message' => 'These are all your tasks',
                'data' => $tasks
            ]);
        } catch (\Throwable $th) {
            Log::error('Get tasks error: ' . $th->getMessage());

            return response()->json(
                [
                    "success" => false,
                    "message" => "Could not get tasks"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function createTask(Request $request)
    {
        try {
            $user = auth()->user();

            $validator = Validator::make($request->all(), [
                'description' => 'required|string'
            ], ['description.required' => '¡La tarea debe tener una descripción!']);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $validData = $validator->validated();

            // // POR QUERY BUILDER PURO -- NO METE TIMESTAMPS SI NO SE LAS ESPECIFICO AQUÍ
            // DB::table('tasks')->insert(['description'=>$validData['description'], 'user_id'=>$user->id]);

            // // QUERY BUILDER CON ORM -- METE TIMESTAMPS, NO ES NECESARIO QUE LOS CAMPOS SEAN $FILLABLE
            // $task = new Task;
            // $task->description = $validData['description'];
            // $task->user_id = $user->id;
            // $task->save();

            // POR MODELO - ORM -- METE TIMESTAMPS, ES NECESARIO QUE LOS CAMPOS SEAN $FILLABLE
            $task = Task::create([
                'description' => $validData['description'],
                'user_id' => $user->id
            ]);

            return response()->json([
                'message' => 'Task created'
            ], Response::HTTP_CREATED);
        } catch (\Throwable $th) {

            Log::error('Create task error: ' . $th->getMessage());

            return response()->json(
                [
                    "success" => false,
                    "message" => "Task creation error"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function updateTask(Request $request)
    {
        try {

            $validator = Validator::make(
                $request->all(),
                [
                    'description' => 'string',
                    'id' => 'required',
                    'status' => 'boolean'
                ],
                ['id.required' => 'La ID de la tarea es necesaria']
            );

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $validData = $validator->validated();

            $task = Task::find($validData['id']);

            // $task = Task::findOrFail($validData['id']);

            if (!$task) {
                return response()->json(
                    [
                        'message' => 'Task not found'
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }
            // ORM PURO - ACTUALIZA EL UPDATED_AT

            if (isset($validData['description']) and isset($validData['status'])) {
                $task->update(['description' => $validData['description'], 'status' => $validData['status']]);
            } else if (isset($validData['description'])) {
                $task->update(['description' => $validData['description']]);
            } else if (isset($validData['status'])) {
                $task->update(['status' => $validData['status']]);
            }

            // QUERY BUILDER CON SAVE - ACTUALIZA EL UPDATED_AT

            // if(isset($validData['description'])){
            //     $task->description=$validData['description'];
            // }
            // if(isset($validData['status'])){
            //     $task->status=$validData['status'];
            // }
            // $task->save();

            return response()->json(
                [
                    'message' => 'Task updated'
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            Log::error('Error updating the task: ' . $th->getMessage());

            return response()->json(
                [
                    "success" => false,
                    "message" => "Error updating the task"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function deleteTask($id)
    {

        try {
            // $task = Task::find($id);

            // CON ORM PURO - NECESARIO ENCONTRAR LA TAREA PRIMERO
            // $task->delete();

            // CON ORM PURO - NO ES NECESARIO ENCONTRAR LA TAREA, PASAMOS DIRECTAMENTE LA ID
            Task::destroy($id);

            return response()->json(
                [
                    'message' => 'Task deleted'
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            Log::error('Error deleting the task: ' . $th->getMessage());

            return response()->json(
                [
                    "success" => false,
                    "message" => "Error deleting the task"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
