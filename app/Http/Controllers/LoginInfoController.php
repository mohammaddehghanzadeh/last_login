<?php

namespace App\Http\Controllers;

use App\Http\Resources\LoginInfoResource;
use App\Services\LoginInfoService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class LoginInfoController extends Controller
{
    protected $LoginInfoService;
    public function __construct(LoginInfoService $LoginInfoService)
    {
        $this->LoginInfoService = $LoginInfoService;
    }


    public function lastLogin(Request $request){
        try {
            // Retrieve the resolved user instance from the request attributes (set by middleware)
            $user = $request->get('resolved_user');


            // Defensive check: make sure user::first() returned a valid instance.
            // Log email to help trace the issue if creation unexpectedly fails.
            if (!$user) {
                throw new Exception('User not found. user::where()->first() returned null. ');
            }

            // Retrieve the last login details of the given user
            $lastLogin = $this->LoginInfoService->lastLogin($user);

            // If the operation failed (status is false), return an error response with the message
            if (!$lastLogin['status']) {
                return response()->json([
                    'error' => $lastLogin['message']
                ]);
            }

            // Return the last login data wrapped in a LoginInfoResource for consistent API formatting
            return new LoginInfoResource($lastLogin['last_login']);

        }catch (\Exception $exception){
            // Log the error for debugging and tracking
            Log::error($exception->getMessage(),[
                'message' => $exception->getMessage(),
            ]);
            // In case of any exception (e.g., database issue), return a failure response
            return response()->json([
                'message' => 'An error occurred while retrieving login details.'
            ]);
        }
    }
}
