<?php

namespace App\Models;

use App\Models\Events\SetModelUuid;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $dispatchesEvents = [
        'creating' => SetModelUuid::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['uuid',
        'first_name', 'last_name', 'email', 'avatar', 'address', 'phone_number', 'is_marketing', 'is_admin',
        'password',
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

    public function jwtTokens(): HasMany
    {
        return $this->hasMany(JwtToken::class, 'user_id');
    }

    /**
     * @return JwtToken
     *
     * JWT Signature -> HMACSHA256(base64UrlEncode(header) + "." + base64UrlEncode(payload), secret)
     */
    public function generateJwtToken(): JwtToken
    {
        $expiresAt = now()->add('seconds', config('settings.jwt.lifetime'));
        $payload = [
            'iss' => config('app.url'), 'user_uuid' => $this->uuid, 'exp' => $expiresAt->timestamp,
        ];

        $headers_encoded = base64url_encode(json_encode(config('settings.jwt.headers')));
        $payload_encoded = base64url_encode(json_encode($payload));

        $signature = hash_hmac(
            algo: config('settings.jwt.algorithm'),
            data: "{$headers_encoded}.{$payload_encoded}",
            key: config('settings.jwt.secret'),
            binary: true
        );
        $signature_encoded = base64url_encode($signature);

        return JwtToken::create([
            'unique_id' => "{$headers_encoded}.{$payload_encoded}.{$signature_encoded}",
            'user_id' => $this->id,
            'token_title' => $this->first_name . ' ' . now()->timestamp,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Fetch user by Credentials
     *
     * @param array $credentials
     * @return Authenticatable|null
     */
    public function fetchUserByCredentials(array $credentials): ?Authenticatable
    {
        return User::where(['email' => $credentials['email']])->first();
    }
}
