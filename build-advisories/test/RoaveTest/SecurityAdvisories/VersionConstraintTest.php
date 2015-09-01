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
        $this->assertRegExp('/(\d+.)*\d+/', $constraint->getLowerBound());
        $this->assertRegExp('/(\d+.)*\d+/', $constraint->getUpperBound());

        $this->assertSame((bool) preg_match('/>=/', $stringConstraint), $constraint->isLowerBoundIncluded());
        $this->assertSame((bool) preg_match('/<=/',$stringConstraint), $constraint->isUpperBoundIncluded());
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
        ]);
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
