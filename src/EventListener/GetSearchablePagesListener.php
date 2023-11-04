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

use Bwein\Gallery\Model\GalleryCategoryModel;
use Bwein\Gallery\Model\GalleryModel;
use Bwein\Gallery\Renderer\GalleryUrlRenderer;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Database;
use Contao\Date;
use Contao\PageModel;

class GetSearchablePagesListener
{
    private GalleryUrlRenderer $urlRenderer;

    /**
     * @internal
     *
     * @required
     */
    public function setUrlRenderer(GalleryUrlRenderer $urlRenderer): void
    {
        $this->urlRenderer = $urlRenderer;
    }

    /**
     * @Hook("getSearchablePages")
     */
    public function onGetSearchablePages(array $pages, int|null $rootId = null, bool $isSitemap = false, string|null $language = null): array
    {
        $root = [];

        if ($rootId > 0) {
            $root = Database::getInstance()->getChildRecords($rootId, 'tl_page');
        }

        $root = array_map('intval', $root);

        $processed = [];
        $time = Date::floorToMinute();

        // Get all gallery categories
        $category = GalleryCategoryModel::findByProtected('');

        // Walk through each archive
        if (null !== $category) {
            while ($category->next()) {
                // Skip gallery categories without target page
                if (!$category->jumpTo) {
                    continue;
                }

                // Skip category outside the root nodes
                if (!empty($root) && !\in_array((int) $category->jumpTo, $root, true)) {
                    continue;
                }

                // Get the URL of the jumpTo page
                if (!isset($processed[$category->jumpTo])) {
                    $parent = PageModel::findWithDetails($category->jumpTo);

                    // The target page does not exist
                    if (null === $parent) {
                        continue;
                    }

                    // The target page has not been published
                    if (!$parent->published || ($parent->start && $parent->start > $time) || ($parent->stop && $parent->stop <= $time + 60)) {
                        continue;
                    }

                    if ($isSitemap) {
                        // The target page is protected
                        if ($parent->protected) {
                            continue;
                        }

                        // The target page is exempt from the sitemap
                        if ('noindex,nofollow' === $parent->robots) {
                            continue;
                        }
                    }

                    // Generate the URL
                    $processed[$category->jumpTo] = $parent;
                }

                $page = $processed[$category->jumpTo];

                // Get the galleries
                $gallery = GalleryModel::findPublishedDefaultByPid((int) $category->id);

                if (null !== $gallery) {
                    while ($gallery->next()) {
                        if ($isSitemap && 'noindex,nofollow' === $gallery->robots) {
                            continue;
                        }

                        $pages[] = $this->urlRenderer->generateGalleryUrl($gallery, true, $page);
                    }
                }
            }
        }

        return $pages;
    }
}
