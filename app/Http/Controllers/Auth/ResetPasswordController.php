<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\JwtToken;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordSentOtpRequest;
use App\Http\Requests\Auth\ResetPasswordVerifyOtpRequest;
use App\Mail\SendOtpMail;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ResetPasswordController extends Controller
{
    public function sendOtp(ResetPasswordSentOtpRequest $request){
        try{
            $otp = mt_rand(100000,999999);
            Otp::create([
                'email' => $request->email,
                'otp' => $otp,
            ]);

            Mail::to($request->email)->send(new SendOtpMail($otp));

            return response()->json([
                'status'=>true,
                'message'=> 'Otp Sent to Your Email',
            ]);
        }catch(\Exception $e){
            Log::critical($e->getMessage() . ' '  . $e->getFile() . ' ' . $e->getLine());
            return response()->json([
                'status'=>false,
                'message'=> 'Something went wrong',
            ],500);
        }
    }

    public function verifyOtp(ResetPasswordVerifyOtpRequest $request){
        try{
            Otp::where('email',$request->email)->where('otp',$request->otp)->update([
                'status'=>true,
            ]);

            $exp = 60;
            $token = JwtToken::createToken(['email'=>$request->email], time() + $exp);
            return response()->json([
                'status'=>true,
                'message'=> 'Otp Verified',
            ],200)->cookie('resetPasswordToken',$token['token'], $exp);

        }catch(\Exception $e){
            Log::critical($e->getMessage() . ' '  . $e->getFile() . ' ' . $e->getLine());
            return response()->json([
                'status'=>false,
                'errors'=> ['Something went wrong'],
            ],500);
        }
    }


    public function resetPassword(ResetPasswordRequest $request){
        try{
            if(!$request->cookie('resetPasswordToken')){
                return response()->json([
                    'status'=>false,
                    'errors'=> ['Invalid password request attempt'],
                ],422);
            }

            $decode = JwtToken::verifyToken($request->cookie('resetPasswordToken'));
            if($decode['error']){
                return response()->json([
                    'status'=>false,
                    'message'=> $decode['message'],
                ],500);
            }

            $user = User::whereEmail($decode['payload']->email)->first();
            $user->password = $request->password;
            $user->save();

            return response()->json([
                'status'=>true,
                'message'=> 'Password has been reset',
            ],200)->withoutCookie('resetPasswordToken');

        }catch(\Exception $e){
            Log::critical($e->getMessage() . ' '  . $e->getFile() . ' ' . $e->getLine());
            return response()->json([
                'status'=>false,
                'message'=> 'Something went wrong',
            ],500);
        }
    }

}
