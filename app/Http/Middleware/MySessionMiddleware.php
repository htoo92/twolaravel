<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class MySessionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = User::where([
            ["email","=",$request->get("email")]
        ])->first();
       
        if($user && $user->isLogin){
            // redirect login with message
            return redirect('/errors');
        }
        return $next($request);
    }
}
