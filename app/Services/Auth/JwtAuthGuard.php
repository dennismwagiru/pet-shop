<?php

namespace App\Services\Auth;

use App\Models\JwtToken;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class JwtAuthGuard implements Guard
{
    protected Request $request;
    protected UserProvider $provider;
    protected ?Authenticatable $user;

    /**
     * Create a new authentication guard.
     *
     * @param UserProvider $provider
     * @param Request $request
     */
    public function __construct(UserProvider $provider, Request $request)
    {
        $this->request = $request;
        $this->provider = $provider;
        $this->user = null;
    }

    /**
     * Determine if the user matches the credentials
     *
     * @param $user
     * @param $credentials
     * @return bool
     */
    public function hasValidCredentials($user, $credentials): bool
    {
        return ! is_null($user) && $this->provider->validateCredentials($user, $credentials);
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param array $credentials
     * @return bool
     */
    public function attempt(array $credentials = []): bool {
        $user = $this->provider->retrieveByCredentials($credentials);


        // If an implementation of UserInterface was returned, we'll ask the provider
        // to validate the user against the given credentials, and if they are in
        // fact valid we'll log the users into the application and return true.
        if ($user && $this->hasValidCredentials($user, $credentials)) {
            $this->setUser($user);
            return true;
        }

        return false;
    }

    /**
     * Attempt to authenticate a user using the given credentials with some additional callbacks
     *
     * @param array $credentials
     * @param $callbacks
     * @return bool
     */
    public function attemptWhen(array $credentials = [], $callbacks = null): bool
    {
        if ($this->attempt($credentials)) {

            $user = $this->provider->retrieveByCredentials($credentials);

            if ($this->hasValidCredentials($user, $credentials) && $this->shouldLogin($callbacks, $user)) {
                $this->setUser($user);

                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check(): bool
    {
        return ! is_null($this->user());
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest(): bool
    {
        return ! $this->check();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return Authenticatable|null
     */
    public function user(): ?Authenticatable
    {
        if (! is_null($this->user)) {
            return $this->user;
        }

        $jwtToken = $this->getTokenForRequest();

        if (is_null($jwtToken)) {
            return null;
        }


        if ($this->isTokenValid($jwtToken))
            $this->setUser($this->provider->retrieveById($jwtToken->user_id));

        return $this->user;
    }

    /**
     * Get the token for the current request.
     * @return JwtToken|null
     */
    public function getTokenForRequest (): ?JwtToken
    {
        $authorization = $this->request->header('Authorization');
        $token = str_replace('Bearer ', '', $authorization);

        return JwtToken::where('unique_id', $token)->first();
    }

    /**
     * Checks whether provided token is valid
     *
     * @param JwtToken $jwtToken
     * @return bool
     */
    public function isTokenValid(JwtToken $jwtToken): bool {
        if ($jwtToken->expires_at < now())
            return false;

        $token = $jwtToken->unique_id;

        // split the jwt
        $tokenParts = explode('.', $token);
        $header = base64_decode($tokenParts[0]);
        $payload = base64_decode($tokenParts[1]);
        $signature_provided = $tokenParts[2];


        // build a signature based on the header and payload using the secret
        $base64_url_header = base64url_encode($header);
        $base64_url_payload = base64url_encode($payload);
        $signature = hash_hmac('SHA256', $base64_url_header . "." . $base64_url_payload, config('settings.jwt.secret'), true);
        $base64_url_signature = base64url_encode($signature);

        // verify it matches the signature provided in the jwt
        return ($base64_url_signature === $signature_provided);
    }


    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|mixed|string|null
     */
    public function id(): mixed
    {
        if ($user = $this->user()) {
            return $this->user()->getAuthIdentifier();
        }
        return null;
    }

    /**
     * Validate a user's credentials
     *
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        if (empty($credentials['username']) || empty($credentials['password'])) {
            return false;
        }

        $user = $this->provider->retrieveByCredentials($credentials);

        if (! is_null($user) && $this->provider->validateCredentials($user, $credentials)) {
            $this->setUser($user);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Check whether user exists
     *
     * @return bool
     */
    public function hasUser(): bool
    {
        return !is_null($this->user);
    }

    /**
     * Set the current user.
     *
     * @param Authenticatable $user
     * @return $this
     */
    public function setUser(Authenticatable $user): static
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param $callbacks
     * @param Authenticatable $user
     * @return bool
     */
    protected function shouldLogin($callbacks, Authenticatable $user): bool
    {
        foreach (Arr::wrap($callbacks) as $callback) {
            if (! $callback($user, $this)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Logout current user
     *
     * @return void
     */
    public function logout(): void
    {
        $this->user?->jwt_tokens()
            ->where('expires_at', '>=', now())
            ->update(['expires_at' => now()]);
    }
}
