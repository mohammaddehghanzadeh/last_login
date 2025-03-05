<?php

namespace App\Http\Controllers;

use App\Services\LoginInfoService;
use Illuminate\Http\Request;

class LoginInfoController extends Controller
{
    protected $LoginInfoService;
    public function __construct(LoginInfoService $LoginInfoService)
    {
        $this->LoginInfoService = $LoginInfoService;
    }

    public function lastLogin()
    {
        return $this->LoginInfoService->lastLogin();
    }
}
