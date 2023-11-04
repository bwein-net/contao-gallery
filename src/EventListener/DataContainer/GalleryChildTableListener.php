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

use Bwein\Gallery\Model\GalleryModel;
use Contao\Model;
use Contao\Model\Collection;
use Terminal42\ChangeLanguage\EventListener\DataContainer\AbstractChildTableListener;

class GalleryChildTableListener extends AbstractChildTableListener
{
    protected function getTitleField(): string
    {
        return 'title';
    }

    protected function getSorting(): string
    {
        return 'sorting';
    }

    /**
     * @param GalleryModel             $current
     * @param Collection<GalleryModel> $models
     */
    protected function formatOptions(Model $current, Collection $models): array
    {
        $options = [];

        foreach ($models as $model) {
            $options[$model->id] = sprintf('%s [ID %s]', $model->title, $model->id);
        }

        return $options;
    }
}
