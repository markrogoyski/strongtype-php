<?php

declare(strict_types=1);

namespace StrongType\Constraint;

/**
 * The PHP value types a strong type can hold.
 *
 * Each base class declares exactly one of these as its `VALUE_TYPE` constant, and every
 * constraint declares the set it applies to via `ConstraintInterface::acceptedValueTypes()`.
 * `ConstraintValidator` compares the two when it first resolves a class's constraints, so a
 * constraint that could never engage on a type is rejected at type-definition time rather
 * than silently passing every value.
 */
enum ValueType
{
    case Int;
    case Float;
    case String;
    case Bool;
    case Array;
}
