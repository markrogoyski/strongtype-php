<?php

declare(strict_types=1);

namespace StrongType\Tests;

use StrongType\Util\Stringify;

class StringifyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         NAN returns 'NAN' string without triggering a warning
     */
    public function testNan()
    {
        $this->assertSame('NAN', Stringify::value(\NAN));
    }

    /**
     * @test         Scalar values are stringified via print_r
     * @dataProvider dataProviderForScalarValues
     */
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

    /**
     * @test         Array values are stringified via print_r
     */
    public function testArrayValue()
    {
        $result = Stringify::value([1, 2, 3]);
        $this->assertStringContainsString('Array', $result);
        $this->assertStringContainsString('1', $result);
    }
}
