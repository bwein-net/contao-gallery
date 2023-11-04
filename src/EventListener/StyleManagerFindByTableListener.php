<?php

declare(strict_types=1);

/*
 * This file is part of gallery albums for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

namespace Bwein\Gallery\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Model\Collection;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerModel;
use Oveleon\ContaoComponentStyleManager\StyleManagerModel as StyleManagerV2Model;

/**
 * @Hook("styleManagerFindByTable")
 */
class StyleManagerFindByTableListener
{
    /**
     * @return Collection|array<StyleManagerModel>|StyleManagerModel|array<StyleManagerV2Model>|StyleManagerV2Model|null A collection of models or null if there are no css groups
     */
    public function __invoke(string $table, array $options)
    {
        if ('tl_bwein_gallery' === $table) {
            if (class_exists(StyleManagerV2Model::class)) {
                return StyleManagerV2Model::findBy(['extendGallery=1'], null, $options);
            }

            return StyleManagerModel::findBy(['extendGallery=1'], null, $options);
        }

        return null;
    }
}
