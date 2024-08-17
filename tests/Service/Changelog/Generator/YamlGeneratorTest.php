<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Tests\Service\Changelog\Generator;

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\dto\ChangelogItem;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Enum\CommitType;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Generator\YamlGenerator;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Util\SemVersion;
use PHPUnit\Framework\TestCase;
use Random\Randomizer;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\Yaml\Yaml;

class YamlGeneratorTest extends TestCase
{
    use ClockSensitiveTrait;

    public function testGenerate(): void
    {
        self::mockTime('16.08.2024');
        $filename = bin2hex((new Randomizer())->getBytes(12));
        $generator = new YamlGenerator($filename);
        $changes[] = new ChangelogItem('123', 'int', 'Titel1', null, null, CommitType::FIX, false);
        $changes[] = new ChangelogItem('456', 'dev', 'Titel2', '', 'composer', CommitType::REFACTOR, false);
        $changes[] = new ChangelogItem('789', 'test', 'Titel3', '', 'npm', CommitType::CHORE, false);

        $generator->setVersion(new SemVersion('1.2.3'))->setChanges($changes)->generate();

        $yaml = Yaml::parse(file_get_contents($filename));

        self::assertSame('16.08.2024', $yaml[0]['createAt']);
        self::assertSame('1.2.3', $yaml[0]['version']);
        self::assertCount(3, $yaml[0]['commits']);
        self::assertSame('123', $yaml[0]['commits'][0]['id']);
        self::assertSame('int', $yaml[0]['commits'][0]['visibilityCode']);
        self::assertSame('Titel1', $yaml[0]['commits'][0]['title']);
        self::assertNull($yaml[0]['commits'][0]['description']);
        self::assertNull($yaml[0]['commits'][0]['scope']);
        self::assertSame('fix', $yaml[0]['commits'][0]['type']);
        self::assertFalse($yaml[0]['commits'][0]['bc']);

        unlink($filename);
    }

    public function testAddNextRelease(): void
    {
        self::mockTime('16.08.2024');
        $filename = bin2hex((new Randomizer())->getBytes(12));
        $generator = new YamlGenerator($filename);
        $changes[] = new ChangelogItem('', '', '', '', '', CommitType::FIX, false);
        $changes[] = new ChangelogItem('', '', '', '', '', CommitType::REFACTOR, false);
        $changes[] = new ChangelogItem('', '', '', '', '', CommitType::CHORE, false);

        $generator->setVersion(new SemVersion('1.2.3'))->setChanges($changes)->generate();

        self::mockTime('20.08.2024');
        $generator = new YamlGenerator($filename);
        $changes[] = new ChangelogItem('', '', '', '', '', CommitType::FIX, false);
        $changes[] = new ChangelogItem('', '', '', '', '', CommitType::FEAT, false);
        $changes[] = new ChangelogItem('', '', '', '', '', CommitType::CHORE, false);

        $generator->setVersion(new SemVersion('1.2.3'))->setChanges($changes)->generate();

        $yaml = Yaml::parse(file_get_contents($filename));
        self::assertCount(2, $yaml);
        self::assertSame('20.08.2024', $yaml[0]['createAt']);
        self::assertSame('16.08.2024', $yaml[1]['createAt']);

        unlink($filename);
    }
}
