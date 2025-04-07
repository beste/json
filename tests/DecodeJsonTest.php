<?php

declare(strict_types=1);

namespace Beste\Json\Tests;

use Beste\Json;
use PHPUnit\Framework\Attributes\Test;
use SplFileObject;
use UnexpectedValueException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class DecodeJsonTest extends TestCase
{
    #[Test]
    public function it_rejects_invalid_json(): void
    {
        $this->expectException(UnexpectedValueException::class);

        Json::decode('{');
    }

    #[Test]
    public function it_decodes_to_an_object_by_default(): void
    {
        self::assertIsObject(JSON::decode('{"foo": "bar"}'));
    }

    #[Test]
    public function it_forces_an_array(): void
    {
        // @phpstan-ignore staticMethod.alreadyNarrowedType
        self::assertIsArray(JSON::decode('{"foo": "bar"}', true));
    }

    #[Test]
    public function it_decodes_large_integers_to_string(): void
    {
        $object = JSON::decode('{"large": 9223372036854775808}');
        assert(is_object($object));

        self::assertObjectHasProperty('large', $object);
        self::assertIsString($object->large);
        self::assertSame('9223372036854775808', $object->large);
    }

    #[Test]
    public function it_decodes_a_file(): void
    {
        $path = __DIR__.'/valid.json';
        assert(file_exists($path));

        self::assertIsObject(Json::decodeFile($path));
    }

    #[Test]
    public function it_rejects_an_unreadable_file(): void
    {
        $path = __DIR__.'/non-existing.json';

        self::expectException(UnexpectedValueException::class);
        assert(!file_exists($path));

        Json::decodeFile($path);
    }

    #[Test]
    public function it_rejects_a_file_with_invalid_json(): void
    {
        $path = __DIR__.'/invalid.json';
        assert(file_exists($path));

        self::expectException(UnexpectedValueException::class);
        Json::decodeFile($path);
    }

    #[Test]
    public function it_rejects_a_directory(): void
    {
        self::expectException(UnexpectedValueException::class);
        Json::decodeFile(__DIR__);
    }

    #[Test]
    public function it_resolves_links(): void
    {
        $path = __DIR__.'/valid.json';
        $symlinkPath = __DIR__.'/'.__FUNCTION__.'.json';

        try {
            self::assertNotFalse(symlink($path, $symlinkPath));
            self::assertTrue(is_link($symlinkPath));

            self::assertIsObject(Json::decodeFile($symlinkPath));
        } finally {
            unlink($symlinkPath);
        }
    }

    #[Test]
    public function it_resolves_relative_links(): void
    {
        $path = __DIR__.'/symlinked/symlinked.json';

        self::assertIsObject(Json::decodeFile($path));
    }
}
