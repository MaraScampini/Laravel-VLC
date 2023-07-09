<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'surname' => 'required|string',
                'email' => 'required|unique:users,email|email',
                'password' => ['required', Password::min(8)->mixedCase()->numbers()]
            ], [
                'password.min' => 'La contraseña debe tener al menos 8 caracteres',
                'password' => 'La contraseña debe contener al menos un número, una mayúscula y una minúscula'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $validData = $validator->validated();

            $newUser = User::create([
                'name' => $validData['name'],
                'surname' => $validData['surname'],
                'email' => $validData['email'],
                'password' => bcrypt($validData['password']),
                'role_id' => 2
            ]);

            $token = $newUser->createToken('apiToken')->plainTextToken;

            return response()->json(
                [
                    "success" => true,
                    "message" => "User registered successfully",
                    'data' => $newUser,
                    "token" => $token
                ],
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Can't register user",
                    "data" => $th->getMessage()
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => ['required', Password::min(8)->mixedCase()->numbers()]
            ], [
                'password.min' => 'La contraseña debe tener al menos 8 caracteres',
                'password' => 'La contraseña debe contener al menos un número, una mayúscula y una minúscula'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $validData = $validator->validated();

            $user = User::where('email', $validData['email'])->first();

            if(!$user){
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Email or password are invalid"
                    ],
                    Response::HTTP_NOT_FOUND
                ); 
            }

            if(!Hash::check($validData['password'], $user->password)){
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Email or password are invalid"
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }

            $token = $user->createToken('apiToken')->plainTextToken;

            return response()->json(
                [
                    "success" => true,
                    "message" => "User logged in",
                    "token" => $token
                ],
                Response::HTTP_OK
            );

        } catch (\Throwable $th) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Can't log user in",
                    "data" => $th->getMessage()
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function profile(){
        try {
            $user = auth()->user();
            $tasks = User::where('email', $user->email)->with('task')->get();
            return response()->json(
                [
                    "success" => true,
                    "message" => "User retrieved",
                    "user" => $user,
                    "tasks" => $tasks
                ],
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Can't retrieve user",
                    "data" => $th->getMessage()
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
