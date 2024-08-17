<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Service\Changelog\dto;

use DateTimeImmutable;

final readonly class ChangelogVersion
{
    public function __construct(
        public string $version,
        public DateTimeImmutable $createdAt,
        /** @var ChangelogItem[] $changelogItems */
        public array $changelogItems,
    ) {}

    /**
     * @return array<string,ChangelogItem[]>
     */
    public function getChangelogItemsGroupedByScope(): array
    {
        $groupedChangelogItems = [];
        foreach ($this->changelogItems as $changelogItem) {
            $groupedChangelogItems[$changelogItem->scope ?? 'Allgemein'][] = $changelogItem;
        }

        return $groupedChangelogItems;
    }
}
