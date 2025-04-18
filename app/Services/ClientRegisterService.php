<?php

namespace App\Services;

use App\Models\Client;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ClientRegisterService
{
    protected $LoginInfoService;
    public function __construct(LoginInfoService $LoginInfoService)
    {
        $this->LoginInfoService = $LoginInfoService;
    }

    /**
     * Handle client registration and generate an API token.
     *
     * This method creates a new client based on the provided data (name, email, password),
     * generates an authentication token for the newly registered client, and returns a
     * array response with a success message and the generated token.
     *
     * Additionally, upon successful registration, a login entry for the client is stored
     * in the 'logins' table to track the login activity.
     *
     * If an error occurs during the process (e.g., database issue), an array response
     * with a failure message is returned.
     *
     * @param array $data The client registration data. It should contain 'name', 'email', and 'password'.
     * @return array response with the registration result. On success, it contains the success message
     * and the generated token. On failure, it contains an error message.
     */
    public function registerClient(array $data): array
    {
        try {
            // Create a new client using the provided registration data
            $client = Client::query()->create([
                'name' => $data['name'],// Client's full name
                'email' => $data['email'],// Client's email address
                'password' => bcrypt($data['password']),// Password is hashed using bcrypt
            ]);

            // Defensive check: make sure client::create() returned a valid instance.
            // Log email to help trace the issue if creation unexpectedly fails.
            if (!$client) {
                throw new Exception('User creation failed. client::create() returned null. Email: ' . $data['email']);
            }

            // Delete any existing tokens
            $client->tokens()->delete();

            // Generate a new authentication token for the newly registered client
            $token = $client->createToken('auth_token')->plainTextToken;

            // Defensive check: createToken() should return a valid token string.
            // If token creation fails, throw an exception including the user's email to help debugging.
            if (!$token) {
                throw new Exception('User was created, but token generation failed. Email: ' . $client->email);
            }

            $this->LoginInfoService->storeLoginInfo($client);

            // Return a success response with the registration message and the generated token
            return ([
                'message' => 'Register successfully',
                'token' => $token,
            ]);

        } catch (Exception $e) {
            // Log the error for debugging and tracking
            Log::error('Client registration failed: ' . $e->getMessage(), [
                'exception' => $e,
                'email' => $data['email'] ?? null,
            ]);
            // In case of any exception (e.g., database issue), return a failure response
            return ([
                'message' => 'Register failed',
            ]);
        }
    }
}
