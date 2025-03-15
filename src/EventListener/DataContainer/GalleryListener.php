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
use Bwein\Gallery\Model\GalleryModel;
use Contao\Config;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Slug\Slug;
use Contao\Database;
use Contao\DataContainer;
use Contao\Date;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class GalleryListener.
 */
class GalleryListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
        private readonly ContaoFramework $framework,
        private readonly Slug $slug,
        private readonly ContentUrlGenerator $urlGenerator,
    ) {
    }

    #[AsCallback(table: 'tl_bwein_gallery', target: 'config.onload')]
    public function generatePalette(DataContainer|null $dc = null): void
    {
        if (null === $dc || !$dc->id || 'edit' !== $this->requestStack->getCurrentRequest()->query->get('act')) {
            return;
        }

        $gallery = GalleryModel::findById($dc->id);

        if ('select_preview_image' === $gallery->previewImageType) {
            PaletteManipulator::create()
                ->addField(
                    ['previewImage'],
                    'source_legend',
                    PaletteManipulator::POSITION_APPEND,
                )
                ->applyToPalette('default', 'tl_bwein_gallery')
            ;
        }
    }

    #[AsCallback(table: 'tl_bwein_gallery', target: 'fields.alias.save')]
    public function generateAlias($value, DataContainer $dc): string
    {
        $aliasExists = static fn (string $alias): bool => Database::getInstance()->prepare('SELECT id FROM tl_bwein_gallery WHERE alias=? AND id!=?')->execute($alias, $dc->id)->numRows > 0;

        // Generate alias if there is none
        if ('' === (string) $value) {
            $value = $this->slug->generate($dc->activeRecord->title ?? '', GalleryCategoryModel::findById($dc->activeRecord->pid)->jumpTo, $aliasExists);
        } elseif ($aliasExists($value)) {
            throw new \Exception(\sprintf($this->translator->trans('ERR.aliasExists', [], 'contao_default'), $value));
        }

        return (string) $value;
    }

    #[AsCallback(table: 'tl_bwein_gallery', target: 'fields.startDate.load')]
    #[AsCallback(table: 'tl_bwein_gallery', target: 'fields.endDate.load')]
    public function loadDate($value, DataContainer $dc): int|null
    {
        if (null === $value) {
            return $value;
        }

        return strtotime(date('Y-m-d', (int) $value).' 00:00:00');
    }

    /**
     * Adjust start end end time of the gallery.
     */
    #[AsCallback(table: 'tl_bwein_gallery', target: 'config.onsubmit')]
    public function adjustTime(DataContainer $dataContainer): void
    {
        // Return if there is no active record (override all) or no start date has been
        // set yet
        if (!$dataContainer->activeRecord || !$dataContainer->activeRecord->startDate) {
            return;
        }

        $where['endDate'] = null;

        // Set end date
        if ($dataContainer->activeRecord->endDate) {
            if ($dataContainer->activeRecord->endDate > $dataContainer->activeRecord->startDate) {
                $where['endDate'] = $dataContainer->activeRecord->endDate;
            } else {
                $where['endDate'] = $dataContainer->activeRecord->startDate;
            }
        }

        Database::getInstance()->prepare('UPDATE tl_bwein_gallery %s WHERE id=?')->set($where)->execute($dataContainer->id);
    }

    #[AsCallback(table: 'tl_bwein_gallery', target: 'fields.serpPreview.eval.url')]
    public function getSerpUrl(GalleryModel $model): string
    {
        return $this->urlGenerator->generate($model, [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    #[AsCallback(table: 'tl_bwein_gallery', target: 'list.sorting.child_record')]
    public function listgalleryGalleries(array $row): string
    {
        return '<div class="tl_content_left">'.$row['title'].' <span style="color:#999;padding-left:3px">['.Date::parse(Config::get('dateFormat'), $row['startDate']).']</span></div>';
    }

    #[AsCallback(table: 'tl_bwein_gallery', target: 'config.oninvalidate_cache_tags')]
    public function addSitemapCacheInvalidationTag(DataContainer $dataContainer, array $tags)
    {
        $category = GalleryCategoryModel::findById($dataContainer->activeRecord->pid);

        if (null === $category) {
            return $tags;
        }

        $pageModel = PageModel::findWithDetails($category->jumpTo);

        if (null === $pageModel) {
            return $tags;
        }

        return array_merge($tags, ['contao.sitemap.'.$pageModel->rootId]);
    }
}
