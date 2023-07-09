<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Multitask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class MultitaskController extends Controller
{
    public function createMultitask(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'description' => 'required|string',
                'user_id' => 'required'
            ], ['description.required' => '¡La tarea debe tener una descripción!']);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $validData = $validator->validated();

            $multitask = Multitask::create([
                'description' => $validData['description']
            ]);
            $multitask->user()->attach($validData['user_id'], ['owner'=>true]);

            return response()->json([
                'message' => 'Task created',
                'data' => $multitask
            ], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error('Create multitask error: ' . $th->getMessage());

            return response()->json(
                [
                    "success" => false,
                    "message" => "Task creation error"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function joinTask(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'id' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $validData = $validator->validated();

            $multitask = Multitask::find($validData['id']);

            $multitask->user()->attach($validData['user_id'], ['owner' => false]);

            return response()->json([
                'message' => 'Task joined',
                'data' => $multitask
            ], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error('Join multitask error: ' . $th->getMessage());

            return response()->json(
                [
                    "success" => false,
                    "message" => "Task joining error"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function leaveTask(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'id' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $validData = $validator->validated();

            $multitask = Multitask::find($validData['id']);

            $isActive = $multitask->user()->find($validData['user_id']);
            $isOwner = $multitask->user()->wherePivot('owner', true)->find($validData['user_id']);
            if ($isActive && !$isOwner) {

                $multitask->user()->detach($validData['user_id']);
            } else if ($isOwner) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "You cannot leave a task created by you"
                    ],
                    Response::HTTP_NOT_ACCEPTABLE
                );
            } else {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "You are not in that task"
                    ],
                    Response::HTTP_NOT_ACCEPTABLE
                );
            }



            return response()->json([
                'message' => 'You left the task',
                'data' => $multitask
            ], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error('Join multitask error: ' . $th->getMessage());

            return response()->json(
                [
                    "success" => false,
                    "message" => "Task leaving error"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
