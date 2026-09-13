<?php

declare(strict_types=1);

namespace StrongType\Exception;

/**
 * A constraint could not be evaluated against the value at all -- as opposed to evaluating
 * it and finding it invalid (that's ConstraintViolationException). Thrown directly from
 * validate() rather than returned as a normal violation string specifically so that Not,
 * AnyOf, and AllOf -- which only ever interpret validate()'s *return value* as pass/fail --
 * cannot invert or short-circuit around an inability to evaluate: an uncaught exception
 * propagates straight through them unexamined, the same way ConstraintConfigurationException
 * already does, rather than being read back as "this child failed" and inverted into a pass.
 */
class ConstraintEvaluationException extends StrongTypeException
{
}
