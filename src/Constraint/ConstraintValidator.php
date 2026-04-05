<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Exception\StrongTypeException;

final class ConstraintValidator
{
    /** @var array<class-string, list<ConstraintInterface>> */
    private static array $cache = [];

    public static function validate(object $instance, mixed $value): void
    {
        $class = $instance::class;

        if (!isset(self::$cache[$class])) {
            self::$cache[$class] = self::resolveConstraints($class);
        }

        foreach (self::$cache[$class] as $constraint) {
            $error = $constraint->validate($value, $class);
            if ($error !== null) {
                throw new StrongTypeException($error);
            }
        }
    }

    /**
     * @param class-string $class
     * @return list<ConstraintInterface>
     */
    private static function resolveConstraints(string $class): array
    {
        $ref = new \ReflectionClass($class);
        $constraints = [];

        // Walk class hierarchy -- parent attributes are inherited
        do {
            foreach ($ref->getAttributes(ConstraintInterface::class, \ReflectionAttribute::IS_INSTANCEOF) as $attr) {
                $constraints[] = $attr->newInstance();
            }
        } while ($ref = $ref->getParentClass());

        usort($constraints, fn(ConstraintInterface $a, ConstraintInterface $b) => $a->priority() <=> $b->priority());

        return $constraints;
    }
}
