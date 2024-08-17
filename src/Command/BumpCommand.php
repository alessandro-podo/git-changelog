<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Command;

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Generator\YamlGenerator;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Util\GitCommands;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Util\SemVersion;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'changelog:bump', description: 'Erzeugt die Changelog.yml und legt ein neues Release an')]
class BumpCommand extends Command
{
    public function __construct(
        private YamlGenerator $yamlGenerator,
        private GitCommands $gitCommands,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // version herausfinden
        $changes = $this->gitCommands->getCommitSinceLastTag();
        $version = new SemVersion($this->gitCommands->getLastTag());
        $newSemVer = $version->findNewSemVer($changes);
        $nextVersion = $version->bump($newSemVer);
        $this->yamlGenerator->setVersion($nextVersion)->setChanges($changes)->generate();

        // Version string in composer.json setzten
        $composerJsonFile = 'composer.json';

        /** @phpstan-ignore-next-line */
        $composerJson = json_decode(file_get_contents($composerJsonFile), true);

        /** @phpstan-ignore-next-line offsetAccess.nonOffsetAccessible */
        $composerJson['version'] = (string) $nextVersion;
        file_put_contents($composerJsonFile, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // git commit mit dem tag
        $this->gitCommands->releaseNewVersion($nextVersion);

        $io->success('Neue Version: '.$nextVersion.' ist ausgerollt');

        return Command::SUCCESS;
    }
}
