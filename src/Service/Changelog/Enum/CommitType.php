<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Service\Changelog\Enum;

enum CommitType: string
{
    case FIX = 'fix';
    case FEAT = 'feat';
    case REFACTOR = 'refactor';
    case CHORE = 'chore';
    case PERF = 'perf';

    public function icon(): string
    {
        return match ($this) {
            self::FIX => '🐛',
            self::FEAT => '✨',
            self::PERF => '🐎',
            self::REFACTOR => '🔨',
            self::CHORE => '📦',
        };
    }
}
