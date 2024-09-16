<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Command;

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Enum\CommitType;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Parser\GitCommitMessageParser;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Util\GitCommands;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use Webmozart\Assert\Assert;

#[AsCommand(name: 'changelog:commit', description: 'Erzeugt die Changelog.yml und legt ein neues Release an')]
class CommitCommand extends Command
{
    private string $commitMessage = '';

    public function __construct(
        private GitCommands $gitCommands,
        private GitCommitMessageParser $gitCommitMessageParser,
        /** @var array<string,list<string>> $visibilityMapping */
        private array $visibilityMapping,
        /** @var string[] $validScopes */
        private array $validScopes,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Zeigt nur die CommitMsg an');
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);

        $commitType = $this->auswahlTyp($io);
        $scope = $this->auswahlScope($io);
        $visibility = $this->visibility($io);
        $commitMsg = $io->ask('Commit Message');
        $title = $io->ask('Title');

        Assert::string($commitMsg);
        Assert::nullOrStringNotEmpty($title);
        $this->createCommitMessage($scope, $visibility, $title, $commitType, $commitMsg);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Die Commit Message lautet:');
        $io->writeln($this->commitMessage);

        if ($input->getOption('dry-run')) {
            return Command::SUCCESS;
        }

        $this->gitCommands->add('.');
        $this->gitCommands->commit($this->commitMessage);

        $io->success('Commited');

        return Command::SUCCESS;
    }

    private function auswahlTyp(SymfonyStyle $io): string
    {
        $visibilitiesGrouped = array_map(static fn (CommitType $commitType) => $commitType->value, CommitType::cases());
        $question = new ChoiceQuestion(
            'Welchen Committype soll es haben?',
            $visibilitiesGrouped,
        );
        $question->setAutocompleterValues($visibilitiesGrouped);
        $question->setErrorMessage('Commit type %s is invalid.');

        $answer = $io->askQuestion($question);
        Assert::string($answer);

        return $answer;
    }

    private function auswahlScope(SymfonyStyle $io): string
    {
        // get all changed Files
        $changedFiles = $this->gitCommands->findChangedFiles();
        // get old commits for changed Files
        $commits = [];
        foreach ($changedFiles as $changedFile) {
            $commits = [...$commits, ...$this->gitCommands->findLastCommitsByFile($changedFile)];
        }

        // get scope from old commits
        $choices = [];
        $choices[] = 'other';
        foreach ($commits as $commit) {
            try {
                $choices[] = $this->gitCommitMessageParser->parse($commit, 'hash')->scope ?? 'none';
            } catch (Throwable) {
                // ignore
            }
        }

        $choices = array_unique($choices);

        $question = new ChoiceQuestion(
            'Welchen Scope soll es haben?',
            $choices,
        );
        $question->setErrorMessage('Scope %s is invalid.');
        $question->setAutocompleterValues($choices);

        $answer = $io->askQuestion($question);
        Assert::string($answer);
        if ('other' !== $answer) {
            return $answer;
        }

        $choices = ['none', ...$this->validScopes];
        $question = new ChoiceQuestion(
            'Welchen Scope soll es haben?',
            $choices,
        );
        $question->setErrorMessage('Scope %s is invalid.');
        $question->setAutocompleterValues($choices);

        $answer = $io->askQuestion($question);
        Assert::string($answer);

        return $answer;
    }

    private function visibility(SymfonyStyle $io): string
    {
        $visibilitiesGrouped = [];
        foreach ($this->visibilityMapping as $role => $visibilities) {
            foreach ($visibilities as $visibility) {
                $visibilitiesGrouped[$visibility][$role] = $role;
            }
        }

        $choices = [];
        $choices[] = 'none';
        foreach ($visibilitiesGrouped as $visibility => $role) {
            $io->note($visibility.': '.implode(',', $role));
            $choices[] = $visibility;
        }

        $question = new ChoiceQuestion(
            'Welchen Visibility soll es haben?',
            $choices,
        );
        $question->setAutocompleterValues($choices);
        $question->setErrorMessage('Visibility %s is invalid.');

        $answer = $io->askQuestion($question);
        Assert::string($answer);

        return $answer;
    }

    private function createCommitMessage(string $scope, string $visibility, ?string $title, string $commitType, string $commitMsg): void
    {
        $scopeStr = null;
        if ('none' !== $scope) {
            $scopeStr = '('.$scope.')';
        }

        $visibilityStr = null;
        if ('none' !== $visibility) {
            $visibilityStr = "\nv: ".$visibility;
        }

        $titleStr = null;
        if (null !== $title) {
            $titleStr = "\ntitle: ".$title;
        }

        $this->commitMessage = $commitType.$scopeStr.': '.$commitMsg."\n".$visibilityStr.$titleStr;
    }
}
