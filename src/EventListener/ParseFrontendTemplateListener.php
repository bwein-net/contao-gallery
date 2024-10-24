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

use Bwein\Gallery\Model\GalleryModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Image;
use Contao\System;
use MadeYourDay\RockSolidFrontendHelper\FrontendHooks;

class ParseFrontendTemplateListener extends FrontendHooks
{
    #[AsHook('parseFrontendTemplate')]
    public function __invoke(string $buffer, string $template): string
    {
        if (!$template || !class_exists(FrontendHooks::class) || !($permissions = FrontendHooks::checkLogin())) {
            return $buffer;
        }

        $data = [];

        // get the first tag
        if (preg_match('(<[a-z0-9]+\\s(?>"[^"]*"|\'[^\']*\'|[^>"\'])+)i', $buffer, $matches)) {
            // search for a gallery id injected by gallery module
            if (preg_match('(^(.*\\sclass="[^"]*)'.GalleryModel::FRONTEND_HELPER_CSS_CLASS_PREFIX.'(\d+)(.*)$)is', $matches[0], $matches2)) {
                $data['toolbar'] = true;
                // remove the gallery id class
                $buffer = str_replace($matches2[0], $matches2[1].$matches2[3], $buffer);

                if (\in_array('beModules', $permissions, true)) {
                    System::loadLanguageFile('tl_bwein_gallery');
                    $data['links']['be-module'] = [
                        'url' => FrontendHooks::getBackendURL('gallery', 'tl_bwein_gallery', $matches2[2]),
                        'label' => \sprintf(\is_array($GLOBALS['TL_LANG']['tl_bwein_gallery']['edit']) ? $GLOBALS['TL_LANG']['tl_bwein_gallery']['edit'][1] : $GLOBALS['TL_LANG']['tl_bwein_gallery']['edit'], $matches2[2]),
                        'icon' => Image::getPath('bundles/bweingallery/icons/gallery.svg'),
                    ];
                }
            }
        }

        return static::insertData($buffer, $data);
    }
}
