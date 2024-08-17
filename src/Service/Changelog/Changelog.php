<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Service\Changelog;

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Contract\ParserInterface;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Contract\RenderInterface;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Parser\PlannedChangesParser;

class Changelog
{
    public function __construct(
        private RenderInterface $render,
        private ParserInterface $parser,
        private PlannedChangesParser $plannedChangesParser,
    ) {}

    public function render(): string
    {
        $cv = $this->parser->getChangelogVersions();
        $pl = $this->plannedChangesParser->get();

        return $this->render->render($cv, $pl);
    }
}
