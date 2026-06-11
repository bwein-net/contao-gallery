<?php

declare(strict_types=1);

/*
 * This file is part of gallery albums for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

namespace Bwein\Gallery\EventListener\DataContainer;

use Bwein\Gallery\Security\ContaoGalleryPermissions;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Database;
use Contao\DataContainer;
use Symfony\Bundle\SecurityBundle\Security;

class ModuleListener
{
    public function __construct(private readonly Security $security)
    {
    }

    #[AsCallback(table: 'tl_module', target: 'fields.bweinGalleryCategories.options')]
    public function getGalleryCategories(DataContainer $dataContainer): array
    {
        $return = [];
        $categories = Database::getInstance()->execute('SELECT id, title FROM tl_bwein_gallery_category ORDER BY title');

        while ($categories->next()) {
            if ($this->security->isGranted(ContaoGalleryPermissions::USER_CAN_EDIT_CATEGORY, $categories->id)) {
                $return[$categories->id] = $categories->title;
            }
        }

        return $return;
    }

    #[AsCallback(table: 'tl_module', target: 'fields.bweinGalleryReaderModule.options')]
    public function getReaderModules(DataContainer $dataContainer): array
    {
        $return = [];
        $modules = Database::getInstance()->execute("SELECT m.id, m.name, t.name AS theme FROM tl_module m LEFT JOIN tl_theme t ON m.pid=t.id WHERE m.type='galleryreader' ORDER BY t.name, m.name");

        while ($modules->next()) {
            $return[$modules->theme][$modules->id] = $modules->name.' (ID '.$modules->id.')';
        }

        return $return;
    }
}
