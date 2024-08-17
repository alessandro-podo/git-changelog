<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Service\Changelog\Parser;

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Contract\ParserInterface;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\dto\ChangelogItem;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\dto\ChangelogVersion;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Enum\CommitType;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Exception\ParseException;
use DateTimeImmutable;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

class YamlParser implements ParserInterface
{
    public function __construct(
        private readonly string $filename
    ) {
        if (!is_readable($this->filename)) {
            throw new ParseException(\sprintf('Der Pfad %s ist nicht lesbar', $filename));
        }
    }

    public function getChangelogVersions(): array
    {
        $contents = file_get_contents($this->filename);
        Assert::string($contents);

        $parse = Yaml::parse($contents);
        Assert::isArray($parse);
        $changelogVersions = [];

        foreach ($parse as $item) {
            Assert::isArray($item);
            Assert::keyExists($item, 'commits');
            $changelogItems = [];
            foreach ($item['commits'] as $commit) {
                $changelogItems[] = $this->createChangelogItem($commit);
            }

            $changelogVersions[] = $this->createChangelogVersion($item, $changelogItems);
        }

        return $changelogVersions;
    }

    /**
     * @param ChangelogItem[] $changelogItems
     */
    private function createChangelogVersion(mixed $item, array $changelogItems): ChangelogVersion
    {
        Assert::isArray($item);
        Assert::keyExists($item, 'version');
        Assert::keyExists($item, 'createAt');
        $version = $item['version'];
        $createAt = $item['createAt'];
        Assert::string($version);
        Assert::string($createAt);

        return new ChangelogVersion($version, new DateTimeImmutable($createAt), $changelogItems);
    }

    private function createChangelogItem(mixed $commit): ChangelogItem
    {
        Assert::isArray($commit);
        Assert::keyExists($commit, 'id');
        Assert::keyExists($commit, 'visibilityCode');
        Assert::keyExists($commit, 'title');
        Assert::keyExists($commit, 'description');
        Assert::keyExists($commit, 'scope');
        Assert::keyExists($commit, 'type');
        Assert::keyExists($commit, 'bc');

        $id = $commit['id'];
        $visibilityCode = $commit['visibilityCode'];
        $title = $commit['title'];
        $description = $commit['description'];
        $scope = $commit['scope'];
        $type = $commit['type'];
        $bcBreak = $commit['bc'];

        Assert::string($id);
        Assert::string($visibilityCode);
        Assert::string($title);
        Assert::nullOrStringNotEmpty($description);
        Assert::nullOrStringNotEmpty($scope);
        Assert::string($type);
        Assert::boolean($bcBreak);

        return new ChangelogItem($id, $visibilityCode, $title, $description, $scope, CommitType::from($type), $bcBreak);
    }
}
