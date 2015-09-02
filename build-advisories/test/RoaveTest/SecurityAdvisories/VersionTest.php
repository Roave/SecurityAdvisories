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
 * Tests for {@see \Roave\SecurityAdvisories\Version}
 *
 * @covers \Roave\SecurityAdvisories\Version
 */
class VersionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider invalidVersionStringsProvider
     *
     * @param string $versionString
     */
    public function testVersionWillNotAllowInvalidFormats($versionString)
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        Version::fromString($versionString);
    }

    /**
     * @dataProvider validVersionStringProvider
     *
     * @param string $versionString
     */
    public function testGetVersionWithValidVersion($versionString)
    {
        $version = Version::fromString($versionString);

        $this->assertInstanceOf(Version::class, $version);
        $this->assertSame($versionString, $version->getVersion());
    }

    /**
     * @dataProvider greaterThanComparisonVersionsProvider
     *
     * @param string $version1String
     * @param string $version2String
     * @param bool   $v1GreaterThanV2
     * @param bool   $v2GreaterThanV1
     */
    public function testGreaterThanVersionWith($version1String, $version2String, $v1GreaterThanV2, $v2GreaterThanV1)
    {
        $version1 = Version::fromString($version1String);
        $version2 = Version::fromString($version2String);

        $this->assertSame($v1GreaterThanV2, $version1->isGreaterThan($version2));
        $this->assertSame($v2GreaterThanV1, $version2->isGreaterThan($version1));
    }

    public function validVersionStringProvider()
    {
        return [
            ['0'],
            ['1'],
            ['12345'],
            ['0.1.2.3.4'],
            ['1.2.3.4'],
            ['1.2.3.4.5.6.7.8.9.10'],
            ['12345.12345.12345.12345.0'],
        ];
    }

    public function greaterThanComparisonVersionsProvider()
    {
        $versions = [
            ['0', '0', false, false],
            ['1', '0', true, false],
            ['1.1', '1.1', false, false],
            ['1.10', '1.1', true, false],
            ['1.100', '1.100', false, false],
            ['1.2', '1.100', false, true],
            ['1.1', '1.1.0', false, false],
            ['1.1', '1.1.0.0', false, false],
            ['1.1', '1.1.0.0.1', false, true],
            ['1.0.0.0.0.0.2', '1.0.0.0.0.2', false, true],
            ['1.0.12', '1.0.11', true, false],
        ];

        return array_combine(
            array_map(
                function (array $versionData) {
                    return $versionData[0] . ' > ' . $versionData[1];
                },
                $versions
            ),
            $versions
        );
    }

    public function invalidVersionStringsProvider()
    {
        return [
            [''],
            ['12.a'],
            ['alpha'],
            ['beta'],
            ['1-a'],
            ['1.2.a'],
            ['.1'],
        ];
    }
}
