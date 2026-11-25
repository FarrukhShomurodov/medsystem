<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refuses query and form input that is not valid UTF-8 or carries a NUL byte. Neither can be
 * typed into a form, and both break the database layer: PostgreSQL rejects the bytes (SQLSTATE
 * 22021, a 500 wherever a filter reaches a query) and PDO cuts a bound string at the NUL byte,
 * so the query would silently run on another value. Uploaded files are not inspected.
 */
class RejectMalformedInput
{
    public function handle(Request $request, Closure $next): Response
    {
        $body = $request->isJson() ? $request->json()->all() : $request->request->all();

        abort_if(
            ! $this->isWellFormed($request->query->all()) || ! $this->isWellFormed($body),
            Response::HTTP_BAD_REQUEST,
            'Запрос содержит недопустимые символы.',
        );

        return $next($request);
    }

    /**
     * @param  array<array-key, mixed>  $input
     */
    private function isWellFormed(array $input): bool
    {
        foreach ($input as $key => $value) {
            $valid = $this->isWellFormedString((string) $key) && match (true) {
                is_array($value) => $this->isWellFormed($value),
                is_string($value) => $this->isWellFormedString($value),
                default => true,
            };

            if (! $valid) {
                return false;
            }
        }

        return true;
    }

    private function isWellFormedString(string $value): bool
    {
        return mb_check_encoding($value, 'UTF-8') && ! str_contains($value, "\0");
    }
}
