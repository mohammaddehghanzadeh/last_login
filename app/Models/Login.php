<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    use HasFactory;

    protected $fillable = [
        'userable_id',
        'userable_type',
        'ip_address',
        'user_agent',
        'timezone',
        'login_at',
    ];

    public function userable()
    {
        return $this->morphTo();
    }
}
