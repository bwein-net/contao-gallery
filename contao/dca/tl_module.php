<?php

declare(strict_types=1);

/*
 * This file is part of gallery albums for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

use Contao\Controller;
use ErdmannFreunde\ContaoGridBundle\EventListener\DataContainer\GridColsOptionsListener;

// Add palettes to tl_module
$GLOBALS['TL_DCA']['tl_module']['palettes']['gallerylist'] = '{title_legend},name,headline,type;
                                                                        {config_legend},bweinGalleryCategories,bweinGalleryReaderModule,numberOfItems,bweinGalleryListFeatured,bweinGalleryListOrder,skipFirst,perPage,bweinGalleryPreviewImage;
                                                                        {template_legend:hide},bweinGalleryTemplate,customTpl;
                                                                        {image_legend:hide},imgSize;
                                                                        {grid_legend},grid_columns;
                                                                        {protected_legend:hide},protected;
                                                                        {expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['galleryreader'] = '{title_legend},name,headline,type;
                                                                        {config_legend},bweinGalleryCategories,overviewPage,customLabel;
                                                                        {template_legend:hide},bweinGalleryTemplate,customTpl;
                                                                        {image_legend:hide},imgSize,fullsize,bweinGalleryPerRow,numberOfItems,perPage;
                                                                        {grid_legend},grid_columns;
                                                                        {protected_legend:hide},protected;
                                                                        {expert_legend:hide},guests,cssID';

// Add fields to tl_module
$GLOBALS['TL_DCA']['tl_module']['fields']['bweinGalleryCategories'] =
[
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['multiple' => true, 'mandatory' => true],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['bweinGalleryListFeatured'] =
[
    'exclude' => true,
    'inputType' => 'select',
    'options' => [
        'all_galleries',
        'featured_galleries',
        'unfeatured_galleries',
        'featured_galleries_first',
    ],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['bweinGalleryListFeaturedOptions'],
    'eval' => ['tl_class' => 'w50 clr'],
    'sql' => "varchar(25) NOT NULL default 'all_galleries'",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['bweinGalleryListOrder'] =
[
    'exclude' => true,
    'inputType' => 'select',
    'options' => [
        'order_date_asc',
        'order_date_desc',
        'order_title_asc',
        'order_title_desc',
        'order_random',
    ],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['bweinGalleryListOrderOptions'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(32) NOT NULL default 'order_date_desc'",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['bweinGalleryPreviewImage'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options' => [
        'use_album_options',
        'no_preview_images',
        'random_images',
        'first_image',
        'random_images_at_no_preview_images',
        'first_image_at_no_preview_images',
    ],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['bweinGalleryPreviewImageOptions'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(64) NOT NULL default 'use_album_options'",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['bweinGalleryReaderModule'] =
[
    'exclude' => true,
    'inputType' => 'select',
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
    'sql' => 'int(10) unsigned NOT NULL default 0',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['bweinGalleryTemplate'] =
[
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => static fn () => Controller::getTemplateGroup('album_'),
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['bweinGalleryPerRow'] =
[
    'exclude' => true,
    'inputType' => 'select',
    'options' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
    'eval' => ['tl_class' => 'w50'],
    'sql' => 'smallint(5) unsigned NOT NULL default 4',
];

if (class_exists('GridClass') || class_exists(GridColsOptionsListener::class)) {
    $GLOBALS['TL_DCA']['tl_module']['fields']['grid_columns'] =
    [
        'exclude' => true,
        'search' => true,
        'inputType' => 'select',
        'options_callback' => class_exists(GridColsOptionsListener::class) ? [GridColsOptionsListener::class, method_exists(GridColsOptionsListener::class, 'onOptionsCallback') ? 'onOptionsCallback' : '__invoke'] : ['GridClass', 'getGridCols'],
        'eval' => [
            'mandatory' => false,
            'multiple' => true,
            'size' => 10,
            'tl_class' => 'w50 w50h autoheight',
            'chosen' => true,
        ],
        'sql' => 'text NULL',
    ];
}
