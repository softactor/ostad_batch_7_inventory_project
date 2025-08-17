<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\JwtToken;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function login(LoginRequest $request){
        try{
            $user = User::whereEmail($request->email)->first();
            if(!Hash::check($request->password, $user->password)){
                return response()->json([
                    'status' => false,
                    'errors' => ['Invalid Credentials'],
                ],422);
            }

            $userData = [
                'name' => $user->name,
                'email' => $user->email,
                'id' => $user->id,
                'role' => $user->role,
                'image' => $user->profile->avatar_url,
            ];
            $exp = 60 * 24;
            $token = JwtToken::createToken($userData,time() + $exp);

            return response()->json([
                'status' => true,
                'message' => 'Login Success',
                'data' => new UserResource($user),
            ],200)->cookie('token', $token['token'], $exp);

        }catch (\Exception $e){
            Log::critical($e->getMessage() . ' ' .  $e->getFile() . ' ' . $e->getLine());
            return response([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }
}
