<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Exception\ConstraintConfigurationException;
use StrongType\Exception\ConstraintViolationException;
use StrongType\Util\ShortClassName;

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
                throw new ConstraintViolationException($error);
            }
        }
    }

    /**
     * @param class-string $class
     * @return list<ConstraintInterface>
     */
    private static function resolveConstraints(string $class): array
    {
        $classRef    = new \ReflectionClass($class);
        $constraints = [];

        // Walk class hierarchy -- parent attributes are inherited
        $ref = $classRef;
        do {
            foreach ($ref->getAttributes(ConstraintInterface::class, \ReflectionAttribute::IS_INSTANCEOF) as $attr) {
                $constraints[] = $attr->newInstance();
            }
        } while ($ref = $ref->getParentClass());

        usort($constraints, fn(ConstraintInterface $a, ConstraintInterface $b) => $a->priority() <=> $b->priority());

        self::assertApplicable($class, $classRef, $constraints);

        return $constraints;
    }

    /**
     * Reject constraints that can never engage on this class's value type.
     *
     * Each base fixes its value type, so a constraint whose accepted set excludes it is
     * dead code: it would pass every value (or, under `Not`, reject every value) without
     * ever inspecting one. That is a type-definition bug, so it surfaces here -- once per
     * class, on the first construction of a value of that type, alongside the constraint
     * constructor invariants -- rather than silently at validation time.
     *
     * @param class-string $class
     * @param \ReflectionClass<object> $classRef
     * @param list<ConstraintInterface> $constraints
     */
    private static function assertApplicable(string $class, \ReflectionClass $classRef, array $constraints): void
    {
        // getReflectionConstant() reports absence unambiguously, where getConstant()
        // conflates "no such constant" with a constant whose value is false. Every
        // strong-type base declares VALUE_TYPE; a class without one is not a constrained
        // value type, so there is no domain to check it against.
        if ($classRef->getReflectionConstant('VALUE_TYPE') === false) {
            return;
        }

        // Read the value through constant(): ReflectionClassConstant::getValue() is
        // declared as returning only scalars, arrays and null, which predates enum-typed
        // class constants, so static analysis would mis-type the enum instance.
        $classValueType = \constant("{$class}::VALUE_TYPE");

        if (!$classValueType instanceof ValueType) {
            return;
        }

        foreach ($constraints as $constraint) {
            $accepted = $constraint->acceptedValueTypes();
            if (\in_array($classValueType, $accepted, true)) {
                continue;
            }

            $constraintShort = ShortClassName::of($constraint::class);
            $acceptedList    = $accepted === []
                ? 'no value type'
                : '[' . \implode(', ', \array_map(static fn(ValueType $t): string => $t->name, $accepted)) . ']';

            throw new ConstraintConfigurationException(
                "{$constraintShort} is not applicable to {$classValueType->name} values on {$class}; it applies to {$acceptedList}"
            );
        }
    }
}
