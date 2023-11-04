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
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\ChangeLanguage\EventListener\BackendView\ParentChildViewListener;
use Terminal42\ChangeLanguage\EventListener\DataContainer\ParentTableListener;

/**
 * @Hook("loadDataContainer")
 */
class LoadDataContainerListener
{
    private TranslatorInterface $translator;

    private ParameterBagInterface $params;

    public function __construct(TranslatorInterface $translator, ParameterBagInterface $params)
    {
        $this->translator = $translator;
        $this->params = $params;
    }

    public function __invoke(string $table): void
    {
        $bundles = $this->params->get('kernel.bundles');

        if (isset($bundles['Terminal42ChangeLanguageBundle'])) {
            switch ($table) {
                case 'tl_bwein_gallery_category':
                    $listener = new ParentTableListener($table);
                    $listener->register();
                    break;

                case 'tl_bwein_gallery':
                    $listener = new MissingLanguageIconListener($this->translator);
                    $listener->register($table);

                    $listener = new GalleryChildTableListener($table);
                    $listener->register();

                    $listener = new ParentChildViewListener($table);
                    $listener->register();
                    break;
            }
        }
    }
}
