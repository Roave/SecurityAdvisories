<?php

namespace Roave\SecurityAdvisories;

/**
 * A simple version, such as 1.0 or 1.0.0.0 or 2.0.1.3.2
 */
final class Version
{
    const VALIDITY_MATCHER = '^(\d+.)*\d+$';

    /**
     * @var string
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
        if (! preg_match('/' . self::VALIDITY_MATCHER . '/', $version)) {
            throw new \InvalidArgumentException(sprintf('Given version "%s" is not a valid version string', $version));
        }

        return new self(array_values(array_map(
            function ($versionNumber) {
                return (int) $versionNumber;
            },
            explode('.', $version)
        )));
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return implode('.', $this->versionNumbers);
    }
}
