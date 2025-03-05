<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'last_login_time' => $this->login_at,
            'ip_address' => $this->ip_address,
            'timezone' => $this->timezone,
            'user_agent' => $this->user_agent,
        ];
    }
}
