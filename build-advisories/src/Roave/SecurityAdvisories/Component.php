<?php

namespace Roave\SecurityAdvisories;

final class Component
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Advisory[]
     */
    private $advisories;

    /**
     * @param string     $name
     * @param Advisory[] $advisories
     */
    public function __construct($name, array $advisories)
    {
        $this->name       = (string) $name;
        $this->advisories = $advisories;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getConflictConstraint()
    {
        // too simplified when multiple branches reference congruent versions
        return implode(
            '|',
            array_filter(array_map(
                function (Advisory $advisory) {
                    return $advisory->getConstraint();
                },
                $this->advisories
            ))
        );
    }
}
