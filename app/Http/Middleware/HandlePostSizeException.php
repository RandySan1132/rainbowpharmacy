<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Exceptions\PostTooLargeException;

class HandlePostSizeException
{
    public function handle($request, Closure $next)
    {
        try {
            return $next($request);
        } catch (PostTooLargeException $e) {
            return redirect()->back()->with('error', 'The uploaded file is too large.');
        }
    }
}
