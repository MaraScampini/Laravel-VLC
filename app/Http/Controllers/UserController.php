<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function deleteUser()
    {
        try {
            $user = auth()->user();
            $userFound = User::find($user->id);
            $userFound->delete();
            // User::destroy($user->id);

            return response()->json(
                [
                    'message' => 'User deleted'
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            Log::error('Error deleting the user: ' . $th->getMessage());

            return response()->json(
                [
                    "success" => false,
                    "message" => "Error deleting the user"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function restoreUser($id)
    {
        try {
            // $user = auth()->user();
            User::withTrashed()->where('id', $id)->restore();

            return response()->json(
                [
                    'message' => 'User restored'
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            Log::error('Error restoring the user: ' . $th->getMessage());

            return response()->json(
                [
                    "success" => false,
                    "message" => "Error restoring the user"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
