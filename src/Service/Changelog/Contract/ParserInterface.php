<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Service\Changelog\Contract;

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\dto\ChangelogVersion;

interface ParserInterface
{
    /**
     * @return ChangelogVersion[]
     */
    public function getChangelogVersions(): array;
}
