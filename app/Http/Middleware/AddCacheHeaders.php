<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddCacheHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldCache($request)) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        }

        return $response;
    }

    protected function shouldCache(Request $request): bool
    {
        return $request->is('storage/*') ||
               $request->is('build/*') ||
               str_ends_with($request->path(), '.css') ||
               str_ends_with($request->path(), '.js') ||
               str_ends_with($request->path(), '.webp') ||
               str_ends_with($request->path(), '.jpg') ||
               str_ends_with($request->path(), '.jpeg') ||
               str_ends_with($request->path(), '.png') ||
               str_ends_with($request->path(), '.svg');
    }
}
