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
use Roave\SecurityAdvisories\Boundary;
use Roave\SecurityAdvisories\Version;

/**
 * Tests for {@see \Roave\SecurityAdvisories\Boundary}
 *
 * @covers \Roave\SecurityAdvisories\Boundary
 */
class BoundaryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider invalidBoundaryStrings
     *
     * @param string $boundaryString
     *
     * @return void
     */
    public function testRejectsInvalidBoundaryStrings(string $boundaryString) : void
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        Boundary::fromString($boundaryString);
    }

    /**
     * @dataProvider validBoundaryStrings
     *
     * @param string $boundaryString
     * @param string $expectedNormalizedString
     *
     * @return void
     */
    public function testValidBoundaryString(string $boundaryString, string $expectedNormalizedString) : void
    {
        $boundary = Boundary::fromString($boundaryString);

        self::assertInstanceOf(Boundary::class, $boundary);
        self::assertSame($expectedNormalizedString, $boundary->getBoundaryString());
        self::assertEquals($boundary, Boundary::fromString($boundary->getBoundaryString()));
    }

    /**
     * @return string[][]
     */
    public function invalidBoundaryStrings() : array
    {
        return [
            [''],
            ['foo'],
            ['1'],
            ['1.2.3'],
            ['1.2.3='],
            ['1.2.3<='],
            ['1.2.3<'],
            ['1.2.3>'],
            ['1.2.3>='],
            ['<'],
            ['>'],
            ['<='],
            ['>='],
            ['='],
            ['=='],
            ['><'],
            ['<>'],
            ['=>'],
            ['=<'],
            ['=>1.2'],
            ['=<1.2'],
            ['1.2'],
        ];
    }

    /**
     * @return string[][]
     */
    public function validBoundaryStrings() : array
    {
        return [
            ['>1.2.3', '>1.2.3'],
            ['>=1.2.3', '>=1.2.3'],
            ['=1.2.3', '=1.2.3'],
            ['<=1.2.3', '<=1.2.3'],
            ['<1.2.3', '<1.2.3'],
            ['>1.2.3.0', '>1.2.3'],
            ['>=1.2.3.0', '>=1.2.3'],
            ['=1.2.3.0', '=1.2.3'],
            ['<=1.2.3.0', '<=1.2.3'],
            ['<1.2.3.0', '<1.2.3'],
            ['>1.0', '>1'],
            ['>=1.0', '>=1'],
            ['=1.0', '=1'],
            ['<=1.0', '<=1'],
            ['<1.0', '<1'],
            ['>  1.2.3', '>1.2.3'],
            ['>=  1.2.3', '>=1.2.3'],
            ['=  1.2.3', '=1.2.3'],
            ['<=  1.2.3', '<=1.2.3'],
            ['<  1.2.3', '<1.2.3'],
        ];
    }
}
