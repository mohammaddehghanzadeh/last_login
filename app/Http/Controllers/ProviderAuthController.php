<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\services\ProviderService;
use Illuminate\Http\Request;

class ProviderAuthController extends Controller
{
    protected $providerService;
    public function __construct(ProviderService $providerService)
    {
        $this->providerService = $providerService;
    }
    public function register(RegisterRequest $request)
    {
        return $this->providerService->registerProvider($request->validated());
    }

    public function login(LoginRequest $request)
    {
        return $this->providerService->loginProvider($request->validated());
    }
}
