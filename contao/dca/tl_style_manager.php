<?php

declare(strict_types=1);

/*
 * This file is part of gallery albums for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Oveleon\ContaoComponentStyleManager\StyleManager as StyleManagerV2;
use Oveleon\ContaoComponentStyleManager\StyleManager\StyleManager;

if (class_exists(StyleManager::class) || class_exists(StyleManagerV2::class)) {
    PaletteManipulator::create()
        ->addField(
            ['extendGallery'],
            'publish_legend',
            PaletteManipulator::POSITION_APPEND,
        )
        ->applyToPalette('default', 'tl_style_manager')
    ;

    // Extend fields
    $GLOBALS['TL_DCA']['tl_style_manager']['fields']['extendGallery'] =
    [
        'label' => &$GLOBALS['TL_LANG']['tl_style_manager']['extendGallery'],
        'exclude' => true,
        'filter' => true,
        'inputType' => 'checkbox',
        'eval' => ['tl_class' => 'clr'],
        'sql' => ['type' => 'boolean', 'default' => false],
        'default' => 0,
    ];
}
