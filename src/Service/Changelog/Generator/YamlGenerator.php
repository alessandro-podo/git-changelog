<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Service\Changelog\Generator;

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Contract\GeneratorInterface;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\dto\ChangelogItem;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Exception\RuntimeException;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Util\SemVersion;
use DateMalformedStringException;
use Symfony\Component\Yaml\Yaml;
use Throwable;
use Webmozart\Assert\Assert;

use function Symfony\Component\Clock\now;

class YamlGenerator implements GeneratorInterface
{
    private SemVersion $version;

    /**
     * @var ChangelogItem[]
     */
    private array $changes;

    public function __construct(
        private readonly string $filename,
    ) {}

    public function setVersion(SemVersion $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @param ChangelogItem[] $changes
     */
    public function setChanges(array $changes): self
    {
        $this->changes = $changes;

        return $this;
    }

    /**
     * @throws DateMalformedStringException
     * @throws RuntimeException
     */
    public function generate(): void
    {
        try {
            $oldEntries = $this->loadFile();
        } catch (Throwable) {
            $oldEntries = [];
            file_put_contents($this->filename, '');
        }

        $commits = [];
        foreach ($this->changes as $change) {
            $commits[] = $change->toArray();
        }

        $yamlstring = Yaml::dump(
            [
                [
                    'version' => (string) $this->version,
                    'createAt' => now()->format('d.m.Y'),
                    'commits' => $commits,
                ],
                ...$oldEntries,
            ],
            flags: Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK
        );
        file_put_contents($this->filename, $yamlstring);
    }

    /**
     * @throws RuntimeException
     */
    /** @phpstan-ignore-next-line */
    private function loadFile(): array
    {
        if (!is_readable($this->filename)) {
            throw new RuntimeException('Die Datei '.$this->filename.' konnte nicht geladen werden');
        }

        $fileContent = file_get_contents($this->filename);
        Assert::string($fileContent);
        $array = Yaml::parse($fileContent);
        Assert::isArray($array);

        return $array;
    }
}
