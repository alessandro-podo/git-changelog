<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Service\Changelog\Util;

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\dto\ChangelogItem;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Exception\RuntimeException;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Parser\GitCommitMessageParser;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

/**
 * @codeCoverageIgnore
 */
class GitCommands
{
    public const DELIMITER_MESSAGE = '----------DELIMITER_MESSAGE---------';

    public const DELIMITER_PARAMETER = '----------DELIMITER_PARAMETER---------';

    public function __construct(
        private KernelInterface $kernel,
        private GitCommitMessageParser $gitCommitMessageParser,
        private string $filename,
    ) {}

    /**
     * @return ChangelogItem[]
     *
     * @throws RuntimeException
     */
    public function getCommitSinceLastTag(): array
    {
        $lastTag = $this->getLastTag();

        $format = '%B'.self::DELIMITER_PARAMETER.'%H'.self::DELIMITER_MESSAGE;

        $cmd = "git log --pretty=format:'".$format."' --abbrev-commit --no-merges ".$lastTag.'..HEAD';
        $string = $this->process($cmd);

        $messages = explode(self::DELIMITER_MESSAGE, $string);
        $return = [];
        foreach ($messages as $message) {
            if ('' === $message) {
                continue;
            }

            [$raw, $hash] = explode(self::DELIMITER_PARAMETER, $message);
            $return[] = $this->gitCommitMessageParser->parse(trim($raw), $hash);
        }

        return $return;
    }

    public function getLastTag(): string
    {
        return $this->process("git -C '%path%' describe --tags --abbrev=0");
    }

    public function add(string $file): void
    {
        $this->process("git -C '%path%' add ".$file);
    }

    public function commit(string $message): void
    {
        $message = str_replace('"', "'", $message); // Escape
        $cmd = 'git -C \'%path%\' commit -m "'.$message.'"';

        $this->process($cmd);
    }

    public function tag(string $version): void
    {
        $cmd = "git -C '%path%' tag ".$version;

        $this->process($cmd);
    }

    public function releaseNewVersion(SemVersion $version): void
    {
        $this->add($this->filename);
        $this->add('composer.json');
        $this->commit('chore(release): '.$version);
        $this->tag('v'.$version);
    }

    private function process(string $cmd): string
    {
        $cmd = str_replace('%path%', $this->kernel->getProjectDir(), $cmd);

        $process = Process::fromShellCommandline($cmd);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(\sprintf('Beim ausführen von `%s` kam es zu folgendem Fehler: `%s`', $cmd, $process->getErrorOutput()));
        }

        return trim($process->getOutput());
    }
}
