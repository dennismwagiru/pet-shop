<?php

namespace App\Models;

use Eloquent;
use Illuminate\Support\Carbon;
use App\Models\Events\SetModelUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Services\Utilities\FilterBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotificationCollection;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $uuid
 * @property string $first_name
 * @property string $last_name
 * @property bool $is_admin
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string $avatar
 * @property string $address
 * @property string $phone_number
 * @property bool $is_marketing
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $last_login_at
 * @property-read Collection<int, JwtToken> $jwtTokens
 * @property-read int|null $jwt_tokens_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static Builder|User filterBy($filters)
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereAddress($value)
 * @method static Builder|User whereAvatar($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereFirstName($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereIsAdmin($value)
 * @method static Builder|User whereIsMarketing($value)
 * @method static Builder|User whereLastLoginAt($value)
 * @method static Builder|User whereLastName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User wherePhoneNumber($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereUuid($value)
 * @mixin Eloquent
 */
class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    /**
     * @var string[]
     */
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

    /**
     * @return HasMany<JwtToken>
     */
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
     * @param array<string, string> $credentials
     * @return Authenticatable|null
     */
    public function fetchUserByCredentials(array $credentials): ?Authenticatable
    {
        return User::where(['email' => $credentials['email']])->first();
    }

    /**
     * @param Builder<Model> $query
     * @param array<string, string> $filters
     * @return Builder<Model>
     */
    public function scopeFilterBy(Builder $query, array $filters): Builder
    {
        $filter = new FilterBuilder($query, $filters, 'App\Models\Filters\User');

        return $filter->apply();
    }
}
