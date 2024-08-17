<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Service\Changelog\Contract;

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\dto\ChangelogVersion;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\dto\PlannedChanges;

interface RenderInterface
{
    /**
     * @param ChangelogVersion[]                  $changelogVersions
     * @param null|array<string,PlannedChanges[]> $plannedChanges
     */
    public function render(array $changelogVersions, ?array $plannedChanges): string;
}
