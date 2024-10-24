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

use Bwein\Gallery\Model\GalleryCategoryModel;
use Contao\BackendUser;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Database;
use Contao\DataContainer;

/**
 * Class ModuleListener.
 */
class ModuleListener
{
    /**
     * Import the back end user object.
     */
    public function __construct(private readonly ContaoFramework $framework)
    {
    }

    /**
     * @Callback(table="tl_module", target="fields.bweinGalleryCategories.options")
     */
    public function getGalleryCategories(DataContainer $dataContainer): array
    {
        /** @var BackendUser $backendUser */
        $backendUser = $this->framework->getAdapter(BackendUser::class)->getInstance();

        if (!$backendUser->isAdmin && !\is_array($backendUser->gallery)) {
            return [];
        }

        $return = [];
        $categories = Database::getInstance()->execute('SELECT id, title FROM tl_bwein_gallery_category ORDER BY title');

        while ($categories->next()) {
            /** @var GalleryCategoryModel $categories */
            if ($backendUser->hasAccess($categories->id, 'gallery')) {
                $return[$categories->id] = $categories->title;
            }
        }

        return $return;
    }

    /**
     * @Callback(table="tl_module", target="fields.bweinGalleryReaderModule.options")
     */
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
