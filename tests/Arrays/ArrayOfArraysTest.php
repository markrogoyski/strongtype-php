<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\ArrayOfArrays;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ArrayOfArraysTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(array $values)
    {
        // When
        $arrayOfArrays = new ArrayOfArrays($values);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(array $values)
    {
        // Given
        $arrayOfArrays = new ArrayOfArrays($values);

        // When
        $obtainedValues = $arrayOfArrays->getValues();

        // Then
        $this->assertSame($values, $obtainedValues);

        // And
        foreach ($values as $value) {
            $this->assertIsArray($value);
        }
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(array $values)
    {
        // Given
        $arrayOfArrays = new ArrayOfArrays($values);

        // When
        $debugInfo = $arrayOfArrays->__debugInfo();

        // Then
        $this->assertSame($values, $debugInfo['values']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [[[1]]],
            [[[1.1]]],
            [[['string']]],
            [[[true]]],
            [[[null]]],
            [[[\NAN]]],
            [[[new \stdClass()]]],
            [[[1, 2, 3, 4, 5]]],
            [[[1.1, 2.2, 3.3]]],
            [[[false, false]]],
            [[['hello', 'world', 'strong', 'type', 'php']]],
            [[[new \stdClass(), new \stdClass(), new \stdClass()]]],
            [[[1], [2], [3]]],
            [[[1.1, 2.2], [3.3, 4.4]]],
            [[['string'], ['hello', 'world'], ['php', 'strong', 'type']]],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(array $values, string $expected)
    {
        // Given
        $arrayOfArrays = new ArrayOfArrays($values);

        // When
        $stringRepresentation = (string) $arrayOfArrays;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [[[1, 2, 3], [3, 4, 5]], '[[1,2,3],[3,4,5]]'],
            [[[1.1, 2.2], [3.3]], '[[1.1,2.2],[3.3]]'],
            [[[false, true],[true, false]], '[[false,true],[true,false]]'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(array $values, string $expected)
    {
        // Given
        $arrayOfArrays = new ArrayOfArrays($values);

        // When
        $jsonSerialization = \json_encode($arrayOfArrays);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            [[[1, 2, 3], [3, 4, 5]], '[[1,2,3],[3,4,5]]'],
            [[[1.1, 2.2], [3.3]], '[[1.1,2.2],[3.3]]'],
            [[[false, true],[true, false]], '[[false,true],[true,false]]'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidCountedValues')]
    public function testCountableInterface(array $values, int $expectedCount)
    {
        // Given
        $arrayOfArrays = new ArrayOfArrays($values);

        // Then
        $this->assertCount($expectedCount, $arrayOfArrays);
    }

    #[Test]
    #[DataProvider('dataProviderForValidCountedValues')]
    public function testIteratorInterface(array $values, int $expectedCount)
    {
        // Given
        $arrayOfArrays = new ArrayOfArrays($values);

        // And
        $i = -1;
        $finalCount = $expectedCount - 1;

        // When
        foreach ($arrayOfArrays as $array) {
            $i++;
            $this->assertIsArray($array);
            $this->assertSame($values[$i], $array);
        }

        // Then
        $this->assertSame($finalCount, $i);

        // And when reset the iterator
        $i = -1;
        foreach ($arrayOfArrays as $array) {
            $i++;
            $this->assertIsArray($array);
            $this->assertSame($values[$i], $array);
        }

        // Then
        $this->assertSame($finalCount, $i);
    }

    public static function dataProviderForValidCountedValues(): array
    {
        return [
            [[[1]], 1],
            [[[1, 2]], 1],
            [[[1, 2, 3], [2, 3, 4], [3, 4, 5]], 3],
            [[['one', 'two', 'three', 'four', 'five', 'six']], 1],
            [[['one', 'two', 'three', 'four', 'five', 'six'], ['two', 'three']], 2],
        ];
    }

    #[Test]
    public function testStringRepresentationTerminatesOnSelfReferentialArray()
    {
        // Given an inner array that contains a reference to itself.
        // ArrayOfArrays validly accepts arrays-of-arrays, including ones with
        // internal cycles. json_encode throws JsonException("Recursion detected"),
        // so the fallback stringifier must terminate cleanly rather than recurse.
        $inner = [];
        $inner[] = &$inner;
        $arr = new ArrayOfArrays([$inner]);

        // When casting to string
        $rendered = (string) $arr;

        // Then __toString must not exhaust the stack and must mark the cycle.
        $this->assertStringContainsString('*RECURSION*', $rendered);
    }

    #[Test]
    public function testStringRepresentationRendersJustUnderDepthGuard()
    {
        // Given a structure one level shy of the depth guard. The fallback's
        // STRINGIFY_MAX_DEPTH is 64, and the leaf reaches stringifyJsonishValue
        // at depth ($levels + 1) — one hop is consumed walking from the outer
        // ArrayType::values wrapper into its first child. $levels = 62 puts
        // the NAN leaf at depth 63, just under the >= 64 trip point. NAN is
        // used because it forces json_encode to throw and hands control to the
        // fallback renderer (the only path under test here).
        $value = \NAN;
        for ($i = 0; $i < 62; $i++) {
            $value = [$value];
        }
        $arr = new ArrayOfArrays([$value]);

        // When casting to string
        $rendered = (string) $arr;

        // Then the leaf renders as 'NaN' and no recursion marker is emitted.
        $this->assertStringNotContainsString('*RECURSION*', $rendered);
        $this->assertStringContainsString('NaN', $rendered);
    }

    #[Test]
    public function testStringRepresentationMarksAtDepthGuard()
    {
        // Given a structure one level past the depth guard: $levels = 63 puts
        // the NAN leaf at depth 64 in stringifyJsonishValue — exactly the >= 64
        // boundary where STRINGIFY_MAX_DEPTH cuts off recursion.
        $value = \NAN;
        for ($i = 0; $i < 63; $i++) {
            $value = [$value];
        }
        $arr = new ArrayOfArrays([$value]);

        // When casting to string
        $rendered = (string) $arr;

        // Then the marker fires for the over-depth leaf and 'NaN' is replaced.
        $this->assertStringContainsString('*RECURSION*', $rendered);
        $this->assertStringNotContainsString('NaN', $rendered);
    }

    #[Test]
    public function testStringRepresentationTruncatesAWideArrayAtTheNodeBudget()
    {
        // Given a flat array wide enough to exceed STRINGIFY_MAX_NODES (10,000)
        // regardless of depth -- the general "too much total work" case the node
        // budget exists for, independent of any self-reference. NAN forces
        // json_encode to throw, handing rendering to the fallback.
        $arr = new ArrayOfArrays([\array_fill(0, 20_000, \NAN)]);

        // When casting to string
        $rendered = (string) $arr;

        // Then rendering stops well short of all 20,000 leaves and marks the cutoff.
        $this->assertStringContainsString('*TRUNCATED*', $rendered);
        $this->assertLessThan(20_000, \substr_count($rendered, 'NaN'));
    }

    #[Test]
    public function testStringRepresentationTerminatesOnMultiBranchSelfReferentialArrayWithoutExhaustingMemory(): void
    {
        // Given a self-referential array with two independent branches back to
        // itself at every level. STRINGIFY_MAX_DEPTH alone only bounds a single
        // recursive path -- a cycle that branches at every level instead produces
        // exponentially many nodes (2^depth) well within that depth bound, which
        // is what the total-node budget (not the depth guard) exists to catch.
        // Before that budget existed, this exact shape exhausted a 64 MiB process
        // in under a second. Run it in a memory-capped subprocess rather than
        // in-process, so a regression here fails this one test instead of
        // crashing the whole suite.
        $autoloader = \var_export(\dirname(__DIR__, 2) . '/vendor/autoload.php', true);
        $script = <<<PHP
            require {$autoloader};
            \$inner = ['leaf' => 1];
            \$inner['branch1'] = &\$inner;
            \$inner['branch2'] = &\$inner;
            \$arr = new StrongType\Arrays\ArrayOfArrays([\$inner]);
            \$rendered = (string) \$arr;
            fwrite(STDOUT, (string) \strlen(\$rendered));
            PHP;

        $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = \proc_open(['php', '-d', 'memory_limit=64M', '-r', $script], $descriptors, $pipes);
        $this->assertIsResource($process);

        $stdout = \stream_get_contents($pipes[1]);
        $stderr = \stream_get_contents($pipes[2]);
        \fclose($pipes[1]);
        \fclose($pipes[2]);
        $exitCode = \proc_close($process);

        // When / Then -- the subprocess exits cleanly with a bounded, non-empty
        // rendering, rather than being killed for exhausting its memory limit.
        $this->assertSame(0, $exitCode, "subprocess did not exit cleanly, stderr: {$stderr}");
        $this->assertGreaterThan(0, (int) \trim($stdout));
    }

    #[Test]
    public function testStringRepresentationTerminatesOnAWideArrayAtTheDepthGuardWithoutExhaustingMemory(): void
    {
        // Given a wide array (100 x 20,000 leaves) sitting exactly past
        // STRINGIFY_MAX_DEPTH. The depth-limit check must not skip the budget
        // decrement: if it did, every one of those 2,000,000 leaves would be
        // visited and rendered as "*RECURSION*" for free, since the depth check
        // alone stops recursion but not iteration over that many siblings. This
        // exact shape exhausted a 64 MiB process in a fraction of a second before
        // the budget check was moved ahead of the depth check. Run it in a
        // memory-capped subprocess rather than in-process, so a regression here
        // fails this one test instead of crashing the whole suite.
        //
        // $value itself (not [$value]) must be the constructor argument: wrapping
        // it in one more array consumes an extra depth level before the 62 manual
        // wraps are even reached, so the depth guard would trip one level too
        // early -- at the 100-wide array's own children -- and never descend far
        // enough to touch the 20,000-wide leaves the budget bypass depends on.
        $autoloader = \var_export(\dirname(__DIR__, 2) . '/vendor/autoload.php', true);
        $script = <<<PHP
            require {$autoloader};
            \$value = \array_fill(0, 100, \array_fill(0, 20_000, \NAN));
            for (\$i = 0; \$i < 62; \$i++) {
                \$value = [\$value];
            }
            \$arr = new StrongType\Arrays\ArrayOfArrays(\$value);
            \$rendered = (string) \$arr;
            fwrite(STDOUT, \$rendered);
            PHP;

        $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = \proc_open(['php', '-d', 'memory_limit=64M', '-r', $script], $descriptors, $pipes);
        $this->assertIsResource($process);

        $stdout = \stream_get_contents($pipes[1]);
        $stderr = \stream_get_contents($pipes[2]);
        \fclose($pipes[1]);
        \fclose($pipes[2]);
        $exitCode = \proc_close($process);

        // When / Then -- the subprocess exits cleanly, and the budget (not just
        // the depth guard) actually engaged: the rendering must be truncated,
        // since the 100 x 20,000 leaves sitting at the depth boundary vastly
        // exceed STRINGIFY_MAX_NODES. A rendering that finished without hitting
        // "*TRUNCATED*" would mean the budget never bound this shape at all.
        $this->assertSame(0, $exitCode, "subprocess did not exit cleanly, stderr: {$stderr}");
        $this->assertStringContainsString('*TRUNCATED*', $stdout);
    }

    #[Test]
    public function testStringRepresentationRendersAssociativeFallback()
    {
        // Given a string-keyed array-of-arrays whose NAN leaf forces
        // json_encode to throw, handing rendering to the fallback. Because the
        // top level is associative (not a list), the fallback must JSON-encode
        // the keys and emit an object form.
        $arr = new ArrayOfArrays([
            'first'  => [1],
            'second' => [\NAN],
            'nested' => ['inner' => 2],
        ]);

        // When casting to string
        $rendered = (string) $arr;

        // Then keys are quoted and the structure renders as a JSON object.
        $this->assertSame('{"first":[1],"second":[NaN],"nested":{"inner":2}}', $rendered);
    }

    #[Test]
    public function testEqualsHandlesSelfReferentialArraysWithoutThrowing()
    {
        // Given two distinct self-referential inner arrays of identical shape. Native ===
        // throws ("Nesting level too deep") comparing them directly.
        $innerA = [];
        $innerA[] = &$innerA;
        $innerB = [];
        $innerB[] = &$innerB;
        $a = new ArrayOfArrays([$innerA]);
        $b = new ArrayOfArrays([$innerB]);

        // When
        $result = $a->equals($b);

        // Then: no crash, and no false claim of equality for two structures this method
        // cannot safely compare all the way through (see ValueEquality::strictSame()).
        $this->assertFalse($result);
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(array $values)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $arrayOfArrays = new ArrayOfArrays($values);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [[]],
            [[1]],
            [[1.1]],
            [['string']],
            [[1, 2, 3]],
            [[null]],
            [[\NAN]],
            [[new \stdClass()]],
        ];
    }
}
