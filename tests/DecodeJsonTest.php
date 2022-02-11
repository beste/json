<?php

declare(strict_types=1);

namespace Beste\Json\Tests;

use Beste\Json;
use UnexpectedValueException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class DecodeJsonTest extends TestCase
{
    /** @test */
    public function it_rejects_invalid_json(): void
    {
        $this->expectException(UnexpectedValueException::class);

        Json::decode('{');
    }

    /** @test */
    public function it_decodes_to_an_object_by_default(): void
    {
        $this->assertIsObject(JSON::decode('{"foo": "bar"}'));
    }

    /** @test */
    public function it_forces_an_array(): void
    {
        $this->assertIsArray(JSON::decode('{"foo": "bar"}', true));
    }

    /** @test */
    public function it_decodes_large_integers_to_string(): void
    {
        $object = JSON::decode('{"large": 9223372036854775808}');
        assert(is_object($object));

        $this->assertObjectHasAttribute('large', $object);
        $this->assertIsString($object->large);
        $this->assertSame('9223372036854775808', $object->large);
    }

    /** @test */
    public function it_decodes_a_file(): void
    {
        $path = __DIR__.'/valid.json';
        assert(file_exists($path));

        $this->assertIsObject(Json::decodeFile($path));
    }

    /** @test */
    public function it_rejects_an_unreadable_file(): void
    {
        $path = __DIR__.'/non-existing.json';

        $this->expectException(UnexpectedValueException::class);
        assert(!file_exists($path));

        Json::decodeFile($path);
    }

    /** @test */
    public function it_rejects_a_file_with_invalid_json(): void
    {
        $path = __DIR__.'/invalid.json';

        $this->expectException(UnexpectedValueException::class);
        assert(file_exists($path));

        $this->assertIsObject(Json::decodeFile($path));
    }
}
