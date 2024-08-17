<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Service\Changelog\Contract;

interface GeneratorInterface
{
    public function generate(): void;
}
