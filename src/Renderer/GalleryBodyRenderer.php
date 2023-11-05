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
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Image\Studio\FigureBuilder;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\Environment;
use Contao\Input;
use Contao\ModuleModel;
use Contao\Pagination;
use Contao\Template;

class GalleryBodyRenderer
{
    protected Studio $studio;

    private int $offset = 0;

    private int $total = 0;

    private int $limit = 0;

    public function __construct(Studio $studio)
    {
        $this->studio = $studio;
    }

    public function renderGalleryBody(Template $template, ModuleModel $model, GalleryModel $gallery): void
    {
        $imageFiles = $template->imageFiles;

        // Limit the total number of images
        if ($model->bweinGalleryNumberOfItems > 0) {
            $imageFiles = \array_slice($imageFiles, 0, $model->bweinGalleryNumberOfItems);
        }

        $this->offset = 0;
        $this->total = \count($imageFiles);
        $this->limit = $this->total;
        $this->addPagination($template, $model, $gallery);

        $figureBuilder = $this->studio->createFigureBuilder()
            ->setSize($model->imgSize)
            ->setLightboxGroupIdentifier('lb'.$gallery->id)
            ->enableLightbox((bool) $model->fullsize)
        ;

        $this->addGalleryBody($template, $model, $imageFiles, $figureBuilder);

        // Add figures before and after the current page
        $this->addFiguresBefore($template, $model, $imageFiles, $figureBuilder);
        $this->addFiguresAfter($template, $model, $imageFiles, $figureBuilder);
    }

    protected function addGalleryBody(Template $template, ModuleModel $model, array $imageFiles, FigureBuilder $figureBuilder): void
    {
        $rowcount = 0;
        $colwidth = floor(100 / $model->bweinGalleryPerRow);
        $body = [];
        $figures = [];

        // Rows
        for ($i = $this->offset; $i < $this->limit; $i += $model->bweinGalleryPerRow) {
            $class_tr = '';

            if (0 === $rowcount) {
                $class_tr .= ' row_first';
            }

            if ($i + $model->bweinGalleryPerRow >= $this->limit) {
                $class_tr .= ' row_last';
            }

            $class_eo = 0 === $rowcount % 2 ? ' even' : ' odd';

            // Columns
            for ($j = 0; $j < $model->bweinGalleryPerRow; ++$j) {
                $class_td = '';

                if (0 === $j) {
                    $class_td .= ' col_first';
                }

                if ($j === $model->bweinGalleryPerRow - 1) {
                    $class_td .= ' col_last';
                }

                // Image / empty cell
                if ($j + $i < $this->limit && null !== ($image = $imageFiles[$i + $j] ?? null)) {
                    $figure = $figureBuilder
                        ->fromId($image['id'])
                        ->build()
                    ;

                    $cellData = $figure->getLegacyTemplateData();
                    $cellData['figure'] = $figure;
                    $figures[] = $figure;
                } else {
                    $cellData = ['addImage' => false];
                }

                // Add column width and class
                $cellData['colWidth'] = $colwidth.'%';
                $cellData['class'] = 'col_'.$j.$class_td;

                $body['row_'.$rowcount.$class_tr.$class_eo][$j] = (object) $cellData;
            }

            ++$rowcount;
        }

        $template->body = $body;
        $template->perRow = $model->bweinGalleryPerRow;
        $template->figures = $figures;
    }

    protected function addFiguresBefore(Template $template, ModuleModel $model, array $imageFiles, FigureBuilder $figureBuilder): void
    {
        $figures = [];

        if (!(bool) $model->fullsize) {
            $template->figuresBefore = $figures;
        }

        for ($i = 0; $i < $this->offset; ++$i) {
            if (null !== ($image = $imageFiles[$i] ?? null)) {
                $figure = $figureBuilder->fromId($image['id'])->build();
                $figures[] = $figure;
            }
        }

        $template->figuresBefore = $figures;
    }

    protected function addFiguresAfter(Template $template, ModuleModel $model, array $imageFiles, FigureBuilder $figureBuilder): void
    {
        $figures = [];

        if (!(bool) $model->fullsize) {
            $template->figuresAfter = $figures;
        }

        for ($i = $this->limit; $i < $this->total; ++$i) {
            if (null !== ($image = $imageFiles[$i] ?? null)) {
                $figure = $figureBuilder->fromId($image['id'])->build();
                $figures[] = $figure;
            }
        }

        $template->figuresAfter = $figures;
    }

    protected function addPagination(Template $template, ModuleModel $model, GalleryModel $gallery): void
    {
        // Paginate the result if not randomly sorted
        if ($model->bweinGalleryPerPage > 0 && 'random' !== $gallery->sortBy) {
            // Get the current page
            $id = 'page_g'.$gallery->id;
            $page = (int) (Input::get($id) ?? 1);

            // Do not index or cache the page if the page number is outside the range
            if ($page < 1 || $page > max(ceil($this->total / $model->bweinGalleryPerPage), 1)) {
                throw new PageNotFoundException('Page not found: '.Environment::get('uri'));
            }

            // Set limit and offset
            $this->offset = ($page - 1) * (int) $model->bweinGalleryPerPage;
            $this->limit = min($model->bweinGalleryPerPage + $this->offset, $this->total);

            $objPagination = new Pagination($this->total, $model->bweinGalleryPerPage, Config::get('maxPaginationLinks'), $id);
            $template->pagination = $objPagination->generate("\n  ");
        }
    }
}
