<?php

declare(strict_types=1);

namespace Beste\Json\Tests;

use Beste\Json;
use PHPUnit\Framework\Attributes\Test;
use UnexpectedValueException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class EncodeJsonTest extends TestCase
{
    #[Test]
    public function it_does_not_escape_slashes(): void
    {
        self::assertSame('{"slash":"/"}', Json::encode(['slash' => '/']));
    }

    #[Test]
    public function it_does_not_escape_unicode(): void
    {
        self::assertSame('{"emoji":"🚀"}', Json::encode(['emoji' => '🚀']));
    }

    #[Test]
    public function it_rejects_invalid_resources(): void
    {
        self::expectException(UnexpectedValueException::class);

        // The point is that resources cannot be encoded, not what's in the file :)
        Json::encode(fopen(__DIR__.'/valid.json', 'rb'));
    }

    #[Test]
    public function it_pretty_prints(): void
    {
        $expected = <<<'PRETTY'
        {
            "pretty": "print"
        }
        PRETTY;

        self::assertSame($expected, Json::pretty(['pretty' => 'print']));
    }
}
