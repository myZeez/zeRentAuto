<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SetResponseContentLength
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('HEAD') || $response->isEmpty()
            || $response instanceof StreamedResponse || $response instanceof BinaryFileResponse
            || $response->headers->has('Transfer-Encoding') || $response->headers->has('Content-Encoding')) {
            return $response;
        }

        $content = $response->getContent();

        if ($content !== false) {
            // VS Code tunnels need an explicit end for buffered PHP responses.
            $response->headers->set('Content-Length', (string) strlen($content));
        }

        return $response;
    }
}
