<?php

declare(strict_types=1);

namespace Nazim\Http\Util;

class JsonSerializer
{
    /**
     * Encode data to JSON
     *
     * @param mixed $data The data to encode
     * @param int $options JSON encoding options
     * @param int $depth Maximum depth
     * @return string The JSON encoded string
     * @throws \JsonException If encoding fails
     */
    public static function encode(
        mixed $data,
        int $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        int $depth = 512
    ): string {
        return json_encode($data, $options | JSON_THROW_ON_ERROR, $depth);
    }

    /**
     * Decode JSON to data
     *
     * @param string $json The JSON string to decode
     * @param bool $assoc Whether to return associative arrays
     * @param int $depth Maximum recursion depth
     * @param int $options JSON decoding options
     * @return mixed The decoded data
     * @throws \JsonException If decoding fails
     */
    public static function decode(
        string $json,
        bool $assoc = true,
        int $depth = 512,
        int $options = 0
    ): mixed {
        return json_decode($json, $assoc, $depth, $options | JSON_THROW_ON_ERROR);
    }
} 