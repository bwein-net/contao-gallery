<?php

declare(strict_types=1);

/*
 * This file is part of gallery albums for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

namespace Bwein\Gallery\Renderer;

use Bwein\Gallery\Model\GalleryModel;
use Contao\Config;
use Contao\Environment;
use Contao\Frontend;
use Contao\PageModel;
use Contao\StringUtil;

/**
 * Class GalleryUrlRenderer.
 */
class GalleryUrlRenderer extends Frontend
{
    /**
     * URL cache array.
     *
     * @var array
     */
    private static $urlCache = [];

    /**
     * Generate a URL and return it as string.
     *
     * @param GalleryModel $gallery
     *
     * @return string
     *
     * @throws \Exception
     */
    public function generateGalleryUrl($gallery, bool $absolute = false, PageModel|null $page = null)
    {
        $cacheKey = 'id_'.$gallery->id.($absolute ? '_absolute' : '');

        // Load the URL from cache
        if (isset(self::$urlCache[$cacheKey])) {
            return self::$urlCache[$cacheKey];
        }

        // Initialize the cache
        self::$urlCache[$cacheKey] = null;

        // Link to the default page
        if (null === self::$urlCache[$cacheKey]) {
            if (null === $page) {
                $page = PageModel::findByPk($gallery->getRelated('pid')->jumpTo);
            }

            if (!$page instanceof PageModel) {
                self::$urlCache[$cacheKey] = StringUtil::ampersand(Environment::get('request'));
            } else {
                $params = (Config::get('useAutoItem') ? '/' : '/items/').($gallery->alias ?: $gallery->id);

                self::$urlCache[$cacheKey] = StringUtil::ampersand($absolute ? $page->getAbsoluteUrl($params) : $page->getFrontendUrl($params));
            }
        }

        return self::$urlCache[$cacheKey];
    }
}
