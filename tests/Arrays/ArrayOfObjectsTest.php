<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\ArrayOfObjects;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ArrayOfObjectsTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(array $values)
    {
        // When
        $arrayOfObjects = new ArrayOfObjects($values);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(array $values)
    {
        // Given
        $arrayOfObjects = new ArrayOfObjects($values);

        // When
        $obtainedValues = $arrayOfObjects->getValues();

        // Then
        $this->assertSame($values, $obtainedValues);

        // And
        foreach ($values as $value) {
            $this->assertIsObject($value);
        }
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(array $values)
    {
        // Given
        $arrayOfObjects = new ArrayOfObjects($values);

        // When
        $debugInfo = $arrayOfObjects->__debugInfo();

        // Then
        $this->assertSame($values, $debugInfo['values']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [[new \stdClass()]],
            [[new \stdClass(), new \stdClass()]],
            [[new \ArrayIterator([])]],
        ];
    }

    #[Test]
    public function testStringRepresentation()
    {
        // Given
        $arrayOfObjects = new ArrayOfObjects([new \stdClass()]);

        // When
        $stringRepresentation = (string) $arrayOfObjects;

        // Then
        $this->assertSame('[{}]', $stringRepresentation);
    }

    #[Test]
    public function testJsonSerialization()
    {
        // Given
        $arrayOfObjects = new ArrayOfObjects([new \stdClass()]);

        // When
        $jsonSerialization = \json_encode($arrayOfObjects);

        // Then
        $this->assertSame('[{}]', $jsonSerialization);
    }

    #[Test]
    #[DataProvider('dataProviderForValidCountedValues')]
    public function testCountableInterface(array $values, int $expectedCount)
    {
        // Given
        $arrayOfObjects = new ArrayOfObjects($values);

        // Then
        $this->assertCount($expectedCount, $arrayOfObjects);
    }

    #[Test]
    #[DataProvider('dataProviderForValidCountedValues')]
    public function testIteratorInterface(array $values, int $expectedCount)
    {
        // Given
        $arrayOfObjects = new ArrayOfObjects($values);

        // And
        $i = -1;
        $finalCount = $expectedCount - 1;

        // When
        foreach ($arrayOfObjects as $object) {
            $i++;
            $this->assertIsObject($object);
            $this->assertSame($values[$i], $object);
        }

        // Then
        $this->assertSame($finalCount, $i);

        // And when reset the iterator
        $i = -1;
        foreach ($arrayOfObjects as $object) {
            $i++;
            $this->assertIsObject($object);
            $this->assertSame($values[$i], $object);
        }

        // Then
        $this->assertSame($finalCount, $i);
    }

    public static function dataProviderForValidCountedValues(): array
    {
        return [
            [[new \stdClass()], 1],
            [[new \stdClass(), new \stdClass()], 2],
            [[new \ArrayIterator([])], 1],
        ];
    }

    #[Test]
    public function testStringRepresentationDoesNotPropagateThrowingJsonSerializable()
    {
        // Given an object whose jsonSerialize() throws — json_encode propagates
        // such exceptions directly (they are not wrapped as JsonException).
        $throwing = new class () implements \JsonSerializable {
            public function jsonSerialize(): mixed
            {
                throw new \RuntimeException('boom');
            }
        };
        $arr = new ArrayOfObjects([$throwing]);

        // When casting to string
        $rendered = (string) $arr;

        // Then __toString must not propagate the inner exception, and must not
        // route through print_r() / __debugInfo() (which could itself fatal).
        // It should fall back to a stable object(FQCN) marker.
        $this->assertStringContainsString('object(', $rendered);
    }

    #[Test]
    public function testStringRepresentationDoesNotInvokeDebugInfoOnFallback()
    {
        // Given an object whose __debugInfo() throws — print_r() invokes
        // __debugInfo() and PHP fatals if it does not return an array, so the
        // fallback path must avoid print_r() for arbitrary objects.
        $hostile = new class () implements \JsonSerializable {
            public function jsonSerialize(): mixed
            {
                throw new \RuntimeException('boom');
            }

            /** @return array<string, mixed> */
            public function __debugInfo(): array
            {
                throw new \RuntimeException('debug boom');
            }
        };
        $arr = new ArrayOfObjects([$hostile]);

        // When casting to string — this must not fatal nor propagate the throw.
        $rendered = (string) $arr;

        // Then the fallback marker is used.
        $this->assertStringContainsString('object(', $rendered);
    }

    #[Test]
    public function testStringRepresentationTerminatesOnSelfReturningJsonSerializable()
    {
        // Given a JsonSerializable whose jsonSerialize() returns $this, paired
        // with a throwing JsonSerializable that forces the top-level json_encode
        // to fail (otherwise PHP encodes $this as its properties `{}` and never
        // enters the fallback). Inside the fallback, drainage of the self-
        // returning object yields $this again — without a depth bump on the
        // recursive call, this would loop forever.
        $selfReturning = new class () implements \JsonSerializable {
            #[\Override]
            public function jsonSerialize(): mixed
            {
                return $this;
            }
        };
        $thrower = new class () implements \JsonSerializable {
            #[\Override]
            public function jsonSerialize(): mixed
            {
                throw new \RuntimeException('force fallback');
            }
        };
        $arr = new ArrayOfObjects([$selfReturning, $thrower]);

        // When casting to string — must terminate via the depth guard.
        $rendered = (string) $arr;

        // Then the recursion marker is emitted for the self-returning object.
        $this->assertStringContainsString('*RECURSION*', $rendered);
    }

    #[Test]
    public function testStringRepresentationDoesNotPropagateThrowFromNestedJsonSerializableProperty()
    {
        // Given a plain object whose public property is a JsonSerializable that
        // throws. json_encode() walks public properties and propagates the
        // exception, so the plain-object fallback must guard against it.
        $throwing = new class () implements \JsonSerializable {
            #[\Override]
            public function jsonSerialize(): mixed
            {
                throw new \RuntimeException('inner boom');
            }
        };
        $host = new \stdClass();
        $host->payload = $throwing;
        $arr = new ArrayOfObjects([$host]);

        // When casting to string — must not propagate the inner throw.
        $rendered = (string) $arr;

        // Then the fallback marker is used for the host object (the throw at
        // the top-level json_encode triggers the fallback, then json_encode on
        // $host itself propagates again and is caught by the plain-object
        // guard).
        $this->assertStringContainsString('object(', $rendered);
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(array $values)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $arrayOfObjects = new ArrayOfObjects($values);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [[]],
            [[1]],
            [[1.0]],
            [['string']],
            [[null]],
            [[true]],
        ];
    }
}
