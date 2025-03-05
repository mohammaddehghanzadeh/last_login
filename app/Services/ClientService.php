<?php

namespace App\Services;

use App\Models\Client;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ClientService
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
     * JSON response with a success message and the generated token.
     *
     * If an error occurs during the process (e.g., database issue), a JSON response
     * with a failure message is returned.
     *
     * @param array $data The client registration data. It should contain 'name', 'email', and 'password'.
     * @return JsonResponse JSON response with the registration result. On success, it contains the success message
     * and the generated token. On failure, it contains an error message.
     */
    public function registerClient(array $data): JsonResponse
    {
        try {
            // Create a new client using the provided registration data
            $client = Client::query()->create([
                'name' => $data['name'],// Client's full name
                'email' => $data['email'],// Client's email address
                'password' => bcrypt($data['password']),// Password is hashed using bcrypt
            ]);

            // Generate a new authentication token for the newly registered client
            $token = $client->createToken('auth_token')->plainTextToken;

            // Return a success response with the registration message and the generated token
            return response()->json([
                'message' => 'Register successfully',
                'token' => $token,
            ], 201);// HTTP status code 201 indicates successful resource creation

        } catch (Exception $e) {
            // In case of any exception (e.g., database issue), return a failure response
            return response()->json([
                'message' => 'Register failed',
            ], 500);// HTTP status code 500 indicates a server error
        }
    }

    /**
     * Handle client login and generate an API token.
     *
     * This method checks if the client exists by the provided email, verifies the provided password,
     * logs the client in, deletes any existing tokens, and generates a new authentication token for the client.
     *
     * If login is successful, a JSON response with a success message and the generated token is returned.
     * If the login fails (due to wrong credentials or non-existent client), an error message with a 401 status is returned.
     *
     * @param array $data The client login credentials, including 'email' and 'password'.
     * @return JsonResponse JSON response with the login result. On success, it contains the success message
     * and the generated token. On failure, it contains an error message.
     */
    public function loginClient(array $data): TokenResource|JsonResponse
    {
        try {
            // Find client by email
            $client = Client::where('email', $data['email'])->firstOrFail();

            // Verify password
            if (Hash::check($data['password'], $client->password)) {
                // Log the client in
                Auth::login($client);

                // Delete any existing tokens
                $client->tokens()->delete();

                // Generate a new token
                $token = $client->createToken('auth_token')->plainTextToken;

                $this->LoginInfoService->storeLoginInfo();

                // Return successful response with the token
                return response()->json([
                    'message' => 'Login successfully',
                    'token' => $token,
                ]);
            }

            // If the password is incorrect, return unauthorized response
            return response()->json(['message' => 'Unauthorized'], 401); // HTTP status code 401 indicates unauthorized access

        } catch (ModelNotFoundException $e) {
            // If client not found, return unauthorized response
            return response()->json(['message' => 'Unauthorized'], 401); // HTTP status code 401 indicates unauthorized access

        }
    }
}
