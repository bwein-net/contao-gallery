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

// Extend the default palette
PaletteManipulator::create()
    ->addLegend('gallery_legend', 'amg_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField(['gallery', 'galleryp'], 'gallery_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_user_group')
;

// Add fields to tl_user_group
$GLOBALS['TL_DCA']['tl_user_group']['fields']['gallery'] =
[
    'label' => &$GLOBALS['TL_LANG']['tl_user']['gallery'],
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_bwein_gallery_category.title',
    'eval' => ['multiple' => true],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['galleryp'] =
[
    'label' => &$GLOBALS['TL_LANG']['tl_user']['galleryp'],
    'inputType' => 'checkbox',
    'options' => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['multiple' => true],
    'sql' => 'blob NULL',
];
