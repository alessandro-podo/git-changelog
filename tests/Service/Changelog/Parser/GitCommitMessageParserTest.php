<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator\Tests\Service\Changelog\Parser;

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Enum\CommitType;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Exception\ParseException;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Parser\GitCommitMessageParser;
use PHPUnit\Framework\TestCase;

class GitCommitMessageParserTest extends TestCase
{
    public function testParseWithoutBody(): void
    {
        $parser = new GitCommitMessageParser(['composer'], 'dev');

        $result = $parser->parse('feat(composer): first', '39bc6761f1b6fffcffff62a81044b4be875ceeb9');
        self::assertSame(CommitType::FEAT, $result->type);
        self::assertSame('composer', $result->scope);
        self::assertSame('dev', $result->visibilityCode);
        self::assertNull($result->description);
        self::assertSame('first', $result->title);
        self::assertSame('39bc6761f1b6fffcffff62a81044b4be875ceeb9', $result->id);
    }

    public function testParseBreakingChange(): void
    {
        $parser = new GitCommitMessageParser(['composer'], 'dev');

        $result = $parser->parse('feat(composer): first', '39bc6761f1b6fffcffff62a81044b4be875ceeb9');
        self::assertSame(CommitType::FEAT, $result->type);
        self::assertSame('composer', $result->scope);
        self::assertSame('dev', $result->visibilityCode);
        self::assertNull($result->description);
        self::assertSame('first', $result->title);
        self::assertSame('39bc6761f1b6fffcffff62a81044b4be875ceeb9', $result->id);
    }

    public function testParseWithoutBodyAndScope(): void
    {
        $parser = new GitCommitMessageParser(['composer'], 'dev');

        $result = $parser->parse('feat: first', '39bc6761f1b6fffcffff62a81044b4be875ceeb9');
        self::assertSame(CommitType::FEAT, $result->type);
        self::assertNull($result->scope);
        self::assertSame('dev', $result->visibilityCode);
        self::assertNull($result->description);
        self::assertSame('first', $result->title);
        self::assertSame('39bc6761f1b6fffcffff62a81044b4be875ceeb9', $result->id);
    }

    public function testParseWithBody(): void
    {
        $parser = new GitCommitMessageParser(['composer'], 'dev');

        $result = $parser->parse('feat(composer): add ux-icons


das istder Body

auch mulitline gehört dazu


asd


visibility: intern

ROLE2: intern2', '4bb1bc67b4014f81d945a7897f169ffb5d68e267');
        self::assertSame(CommitType::FEAT, $result->type);
        self::assertSame('composer', $result->scope);
        self::assertSame('intern', $result->visibilityCode);
        self::assertSame('das istder Body

auch mulitline gehört dazu


asd', $result->description);
        self::assertSame('add ux-icons', $result->title);
    }

    public function testParseWithDifferentTitleAndDescription(): void
    {
        $parser = new GitCommitMessageParser(['composer'], 'dev');

        $result = $parser->parse('feat(composer): add ux-icons


das istder Body

auch mulitline gehört dazu


asd


v: intern
title: anderer Title
description: das ist eine andere Beschreibung
ROLE2: intern2', '4bb1bc67b4014f81d945a7897f169ffb5d68e267');
        self::assertSame(CommitType::FEAT, $result->type);
        self::assertSame('composer', $result->scope);
        self::assertSame('intern', $result->visibilityCode);
        self::assertSame('das ist eine andere Beschreibung', $result->description);
        self::assertSame('anderer Title', $result->title);
    }

    public function testParseWithWrongScope(): void
    {
        $parser = new GitCommitMessageParser(['composer', 'schedule'], 'dev');

        $this->expectException(ParseException::class);
        $parser->parse('fix(wrong): first', '39bc6761f1b6fffcffff62a81044b4be875ceeb9');
    }
}
