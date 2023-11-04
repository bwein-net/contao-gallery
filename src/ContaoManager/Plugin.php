<?php

declare(strict_types=1);

/*
 * This file is part of gallery albums for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

namespace Bwein\Gallery\ContaoManager;

use Bwein\Gallery\BweinGalleryBundle;
use Codefog\TagsBundle\CodefogTagsBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ConfigPluginInterface;
use ErdmannFreunde\ContaoGridBundle\ErdmannFreundeContaoGridBundle;
use MadeYourDay\RockSolidFrontendHelper\RockSolidFrontendHelperBundle;
use Oveleon\ContaoComponentStyleManager\ContaoComponentStyleManager;
use Symfony\Component\Config\Loader\LoaderInterface;

class Plugin implements BundlePluginInterface, ConfigPluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(BweinGalleryBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class,
                    ContaoComponentStyleManager::class,
                    'euf_grid',
                    ErdmannFreundeContaoGridBundle::class,
                    CodefogTagsBundle::class,
                    RockSolidFrontendHelperBundle::class, ]),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig): void
    {
        if (class_exists(RockSolidFrontendHelperBundle::class)) {
            $loader->load(__DIR__.'/../../config/config_rsfh.yaml');
        }
    }
}
