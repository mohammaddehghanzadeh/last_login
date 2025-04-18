<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Provider;
use App\services\ProviderRegisterService;
use App\Services\UserLoginService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AuthProviderController extends Controller
{
    protected $providerService;
    protected $userLoginService;

    public function __construct(ProviderRegisterService $providerService, UserLoginService $userLoginService)
    {
        $this->providerService = $providerService;
        $this->userLoginService = $userLoginService;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $this->providerService->registerProvider($request->validated());
        return response()->json($data);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // Find provider by email
            $provider = Provider::query()->where('email', $request->get('email'))->first();

            if (!$provider) {
                throw new Exception('User lookup failed. Provider::where()->first() returned null. Email: ' . $request->get('email'));
            }

            $data = $this->userLoginService->login($request->validated(), $provider);
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
