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
        $this->assertIsObject(JSON::decode('{"foo": "bar"}'));
    }

    #[Test]
    public function it_forces_an_array(): void
    {
        $this->assertIsArray(JSON::decode('{"foo": "bar"}', true));
    }

    #[Test]
    public function it_decodes_large_integers_to_string(): void
    {
        $object = JSON::decode('{"large": 9223372036854775808}');
        assert(is_object($object));

        $this->assertObjectHasProperty('large', $object);
        $this->assertIsString($object->large);
        $this->assertSame('9223372036854775808', $object->large);
    }

    #[Test]
    public function it_decodes_a_file(): void
    {
        $path = __DIR__.'/valid.json';
        assert(file_exists($path));

        $this->assertIsObject(Json::decodeFile($path));
    }

    #[Test]
    public function it_rejects_an_unreadable_file(): void
    {
        $path = __DIR__.'/non-existing.json';

        $this->expectException(UnexpectedValueException::class);
        assert(!file_exists($path));

        Json::decodeFile($path);
    }

    #[Test]
    public function it_rejects_a_file_with_invalid_json(): void
    {
        $path = __DIR__.'/invalid.json';
        assert(file_exists($path));

        $this->expectException(UnexpectedValueException::class);
        Json::decodeFile($path);
    }

    #[Test]
    public function it_rejects_a_directory(): void
    {
        $this->expectException(UnexpectedValueException::class);
        Json::decodeFile(__DIR__);
    }

    #[Test]
    public function it_resolves_links(): void
    {
        $path = __DIR__.'/valid.json';
        $symlinkPath = __DIR__.'/'.__FUNCTION__.'.json';

        try {
            $this->assertNotFalse(symlink($path, $symlinkPath));
            $this->assertTrue(is_link($symlinkPath));

            $this->assertIsObject(Json::decodeFile($symlinkPath));
        } finally {
            unlink($symlinkPath);
        }
    }

    #[Test]
    public function it_resolves_relative_links(): void
    {
        $path = __DIR__.'/symlinked/symlinked.json';

        $this->assertIsObject(Json::decodeFile($path));
    }
}
