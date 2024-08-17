<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Service\Changelog\dto;

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Enum\CommitType;

final readonly class PlannedChanges
{
    public function __construct(
        public string $title,
        public ?string $description,
        public bool $ready,
        public CommitType $type,
    ) {}
}
