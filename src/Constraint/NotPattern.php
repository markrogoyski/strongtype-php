<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Exception\ConstraintConfigurationException;
use StrongType\Exception\ConstraintEvaluationException;
use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final readonly class NotPattern implements ConstraintInterface
{
    use StringValueType;

    /** @param non-empty-string $pattern */
    public function __construct(private string $pattern)
    {
        if (@\preg_match($pattern, '') === false) {
            throw new ConstraintConfigurationException("NotPattern: invalid regex pattern: {$pattern}");
        }
    }

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_string($value)) {
            $matched = \preg_match($this->pattern, $value);
            // preg_match() returns false (not 0) on a regex engine error -- e.g. backtrack
            // or recursion-limit exhaustion on an adversarial input. false is falsy, so a
            // naive `if (preg_match(...))` treats an engine error identically to "no
            // match" and fails open. An unevaluable pattern must reject, not silently pass --
            // and it must do so by throwing rather than returning a normal violation string,
            // so that a #[Not(new NotPattern(...))] composition can't invert "couldn't tell"
            // into "so it must be fine" (see ConstraintEvaluationException).
            if ($matched === false) {
                throw new ConstraintEvaluationException(
                    "{$short} type must be evaluable against pattern {$this->pattern} (regex engine error), got {$value}"
                );
            }
            if ($matched === 1) {
                return "{$short} type must not match pattern {$this->pattern}, got {$value}";
            }
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 100;
    }
}
