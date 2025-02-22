<?php

declare(strict_types=1);

/*
 * This file is part of gallery albums for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

namespace Bwein\Gallery\Data\Extractor;

use Bwein\Gallery\Model\GalleryModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\FilesModel;
use Contao\PageModel;
use Contao\StringUtil;
use Hofff\Contao\SocialTags\Data\Extractor\AbstractExtractor;
use Hofff\Contao\SocialTags\Data\OpenGraph\OpenGraphExtractor;
use Hofff\Contao\SocialTags\Data\OpenGraph\OpenGraphExtractorPlugin;
use Hofff\Contao\SocialTags\Data\OpenGraph\OpenGraphType;
use Hofff\Contao\SocialTags\Data\TwitterCards\TwitterCardsExtractor;
use Hofff\Contao\SocialTags\Data\TwitterCards\TwitterCardsExtractorPlugin;
use Hofff\Contao\SocialTags\Util\TypeUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @implements OpenGraphExtractor<GalleryModel, PageModel>
 * @implements TwitterCardsExtractor<GalleryModel, PageModel>
 */
final class GalleryDataExtractor extends AbstractExtractor implements OpenGraphExtractor, TwitterCardsExtractor
{
    /** @use OpenGraphExtractorPlugin<Gallery, PageModel> */
    use OpenGraphExtractorPlugin;

    /** @use TwitterCardsExtractorPlugin<Gallery, PageModel> */
    use TwitterCardsExtractorPlugin;

    public function __construct(
        protected ContaoFramework $framework,
        protected RequestStack $requestStack,
        protected ResponseContextAccessor $responseContextAccessor,
        protected InsertTagParser $insertTagParser,
        protected string $projectDir,
        private readonly ContentUrlGenerator $urlGenerator,
    ) {
    }

    public function supports(object $reference, object|null $fallback = null): bool
    {
        if (!$reference instanceof GalleryModel) {
            return false;
        }

        return $fallback instanceof PageModel;
    }

    public function supportedDataContainers(): array
    {
        return ['tl_bwein_gallery'];
    }

    /**
     * Returns the meta description if present, otherwise the shortened description.
     */
    protected function getContentDescription(object $reference): string|null
    {
        if (TypeUtil::isStringWithContent($reference->metaDescription)) {
            return $this->replaceInsertTags(trim(str_replace(["\n", "\r"], [' ', ''], $reference->metaDescription)));
        }

        if (!TypeUtil::isStringWithContent($reference->description)) {
            return null;
        }

        // Generate the description the same way as the gallery reader does
        $description = $this->replaceInsertTags($reference->description);
        $description = strip_tags($description);
        $description = str_replace("\n", ' ', $description);

        return StringUtil::substr($description, 320);
    }

    protected function getContentTitle(object $reference): string
    {
        return (string) ($reference->metaTitle ?: $reference->title);
    }

    protected function defaultOpenGraphType(): OpenGraphType
    {
        return new OpenGraphType('article');
    }

    protected function getContentUrl(object $reference): string
    {
        return $this->urlGenerator->generate(
            $reference,
            [],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }

    protected function getImage(string $key, object $gallery, object|null $fallback = null): FilesModel|null
    {
        $images = (array) StringUtil::deserialize($gallery->images, true);
        if ('select_preview_image' === $gallery->previewImageType && $gallery->previewImage) {
            $image = $gallery->previewImage;
        } elseif (!empty($images)) {
            switch ($gallery->previewImageType) {
                case 'no_preview_image':
                    return null;
                case 'first_preview_image':
                    $image = current($images);
                    break;
                default:
                    $image = $images[random_int(0, \count($images) - 1)];
            }
        } elseif ($fallback && $fallback->{$key}) {
            $image = $fallback->{$key};
        } else {
            return null;
        }

        return $this->getFileModel($image);
    }
}
