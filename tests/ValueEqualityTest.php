<?php

declare(strict_types=1);

namespace StrongType\Tests;

use StrongType\Util\ValueEquality;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ValueEqualityTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForSamePairs')]
    public function testSame(mixed $a, mixed $b, bool $expected)
    {
        // When
        $same = ValueEquality::same($a, $b);

        // Then
        $this->assertSame($expected, $same);
    }

    public static function dataProviderForSamePairs(): array
    {
        return [
            'identical ints'            => [1, 1, true],
            'different ints'            => [1, 2, false],
            'int vs numeric string'     => [1, '1', false],
            'bool vs int'               => [true, 1, false],
            'identical strings'         => ['abc', 'abc', true],
            'different strings'         => ['abc', 'abd', false],
            'identical floats'          => [1.5, 1.5, true],
            'different floats'          => [1.5, 2.5, false],
            'NAN vs NAN'                => [\NAN, \NAN, true],
            'NAN vs a real float'       => [\NAN, 1.0, false],
            'NAN vs an int'             => [\NAN, 1, false],
            'INF vs INF'                => [\INF, \INF, true],
            'INF vs -INF'               => [\INF, -\INF, false],
            'identical arrays'          => [[1, 2], [1, 2], true],
            'differently ordered arrays' => [[1, 2], [2, 1], false],
            'null vs null'              => [null, null, true],
            'null vs empty string'      => [null, '', false],
        ];
    }
}
