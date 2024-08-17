<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Tests\Service\Changelog\Parser;

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Exception\ParseException;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Parser\YamlParser;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertCount;

class YamlParserTest extends TestCase
{
    public function testChagelogVersions(): void
    {
        $yamlParser = new YamlParser(__DIR__.\DIRECTORY_SEPARATOR.'changelog.yml');
        assertCount(6, $yamlParser->getChangelogVersions());
    }

    public function testException(): void
    {
        $this->expectException(ParseException::class);
        new YamlParser(__DIR__.\DIRECTORY_SEPARATOR.'changelog.yaml');
    }
}
