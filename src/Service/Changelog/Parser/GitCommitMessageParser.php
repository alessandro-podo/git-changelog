<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Service\Changelog\Parser;

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\dto\ChangelogItem;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Enum\CommitType;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Exception\ParseException;

class GitCommitMessageParser
{
    /** @phpstan-ignore-next-line */
    private const HEADER_REGEX = '/^(?<type>[a-z]+)(\((?<scope>.+)\))?[:][[:blank:]](?<title>.+)/iums';

    /** @phpstan-ignore-next-line */
    private const FOOTER_REGEX = '/(?<token>^([a-z0-9_-]+|BREAKING[[:blank:]]CHANGES?))(?<value>([:][[:blank:]]|[:]?[[:blank:]][#](?=\w)).*?)$/iums';

    public function __construct(
        /** @var string[] $validScopes */
        private array $validScopes,
        private string $defaultVisibility,
    ) {}

    public function parse(string $commitMessage, string $hash): ChangelogItem
    {
        $rows = explode("\n", $commitMessage);

        $subject = $rows[0];
        unset($rows[0]);

        preg_match(self::HEADER_REGEX, $subject, $matchesSubject);

        /** @phpstan-ignore-next-line */
        $scope = $matchesSubject['scope'];
        if ('' === $scope) {
            $scope = null;
        }

        if (null !== $scope && !\in_array($scope, $this->validScopes, true)) {
            throw new ParseException(\sprintf('Der %s ist nicht in den erlauben Scopes %s enthalten.', $scope, implode(',', $this->validScopes)));
        }

        /** @phpstan-ignore-next-line */
        $title = trim($matchesSubject['title']);

        /** @phpstan-ignore-next-line */
        $type = $matchesSubject['type'];

        $body = implode("\n", $rows);

        $footers = [];
        if (preg_match_all(self::FOOTER_REGEX, $body, $matchesBody, PREG_SET_ORDER, 0)) {
            foreach ($matchesBody as $match) {
                $body = str_replace($match[0], '', $body);

                $value = ltrim($match['value'], ':');
                $footers[$match['token']] = $value;
            }
        }

        $description = trim($body);
        if ('' === $description) {
            $description = null;
        }

        $visibilityCode = $footers['v'] ?? $footers['visibility'] ?? $this->defaultVisibility;

        if (\array_key_exists('title', $footers)) {
            $title = trim($footers['title']);
        }

        if (\array_key_exists('description', $footers)) {
            $description = trim($footers['description']);
        }

        return new ChangelogItem(
            $hash,
            trim($visibilityCode),
            $title,
            $description,
            $scope,
            CommitType::from($type),
            false,
        );
    }
}
