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
