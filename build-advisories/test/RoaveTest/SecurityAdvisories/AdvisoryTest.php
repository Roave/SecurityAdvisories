<?php

namespace RoaveTest\SecurityAdvisories;

use PHPUnit_Framework_TestCase;
use Roave\SecurityAdvisories\Advisory;

/**
 * Tests for {@see \Roave\SecurityAdvisories\Advisory}
 *
 * @covers \Roave\SecurityAdvisories\Advisory
 */
class AdvisoryTest extends PHPUnit_Framework_TestCase
{
    public function testFromArrayWithValidConfig()
    {
        $advisory = Advisory::fromArrayData([
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

        $this->assertInstanceOf(Advisory::class, $advisory);

        $this->assertSame('foo/bar', $advisory->getComponentName());
        $this->assertSame('>=1.0,<1.1|>=2.0,<2.1', $advisory->getConstraint());
    }
}
