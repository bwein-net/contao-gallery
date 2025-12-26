<?php

declare(strict_types=1);

/*
 * This file is part of gallery albums for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

use Contao\BackendUser;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\System;
use Oveleon\ContaoComponentStyleManager\StyleManager as StyleManagerV2;
use Oveleon\ContaoComponentStyleManager\StyleManager\StyleManager;

System::loadLanguageFile('tl_content');

$GLOBALS['TL_DCA']['tl_bwein_gallery'] =
[
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_bwein_gallery_category',
        'switchToEdit' => true,
        'enableVersioning' => true,
        'markAsCopy' => 'title',
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'alias' => 'index',
                'pid,start,stop,published' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['startDate'],
            'headerFields' => ['title', 'jumpTo', 'tstamp', 'protected'],
            'panelLayout' => 'filter;sort,search,limit',
            'child_record_class' => 'no_padding',
        ],
        'operations' => [
            'edit',
            'copy',
            'cut',
            'delete',
            'toggle' => [
                'href' => 'act=toggle&amp;field=published',
                'icon' => 'visible.svg',
                'showInHeader' => true,
            ],
            'feature' => [
                'href' => 'act=toggle&amp;field=featured',
                'icon' => 'featured.svg',
            ],
            'show',
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['previewImageType'],
        'default' => '{title_legend},title,featured,alias,author;
                        {date_legend},startDate,endDate;
                        {source_legend},images,sortBy,previewImageType;
                        {info_legend},event,place,photographer,description;
                        {meta_legend},metaTitle,robots,metaDescription,serpPreview;
                        {expert_legend:hide},cssClass;
                        {publish_legend},published,start,stop;',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_bwein_gallery_category.title',
            'sql' => 'int(10) unsigned NOT NULL default 0',
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'tstamp' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'title' => [
            'search' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'featured' => [
            'toggle' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => ['type' => 'boolean', 'default' => false],
            'default' => 0,
        ],
        'alias' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'alias', 'doNotCopy' => true, 'unique' => true, 'maxlength' => 255, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(255) BINARY NOT NULL default ''",
        ],
        'author' => [
            'default' => BackendUser::getInstance()->id,
            'search' => true,
            'filter' => true,
            'sorting' => false,
            'flag' => DataContainer::SORT_ASC,
            'inputType' => 'select',
            'foreignKey' => 'tl_user.name',
            'eval' => ['doNotCopy' => true, 'chosen' => true, 'mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => 'int(10) unsigned NOT NULL default 0',
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'startDate' => [
            'default' => time(),
            'filter' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_MONTH_DESC,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'mandatory' => true, 'doNotCopy' => true, 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => 'bigint(20) NULL',
        ],
        'endDate' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'doNotCopy' => true, 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => 'bigint(20) NULL',
        ],
        'images' => [
            'inputType' => 'fileTree',
            'eval' => [
                'multiple' => true,
                'isSortable' => true,
                'fieldType' => 'checkbox',
                'files' => true,
                'mandatory' => true,
                'isGallery' => true,
                'extensions' => '%contao.image.valid_extensions%',
            ],
            'sql' => 'blob NULL',
        ],
        'sortBy' => [
            'inputType' => 'select',
            'options' => ['name_asc', 'name_desc', 'date_asc', 'date_desc', 'random', 'custom'],
            'reference' => &$GLOBALS['TL_LANG']['tl_content'],
            'eval' => ['tl_class' => 'w50 clr', 'includeBlankOption' => true],
            'sql' => "varchar(32) COLLATE ascii_bin NOT NULL default ''",
        ],
        'previewImageType' => [
            'inputType' => 'select',
            'options' => [
                'random_preview_image',
                'first_preview_image',
                'no_preview_image',
                'select_preview_image',
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_bwein_gallery']['previewImageTypeOptions'],
            'eval' => ['submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'previewImage' => [
            'inputType' => 'fileTree',
            'eval' => [
                'mandatory' => true,
                'fieldType' => 'radio',
                'files' => true,
                'filesOnly' => true,
                'extensions' => '%contao.image.valid_extensions%',
                'tl_class' => 'clr', ],
            'sql' => 'binary(16) NULL',
        ],
        'event' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'place' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'photographer' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'clr'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'description' => [
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'tl_class' => 'clr', 'basicEntities' => true],
            'sql' => 'text NULL',
        ],
        'metaTitle' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'robots' => [
            'search' => true,
            'inputType' => 'select',
            'options' => ['index,follow', 'index,nofollow', 'noindex,follow', 'noindex,nofollow'],
            'eval' => ['tl_class' => 'w50', 'includeBlankOption' => true],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'metaDescription' => [
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['style' => 'height:60px', 'decodeEntities' => true, 'tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
        'serpPreview' => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['serpPreview'],
            'inputType' => 'serpPreview',
            'eval' => ['titleFields' => ['metaTitle', 'title'], 'descriptionFields' => ['metaDescription', 'description']],
            'sql' => null,
        ],
        'cssClass' => [
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'published' => [
            'toggle' => true,
            'filter' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true],
            'sql' => ['type' => 'boolean', 'default' => false],
            'default' => 0,
        ],
        'start' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'stop' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
    ],
];

if (class_exists(StyleManager::class) || class_exists(StyleManagerV2::class)) {
    PaletteManipulator::create()
        ->addLegend('style_manager_legend', 'expert_legend', PaletteManipulator::POSITION_BEFORE)
        ->addField('styleManager', 'style_manager_legend', PaletteManipulator::POSITION_APPEND)
        ->applyToPalette('default', 'tl_bwein_gallery')
    ;

    $GLOBALS['TL_DCA']['tl_bwein_gallery']['fields']['styleManager'] = [
        'inputType' => 'stylemanager',
        'eval' => ['tl_class' => 'clr stylemanager'],
        'sql' => 'blob NULL',
    ];
    $GLOBALS['TL_DCA']['tl_bwein_gallery']['fields']['cssClass']['load_callback'] = [
        [class_exists(StyleManagerV2::class) ? StyleManagerV2::class : StyleManager::class, 'onLoad'],
    ];
    $GLOBALS['TL_DCA']['tl_bwein_gallery']['fields']['cssClass']['save_callback'] = [
        [class_exists(StyleManagerV2::class) ? StyleManagerV2::class : StyleManager::class, 'onSave'],
    ];
}
