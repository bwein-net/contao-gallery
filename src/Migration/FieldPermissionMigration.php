<?php

declare(strict_types=1);

/*
 * This file is part of gallery albums for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

namespace Bwein\Gallery\Migration;

use Contao\CoreBundle\Migration\Version507\AbstractFieldPermissionMigration;

class FieldPermissionMigration extends AbstractFieldPermissionMigration
{
    protected function getMapping(): array
    {
        return [
            'galleryp' => [
                'create' => 'tl_bwein_gallery_category::create',
                'delete' => 'tl_bwein_gallery_category::delete',
            ],
        ];
    }
}
