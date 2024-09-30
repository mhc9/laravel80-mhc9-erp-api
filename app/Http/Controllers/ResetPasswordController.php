<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class ResetPasswordController extends Controller
{
    public function resetPassword(Request $request)
    {      
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email);
        $user->update(['password' => bcrypt($request->password)]);

        $token = $user->first()->createToken('myapptoken')->plainTextToken;

        return new JsonResponse([
            'success'   => true, 
            'message'   => "Your password has been reset", 
            'token'     => $token
        ], 200);
    }

    public function changePassword(Request $request)
    {      
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email);
        $user->update(['password' => bcrypt($request->password), 'is_new' => 0]);

        return new JsonResponse([
            'success'   => true, 
            'message'   => "Your password has been reset",
        ], 200);
    }
}
