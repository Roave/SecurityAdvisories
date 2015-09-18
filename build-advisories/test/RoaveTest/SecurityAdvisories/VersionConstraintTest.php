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

namespace RoaveTest\SecurityAdvisories;

use PHPUnit_Framework_TestCase;
use Roave\SecurityAdvisories\Advisory;
use Roave\SecurityAdvisories\Component;
use Roave\SecurityAdvisories\Version;
use Roave\SecurityAdvisories\VersionConstraint;

/**
 * Tests for {@see \Roave\SecurityAdvisories\VersionConstraint}
 *
 * @covers \Roave\SecurityAdvisories\VersionConstraint
 */
class VersionConstraintTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider closedRangesProvider
     *
     * @param string $stringConstraint
     */
    public function testFromRange($stringConstraint)
    {
        $constraint = VersionConstraint::fromString($stringConstraint);

        $this->assertInstanceOf(VersionConstraint::class, $constraint);
        $this->assertTrue($constraint->isSimpleRangeString());
        $this->assertSame($stringConstraint, $constraint->getConstraintString());
        $this->assertInstanceOf(Version::class, $constraint->getLowerBound());
        $this->assertInstanceOf(Version::class, $constraint->getUpperBound());

        $this->assertSame((bool) preg_match('/>=/', $stringConstraint), $constraint->isLowerBoundIncluded());
        $this->assertSame((bool) preg_match('/<=/',$stringConstraint), $constraint->isUpperBoundIncluded());
    }

    public function testLeftOpenEndedRange()
    {
        $constraint = VersionConstraint::fromString('<1');

        $this->assertTrue($constraint->isSimpleRangeString());
        $this->assertSame('<1', $constraint->getConstraintString());
        $this->assertNull($constraint->getLowerBound());
        $this->assertInstanceOf(Version::class, $constraint->getUpperBound());
        $this->assertFalse($constraint->isLowerBoundIncluded());
        $this->assertFalse($constraint->isUpperBoundIncluded());
    }

    public function testRightOpenEndedRange()
    {
        $constraint = VersionConstraint::fromString('>1');

        $this->assertTrue($constraint->isSimpleRangeString());
        $this->assertSame('>1', $constraint->getConstraintString());
        $this->assertNull($constraint->getUpperBound());
        $this->assertInstanceOf(Version::class, $constraint->getLowerBound());
        $this->assertFalse($constraint->isLowerBoundIncluded());
        $this->assertFalse($constraint->isUpperBoundIncluded());
    }

    public function testLeftOpenEndedRangeBoundIncluded()
    {
        $constraint = VersionConstraint::fromString('<=1');

        $this->assertTrue($constraint->isSimpleRangeString());
        $this->assertSame('<=1', $constraint->getConstraintString());
        $this->assertNull($constraint->getLowerBound());
        $this->assertInstanceOf(Version::class, $constraint->getUpperBound());
        $this->assertFalse($constraint->isLowerBoundIncluded());
        $this->assertTrue($constraint->isUpperBoundIncluded());
    }

    public function testRightOpenEndedRangeBoundIncluded()
    {
        $constraint = VersionConstraint::fromString('>=1');

        $this->assertTrue($constraint->isSimpleRangeString());
        $this->assertSame('>=1', $constraint->getConstraintString());
        $this->assertNull($constraint->getUpperBound());
        $this->assertInstanceOf(Version::class, $constraint->getLowerBound());
        $this->assertTrue($constraint->isLowerBoundIncluded());
        $this->assertFalse($constraint->isUpperBoundIncluded());
    }

    /**
     * @dataProvider complexRangesProvider
     *
     * @param string $stringConstraint
     */
    public function testFromRangeWithComplexRanges($stringConstraint)
    {
        $constraint = VersionConstraint::fromString($stringConstraint);

        $this->assertInstanceOf(VersionConstraint::class, $constraint);
        $this->assertFalse($constraint->isSimpleRangeString());
        $this->assertSame($stringConstraint, $constraint->getConstraintString());
    }

    public function testContainsWithMatchingRanges()
    {
        $constraint1 = VersionConstraint::fromString('>1.2.3,<4.5.6');
        $constraint2 = VersionConstraint::fromString('>1.2.4,<4.5.5');

        $this->assertTrue($constraint1->contains($constraint2));
        $this->assertFalse($constraint2->contains($constraint1));
    }

    public function testCannotCompareComplexRanges()
    {
        $constraint1 = VersionConstraint::fromString('1|2');
        $constraint2 = VersionConstraint::fromString('1|2|3');

        $this->assertFalse($constraint1->contains($constraint2));
        $this->assertFalse($constraint2->contains($constraint1));
    }

    /**
     * @dataProvider rangesForComparisonProvider
     *
     * @param string $constraintString1
     * @param string $constraintString2
     * @param bool   $constraint1ContainsConstraint2
     * @param bool   $constraint2ContainsConstraint1
     *
     * @return void
     */
    public function testContainsWithRanges(
        $constraintString1,
        $constraintString2,
        $constraint1ContainsConstraint2,
        $constraint2ContainsConstraint1
    ) {
        $constraint1 = VersionConstraint::fromString($constraintString1);
        $constraint2 = VersionConstraint::fromString($constraintString2);

        $this->assertSame($constraint1ContainsConstraint2, $constraint1->contains($constraint2));
        $this->assertSame($constraint2ContainsConstraint1, $constraint2->contains($constraint1));
    }

    /**
     * @dataProvider rangesForComparisonProvider
     *
     * @param string $constraintString1
     * @param string $constraintString2
     * @param bool   $constraint1ContainsConstraint2
     * @param bool   $constraint2ContainsConstraint1
     *
     * @return void
     */
    public function testCanMergeWithContainedRanges(
        $constraintString1,
        $constraintString2,
        $constraint1ContainsConstraint2,
        $constraint2ContainsConstraint1
    ) {
        $constraint1 = VersionConstraint::fromString($constraintString1);
        $constraint2 = VersionConstraint::fromString($constraintString2);
        $expectation = $constraint2ContainsConstraint1 || $constraint1ContainsConstraint2;

        $this->assertSame($expectation, $constraint1->canMergeWith($constraint2));
        $this->assertSame($expectation, $constraint1->canMergeWith($constraint2));
    }

    /**
     * @dataProvider rangesForComparisonProvider
     *
     * @param string $constraintString1
     * @param string $constraintString2
     * @param bool   $constraint1ContainsConstraint2
     * @param bool   $constraint2ContainsConstraint1
     *
     * @return void
     */
    public function testMergeWithContainedRanges(
        $constraintString1,
        $constraintString2,
        $constraint1ContainsConstraint2,
        $constraint2ContainsConstraint1
    ) {
        $constraint1 = VersionConstraint::fromString($constraintString1);
        $constraint2 = VersionConstraint::fromString($constraintString2);

        if (! ($constraint2ContainsConstraint1 || $constraint1ContainsConstraint2)) {
            $this->setExpectedException(\LogicException::class);
        }

        $merged1 = $constraint1->mergeWith($constraint2);
        $merged2 = $constraint2->mergeWith($constraint1);

        $this->assertEquals($merged1, $merged2);

        if ($constraint1ContainsConstraint2) {
            $this->assertEquals($merged1, $constraint1);
        }

        if ($constraint2ContainsConstraint1) {
            $this->assertEquals($merged1, $constraint2);
        }
    }

    /**
     * @return string[][]
     */
    public function closedRangesProvider()
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
    public function complexRangesProvider()
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
    public function rangesForComparisonProvider()
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
            ['>1', '>=1,<2', false, false],
            ['<=2', '>1,<=2', true, false],
            ['<=2', '>1,<2', true, false],
            ['<2', '>1,<=2', false, false],
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
     * @param mixed[][] $entries
     *
     * @return mixed[][]
     */
    private function dataProviderFirstValueAsProviderKey(array $entries)
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
}
