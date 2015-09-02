<?php

namespace Roave\SecurityAdvisories;

/**
 * A simple version constraint - naively assumes that it is only about ranges like ">=1.2.3,<4.5.6"
 */
final class VersionConstraint
{
    const CLOSED_RANGE_MATCHER    = '^>(=?)\s*((\d+.)*\d+)\s*,\s*<(=?)\s*((\d+.)*\d+)$';
    const LEFT_OPEN_RANGE_MATCHER = '^<(=?)\s*((\d+.)*\d+)$';
    const RIGHT_OPEN_RANGE_MATCHER = '^>(=?)\s*((\d+.)*\d+)$';

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

        if (preg_match('/' . self::LEFT_OPEN_RANGE_MATCHER . '/', $instance->constraintString, $matches)) {
            $instance->upperBoundIncluded  = (bool) $matches[1];
            $instance->upperBound          = Version::fromString($matches[2]);
            $instance->isSimpleRangeString = true;
        }

        if (preg_match('/' . self::RIGHT_OPEN_RANGE_MATCHER . '/', $instance->constraintString, $matches)) {
            $instance->lowerBoundIncluded  = (bool) $matches[1];
            $instance->lowerBound          = Version::fromString($matches[2]);
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

        if (! $this->containsLowerBound($other->lowerBoundIncluded, $other->lowerBound)) {
            return false;
        }

        if (! $this->containsUpperBound($other->upperBoundIncluded, $other->upperBound)) {
            return false;
        }

        // @todo handle inclusion of lower bounds (to be done via testing - easier)

        return true;
    }

    /**
     * @param bool         $otherLowerBoundIncluded
     * @param Version|null $otherLowerBound
     *
     * @return bool
     */
    private function containsLowerBound($otherLowerBoundIncluded, Version $otherLowerBound = null)
    {
        if (! $this->lowerBound) {
            return true;
        }

        if (! $otherLowerBound) {
            return false;
        }

        if (($this->lowerBoundIncluded === $otherLowerBoundIncluded) || $this->lowerBoundIncluded) {
            return $otherLowerBound->isGreaterOrEqualThan($this->lowerBound);
        }

        return $otherLowerBound->isGreaterThan($this->lowerBound);
    }


    /**
     * @param bool         $otherUpperBoundIncluded
     * @param Version|null $otherUpperBound
     *
     * @return bool
     */
    private function containsUpperBound($otherUpperBoundIncluded, Version $otherUpperBound = null)
    {
        if (! $this->upperBound) {
            return true;
        }

        if (! $otherUpperBound) {
            return false;
        }

        if (($this->upperBoundIncluded === $otherUpperBoundIncluded) || $this->upperBoundIncluded) {
            return $this->upperBound->isGreaterOrEqualThan($otherUpperBound);
        }

        return $this->upperBound->isGreaterThan($otherUpperBound);
    }
}
