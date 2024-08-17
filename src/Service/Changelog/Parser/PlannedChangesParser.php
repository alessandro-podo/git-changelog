<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Service\Changelog\Parser;

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\dto\PlannedChanges;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Enum\CommitType;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

class PlannedChangesParser
{
    public function __construct(
        private string $filename,
    ) {}

    /**
     * @return null|array<string,PlannedChanges[]>
     */
    public function get(): ?array
    {
        if (!is_readable($this->filename)) {
            return null;
        }

        /** @phpstan-ignore-next-line argument.type */
        $yaml = Yaml::parse(file_get_contents($this->filename));
        if (!is_iterable($yaml)) {
            return [];
        }

        $return = [];
        foreach ($yaml as $scope => $changes) {
            Assert::isArray($changes);
            Assert::string($scope);
            foreach ($changes as $change) {
                Assert::isArray($change);
                Assert::keyExists($change, 'title');
                Assert::keyExists($change, 'description');
                Assert::keyExists($change, 'ready');
                Assert::keyExists($change, 'type');
                $return[$scope][] = new PlannedChanges(
                    $change['title'],
                    $change['description'],
                    $change['ready'],
                    CommitType::from($change['type'])
                );
            }
        }

        return $return;
    }
}
