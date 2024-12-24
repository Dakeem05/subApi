<?php 

namespace App\Services\Api\V1;

use App\Mail\UserForgotPassword;
use App\Mail\UserVerifyEmail;
use App\Models\PasswordResetToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthenticationService {
    public function register (object $user_data) :object
    {
        $referral_code = $this->generateReferralCode();

        if (!$referral_code) {
            return (object) [
                'success' => false,
                'message' => 'Something went wrong while generating referral code; try again later.'
            ];
        }

        try {

            $user = User::create([
                'name' => $user_data->name,
                'email' => $user_data->email,
                'password' => Hash::make($user_data->password),
                'referral_code' => $referral_code,
                'referrer_code' => isset($user_data->referral_code) ? $user_data->referral_code : null,
            ]);
            
            if ($this->sendRegistrationMail($user, PasswordResetToken::GenerateOtp($user->email))) {
                return (object) [
                    'success' => true,
                    'message' => $user
                ];
            }
            return (object) [
                'success' => false,
                'message' => 'Something went wrong with the emailing system; try again later.'
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            return (object) [
                'success' => false,
                'message' => 'Something went wrong.'
            ];
        }
    }

    public function resend (object $user_data)
    {
        $user = User::where('email', $user_data->email)->where('email_verified_at', null)->first();
        
        if ($user !== null){
            if ($this->sendRegistrationMail($user, PasswordResetToken::GenerateOtp($user->email))) {
                return (object) [
                    'success' => true,
                    'message' => 'The otp has been resent.'
                ];
            }
            return (object) [
                'success' => false,
                'message' => 'Something went wrong with the emailing system; try again later.'
            ];
        } else {
            return null;
        }
    }

    public function verify (object $user_data)
    {
        $user = User::where('email' , $user_data->email)->first();
        $instance = PasswordResetToken::where('email', $user_data->email)->first();
        if ($instance !== null){
            if($user_data->otp == $instance->token){
                $user->update(['email_verified_at' => Carbon::now()]);
                $instance->delete();
                
                return (object) [
                    'success' => true,
                    'message' => 'Registration successful.'
                ];
            } else {
                return (object) [
                    'success' => false,
                    'message' => 'Wrong code? Resend.'
                ];
            }    
        } else {
            return (object) [
                'success' => false,
                'message' => 'Wrong code? Resend.'
            ];
        }
    }

    public function forgotPassword (object $user_data)
    {
        $user = User::where('email', $user_data->email)->first();
        
        if ($user !== null){
            if ($this->sendForgotPasswordMail($user, PasswordResetToken::GenerateOtp($user->email))) {
                return (object) [
                    'success' => true,
                    'message' => 'Sent, check your mail.'
                ];
            }

            return (object) [
                'success' => false,
                'message' => 'Something went wrong with the emailing system; try again later.'
            ];

        } else {
            return null;
        }
    }

    public function verifyForgot (object $user_data)
    {
        $user = User::where('email' , $user_data->email)->first();
        $instance = PasswordResetToken::where('email', $user_data->email)->first();
        if ($instance !== null){
            if($user_data->otp == $instance->token){
                $instance->otp_verified_at = Carbon::now();
                $instance->save();

                return (object) [
                    'success' => true,
                    'message' => 'Verification successful.'
                ];
            } else {
                return (object) [
                    'success' => false,
                    'message' => 'Wrong code? Resend.'
                ];
            }    
        } else {
            return (object) [
                'success' => false,
                'message' => 'Wrong code? Resend.'
            ];
        }
    }

    public function changePassword (object $user_data)
    {
        $user = User::where('email' , $user_data->email)->first();
        $instance = PasswordResetToken::where('email', $user_data->email)->first();
        if ($instance !== null){
            if ($instance->otp_verified_at !== null){
                $user->update([
                'password' => Hash::make($user_data->password),
                ]);
                $instance->delete();

                return (object) [
                    'success' => true,
                    'message' => 'Password changed.'
                ];

            } else {
                return (object) [
                    'success' => false,
                    'message' => 'User hasn\'t verified otp.'
                ];
            }
        } else {
            return (object) [
                'success' => false,
                'message' => 'User has not requested any otp.'
            ];
        }
        
    }

    private function sendForgotPasswordMail ($user, $otp) :bool
    {
        try {
            Mail::to($user->email)->send(new UserForgotPassword($user->email, $user->name, $otp));

            return true;
        }
        catch (\Exception $e) {
            Log::channel('site_issues')->info($e->getMessage());

            return false;
        }
    }

    private function sendRegistrationMail ($user, $otp) :bool
    {
        try {
            Mail::to($user->email)->send(new UserVerifyEmail($user->email, $user->name, $otp));

            return true;
        }
        catch (\Exception $e) {
            Log::channel('site_issues')->info($e->getMessage());

            return false;
        }
    }

    
    private function generateReferralCode($string_length = 7, $recursion_limit = 10)
    {
        if ($recursion_limit <= 0) {
            // We don't expect this to generate a code 10 times and all the codes are taken or for something to go wrong.
            // If such happens which is rare but not impossible, break out of the recursive loop.
            return null;
        }

        $randomString = Str::random($string_length);
        $code = 'vis-'.Str::lower($randomString);

        if (! $this->checkIfCodeExists($code)) {
            return $code;
        } else {
            return $this->generateReferralCode($string_length, $recursion_limit - 1);
        }
    }

    private function checkIfCodeExists(string $code)
    {
        return User::where('referral_code', $code)->exists();
    }   
}