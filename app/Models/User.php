<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['uuid',
        'first_name', 'last_name', 'email', 'avatar', 'address', 'phone_number', 'is_marketing'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_admin' => 'boolean',
        'email_verified_at' => 'datetime',
        'is_marketing' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * @return string
     *
     * JWT Signature -> HMACSHA256(base64UrlEncode(header) + "." + base64UrlEncode(payload), secret)
     */
    public function generateJwtToken(): string {
        $payload = array(
            'iss' => config('app.url'),
            'user_uuid' => $this->uuid,
            'exp' => now()->add('seconds', config('settings.jwt.lifetime'))
        );

        $headers_encoded = base64url_encode(json_encode(config('settings.jwt.headers')));
        $payload_encoded = base64url_encode(json_encode($payload));

        $signature = hash_hmac(
            algo: config('settings.jwt.algorithm'),
            data: "$headers_encoded.$payload_encoded",
            key: config('settings.jwt.secret'),
            binary: true
        );
        $signature_encoded = base64url_encode($signature);

        return "$headers_encoded.$payload_encoded.$signature_encoded";
    }
}
