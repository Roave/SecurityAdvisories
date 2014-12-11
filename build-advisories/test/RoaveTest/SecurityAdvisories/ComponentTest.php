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

/**
 * Tests for {@see \Roave\SecurityAdvisories\Component}
 *
 * @covers \Roave\SecurityAdvisories\Component
 */
class ComponentTest extends PHPUnit_Framework_TestCase
{
    public function testFromMultipleAdvisories()
    {
        $advisory1 = Advisory::fromArrayData([
            'reference' => 'composer://foo/bar',
            'branches' => [
                '1.0.x' => [
                    'versions' => ['>=1.0', '<1.1'],
                ],
                '2.0.x' => [
                    'versions' => ['>=2.0', '<2.1'],
                ],
            ],
        ]);
        $advisory2 = Advisory::fromArrayData([
            'reference' => 'composer://foo/bar',
            'branches' => [
                '1.0.x' => [
                    'versions' => ['>=3.0', '<3.1'],
                ],
                '2.0.x' => [
                    'versions' => ['>=4.0', '<4.1'],
                ],
            ],
        ]);

        $component = new Component('foo/bar', [$advisory1, $advisory2]);

        $this->assertInstanceOf(Component::class, $component);

        $this->assertSame('>=1.0,<1.1|>=2.0,<2.1|>=3.0,<3.1|>=4.0,<4.1', $component->getConflictConstraint());
        $this->assertSame('foo/bar', $component->getName());
    }
}
