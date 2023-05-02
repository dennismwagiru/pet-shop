<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class JSONWebTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response) $next
     * @return Response
     *
     * @JWT Headers
     * {
     *   "alg": "HS256",
     *   "typ": "JWT"
     * }
     *
     * TODO Add expiry Check
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authorization = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $authorization);

        // split the jwt
        $tokenParts = explode('.', $token);
        $header = base64_decode($tokenParts[0]);
        $payload = base64_decode($tokenParts[1]);
        $signature_provided = $tokenParts[2];


        // build a signature based on the header and payload using the secret
        $base64_url_header = $this->base64url_encode($header);
        $base64_url_payload = $this->base64url_encode($payload);
        $signature = hash_hmac('SHA256', $base64_url_header . "." . $base64_url_payload, config('settings.jwt.secret'), true);
        $base64_url_signature = $this->base64url_encode($signature);

        // verify it matches the signature provided in the jwt
        $is_signature_valid = ($base64_url_signature === $signature_provided);

        if (!$is_signature_valid) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }
        return $next($request);
    }

    /**
     * @param $str
     * @return string
     */
    private function base64url_encode($str): string {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    }
}
