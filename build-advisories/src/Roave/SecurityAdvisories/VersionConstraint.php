<?php

namespace Roave\SecurityAdvisories;

/**
 * A simple version constraint - naively assumes that it is only about ranges like ">=1.2.3,<4.5.6"
 */
final class VersionConstraint
{
    const CLOSED_RANGE_MATCHER = '^>(=?)\s*((\d+.)*\d+)\s*,\s*<(=?)\s*((\d+.)*\d+)$';

    /**
     * @var string
     */
    private $constraintString;

    /**
     * @var bool whether this constraint is a simple range string: complex constraints currently cannot be compared
     */
    private $isSimpleRangeString = false;

    /**
     * @var bool whether the lower bound is included or excluded
     */
    private $lowerBoundIncluded = false;

    /**
     * @var Version|null the upper bound of this constraint, null if unbound
     */
    private $lowerBound;

    /**
     * @var bool whether the upper bound is included or excluded
     */
    private $upperBoundIncluded = false;

    /**
     * @var Version|null the upper bound of this constraint, null if unbound
     */
    private $upperBound;

    /**
     * @param string $constraintString
     */
    private function __construct($constraintString)
    {
        $this->constraintString = (string) $constraintString;
    }

    /**
     * @param string $versionConstraint
     *
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public static function fromString($versionConstraint)
    {
        $instance = new self($versionConstraint);

        if (preg_match('/' . self::CLOSED_RANGE_MATCHER . '/', $instance->constraintString, $matches)) {
            $instance->lowerBoundIncluded  = (bool) $matches[1];
            $instance->upperBoundIncluded  = (bool) $matches[4];
            $instance->lowerBound          = Version::fromString($matches[2]);
            $instance->upperBound          = Version::fromString($matches[5]);
            $instance->isSimpleRangeString = true;
        }

        // @TODO handle cases with missing lower or upper range

        return $instance;
    }

    /**
     * @return bool
     */
    public function isSimpleRangeString()
    {
        return $this->isSimpleRangeString;
    }

    /**
     * @return string
     */
    public function getConstraintString()
    {
        return $this->constraintString;
    }

    /**
     * @return boolean
     */
    public function isLowerBoundIncluded()
    {
        return $this->lowerBoundIncluded;
    }

    /**
     * @return null|Version
     */
    public function getLowerBound()
    {
        return $this->lowerBound;
    }

    /**
     * @return null|Version
     */
    public function getUpperBound()
    {
        return $this->upperBound;
    }

    /**
     * @return boolean
     */
    public function isUpperBoundIncluded()
    {
        return $this->upperBoundIncluded;
    }

    /**
     * @param VersionConstraint $other
     *
     * @return bool
     */
    public function contains(VersionConstraint $other)
    {
        if (! ($this->isSimpleRangeString && $other->isSimpleRangeString)) {
            // cannot compare - too complex :-(
            return false;
        }

        if ($this->lowerBound->isGreaterThan($other->lowerBound)) {
            return false;
        }

        if ($other->upperBound->isGreaterThan($this->upperBound)) {
            return false;
        }

        // @todo handle inclusion of lower bounds (to be done via testing - easier)

        return true;
    }
}
