<?php

declare(strict_types=1);

/*
 * This file is part of gallery albums for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

namespace Bwein\Gallery\EventListener\Navigation;

use Bwein\Gallery\Model\GalleryCategoryModel;
use Bwein\Gallery\Model\GalleryModel;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\PageModel;
use Terminal42\ChangeLanguage\EventListener\Navigation\AbstractNavigationListener;

/**
 * @Hook("changelanguageNavigation")
 */
class GalleryNavigationListener extends AbstractNavigationListener
{
    protected function getUrlKey(): string
    {
        return 'items';
    }

    protected function findCurrent(): GalleryModel|null
    {
        $alias = $this->getAutoItem();

        if ('' === $alias) {
            return null;
        }

        /** @var PageModel $objPage */
        global $objPage;

        if (null === ($archives = GalleryCategoryModel::findBy('jumpTo', $objPage->id))) {
            return null;
        }

        // Fix Contao bug that returns a collection (see contao-changelanguage#71)
        $options = ['limit' => 1, 'return' => 'Model'];

        return GalleryModel::findPublishedByParentAndIdOrAlias($alias, $archives->fetchEach('id'), $options);
    }

    protected function findPublishedBy(array $columns, array $values = [], array $options = []): GalleryModel|null
    {
        return GalleryModel::findOneBy(
            $this->addPublishedConditions($columns, GalleryModel::getTable()),
            $values,
            $options,
        );
    }
}
