<?php

declare(strict_types=1);

/*
 * This file is part of gallery albums for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

namespace Bwein\Gallery\EventListener;

use Bwein\Gallery\EventListener\DataContainer\GalleryChildTableListener;
use Bwein\Gallery\EventListener\DataContainer\MissingLanguageIconListener;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\ChangeLanguage\EventListener\BackendView\ParentChildViewListener;
use Terminal42\ChangeLanguage\EventListener\DataContainer\ParentTableListener;
use Terminal42\ChangeLanguage\PageFinder;

#[AsHook('loadDataContainer')]
class LoadDataContainerListener
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ParameterBagInterface $params,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function __invoke(string $table): void
    {
        $bundles = $this->params->get('kernel.bundles');

        if (isset($bundles['Terminal42ChangeLanguageBundle'])) {
            switch ($table) {
                case 'tl_bwein_gallery_category':
                    $listener = new ParentTableListener();
                    $listener->register($table);
                    break;

                case 'tl_bwein_gallery':
                    $listener = new MissingLanguageIconListener($this->translator);
                    $listener->register($table);

                    $listener = new GalleryChildTableListener($this->requestStack);
                    $listener->register($table);

                    $listener = new ParentChildViewListener(new PageFinder(), $this->requestStack);
                    $listener->register($table);
                    break;
            }
        }
    }
}
