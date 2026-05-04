<?php

declare(strict_types=1);

namespace StrongType\Tests;

use StrongType\Util\ShortClassName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ShortClassNameTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForFqcn')]
    public function testOf(string $fqcn, string $expected)
    {
        // When
        $short = ShortClassName::of($fqcn);

        // Then
        $this->assertSame($expected, $short);
    }

    public static function dataProviderForFqcn(): array
    {
        return [
            'namespaced'                    => ['StrongType\\Int\\PositiveInt', 'PositiveInt'],
            'deeply namespaced'             => ['Foo\\Bar\\Baz\\Quux', 'Quux'],
            'single segment in namespace'   => ['Foo\\Bar', 'Bar'],
            'global namespace'              => ['Portish', 'Portish'],
            'global namespace single char'  => ['X', 'X'],
            'leading backslash absolute'    => ['\\Foo\\Bar', 'Bar'],
            'leading backslash global'      => ['\\Portish', 'Portish'],
        ];
    }
}
