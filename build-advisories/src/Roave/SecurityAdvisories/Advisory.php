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

namespace Roave\SecurityAdvisories;

final class Advisory
{
    /**
     * @var string
     */
    private $componentName;

    /**
     * @var VersionConstraint[]
     */
    private $branchConstraints;

    /**
     * @param string              $componentName
     * @param VersionConstraint[] $branchConstraints
     */
    private function __construct($componentName, array $branchConstraints)
    {
        static $checkType;

        $checkType = $checkType ?: function (VersionConstraint ...$versionConstraints) {
            return $versionConstraints;
        };

        $this->componentName     = (string) $componentName;
        $this->branchConstraints = $checkType(...$branchConstraints);
    }

    /**
     * @param array $config
     *
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public static function fromArrayData(array $config)
    {
        // @TODO may want to throw exceptions on missing/invalid keys
        return new self(
            str_replace('composer://', '', $config['reference']),
            array_values(array_map(
                function (array $branchConfig) {
                    return VersionConstraint::fromString(implode(',', (array) $branchConfig['versions']));
                },
                $config['branches']
            ))
        );
    }

    /**
     * @return string
     */
    public function getComponentName()
    {
        return $this->componentName;
    }

    /**
     * @return string|null
     */
    public function getConstraint()
    {
        // @TODO may want to escape this
        return implode(
            '|',
            array_map(
                function (VersionConstraint $versionConstraint) {
                    return $versionConstraint->getConstraintString();
                },
                $this->branchConstraints
            )
        ) ?: null;
    }
}
