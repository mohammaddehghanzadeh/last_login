<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Client;
use App\services\ClientRegisterService;
use App\Services\UserLoginService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AuthClientController extends Controller
{
    protected $clientService;
    protected $userLoginService;

    public function __construct(ClientRegisterService $clientService, UserLoginService $userLoginService)
    {
        $this->clientService = $clientService;
        $this->userLoginService = $userLoginService;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $this->clientService->registerClient($request->validated());
        return response()->json($data);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // Find client by email
            $client = Client::query()->where('email', $request->get('email'))->first();

            if (!$client) {
                throw new Exception('User lookup failed. Client::where()->first() returned null. Email: ' . $request->get('email'));
            }

            $data = $this->userLoginService->login($request->validated(), $client);
            return response()->json($data);

        } catch (Exception $e) {
            // Log the error for debugging and tracking
            Log::error('User login failed: ' . $e->getMessage(), [
                'message' => $e->getMessage(),
                'email' => $request->get('email') ?? null,
            ]);
            // In case of any exception (e.g., database issue), return a failure response
            return response()->json(['message' => 'Unauthorized']);
        }
    }
}
