<?php

declare(strict_types=1);

/*
 * This file is part of gallery albums for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

namespace Bwein\Gallery\Routing;

use Bwein\Gallery\Model\GalleryCategoryModel;
use Bwein\Gallery\Model\GalleryModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\Content\ContentUrlResolverInterface;
use Contao\CoreBundle\Routing\Content\ContentUrlResult;
use Contao\PageModel;

class GalleryResolver implements ContentUrlResolverInterface
{
    public function __construct(private readonly ContaoFramework $framework)
    {
    }

    public function resolve(object $content): ContentUrlResult|null
    {
        if (!$content instanceof GalleryModel) {
            return null;
        }

        $pageAdapter = $this->framework->getAdapter(PageModel::class);
        $categoryAdapter = $this->framework->getAdapter(GalleryCategoryModel::class);

        // Link to the default page
        return ContentUrlResult::resolve($pageAdapter->findPublishedById((int) $categoryAdapter->findById($content->pid)?->jumpTo));
    }

    public function getParametersForContent(object $content, PageModel $pageModel): array
    {
        if (!$content instanceof GalleryModel) {
            return [];
        }

        return ['parameters' => '/'.($content->alias ?: $content->id)];
    }
}
