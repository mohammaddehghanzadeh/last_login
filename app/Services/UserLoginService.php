<?php

namespace App\Services;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserLoginService
{
    protected $LoginInfoService;

    public function __construct(LoginInfoService $LoginInfoService)
    {
        $this->LoginInfoService = $LoginInfoService;
    }

    /**
     * Handle user login and generate an API token.
     *
     * This method checks if the user exists by the user email, verifies the user password,
     * logs the user in, deletes any existing tokens, and generates a new authentication token for the user.
     *
     * If login is successful, a JSON response with a success message and the generated token is returned.
     * If the login fails (due to wrong credentials or non-existent user), an error message returned.
     *
     * @param array $data The user login credentials, including 'email' and 'password'.
     * @param object $user An instance of the user model (e.g., Provider or Client).
     * @return array response with the login result. On success, it contains the success message
     * and the generated token. On failure, it contains an error message.
     * @throws Exception If token generation fails after successful password verification.
     */
    public function login(array $data, object $user): array
    {
        try {
            // Verify password
            if (Hash::check($data['password'], $user->password)) {

                // Delete any existing tokens
                $user->tokens()->delete();

                // Generate a new token
                $token = $user->createToken('auth_token')->plainTextToken;

                // Defensive check: createToken() should return a valid token string.
                // If token creation fails, throw an exception including the user's email to help debugging.
                if (!$token) {
                    throw new Exception('Password was verified, but token generation failed. User::createToken() returned null. Email: ' . $user->email);
                }

                $this->LoginInfoService->storeLoginInfo($user);

                // Return successful response with the token
                return ([
                    'message' => 'Login successfully',
                    'token' => $token,
                ]);
            }

            // If the password is incorrect, return unauthorized response
            return (['message' => 'Unauthorized']);

        } catch (ModelNotFoundException $e) {
            // Log the error for debugging and tracking
            Log::error('User login failed: ' . $e->getMessage(), [
                'exception' => $e,
                'email' => $data['email'] ?? null,
            ]);
            // If user not found, return unauthorized response
            return (['message' => 'Unauthorized']);

        }
    }
}
