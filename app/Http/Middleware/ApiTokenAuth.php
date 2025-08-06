<?php

namespace App\Http\Middleware;

use Closure;
use App\User;
use Illuminate\Support\Facades\Cache;

class ApiTokenAuth
{
    public function handle($request, Closure $next)
    {
        // ✅ Skip auth for non-API routes or internal Guzzle calls
        if (
            !$request->is('api/*') ||
            stripos($request->header('User-Agent'), 'GuzzleHttp') !== false
        ) {
            return $next($request);
        }

        // 🔐 Token from header or input
        $token = $request->header('X-Auth-Token') ?: $request->input('token');

        if (empty($token)) {
            return $this->unauthorizedResponse('Authentication token missing');
        }

        // ✅ Check cache for token validity
        $userId = Cache::get('token_' . $token);

        if (!$userId) {
            return $this->unauthorizedResponse('Token expired or invalid');
        }

        // 🔁 Refresh token expiration time (e.g., 2 hours)
        Cache::put('token_' . $token, $userId, now()->addHours(2));

        // 🔄 Retrieve and log in the user
        $user = User::find($userId);
        if (!$user || $user->token !== $token) {
            return $this->unauthorizedResponse('Invalid authentication token');
        }

        auth()->login($user);
        $request->merge(['user' => $user]);

        return $next($request);
    }

    private function unauthorizedResponse($message)
    {
        $accept = request()->header('Accept');
        $responseData = [
            'status' => 'FAIL',
            'message' => $message
        ];

        if (str_contains($accept, 'application/xml')) {
            $xml = new \SimpleXMLElement('<Response/>');
            foreach ($responseData as $key => $value) {
                $xml->addChild($key, $value);
            }

            return response($xml->asXML(), 401)
                ->header('Content-Type', 'application/xml');
        }

        return response()->json($responseData, 401);
    }
}
