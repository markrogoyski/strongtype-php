<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\Uuid;
use StrongType\Exception\StrongTypeException;
use StrongType\String\StringType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[Uuid]
readonly class UuidTestString extends StringType
{
}

class UuidTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('validUuids')]
    public function testValidUuids(string $uuid): void
    {
        $this->assertSame($uuid, (new UuidTestString($uuid))->value);
    }

    public static function validUuids(): array
    {
        return [
            'lowercase'  => ['550e8400-e29b-41d4-a716-446655440000'],
            'uppercase'  => ['550E8400-E29B-41D4-A716-446655440000'],
            'mixed case' => ['550e8400-E29B-41d4-a716-446655440000'],
            'all zeros'  => ['00000000-0000-0000-0000-000000000000'],
            'v4 uuid'    => ['f47ac10b-58cc-4372-a567-0e02b2c3d479'],
        ];
    }

    #[Test]
    #[DataProvider('invalidUuids')]
    public function testInvalidUuids(string $uuid): void
    {
        $this->expectException(StrongTypeException::class);
        new UuidTestString($uuid);
    }

    public static function invalidUuids(): array
    {
        return [
            'too short'       => ['550e8400-e29b-41d4-a716'],
            'missing dashes'  => ['550e8400e29b41d4a716446655440000'],
            'wrong format'    => ['not-a-uuid-at-all'],
            'extra chars'     => ['550e8400-e29b-41d4-a716-44665544000g'],
            'empty string'    => [''],
        ];
    }
}
