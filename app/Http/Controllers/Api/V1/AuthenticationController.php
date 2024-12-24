<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\RegistrationVerifyRequest;
use App\Http\Requests\Api\V1\SignupRequest;
use App\Http\Requests\Api\V1\VerifyForgotPassword;
use App\Services\Api\V1\AuthenticationService;
use App\Traits\Api\V1\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticationController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private AuthenticationService $auth_service)
    {        
    }

    public function register (SignupRequest $request)
    {
        $_data = (Object) $request->validated();

        $request = $this->auth_service->register($_data);

        if ($request->success == true) {
            $token = Auth::login($request);
            return $this->successResponse([
                "user" => $request->message,
                "token" => $token
            ], "Signup successful", 201);
        }
        
        return $this->errorResponse($request->message);
    }

    public function resend(String $email)
    {
        $_data = (Object) array(
            "email" => $email,
        );

        $request = $this->auth_service->resend($_data);

        if ($request == null) {
            return $this->notFoundResponse('User not found.');
        }
        
        if ($request->success == true) {
            return $this->successResponse($request->message);
        } else{
            return $this->serverErrorResponse($request->message);
        }
    }

    public function verify (RegistrationVerifyRequest $request)
    {
        $_data = (Object) $request->validated();

        $request = $this->auth_service->verify($_data);
        
        if ($request->success == true) {
            return $this->successResponse($request->message);
        } else{
            return $this->serverErrorResponse($request->message);
        }
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $_data = (Object) $request->validated();

        $request = $this->auth_service->forgotPassword($_data);

        if ($request == null) {
            return $this->notFoundResponse('User not found.');
        }
        
        if ($request->success == true) {
            return $this->successResponse($request->message);
        } else{
            return $this->serverErrorResponse($request->message);
        }       
    }

    public function resendForgotPassword(ForgotPasswordRequest $request)
    {
        $_data = (Object) $request->validated();

        $request = $this->auth_service->forgotPassword($_data);
        
        if ($request == null) {
            return $this->notFoundResponse('User not found.');
        }
        
        if ($request->success == true) {
            return $this->successResponse($request->message);
        } else{
            return $this->serverErrorResponse($request->message);
        }   
    }

    public function verifyForgotPassword (RegistrationVerifyRequest $request)
    {
        $_data = (Object) $request->validated();

        $request = $this->auth_service->verifyForgot($_data);
        
        if ($request->success == true) {
            return $this->successResponse($request->message);
        } else{
            return $this->serverErrorResponse($request->message);
        }
    }

    public function changePassword (VerifyForgotPassword $request)
    {
        $_data = (Object) $request->validated();

        $request = $this->auth_service->changePassword($_data);
        
        if ($request->success == true) {
            return $this->successResponse($request->message);
        } else{
            return $this->serverErrorResponse($request->message);
        }

    }

    
}
