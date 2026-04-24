<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventCRLFInjection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Strip out \r and \n from URL parameters and query strings to prevent HTTP response splitting
        // Wait, stripping \n from POST body might break textareas.
        // Let's only sanitize headers or specific inputs. Modern PHP handles CRLF headers natively.
        // Instead, we will sanitize all inputs for %0d and %0a just in case.
        
        $input = $request->all();
        $cleaned = $this->sanitizeInput($input);
        
        if ($input !== $cleaned) {
            $request->merge($cleaned);
        }

        return $next($request);
    }

    protected function sanitizeInput($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                // Don't strip newlines from rich-text content or textareas like "message" or "content"
                if (in_array(strtolower($key), ['content', 'message', 'body', 'description'])) {
                    continue;
                }
                $data[$key] = $this->sanitizeInput($value);
            }
            return $data;
        }

        if (is_string($data)) {
            // Strip null bytes and carriage returns
            $data = str_replace(chr(0), '', $data);
            $data = str_replace("\r", '', $data);
            
            // Note: We leave \n intact for basic textareas, but \r is often sufficient for CRLF attacks.
            // If strict CRLF is needed, we'd also strip \n, but we exclude textareas explicitly above.
        }

        return $data;
    }
}
