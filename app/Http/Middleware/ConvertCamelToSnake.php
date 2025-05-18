<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ConvertCamelToSnake
{
    public function handle(Request $request, Closure $next)
    {
        // Merge all request inputs (query + form + JSON + multipart)
        $converted = [];

        foreach ($request->all() as $key => $value) {
            $converted[$this->camelToSnake($key)] = $value;
        }

        // Replace all input data (non-destructive to files)
        $request->merge($converted);

        return $next($request);
    }

    private function camelToSnake($input)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $input));
    }
}
