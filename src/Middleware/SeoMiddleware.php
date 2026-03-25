<?php

namespace ThreeOhEight\Seo\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use ThreeOhEight\Seo\Seo;

class SeoMiddleware
{
    public function handle(Request $request, Closure $next, string ...$directives): Response
    {
        $seo = app(Seo::class);
        $seo->robots(implode(', ', $directives));

        return $next($request);
    }
}
