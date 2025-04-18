<?php

namespace App\Services;

use App\Http\Resources\LoginInfoResource;
use http\Client\Curl\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use mysql_xdevapi\Exception;

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
    public function storeLoginInfo($user)
    {
        $loginInfo = $user->logins()->create([
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'timezone' => request()->header('Time-Zone', 'UTC'),
            'login_at' => now(),
        ]);

        if (!$loginInfo){
            throw new Exception("Login info creation failed. Login::create() returned null. Email: " . $user->email);
        }
    }

    /**
     * Retrieve the last login details of the given user.
     *
     * This method attempts to fetch the most recent login record associated with the user.
     * If no login record is found, an exception is thrown and caught to handle the error gracefully.
     *
     * @param user The user whose last login information should be retrieved.
     *
     * @return array{
     *     message: string,
     *     status: bool,
     *     last_login?: \App\Models\Login|null
     * } Returns an array containing the status of the operation,
     *     a message, and optionally the last login record if available.
     */
    public function lastLogin($user):array
    {
        try {
            // Retrieve the most recent login record associated via polymorphic relation (loginable)
            $lastLogin = $user->logins()->latest('login_at')->first();

            // If no login record is found for the current polymorphic user (client or provider),
            // throw an exception with context for better error tracking.
            if (!$lastLogin) {
                $userType = get_class($user);
                $userId = $user->id;
                throw new Exception("No login records found for user [type: {$userType}, id: {$userId}].");
            }

            return [
                'message' => 'login records found.',
                'status' => true,
                'last_login' => $lastLogin,
            ];

        } catch (\Exception $e) {
            // Log the error for debugging and tracking
            Log::error($e);
            // In case of any exception (e.g., database issue), return a failure response
            return [
                'message' => 'An error occurred while retrieving login details.',
                'status' => false,
            ];
        }
    }
}
