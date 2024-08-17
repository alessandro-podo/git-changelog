<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Tests\Service\Changelog\Render;

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\dto\ChangelogItem;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\dto\ChangelogVersion;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Enum\CommitType;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Render\HtmlRender;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class HtmlRenderTest extends TestCase
{
    public function testRenderInternRelease(): void
    {
        $loader = new FilesystemLoader();
        $loader->setPaths([__DIR__.'/../../../../templates'], 'GitChangelogGenerator');

        $twig = new Environment($loader);
        $security = $this->createMock(Security::class);
        $security->method('isGranted')->willReturnCallback(static fn (...$args): bool => 'ROLE_AUSBILDER' === $args[0]);
        $render = new HtmlRender($twig, $security, []);

        $changelogItem = new ChangelogItem('asd', 'ROLE_NEVER', 'Titel no', null, 'Component', CommitType::FEAT, false);
        $changelogVersion = new ChangelogVersion('1.3.4', new DateTimeImmutable('01.08.2024'), [$changelogItem]);

        $html = $render->render([$changelogVersion], null);

        self::assertStringContainsStringIgnoringCase('internes Release', $html);
        self::assertStringNotContainsStringIgnoringCase('Titel no', $html);
    }

    public function testRenderRelease(): void
    {
        $loader = new FilesystemLoader();
        $loader->setPaths([__DIR__.'/../../../../templates'], 'GitChangelogGenerator');

        $twig = new Environment($loader);
        $security = $this->createMock(Security::class);
        $security->method('isGranted')->willReturnCallback(static fn (...$args): bool => 'ROLE_AUSBILDER' === $args[0]);
        $render = new HtmlRender($twig, $security, ['ROLE_AUSBILDER' => ['intern']]);

        $changelogItem = new ChangelogItem('asd', 'intern', 'Titel no', null, 'Component', CommitType::FEAT, false);
        $changelogVersion = new ChangelogVersion('1.3.4', new DateTimeImmutable('01.08.2024'), [$changelogItem]);

        $html = $render->render([$changelogVersion], null);

        self::assertStringContainsStringIgnoringCase('Titel no', $html);
        self::assertStringNotContainsStringIgnoringCase('internes Release', $html);
    }
}
