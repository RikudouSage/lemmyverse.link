<?php

namespace Service;

use App\Service\CommunityNameParser;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CommunityNameParserTest extends TestCase
{
    /**
     * @dataProvider getTestData
     */
    public function testParse(
        string $valueToTest,
        bool $success,
        ?string $expectedName = null,
        ?string $expectedInstance = null,
    ): void {
        $instance = new CommunityNameParser();

        if (!$success) {
            $this->expectException(InvalidArgumentException::class);
            $instance->parse($valueToTest);
        } else {
            $result = $instance->parse($valueToTest);
            $this->assertSame($expectedName, $result->name);
            $this->assertSame($expectedInstance, $result->homeInstance);
        }
    }

    public static function getTestData(): iterable
    {
        yield ['wwdits@lemmings.world', true, 'wwdits', 'lemmings.world'];

        yield ['some_community@subdomain.example.com', true, 'some_community', 'subdomain.example.com'];

        yield ['https://wwdits@lemmings.world', false];

        yield ['wwdits', false];

        yield ['lemmings.world', false];

        yield ['<script>alert("hello")</script>', false];

        yield ['<script>"wwdits@lemmings.world"</script>', false];
    }
}
