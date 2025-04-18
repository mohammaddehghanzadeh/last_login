<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class ResolveSanctumUser
{
    /**
     * Handle an incoming request by resolving the authenticated user
     * based on the Sanctum Bearer token and attaching it to the request.
     *
     * This middleware checks the incoming Bearer token, locates the corresponding
     * tokenable model (e.g., Client or Provider), and makes it accessible to
     * controllers through the `resolved_user` request attribute.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): \Symfony\Component\HttpFoundation\Response  $next
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     *         If the token is missing or invalid
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Get Bearer Token from request header
            $accessToken = $request->bearerToken();

            // Find the token in the database
            $token = PersonalAccessToken::findToken($accessToken);

            if (!$token) {
                throw new UnauthorizedHttpException('', "Invalid or missing access token. ");
            }

            // Get the actual user associated with the token
            $user = $token->tokenable;

            // Share user with request
            $request->merge(['resolved_user' => $user]);

            return $next($request);

        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'Unauthorized access.',
            ], 401);
        }
    }
}
