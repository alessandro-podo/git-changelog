<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Tests\Service\Changelog\Util;

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\dto\ChangelogItem;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Enum\CommitType;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Enum\SemVerReleaseType;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Exception\RuntimeException;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Util\SemVersion;
use PHPUnit\Framework\TestCase;

class SemVersionTest extends TestCase
{
    public function testParse(): void
    {
        $this->expectException(RuntimeException::class);
        new SemVersion('1.2');
    }

    public function testBump(): void
    {
        $semVersion = new SemVersion('1.2.3');
        self::assertSame('1.2.4', (string) $semVersion->bump(SemVerReleaseType::PATCH));
        self::assertSame('1.3.0', (string) $semVersion->bump(SemVerReleaseType::MINOR));
        self::assertSame('2.0.0', (string) $semVersion->bump(SemVerReleaseType::MAJOR));
    }

    public function testFindNewSemVerPatch(): void
    {
        $semVersion = new SemVersion('1.2.3');
        $changes = [
            new ChangelogItem('', '', '', '', '', CommitType::FIX, false),
            new ChangelogItem('', '', '', '', '', CommitType::REFACTOR, false),
            new ChangelogItem('', '', '', '', '', CommitType::CHORE, false),
        ];
        self::assertSame(SemVerReleaseType::PATCH, $semVersion->findNewSemVer($changes));
    }

    public function testFindNewSemVerMinor(): void
    {
        $semVersion = new SemVersion('1.2.3');
        $changes = [
            new ChangelogItem('', '', '', '', '', CommitType::FIX, false),
            new ChangelogItem('', '', '', '', '', CommitType::FEAT, false),
            new ChangelogItem('', '', '', '', '', CommitType::CHORE, false),
        ];
        self::assertSame(SemVerReleaseType::MINOR, $semVersion->findNewSemVer($changes));
    }

    public function testFindNewSemVerMajor(): void
    {
        $semVersion = new SemVersion('1.2.3');
        $changes = [
            new ChangelogItem('', '', '', '', '', CommitType::FIX, true),
            new ChangelogItem('', '', '', '', '', CommitType::REFACTOR, false),
            new ChangelogItem('', '', '', '', '', CommitType::CHORE, false),
        ];
        self::assertSame(SemVerReleaseType::MAJOR, $semVersion->findNewSemVer($changes));
    }
}
