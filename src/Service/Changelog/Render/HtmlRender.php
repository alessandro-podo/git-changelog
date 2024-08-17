<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Service\Changelog\Render;

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Contract\RenderInterface;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\dto\ChangelogItem;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\dto\ChangelogVersion;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment;

class HtmlRender implements RenderInterface
{
    public function __construct(
        private Environment $twig,
        private Security $security,
        /** @var array<string,string[]>$visibilityMapping Key ist die Rolle und Strings die visibilties aus dem Commit */
        private array $visibilityMapping,
    ) {}

    public function render(array $changelogVersions, ?array $plannedChanges): string
    {
        $filteredChangelogVersions = [];
        foreach ($changelogVersions as $version) {
            $filteredChangelogItems = [];
            foreach ($version->changelogItems as $changelogItem) {
                if ($this->isAllowed($changelogItem)) {
                    $filteredChangelogItems[] = $changelogItem;
                }
            }

            $filteredChangelogVersions[] = new ChangelogVersion($version->version, $version->createdAt, $filteredChangelogItems);
        }

        return $this->twig->render('@GitChangelogGenerator/changelog.html.twig', ['changelogVersions' => $filteredChangelogVersions, 'plannedChanges' => $plannedChanges]);
    }

    private function isAllowed(ChangelogItem $changelogItem): bool
    {
        $visibility = $changelogItem->visibilityCode;

        $allowed = false;
        foreach ($this->visibilityMapping as $role => $visibilityCodes) {
            if (\in_array($visibility, $visibilityCodes, true) && $this->security->isGranted($role)) {
                $allowed = true;

                break;
            }
        }

        return $allowed;
    }
}
