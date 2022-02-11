<?php

declare(strict_types=1);

namespace Beste;

use JsonException;
use UnexpectedValueException;

final class Json
{
    private const ENCODE_DEFAULT = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
    private const ENCODE_PRETTY = self::ENCODE_DEFAULT | JSON_PRETTY_PRINT;
    private const DECODE_DEFAULT = JSON_BIGINT_AS_STRING;

    /**
     * @throws UnexpectedValueException
     *
     * @return mixed
     */
    public static function decode(string $json, ?bool $forceArray = null)
    {
        $forceArray = $forceArray ?? false;
        $flags = $forceArray ? JSON_OBJECT_AS_ARRAY : 0;

        try {
            return json_decode($json, $forceArray, 512, $flags | self::DECODE_DEFAULT | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new UnexpectedValueException($e->getMessage());
        }
    }

    /**
     * @throws UnexpectedValueException
     *
     * @return mixed
     */
    public static function decodeFile(string $path, bool $forceArray = null)
    {
        if (!is_readable($path)) {
            throw new UnexpectedValueException("The file at '$path' does not exist");
        }

        return self::decode((string) file_get_contents($path), $forceArray);
    }

    /**
     * @param mixed $data
     *
     * @throws UnexpectedValueException
     */
    public static function encode($data, ?int $options = null): string
    {
        $options = $options ?? 0;

        try {
            return json_encode($data, $options | self::ENCODE_DEFAULT | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new UnexpectedValueException($e->getMessage());
        }
    }

    /**
     * @param mixed $value
     *
     * @throws UnexpectedValueException
     */
    public static function pretty($value, ?int $options = null): string
    {
        $options = $options ?? 0;

        return self::encode($value, $options | self::ENCODE_PRETTY);
    }
}
