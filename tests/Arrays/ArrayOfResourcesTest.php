<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\ArrayOfResources;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ArrayOfResourcesTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function testValidValue()
    {
        // Given
        $resource = \tmpfile();

        // When
        $arrayOfResources = new ArrayOfResources([$resource]);

        // Then
        $this->expectNotToPerformAssertions();

        // Cleanup
        \fclose($resource);
    }

    #[Test]
    public function testGetValue()
    {
        // Given
        $resource = \tmpfile();
        $values = [$resource];
        $arrayOfResources = new ArrayOfResources($values);

        // When
        $obtainedValues = $arrayOfResources->getValues();

        // Then
        $this->assertSame($values, $obtainedValues);

        // And
        foreach ($values as $value) {
            $this->assertIsResource($value);
        }

        // Cleanup
        \fclose($resource);
    }

    #[Test]
    public function testDebugInfo()
    {
        // Given
        $resource = \tmpfile();
        $values = [$resource];
        $arrayOfResources = new ArrayOfResources($values);

        // When
        $debugInfo = $arrayOfResources->__debugInfo();

        // Then
        $this->assertSame($values, $debugInfo['values']);

        // Cleanup
        \fclose($resource);
    }

    #[Test]
    public function testCountableInterface()
    {
        // Given
        $resource1 = \tmpfile();
        $resource2 = \tmpfile();
        $arrayOfResources = new ArrayOfResources([$resource1, $resource2]);

        // Then
        $this->assertCount(2, $arrayOfResources);

        // Cleanup
        \fclose($resource1);
        \fclose($resource2);
    }

    #[Test]
    public function testIteratorInterface()
    {
        // Given
        $resource1 = \tmpfile();
        $resource2 = \tmpfile();
        $values = [$resource1, $resource2];
        $arrayOfResources = new ArrayOfResources($values);

        // And
        $i = -1;
        $finalCount = 1;

        // When
        foreach ($arrayOfResources as $resource) {
            $i++;
            $this->assertIsResource($resource);
            $this->assertSame($values[$i], $resource);
        }

        // Then
        $this->assertSame($finalCount, $i);

        // And when reset the iterator
        $i = -1;
        foreach ($arrayOfResources as $resource) {
            $i++;
            $this->assertIsResource($resource);
            $this->assertSame($values[$i], $resource);
        }

        // Then
        $this->assertSame($finalCount, $i);

        // Cleanup
        \fclose($resource1);
        \fclose($resource2);
    }

    #[Test]
    public function testStringRepresentationDoesNotThrowOnResources()
    {
        // Given a resource (which json_encode cannot serialize)
        $resource = \tmpfile();
        $arrayOfResources = new ArrayOfResources([$resource]);

        // When casting to string
        $stringRepresentation = (string) $arrayOfResources;

        // Then __toString must not throw and must produce a stable representation
        $this->assertStringContainsString('resource(#', $stringRepresentation);

        // Cleanup
        \fclose($resource);
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(array $values)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $arrayOfResources = new ArrayOfResources($values);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [[]],
            [[1]],
            [['string']],
            [[null]],
            [[new \stdClass()]],
        ];
    }
}
