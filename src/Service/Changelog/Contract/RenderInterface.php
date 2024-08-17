<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Service\Changelog\Contract;

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\dto\ChangelogVersion;

interface RenderInterface
{
    /** @param ChangelogVersion[] $changelogVersions */
    public function render(array $changelogVersions): string;
}
