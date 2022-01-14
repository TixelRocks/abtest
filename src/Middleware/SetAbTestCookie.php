<?php

namespace Tixel\AbTest\Middleware;

use Tixel\AbTest\AbTest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class SetAbTestCookie
{
    protected $cookieTtl = 86400;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $request->hasCookie(AbTest::COOKIE_NAME)) {
            AbTest::randomise();
            Cookie::queue(AbTest::COOKIE_NAME, abTest()->version(), $this->cookieTtl);
        }

        return $next($request);
    }
}
