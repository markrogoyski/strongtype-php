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
        // When
        $result = Stringify::value(\NAN);

        // Then
        $this->assertSame('NAN', $result);
    }

    #[Test]
    #[DataProvider('dataProviderForScalarValues')]
    public function testScalarValues(mixed $value, string $expected)
    {
        // When
        $result = Stringify::value($value);

        // Then
        $this->assertSame($expected, $result);
    }

    public static function dataProviderForScalarValues(): array
    {
        return [
            'int'          => [42, '42'],
            'negative int' => [-7, '-7'],
            'float'        => [3.14, '3.14'],
            'string'       => ['hello', 'hello'],
            'empty string' => ['', ''],
            'bool true'    => [true, 'true'],
            'bool false'   => [false, 'false'],
            'null'         => [null, 'null'],
            'INF'          => [\INF, 'INF'],
            'negative INF' => [-\INF, '-INF'],
        ];
    }

    #[Test]
    public function testListArrayValue()
    {
        // When
        $result = Stringify::value([1, 2, 3]);

        // Then
        $this->assertSame('[1, 2, 3]', $result);
    }

    #[Test]
    public function testAssociativeArrayValue()
    {
        // When
        $result = Stringify::value(['outer' => ['inner' => 42]]);

        // Then
        $this->assertSame('[outer => [inner => 42]]', $result);
    }

    #[Test]
    public function testEmptyArrayValue()
    {
        // When
        $result = Stringify::value([]);

        // Then
        $this->assertSame('[]', $result);
    }

    #[Test]
    public function testDeeplyNestedArrayIsBounded()
    {
        // When
        $result = Stringify::value([[[[1]]]]);

        // Then
        // Recursion collapses to a marker once the depth cap is reached.
        $this->assertSame('[[[[...]]]]', $result);
    }

    #[Test]
    public function testLargeArrayIsTruncated()
    {
        // When
        $result = Stringify::value(\range(1, 20));

        // Then
        $this->assertSame('[1, 2, 3, 4, 5, 6, 7, 8, 9, 10, ...]', $result);
    }

    #[Test]
    public function testStringableObjectIsNotInvoked()
    {
        // Given
        $object = new class {
            public function __toString(): string
            {
                return 'stringable-value';
            }
        };

        // When
        $result = Stringify::value($object);

        // Then
        // Rendered by type name only; __toString is never invoked.
        $this->assertStringNotContainsString('stringable-value', $result);
        $this->assertStringContainsString('class@anonymous', $result);
    }

    #[Test]
    public function testPlainObjectRendersTypeNameOnly()
    {
        // Given
        $object = new \stdClass();
        $object->foo = 'bar';

        // When
        $result = Stringify::value($object);

        // Then
        // No property walk (which would invoke __debugInfo); type name only.
        $this->assertSame('stdClass', $result);
    }

    #[Test]
    public function testObjectWithHostileDebugInfoIsSafe()
    {
        // Given
        // print_r would invoke __debugInfo() and let this throw escape as an
        // uncatchable warning; Stringify must render the type name instead.
        $object = new class {
            public function __debugInfo(): array
            {
                throw new \RuntimeException('boom');
            }
        };

        // When
        $result = Stringify::value([$object]);

        // Then
        $this->assertStringContainsString('class@anonymous', $result);
    }

    #[Test]
    public function testResource()
    {
        // Given
        $resource = \fopen('php://memory', 'r');

        // When
        try {
            $result = Stringify::value($resource);
        } finally {
            \fclose($resource);
        }

        // Then
        $this->assertSame('resource (stream)', $result);
    }

    #[Test]
    public function testCyclicArrayIsBounded()
    {
        // Given
        $array = [1];
        $array[1] = &$array;

        // When
        $result = Stringify::value($array);

        // Then
        // Recursion collapses to a marker once the depth cap is reached, the
        // same as any other deeply-nested structure.
        $this->assertSame('[1, [1, [1, [...]]]]', $result);
    }

    #[Test]
    public function testClosure()
    {
        // Given
        $closure = function () {
            return 'hi';
        };

        // When
        $result = Stringify::value($closure);

        // Then
        $this->assertSame('Closure', $result);
    }
}
