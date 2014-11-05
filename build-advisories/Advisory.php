<?php

namespace Roave\SecurityAdvisories;

final class Advisory
{
    /**
     * @var string
     */
    private $componentName;

    /**
     * @var array
     */
    private $versionConstraints;

    /**
     * @param string $componentName
     * @param array  $versionConstraints
     */
    public function __construct($componentName, array $versionConstraints)
    {
        $this->componentName      = (string) $componentName;
        $this->versionConstraints = $versionConstraints;
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
            call_user_func_array(
                'array_merge',
                array_map(
                    function (array $branchConfig) {
                        return (array) $branchConfig['versions'];
                    },
                    $config['branches']
                )
            )
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
        return implode(',', $this->versionConstraints) ?: null;
    }
}
