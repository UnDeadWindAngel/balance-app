<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonRequest
{
    public function handle(Request $request, Closure $next)
    {
        // Принудительно устанавливаем JSON для API запросов
        if ($request->is('api/*') && $request->isMethod('POST')) {
            $contentType = $request->header('Content-Type');

            // Если Content-Type содержит application/json, но данные не парсятся
            if (str_contains($contentType, 'application/json')) {
                $content = $request->getContent();

                if (!empty($content)) {
                    $data = json_decode($content, true);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        $request->merge($data);
                    }
                }
            }
        }

        return $next($request);
    }
}
