<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\Api\V1\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    use ApiResponseTrait;

    public function redirect ()
    {
        return $this->successResponse([
            'redirect_url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl()
        ]);
    }   
    
    public function callbackGoogle ()
    {
        try {
            $google_user = Socialite::driver('google')->stateless()->user();
            $res = Arr::flatten($google_user->user);

            $user = User::where('google_id', $res[0])->first();
            if (!$user) {
                $new_user = User::create([
                    'name' => $res[1],
                    'email' => $res[5],
                    'picture' => $res[4],
                    'google_id' => $res[0],
                ]);

                $token = Auth::login($new_user);

                return $this->successResponse([
                    "user" => $user,
                    "token" => $token
                ], "Signup successful", 201);
            } else {
                $token = Auth::login($user);
                return $this->successResponse([
                    'token' => $token,
                    'user' => $user
                ]); 
            }
            
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
