<?php

namespace App\Http\Middleware\Api\V1;

use App\Traits\Api\V1\ApiResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    use ApiResponseTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {        
        $user = Auth::user();
        if($user->role !== 'admin'){
            return $this->serverErrorResponse('Unauthorized access', Response::HTTP_FORBIDDEN);
        } else {
            return $next($request);
        }
    }
}
