<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\ArrayOfResources;

class ArrayOfResourcesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     */
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

    /**
     * @test         Get value
     */
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

    /**
     * @test         Debug info
     */
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

    /**
     * @test         Countable interface
     */
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

    /**
     * @test         Iterator interface
     */
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

    /**
     * @test         Invalid value
     * @dataProvider dataProviderForInvalidValues
     * @param        array $values
     */
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
