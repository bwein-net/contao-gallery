<?php

declare(strict_types=1);

/*
 * This file is part of gallery albums for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

namespace Bwein\Gallery\DependencyInjection\Compiler;

use Bwein\Gallery\Data\Extractor\GalleryDataExtractor;
use Bwein\Gallery\EventListener\SocialTagsListener;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Hofff\Contao\SocialTags\Data\Extractor;
use Hofff\Contao\SocialTags\Data\SocialTagsFactory;
use Hofff\Contao\SocialTags\EventListener\Hook\SocialTagsDataAwareListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\RequestStack;

class ConditionalServiceRegistrationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!class_exists(SocialTagsDataAwareListener::class)) {
            return;
        }

        $container->register(SocialTagsListener::class, SocialTagsListener::class)
            ->setArguments([
                new Reference(RequestStack::class),
                new Reference(SocialTagsFactory::class),
                new Reference(ScopeMatcher::class),
                new Reference(ContaoFramework::class),
            ])
            ->setAutowired(true)
            ->setAutoconfigured(false)
            ->addTag('contao.hook', ['hook' => 'getContentElement'])
            ->addTag('contao.hook', ['hook' => 'getFrontendModule'])
        ;

        $container->register(GalleryDataExtractor::class, GalleryDataExtractor::class)
            ->setArguments([
                new Reference(ContaoFramework::class),
                new Reference(RequestStack::class),
                new Reference(ResponseContextAccessor::class),
                new Reference(InsertTagParser::class),
                new Parameter('kernel.project_dir'),
                new Reference(ContentUrlGenerator::class),
            ])
            ->setAutowired(true)
            ->setAutoconfigured(false)
            ->addTag(Extractor::class)
        ;
    }
}
