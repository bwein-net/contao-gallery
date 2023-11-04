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

use Bwein\Gallery\Model\GalleryModel;
use Bwein\Gallery\Renderer\GalleryUrlRenderer;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\StringUtil;

/**
 * @Hook("replaceInsertTags")
 */
class InsertTagsListener implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    private const SUPPORTED_TAGS = [
        'gallery',
        'gallery_open',
        'gallery_url',
        'gallery_title',
    ];

    private GalleryUrlRenderer $urlRenderer;

    public function __invoke(string $insertTag, bool $useCache, string $cachedValue, array $flags, array $tags, array $cache, int $_rit, int $_cnt)
    {
        $elements = explode('::', $insertTag);
        $key = strtolower($elements[0]);

        if (\in_array($key, self::SUPPORTED_TAGS, true)) {
            return $this->replaceGalleryInsertTags($key, $elements[1], $flags);
        }

        return false;
    }

    /**
     * @internal
     *
     * @required
     */
    public function setUrlRenderer(GalleryUrlRenderer $urlRenderer): void
    {
        $this->urlRenderer = $urlRenderer;
    }

    private function replaceGalleryInsertTags(string $insertTag, string $idOrAlias, array $flags): string
    {
        $this->framework->initialize();

        /** @var GalleryModel $adapter */
        $galleryModel = $this->framework->getAdapter(GalleryModel::class);
        $gallery = $galleryModel->findByIdOrAlias($idOrAlias);

        if (null === $gallery) {
            return '';
        }

        switch ($insertTag) {
            case 'gallery':
                return sprintf(
                    '<a href="%s" title="%s">%s</a>',
                    $this->urlRenderer->generateGalleryUrl($gallery, \in_array('absolute', $flags, true)),
                    StringUtil::specialchars($gallery->title),
                    $gallery->title,
                );

            case 'gallery_open':
                return sprintf(
                    '<a href="%s" title="%s">',
                    $this->urlRenderer->generateGalleryUrl($gallery, \in_array('absolute', $flags, true)),
                    StringUtil::specialchars($gallery->title),
                );

            case 'gallery_url':
                return $this->urlRenderer->generateGalleryUrl($gallery, \in_array('absolute', $flags, true));

            case 'gallery_title':
                return StringUtil::specialchars($gallery->title);
        }

        return '';
    }
}
