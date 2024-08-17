<?php

declare(strict_types=1);

namespace AlessandroPodo\GitChangelogGenerator;

use AlessandroPodo\GitChangelogGenerator\Command\BumpCommand;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Changelog;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Contract\GeneratorInterface;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Contract\ParserInterface;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Contract\RenderInterface;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Generator\YamlGenerator;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Parser\GitCommitMessageParser;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Parser\PlannedChangesParser;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Parser\YamlParser;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Render\HtmlRender;
use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Util\GitCommands;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * @see https://symfony.com/doc/current/bundles/best_practices.html
 */

/**
 * @codeCoverageIgnore
 */
class GitChangelogGenerator extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        /** @phpstan-ignore-next-line method.notFound */
        $root = $definition->rootNode()->children();

        $root
            ->scalarNode('file')
            ->defaultValue('changelog.yml')
            ->info('relativer Pfad zum Projektdir')
            ->end()
        ;
        $root
            ->scalarNode('plannedChangesFile')
            ->defaultValue('plannedChanges.yml')
            ->info('relativer Pfad zum Projektdir')
            ->end()
        ;
        $root
            ->scalarNode('defaultVisibility')
            ->defaultValue('developer')
            ->info('visibility eintrag, wenn nichts angegeben wird')
            ->end()
        ;
        $root
            ->arrayNode('validateMapping')
            ->info('Mapping von visibility value zu ROLE_* ')
            ->example(['ROLE_ADMIN' => ['intern']])

            ->useAttributeAsKey('role')
            ->normalizeKeys(false)
            ->arrayPrototype()
            ->scalarPrototype()->end()
            ->end()
            ->end()
            ->end()
        ;
        $root
            ->arrayNode('scopes')
            ->info('Erlaubte Scopes, damit es konsistent bleibt')
            ->example('composer')
            ->scalarPrototype()->end()
            ->end()
        ;
    }

    /** @phpstan-ignore-next-line missingType.iterableValue */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $file = param('kernel.project_dir').\DIRECTORY_SEPARATOR.$config['file'];
        $plannedChangesFile = param('kernel.project_dir').\DIRECTORY_SEPARATOR.$config['plannedChangesFile'];

        $container->services()
            ->set(BumpCommand::class)
            ->arg('$yamlGenerator', service(YamlGenerator::class))
            ->arg('$gitCommands', service(GitCommands::class))
            ->tag('console.command')
        ;

        $container->services()
            ->set(Changelog::class)
            ->arg('$render', service(RenderInterface::class))
            ->arg('$parser', service(ParserInterface::class))
            ->arg('$plannedChangesParser', service(PlannedChangesParser::class))
        ;

        $container->services()
            ->set(PlannedChangesParser::class)
            ->arg('$filename', $plannedChangesFile)
        ;

        $container->services()->set(HtmlRender::class)
            ->arg('$twig', service(Environment::class))
            ->arg('$security', service(Security::class))
            ->arg('$visibilityMapping', $config['validateMapping'])
            ->alias(RenderInterface::class, HtmlRender::class)
        ;

        $container->services()->set(YamlParser::class)->lazy()
            ->arg('$filename', $file)
            ->alias(ParserInterface::class, YamlParser::class)
        ;

        $container->services()->set(YamlGenerator::class)
            ->arg('$filename', $file)
            ->alias(GeneratorInterface::class, YamlParser::class)
        ;

        $container->services()->set(GitCommands::class)
            ->arg('$kernel', service(KernelInterface::class))
            ->arg('$gitCommitMessageParser', service(GitCommitMessageParser::class))
            ->arg('$filename', basename($file))
        ;

        $container->services()->set(GitCommitMessageParser::class)
            ->arg('$validScopes', $config['scopes'])
            ->arg('$defaultVisibility', $config['defaultVisibility'])
        ;
    }
}
