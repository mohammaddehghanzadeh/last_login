<?php

namespace App\Services;

use App\Http\Resources\LoginInfoResource;
use Illuminate\Support\Facades\Auth;

class LoginInfoService
{
    /**
     * Store the user's last login information (Provider or Client).
     *
     * This method logs the user's login details, including IP address, User Agent,
     * Timezone, and login timestamp.
     *
     * @return void
     */
    public function storeLoginInfo()
    {
        $user = Auth::user();

        $user->logins()->create([
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'timezone' => request()->header('Time-Zone', 'UTC'),
            'login_at' => now(),
        ]);
    }

    /**
     * Retrieve the last login details of the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function lastLogin()
    {
        try {
            $user = Auth::user(); // Get the authenticated user

            if (!$user) {
                return response()->json([
                    'message' => 'User not authenticated.',
                ], 401);
            }

            // Get the last login record
            $lastLogin = $user->logins()->latest('login_at')->firstorfail();

            if (!$lastLogin) {
                return response()->json([
                    'message' => 'No login records found.',
                ], 404);
            }

            return new LoginInfoResource($lastLogin);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving login details.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
