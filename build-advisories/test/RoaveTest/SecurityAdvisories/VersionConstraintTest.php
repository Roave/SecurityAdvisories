<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

declare(strict_types=1);

namespace RoaveTest\SecurityAdvisories;

use PHPUnit_Framework_TestCase;
use ReflectionMethod;
use Roave\SecurityAdvisories\Version;
use Roave\SecurityAdvisories\VersionConstraint;

/**
 * Tests for {@see \Roave\SecurityAdvisories\VersionConstraint}
 *
 * @covers \Roave\SecurityAdvisories\VersionConstraint
 */
final class VersionConstraintTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider closedRangesProvider
     *
     * @param string $stringConstraint
     */
    public function testFromRange(string $stringConstraint) : void
    {
        $constraint = VersionConstraint::fromString($stringConstraint);

        self::assertInstanceOf(VersionConstraint::class, $constraint);
        self::assertTrue($constraint->isSimpleRangeString());
        self::assertInstanceOf(Version::class, $constraint->getLowerBound());
        self::assertInstanceOf(Version::class, $constraint->getUpperBound());

        $constraintAsString = $constraint->getConstraintString();

        self::assertSame((bool) preg_match('/>=/', $stringConstraint), $constraint->isLowerBoundIncluded());
        self::assertSame((bool) preg_match('/<=/',$stringConstraint), $constraint->isUpperBoundIncluded());
        self::assertStringMatchesFormat('%A' . $constraint->getLowerBound()->getVersion() . '%A', $constraintAsString);
        self::assertStringMatchesFormat('%A' . $constraint->getUpperBound()->getVersion() . '%A', $constraintAsString);
    }

    /**
     * @dataProvider normalizableRangesProvider
     *
     * @param string $originalRange
     * @param string $normalizedRange
     */
    public function testOperatesOnNormalizedRanges(string $originalRange, string $normalizedRange) : void
    {
        self::assertSame($normalizedRange, VersionConstraint::fromString($originalRange)->getConstraintString());
    }

    public function testLeftOpenEndedRange() : void
    {
        $constraint = VersionConstraint::fromString('<1');

        self::assertTrue($constraint->isSimpleRangeString());
        self::assertSame('<1', $constraint->getConstraintString());
        self::assertNull($constraint->getLowerBound());
        self::assertInstanceOf(Version::class, $constraint->getUpperBound());
        self::assertFalse($constraint->isLowerBoundIncluded());
        self::assertFalse($constraint->isUpperBoundIncluded());
    }

    public function testRightOpenEndedRange() : void
    {
        $constraint = VersionConstraint::fromString('>1');

        self::assertTrue($constraint->isSimpleRangeString());
        self::assertSame('>1', $constraint->getConstraintString());
        self::assertNull($constraint->getUpperBound());
        self::assertInstanceOf(Version::class, $constraint->getLowerBound());
        self::assertFalse($constraint->isLowerBoundIncluded());
        self::assertFalse($constraint->isUpperBoundIncluded());
    }

    public function testLeftOpenEndedRangeBoundIncluded() : void
    {
        $constraint = VersionConstraint::fromString('<=1');

        self::assertTrue($constraint->isSimpleRangeString());
        self::assertSame('<=1', $constraint->getConstraintString());
        self::assertNull($constraint->getLowerBound());
        self::assertInstanceOf(Version::class, $constraint->getUpperBound());
        self::assertFalse($constraint->isLowerBoundIncluded());
        self::assertTrue($constraint->isUpperBoundIncluded());
    }

    public function testRightOpenEndedRangeBoundIncluded() : void
    {
        $constraint = VersionConstraint::fromString('>=1');

        self::assertTrue($constraint->isSimpleRangeString());
        self::assertSame('>=1', $constraint->getConstraintString());
        self::assertNull($constraint->getUpperBound());
        self::assertInstanceOf(Version::class, $constraint->getLowerBound());
        self::assertTrue($constraint->isLowerBoundIncluded());
        self::assertFalse($constraint->isUpperBoundIncluded());
    }

    /**
     * @dataProvider complexRangesProvider
     *
     * @param string $stringConstraint
     */
    public function testFromRangeWithComplexRanges(string $stringConstraint) : void
    {
        $constraint = VersionConstraint::fromString($stringConstraint);

        self::assertInstanceOf(VersionConstraint::class, $constraint);
        self::assertFalse($constraint->isSimpleRangeString());
        self::assertSame($stringConstraint, $constraint->getConstraintString());
    }

    public function testContainsWithMatchingRanges() : void
    {
        $constraint1 = VersionConstraint::fromString('>1.2.3,<4.5.6');
        $constraint2 = VersionConstraint::fromString('>1.2.4,<4.5.5');

        self::assertTrue($this->callContains($constraint1, $constraint2));
        self::assertFalse($this->callContains($constraint2, $constraint1));
    }

    public function testCannotCompareComplexRanges() : void
    {
        $constraint1 = VersionConstraint::fromString('1|2');
        $constraint2 = VersionConstraint::fromString('1|2|3');

        self::assertFalse($this->callContains($constraint1, $constraint2));
        self::assertFalse($this->callContains($constraint2, $constraint1));
    }

    /**
     * @dataProvider rangesForComparisonProvider
     *
     * @param string $constraintString1
     * @param string $constraintString2
     * @param bool   $constraint1ContainsConstraint2
     * @param bool   $constraint2ContainsConstraint1
     */
    public function testContainsWithRanges(
        string $constraintString1,
        string $constraintString2,
        bool $constraint1ContainsConstraint2,
        bool $constraint2ContainsConstraint1
    ) : void {
        $constraint1 = VersionConstraint::fromString($constraintString1);
        $constraint2 = VersionConstraint::fromString($constraintString2);

        self::assertSame($constraint1ContainsConstraint2, $this->callContains($constraint1, $constraint2));
        self::assertSame($constraint2ContainsConstraint1, $this->callContains($constraint2, $constraint1));
    }

    /**
     * @dataProvider mergeableRangesProvider
     *
     * @param string $constraintString1
     * @param string $constraintString2
     * @param bool   $constraint1ContainsConstraint2
     * @param bool   $constraint2ContainsConstraint1
     */
    public function testCanMergeWithContainedRanges(
        string $constraintString1,
        string $constraintString2,
        bool $constraint1ContainsConstraint2,
        bool $constraint2ContainsConstraint1
    ) : void {
        $constraint1 = VersionConstraint::fromString($constraintString1);
        $constraint2 = VersionConstraint::fromString($constraintString2);
        $expectation = $constraint2ContainsConstraint1 || $constraint1ContainsConstraint2;

        self::assertSame($expectation, $constraint1->canMergeWith($constraint2));
        self::assertSame($expectation, $constraint1->canMergeWith($constraint2));
    }

    /**
     * @dataProvider mergeableRangesProvider
     *
     * @param string $constraintString1
     * @param string $constraintString2
     * @param bool   $constraint1ContainsConstraint2
     * @param bool   $constraint2ContainsConstraint1
     */
    public function testMergeWithMergeableRanges(
        string $constraintString1,
        string $constraintString2,
        bool $constraint1ContainsConstraint2,
        bool $constraint2ContainsConstraint1
    ) : void {
        $constraint1 = VersionConstraint::fromString($constraintString1);
        $constraint2 = VersionConstraint::fromString($constraintString2);

        if (! ($constraint2ContainsConstraint1 || $constraint1ContainsConstraint2)) {
            $this->setExpectedException(\LogicException::class);
        }

        $merged1 = $constraint1->mergeWith($constraint2);
        $merged2 = $constraint2->mergeWith($constraint1);

        self::assertEquals($merged1, $merged2);

        self::assertTrue($this->callContains($merged1, $constraint1));
        self::assertTrue($this->callContains($merged1, $constraint2));
    }

    /**
     * @dataProvider strictlyOverlappingRangesProvider
     *
     * @param string $range1
     * @param string $range2
     * @param string $expected
     */
    public function testCanMergeWithMergeableRanges(string $range1, string $range2, string $expected) : void
    {
        $constraint1 = VersionConstraint::fromString($range1);
        $constraint2 = VersionConstraint::fromString($range2);

        self::assertSame($expected, $constraint1->mergeWith($constraint2)->getConstraintString());
        self::assertSame($expected, $constraint2->mergeWith($constraint1)->getConstraintString());
    }

    /**
     * @dataProvider nonStrictlyOverlappingRangesProvider
     *
     * @param string $range1
     * @param string $range2
     */
    public function testNonMergeableRanges(string $range1, string $range2) : void
    {
        $constraint1 = VersionConstraint::fromString($range1);
        $constraint2 = VersionConstraint::fromString($range2);

        self::assertFalse($this->callOverlapsWith($constraint1, $constraint2));
        self::assertFalse($this->callOverlapsWith($constraint2, $constraint1));

        $this->setExpectedException(\LogicException::class);

        $this->callMergeWithOverlapping($constraint1, $constraint2);
    }

    /**
     * @return string[][]
     */
    public function closedRangesProvider() : array
    {
        $matchedRanges = [
            ['>1.2.3,<4.5.6'],
            ['>=1.2.3,<4.5.6'],
            ['>1.2.3,<=4.5.6'],
            ['>=1.2.3,<=4.5.6'],
            ['>  1.2.3  , < 4.5.6'],
            ['>=  1.2.3  , <4.5.6'],
            ['> 1.2.3 , <=4.5.6'],
            ['>=1.2.3, <=4.5.6'],
            ['>11.22.33,<44.55.66'],
            ['>11.22.33.44.55.66.77,<44.55.66.77.88.99.1010'],
            ['>1,<2'],
        ];

        return array_combine(
            array_map(
                function (array $entry) {
                    return $entry[0];
                },
                $matchedRanges
            ),
            $matchedRanges
        );
    }

    /**
     * @return string[][]
     */
    public function complexRangesProvider() : array
    {
        return $this->dataProviderFirstValueAsProviderKey([
            ['>1.2.3,<4.5.6,<7.8.9'],
            ['1.2.3|4.5.6'],
            ['1'],
            ['1|2'],
            ['<1,<2'],
            ['>1,>2'],
            ['~2'],
            ['>1a2b3,<4c5d6'],
            ['>1a2'],
            ['<1a2'],
        ]);
    }

    /**
     * @return string[][]|bool[][]
     *
     *  - range1
     *  - range2
     *  - range1 contains range2
     *  - range2 contains range1
     */
    public function rangesForComparisonProvider() : array
    {
        $entries = [
            ['>1,<2', '>1,<2', true, true],
            ['>1,<2', '>1.1,<2', true, false],
            ['>1,<2', '>3,<4', false, false],
            ['>1.1,<2.1', '>1.2,<2', true, false],
            ['>100,<200', '>1.0.0,<2.0.0', false, false],
            ['>1.10,<2', '>1.100,<2', true, false],
            ['>1,<2.10', '>1,<2.100', false, true],
            ['>1.0,<2', '>1,<2', true, true],
            ['>1,<2.0', '>1,<2', true, true],
            ['>1.0.0,<2', '>1,<2', true, true],
            ['>1,<2.0.0', '>1,<2', true, true],
            ['>=1,<2', '>1,<2', true, false],
            ['>=1,<2', '>=1,<2', true, true],
            ['>1,<=2', '>1,<2', true, false],
            ['>1,<=2', '>1,<=2', true, true],
            ['>=1,<=2', '>1,<2', true, false],
            ['>=1,<=2', '>=1,<=2', true, true],
            ['>=1,<=2', '>=1,<=2', true, true],
            ['>=1', '>=1,<2', true, false],
            ['>=1', '>1,<2', true, false],
            ['>1', '>=1,<2', false, false], // this is mergeable, but not updated in tests
            ['<=2', '>1,<=2', true, false],
            ['<=2', '>1,<2', true, false],
            ['<2', '>1,<=2', false, false], // this is mergeable, but not updated in tests
            ['<2', '<2', true, true],
            ['<=2', '<=2', true, true],
            ['<=2', '<2', true, false],
            ['<=2', '<1', true, false],
            ['<=2', '<3', false, true],
            ['>2', '>2', true, true],
            ['>=2', '>=2', true, true],
            ['>=2', '>2', true, false],
            ['>=2', '>1', false, true],
            ['>=2', '>3', true, false],
        ];

        return array_combine(
            array_map(
                function (array $entry) {
                    return '(∀ x ∈ (' . $entry[0] . '): x ∈ (' . $entry[1] . ')) = ' . var_export($entry[2], true);
                },
                $entries
            ),
            $entries
        );
    }

    /**
     * @return string[][]|bool[][]
     */
    public function mergeableRangesProvider() : array
    {
        $entries = [
            ['>1,<2', '>1,<2', true, true],
            ['>1,<2', '>1.1,<2', true, false],
            ['>1,<2', '>3,<4', false, false],
            ['>1.1,<2.1', '>1.2,<2', true, false],
            ['>100,<200', '>1.0.0,<2.0.0', false, false],
            ['>1.10,<2', '>1.100,<2', true, false],
            ['>1,<2.10', '>1,<2.100', false, true],
            ['>1.0,<2', '>1,<2', true, true],
            ['>1,<2.0', '>1,<2', true, true],
            ['>1.0.0,<2', '>1,<2', true, true],
            ['>1,<2.0.0', '>1,<2', true, true],
            ['>=1,<2', '>1,<2', true, false],
            ['>=1,<2', '>=1,<2', true, true],
            ['>1,<=2', '>1,<2', true, false],
            ['>1,<=2', '>1,<=2', true, true],
            ['>=1,<=2', '>1,<2', true, false],
            ['>=1,<=2', '>=1,<=2', true, true],
            ['>=1,<=2', '>=1,<=2', true, true],
            ['>=1', '>=1,<2', true, false],
            ['>=1', '>1,<2', true, false],
            ['>1', '>=1,<2', true, true],
            ['<=2', '>1,<=2', true, false],
            ['<=2', '>1,<2', true, false],
            ['<2', '>1,<=2', true, true],
            ['<2', '<2', true, true],
            ['<=2', '<=2', true, true],
            ['<=2', '<2', true, false],
            ['<=2', '<1', true, false],
            ['<=2', '<3', false, true],
            ['>2', '>2', true, true],
            ['>=2', '>=2', true, true],
            ['>=2', '>2', true, false],
            ['>=2', '>1', false, true],
            ['>=2', '>3', true, false],
        ];

        return array_combine(
            array_map(
                function (array $entry) {
                    return '((' . $entry[0] . ') ∩ (' . $entry[1] . ')) ≠ ∅';
                },
                $entries
            ),
            $entries
        );
    }

    /**
     * @return string[][]
     */
    public function normalizableRangesProvider() : array
    {
        return $this->dataProviderFirstValueAsProviderKey([
            ['>1.0,<2.0', '>1,<2'],
            ['>=1.0,<2.0', '>=1,<2'],
            ['>1.0,<=2.0', '>1,<=2'],
            ['>=1.0,<=2.0', '>=1,<=2'],
            ['>1.0', '>1'],
            ['>=1.0', '>=1'],
            ['<1.0', '<1'],
            ['<=1.0', '<=1'],
        ]);
    }

    /**
     * @return string[][]
     */
    public function strictlyOverlappingRangesProvider() : array
    {
        $entries = [
            ['>2,<3', '>2.1,<4', '>2,<4'],
            ['>2,<3', '>1,<2.1', '>1,<3'],
            ['<3', '>1,<3.1', '<3.1'],
            ['>3', '>2.1,<3.1', '>2.1'],
            ['>1,<2', '>=2,<3', '>1,<3'],
            ['>1,<=2', '>2,<3', '>1,<3'],
            ['>1,<2', '>0.1,<=1', '>0.1,<2'],
            ['>=1,<2', '>0.1,<1', '>0.1,<2'],
            ['>1,<=2', '>2', '>1'],
            ['>1,<2', '>=2', '>1'],
            ['>1,<2', '<=1', '<2'],
            ['>=1,<2', '<1', '<2'],
        ];

        return array_combine(
            array_map(
                function (array $entry) {
                    return '((' . $entry[0] . ') ∪ (' . $entry[1] . ')) = (' . $entry[2] . ')';
                },
                $entries
            ),
            $entries
        );
    }

    /**
     * @return string[][]
     */
    public function nonStrictlyOverlappingRangesProvider() : array
    {
        $entries = [
            ['>2,<3', '>3,<4'],
            ['>2,<3', '>=3,<4'],
            ['>2,<=3', '>3,<4'],
            ['>2,<=3', '>=3,<4'],
            ['>2,<3', '>1,<2'],
            ['>2,<3', '>1,<=2'],
            ['>=2,<3', '>1,<2'],
            ['>=2,<3', '>1,<=2'],
            ['foo', '>1,<2'],
            ['>2,<3', 'foo'],
            ['bar', 'foo'],
            ['>1,<4', '>2,<3'], // note: containing, not overlapping.
        ];

        return array_combine(
            array_map(
                function (array $entry) {
                    return '((' . $entry[0] . ') ∩ (' . $entry[1] . ')) = ∅';
                },
                $entries
            ),
            $entries
        );
    }

    /**
     * @param mixed[][] $entries
     *
     * @return mixed[][]
     */
    private function dataProviderFirstValueAsProviderKey(array $entries) : array
    {
        return array_combine(
            array_map(
                function (array $entry) {
                    return $entry[0];
                },
                $entries
            ),
            $entries
        );
    }

    private function callContains(VersionConstraint $versionConstraint, VersionConstraint $other) : bool
    {
        $containsReflection = new ReflectionMethod($versionConstraint, 'contains');

        $containsReflection->setAccessible(true);

        return $containsReflection->invoke($versionConstraint, $other);
    }

    private function callOverlapsWith(VersionConstraint $versionConstraint, VersionConstraint $other) : bool
    {
        $overlapsWithReflection = new ReflectionMethod($versionConstraint, 'overlapsWith');

        $overlapsWithReflection->setAccessible(true);

        return $overlapsWithReflection->invoke($versionConstraint, $other);
    }

    private function callMergeWithOverlapping(VersionConstraint $versionConstraint, VersionConstraint $other) : VersionConstraint
    {
        $mergeWithOverlappingReflection = new ReflectionMethod($versionConstraint, 'mergeWithOverlapping');

        $mergeWithOverlappingReflection->setAccessible(true);

        return $mergeWithOverlappingReflection->invoke($versionConstraint, $other);
    }
}
