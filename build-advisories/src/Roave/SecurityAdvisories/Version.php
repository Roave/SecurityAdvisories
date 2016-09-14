<?php

declare(strict_types=1);

namespace Roave\SecurityAdvisories;

/**
 * A simple version, such as 1.0 or 1.0.0.0 or 2.0.1.3.2
 */
final class Version
{
    const VALIDITY_MATCHER = '/^(?:\d+\.)*\d+$/';

    /**
     * @var string[]
     */
    private $versionNumbers;

    /**
     * @param int[] $versionNumbers
     */
    private function __construct(array $versionNumbers)
    {
        $this->versionNumbers = $versionNumbers;
    }

    /**
     * @param string $version
     *
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public static function fromString($version)
    {
        if (! preg_match(self::VALIDITY_MATCHER, $version)) {
            throw new \InvalidArgumentException(sprintf('Given version "%s" is not a valid version string', $version));
        }

        return new self(self::removeTrailingZeroes(array_map('intval', explode('.', $version))));
    }

    /**
     * @param Version $other
     *
     * @return bool
     */
    public function equalTo(self $other) : bool
    {
        return $other->versionNumbers === $this->versionNumbers;
    }

    /**
     * Compares two versions and sees if this one is greater than the given one
     *
     * @todo may become a simple array comparison (if PHP supports it)
     *
     * @param Version $other
     *
     * @return bool
     */
    public function isGreaterThan(self $other)
    {
        foreach ($other->versionNumbers as $index => $otherVersion) {
            $thisVersion = isset($this->versionNumbers[$index]) ? $this->versionNumbers[$index] : 0;

            if ($thisVersion === $otherVersion) {
                continue;
            }

            return $thisVersion > $otherVersion;
        }

        return (bool) array_filter(array_slice($this->versionNumbers, count($other->versionNumbers)));
    }

    /**
     * Compares two versions and sees if this one is greater or equal than the given one
     *
     * @todo may become a simple array comparison (if PHP supports it)
     *
     * @param Version $other
     *
     * @return bool
     */
    public function isGreaterOrEqualThan(self $other)
    {
        foreach ($other->versionNumbers as $index => $otherVersion) {
            $thisVersion = isset($this->versionNumbers[$index]) ? $this->versionNumbers[$index] : 0;

            if ($thisVersion === $otherVersion) {
                continue;
            }

            return $thisVersion > $otherVersion;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return implode('.', $this->versionNumbers);
    }

    /**
     * @param int[] $versionNumbers
     *
     * @return int[]
     */
    private static function removeTrailingZeroes(array $versionNumbers)
    {
        for ($i = count($versionNumbers) - 1; $i > 0; $i -= 1) {
            if ($versionNumbers[$i] > 0) {
                break;
            }
        }

        return array_slice($versionNumbers, 0, $i + 1);
    }
}
