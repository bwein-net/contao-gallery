<?php

declare(strict_types=1);

/*
 * This file is part of gallery albums for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

namespace Bwein\Gallery\Controller\FrontendModule;

use Bwein\Gallery\Model\GalleryModel;
use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\Environment;
use Contao\Input;
use Contao\Model\Collection;
use Contao\ModuleModel;
use Contao\Pagination;
use Contao\StringUtil;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule('gallerylist', category: 'gallery', template: 'mod_gallerylist')]
class GalleryListFrontendModuleController extends AbstractGalleryFrontendModuleController
{
    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $this->framework->initialize();

        $model->bweinGalleryCategories = $this->sortOutProtected(StringUtil::deserialize($model->bweinGalleryCategories));

        // Return if there are no categories
        if (empty($model->bweinGalleryCategories) || !\is_array($model->bweinGalleryCategories)) {
            return new Response();
        }

        // Show the gallery reader if an gallery has been selected
        if ($model->bweinGalleryReaderModule > 0 && null !== Input::get('auto_item')) {
            return new Response(Controller::getFrontendModule($model->bweinGalleryReaderModule, $template->inColumn));
        }

        $this->addParamsToTemplate($template, $model, $request);

        return $template->getResponse();
    }

    protected function addParamsToTemplate(Template $template, ModuleModel $model, Request $request): void
    {
        $limit = null;
        $offset = (int) $model->skipFirst;

        // Maximum number of galleries
        if ((int) $model->numberOfItems > 0) {
            $limit = (int) $model->numberOfItems;
        }

        // Handle featured gallery
        if ('featured_galleries' === $model->bweinGalleryListFeatured) {
            $featured = true;
        } elseif ('unfeatured_galleries' === $model->bweinGalleryListFeatured) {
            $featured = false;
        } else {
            $featured = null;
        }

        $template->galleries = [];
        $template->empty = $this->translator->trans('MSC.gallery_emptyList', [], 'contao_default');

        // Get the total number of galleries
        $intTotal = $this->countGalleries($model->bweinGalleryCategories, $featured);

        if ($intTotal < 1) {
            return;
        }

        $total = $intTotal - $offset;

        // Split the results
        if ((int) $model->perPage > 0 && (!isset($limit) || $model->numberOfItems > $model->perPage)) {
            // Adjust the overall limit
            if (isset($limit)) {
                $total = min($limit, $total);
            }

            // Get the current page
            $id = 'page_n'.$model->id;
            $page = (int) (Input::get($id) ?? 1);

            // Do not index or cache the page if the page number is outside the range
            if ($page < 1 || $page > max(ceil($total / (int) $model->perPage), 1)) {
                throw new PageNotFoundException('Page not found: '.Environment::get('uri'));
            }

            // Set limit and offset
            $limit = (int) $model->perPage;
            $offset += (max($page, 1) - 1) * (int) $model->perPage;
            $skip = (int) $model->skipFirst;

            // Overall limit
            if ($offset + $limit > $total + $skip) {
                $limit = $total + $skip - $offset;
            }

            // Add the pagination menu
            $pagination = new Pagination($total, (int) $model->perPage, Config::get('maxPaginationLinks'), $id);
            $template->pagination = $pagination->generate("\n  ");
        }

        $galleries = $this->fetchGalleries($model->bweinGalleryCategories, $featured, $limit ?: 0, $offset, $model);

        // Add the galleries
        if (null !== $galleries) {
            $template->galleries = $this->renderer->renderGalleries($model, $galleries);
        }

        $template->categories = $model->bweinGalleryCategories;
    }

    /**
     * Count the total matching galleries.
     */
    protected function countGalleries(array $galleryCategories, bool|null $featured): int
    {
        return GalleryModel::countPublishedByPids($galleryCategories, $featured);
    }

    /**
     * Fetch the matching galleries.
     *
     * @return Collection|GalleryModel|null
     */
    protected function fetchGalleries(array $galleryCategories, bool|null $featured, int $limit, int $offset, ModuleModel $model)
    {
        // Determine sorting
        $t = GalleryModel::getTable();
        $order = '';

        if ('featured_galleries_first' === $model->bweinGalleryListFeatured) {
            $order .= "$t.featured DESC, ";
        }

        match ($model->bweinGalleryListOrder) {
            'order_title_asc' => $order .= "$t.title",
            'order_title_desc' => $order .= "$t.title DESC",
            'order_random' => $order .= 'RAND()',
            'order_date_asc' => $order .= "$t.startdate",
            default => $order .= "$t.startdate DESC",
        };

        return GalleryModel::findPublishedByPids($galleryCategories, $featured, $limit, $offset, ['order' => $order]);
    }
}
