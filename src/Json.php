<?php

declare(strict_types=1);

namespace Beste;

use JsonException;
use SplFileInfo;
use SplFileObject;
use Throwable;
use UnexpectedValueException;

final class Json
{
    private const ENCODE_DEFAULT = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
    private const ENCODE_PRETTY = self::ENCODE_DEFAULT | JSON_PRETTY_PRINT;
    private const DECODE_DEFAULT = JSON_BIGINT_AS_STRING;

    /**
     * param non-empty-string $json
     *
     * @throws UnexpectedValueException
     */
    public static function decode(string $json, ?bool $forceArray = null): mixed
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
     * @param non-empty-string $path
     *
     * @throws UnexpectedValueException
     */
    public static function decodeFile(string $path, bool $forceArray = null): mixed
    {
        $fileInfo = new SplFileInfo($path);

        if ($fileInfo->isLink() && $linkTarget = $fileInfo->getLinkTarget()) {
            $fileInfo = new SplFileInfo($linkTarget);
        }

        if (!$fileInfo->isFile()) {
            throw new UnexpectedValueException("`$path` does not point to a file.");
        }

        if (!$fileInfo->isReadable()) {
            throw new UnexpectedValueException("`$path` is not readable.");
        }

        $file = $fileInfo->openFile();
        $contents = $file->fread($file->getSize());

        if ($contents === false) {
            throw new UnexpectedValueException("Unable to read contents of `$path`");
        }

        if ($contents === '') {
            throw new UnexpectedValueException("The file at `$path` is empty");
        }

        return self::decode($contents, $forceArray);
    }

    /**
     * @throws UnexpectedValueException
     */
    public static function encode(mixed $data, ?int $options = null): string
    {
        $options = $options ?? 0;

        try {
            return json_encode($data, $options | self::ENCODE_DEFAULT | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new UnexpectedValueException($e->getMessage());
        }
    }

    /**
     * @throws UnexpectedValueException
     */
    public static function pretty(mixed $value, ?int $options = null): string
    {
        $options = $options ?? 0;

        return self::encode($value, $options | self::ENCODE_PRETTY);
    }
}
