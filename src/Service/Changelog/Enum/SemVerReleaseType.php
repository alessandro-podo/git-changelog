<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Service\Changelog\Enum;

/**
 * @codeCoverageIgnore
 */
enum SemVerReleaseType: string
{
    case MAJOR = 'major';
    case MINOR = 'minor';
    case PATCH = 'patch';
}
