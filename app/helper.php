<?php

if (!function_exists('base64url_encode')) {
    /**
     * Base64-URL encoded function
     *
     * @param string $str
     * @return string
     */
    function base64url_encode(string $str): string
    {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    }
}

if (!function_exists('boolean')) {
    /**
     * Cast possible string to a bool
     *
     * @param string|bool|null $variable
     * @return bool
     */
    function boolean(string|bool|null $variable): bool
    {
        if (is_null($variable)) {
            return false;
        }

        $typeOfVar = gettype($variable);
        if ($typeOfVar === 'boolean') {
            return $variable;
        } elseif ($typeOfVar === 'string') {
            return $variable === 'true';
        }
        return false;
    }
}
