<?php

namespace Roave\SecurityAdvisories;

final class Advisory
{
    /**
     * @var string
     */
    private $componentName;

    /**
     * @var array[]
     */
    private $branchConstraints;

    /**
     * @param string $componentName
     * @param array  $branchConstraints
     */
    private function __construct($componentName, array $branchConstraints)
    {
        $this->componentName      = (string) $componentName;
        $this->branchConstraints  = $branchConstraints;
    }

    /**
     * @param array $config
     *
     * @return self
     */
    public static function fromArrayData(array $config)
    {
        // @TODO may want to throw exceptions on missing keys
        return new self(
            $config['reference'],
            array_values(array_map(
                function (array $branchConfig) {
                    return (array) $branchConfig['versions'];
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
                function ($constraints) {
                    return implode(',', (array) $constraints);
                },
                $this->branchConstraints
            )
        ) ?: null;
    }
}
