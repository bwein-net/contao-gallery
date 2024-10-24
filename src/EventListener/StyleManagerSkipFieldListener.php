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

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Model\Collection;
use Contao\StringUtil;
use Oveleon\ContaoComponentStyleManager\StyleManagerModel;
use Oveleon\ContaoComponentStyleManager\StyleManagerModel as StyleManagerV2Model;

#[AsHook('styleManagerSkipField')]
class StyleManagerSkipFieldListener
{
    /**
     * @param Collection|StyleManagerModel|StyleManagerV2Model $styleGroups
     */
    public function __invoke($styleGroups, $widget): bool
    {
        if ((bool) $styleGroups->extendGallery && 'tl_bwein_gallery' === $widget->strTable) {
            $dcaTypes = StringUtil::deserialize($styleGroups->dcaTypes);

            if (null !== $dcaTypes && !\in_array($widget->activeRecord->type, $dcaTypes, true)) {
                return true;
            }
        }

        return false;
    }
}
