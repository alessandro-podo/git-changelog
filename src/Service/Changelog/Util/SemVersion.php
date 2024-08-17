<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Service\Changelog\Util;

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\dto\ChangelogItem;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Enum\CommitType;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Enum\SemVerReleaseType;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Exception\RuntimeException;
use Stringable;

class SemVersion implements Stringable
{
    private const VERSION_REGEX = '([0-9]+)\.([0-9]+)\.([0-9]+)(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+[0-9A-Za-z-]+)?';

    private int $major;

    private int $minor;

    private int $patch;

    public function __construct(string $version, string $prefix = 'v')
    {
        $version = str_replace($prefix, '', trim($version));
        $this->parse($version);
    }

    public function __toString(): string
    {
        return $this->major.'.'.$this->minor.'.'.$this->patch;
    }

    public function bump(SemVerReleaseType $release): self
    {
        if (SemVerReleaseType::MAJOR === $release) {
            ++$this->major;
            $this->minor = 0;
            $this->patch = 0;
        }

        if (SemVerReleaseType::MINOR === $release) {
            ++$this->minor;
            $this->patch = 0;
        }

        if (SemVerReleaseType::PATCH === $release) {
            ++$this->patch;
        }

        return new self((string) $this);
    }

    /**
     * @param ChangelogItem[] $changes
     */
    public function findNewSemVer(array $changes): SemVerReleaseType
    {
        $semVer = SemVerReleaseType::PATCH;

        foreach ($changes as $change) {
            if (CommitType::FEAT === $change->type && SemVerReleaseType::PATCH === $semVer) {
                $semVer = SemVerReleaseType::MINOR;
            }

            if ($change->bcBreak) {
                $semVer = SemVerReleaseType::MAJOR;
            }
        }

        return $semVer;
    }

    private function parse(string $version): void
    {
        preg_match('/^'.self::VERSION_REGEX.'$/', $version, $matches);
        if (\count($matches) < 4) {
            throw new RuntimeException('Der Versionsstring kann nicht geparsed werden');
        }

        $this->major = (int) $matches[1];
        $this->minor = (int) $matches[2];
        $this->patch = (int) $matches[3];
    }
}
