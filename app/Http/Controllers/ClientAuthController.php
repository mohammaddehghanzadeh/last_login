<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\services\ClientService;
use Illuminate\Http\Request;

class ClientAuthController extends Controller
{
    protected $clientService;
    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }
    public function register(RegisterRequest $request)
    {
        return $this->clientService->registerClient($request->validated());
    }

    public function login(LoginRequest $request)
    {
        return $this->clientService->loginClient($request->validated());
    }
}
