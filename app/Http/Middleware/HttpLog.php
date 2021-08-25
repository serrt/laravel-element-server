<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class HttpLog
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
        if ($this->shouldLog($request)) {
            $message = $this->getMessage($request);
            Log::info($this->formatMessage($message), $message);
        }
        return $next($request);
    }

    protected function shouldLog(Request $request)
    {
        $method = strtolower($request->method());
        $uri = $request->path();
        return config('app.debug') && !Str::contains($uri, ['log-viewer']) && in_array($method, ['get', 'post', 'put', 'delete']);
    }

    public function getMessage(Request $request)
    {
        return [
            'method' => strtoupper($request->getMethod()),
            'uri' => $request->path(),
            'body' => $request->all(),
        ];
    }

    protected function formatMessage(array $message)
    {
        return "{$message['method']} {$message['uri']}";
    }
}
