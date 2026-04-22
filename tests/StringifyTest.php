<?php

declare(strict_types=1);

namespace StrongType\Tests;

use StrongType\Util\Stringify;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class StringifyTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function testNan()
    {
        $this->assertSame('NAN', Stringify::value(\NAN));
    }

    #[Test]
    #[DataProvider('dataProviderForScalarValues')]
    public function testScalarValues(mixed $value, string $expected)
    {
        $this->assertSame($expected, Stringify::value($value));
    }

    public static function dataProviderForScalarValues(): array
    {
        return [
            'int'          => [42, '42'],
            'float'        => [3.14, '3.14'],
            'string'       => ['hello', 'hello'],
            'bool true'    => [true, '1'],
            'bool false'   => [false, ''],
            'null'         => [null, ''],
            'INF'          => [\INF, 'INF'],
            'negative INF' => [-\INF, '-INF'],
        ];
    }

    #[Test]
    public function testArrayValue()
    {
        $result = Stringify::value([1, 2, 3]);
        $this->assertStringContainsString('Array', $result);
        $this->assertStringContainsString('1', $result);
    }

    #[Test]
    public function testNestedArrayValue()
    {
        $result = Stringify::value(['outer' => ['inner' => 42]]);

        $this->assertStringContainsString('outer', $result);
        $this->assertStringContainsString('inner', $result);
        $this->assertStringContainsString('42', $result);
    }

    #[Test]
    public function testEmptyArrayValue()
    {
        $result = Stringify::value([]);

        $this->assertStringContainsString('Array', $result);
    }

    #[Test]
    public function testStringableObject()
    {
        $object = new class {
            public function __toString(): string
            {
                return 'stringable-value';
            }
        };

        $result = Stringify::value($object);

        // print_r does not invoke __toString; it dumps object state.
        $this->assertStringContainsString('Object', $result);
    }

    #[Test]
    public function testPlainObject()
    {
        $object = new \stdClass();
        $object->foo = 'bar';
        $object->qux = 7;

        $result = Stringify::value($object);

        $this->assertStringContainsString('stdClass', $result);
        $this->assertStringContainsString('foo', $result);
        $this->assertStringContainsString('bar', $result);
    }

    #[Test]
    public function testResource()
    {
        $resource = \fopen('php://memory', 'r');
        try {
            $result = Stringify::value($resource);
            $this->assertStringContainsString('Resource', $result);
        } finally {
            \fclose($resource);
        }
    }

    #[Test]
    public function testClosure()
    {
        $closure = function () {
            return 'hi';
        };

        $result = Stringify::value($closure);

        $this->assertStringContainsString('Closure', $result);
    }
}
