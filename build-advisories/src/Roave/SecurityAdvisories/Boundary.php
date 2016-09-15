<?php

declare(strict_types=1);

namespace Roave\SecurityAdvisories;

/**
 * A simple version, such as 1.0 or 1.0.0.0 or 2.0.1.3.2
 */
final class Boundary
{
    private const MATCHER = '/^(<|<=|=|>=|>)\s*((?:\d+\.)*\d+)$/';

    /**
     * @var Version
     */
    private $version;

    /**
     * @var string one of "<", "<=", "=", ">=", ">"
     */
    private $limitType;

    private function __construct(Version $version, string $limitType)
    {
        $this->version   = $version;
        $this->limitType = $limitType;
    }

    /**
     * @param string $boundary
     *
     * @return Boundary
     *
     * @throws \InvalidArgumentException
     */
    public static function fromString(string $boundary) : self
    {
        if (! preg_match(self::MATCHER, $boundary, $matches)) {
            throw new \InvalidArgumentException(sprintf('The given string "%s" is not a valid boundary', $boundary));
        }

        return new self(
            Version::fromString($matches[2]),
            $matches[1]
        );
    }

    public function adjacentTo(self $other) : bool
    {
        if (! $other->version->equalTo($this->version)) {
            return false;
        }

        // @TODO should be coded as a matrix constant
        return ($this->limitType === '<=' && $other->limitType === '>')
            || ($this->limitType === '<' && $other->limitType === '=')
            || ($this->limitType === '<' && $other->limitType === '>=')
            || ($this->limitType === '=' && $other->limitType === '>')
            // same rules, but opposite direction (avoids recursion via visitor)
            || ($other->limitType === '<=' && $this->limitType === '>')
            || ($other->limitType === '<' && $this->limitType === '=')
            || ($other->limitType === '<' && $this->limitType === '>=')
            || ($other->limitType === '=' && $this->limitType === '>');
    }

    public function getBoundaryString() : string
    {
        return $this->limitType . $this->version->getVersion();
    }
}
