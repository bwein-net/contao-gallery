<?php

declare(strict_types=1);

/*
 * This file is part of gallery albums for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

use Bwein\Gallery\Model\GalleryCategoryModel;
use Bwein\Gallery\Model\GalleryModel;

// Back end module
$GLOBALS['BE_MOD']['content']['gallery'] =
    [
        'tables' => ['tl_bwein_gallery_category', 'tl_bwein_gallery'],
    ];

// Model
$GLOBALS['TL_MODELS']['tl_bwein_gallery_category'] = GalleryCategoryModel::class;
$GLOBALS['TL_MODELS']['tl_bwein_gallery'] = GalleryModel::class;

// Add permissions
$GLOBALS['TL_PERMISSIONS'][] = 'gallery';
$GLOBALS['TL_PERMISSIONS'][] = 'galleryp';
