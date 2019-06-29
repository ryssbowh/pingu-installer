<?php 

namespace PinguInstaller\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class RedirectToInstall
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
        return redirect()->route('install');
    }
}