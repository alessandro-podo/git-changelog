<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Service\Changelog;

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Contract\ParserInterface;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Contract\RenderInterface;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Parser\YamlParser;
use Webmozart\Assert\Assert;

class Changelog
{
    public function __construct(
        private RenderInterface $render,
        private ParserInterface $parser,
    ) {}

    public function render(): string
    {
        $cv = $this->parser->getChangelogVersions();

        return $this->render->render($cv);
    }
}
