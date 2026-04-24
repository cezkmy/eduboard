<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class XssSanitization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();
        $cleaned = $this->sanitizeXSS($input);
        
        if ($input !== $cleaned) {
            $request->merge($cleaned);
        }

        return $next($request);
    }

    protected function sanitizeXSS($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                // Exclude rich-text editor fields where HTML is expected.
                // We rely on Output Escaping or a dedicated HTML Purifier for these fields.
                if (in_array(strtolower($key), ['content', 'description', 'message'])) {
                    continue;
                }
                $data[$key] = $this->sanitizeXSS($value);
            }
            return $data;
        }

        if (is_string($data)) {
            // Strip potentially dangerous tags
            $data = preg_replace('/<(script|style|iframe|object|embed|applet|meta|xml|link|base)[^>]*>.*?(<\/\1>)?/is', '', $data);
            
            // Strip on* event handlers (e.g., onload, onerror, onclick)
            $data = preg_replace('/on[a-z]+=[^>]+/i', '', $data);
            
            // Strip javascript: protocols
            $data = preg_replace('/javascript:/i', '', $data);
            
            // Laravel's strip_tags can be too aggressive, so we use precise regex for basic inputs.
            // For standard input, we completely strip tags to be safe
            $data = strip_tags($data);
        }

        return $data;
    }
}
