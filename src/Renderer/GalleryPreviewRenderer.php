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
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\File;
use Contao\FilesModel;
use Contao\ModuleModel;
use Contao\Template;

class GalleryPreviewRenderer
{
    public function __construct(protected readonly Studio $studio)
    {
    }

    public function renderPreview(Template $template, ModuleModel $model, GalleryModel $gallery): void
    {
        $imageFiles = $template->imageFiles;
        $template->previewFileId = $this->getPreviewImageIdByModuleType($model, $gallery, $imageFiles);

        if (null !== $template->previewFileId) {
            $figureBuilder = $this->studio->createFigureBuilder()
                ->setSize($model->imgSize)
                ->setLightboxGroupIdentifier('lb'.$gallery->id)
                ->enableLightbox((bool) $model->fullsize)
            ;

            $template->previewFigure = $figureBuilder
                ->fromId($template->previewFileId)
                ->build()
            ;
        } else {
            $template->previewFigure = null;
        }
    }

    protected function getPreviewImageIdByModuleType(ModuleModel $model, GalleryModel $gallery, array $imageFiles): int|null
    {
        $id = null;

        switch ($model->bweinGalleryPreviewImage) {
            case 'use_album_options':
                $id = $this->getPreviewImageIdByGalleryType($gallery, $imageFiles);
                break;

            case 'no_preview_images':
                $id = null;
                break;

            case 'random_images':
                $id = $this->getRandomPreviewImageId($imageFiles);
                break;

            case 'first_image':
                $id = $this->getFirstPreviewImageId($imageFiles);
                break;

            case 'random_images_at_no_preview_images':
                if (empty($gallery->previewImageType) || 'no_preview_image' === $gallery->previewImageType) {
                    $id = $this->getRandomPreviewImageId($imageFiles);
                } else {
                    $id = $this->getPreviewImageIdByGalleryType($gallery, $imageFiles);
                }
                break;

            case 'first_image_at_no_preview_images':
                if (empty($gallery->previewImageTyp) || 'no_preview_image' === $gallery->previewImageType) {
                    $id = $this->getFirstPreviewImageId($imageFiles);
                } else {
                    $id = $this->getPreviewImageIdByGalleryType($gallery, $imageFiles);
                }
                break;
        }

        return $id;
    }

    protected function getPreviewImageIdByGalleryType(GalleryModel $gallery, array $imageFiles): int|null
    {
        $id = null;

        if (empty($gallery->previewImageType)) {
            return null;
        }

        switch ($gallery->previewImageType) {
            case 'no_preview_image':
                $id = null;
                break;

            case 'random_preview_image':
                $id = $this->getRandomPreviewImageId($imageFiles);
                break;

            case 'first_preview_image':
                $id = $this->getFirstPreviewImageId($imageFiles);
                break;

            case 'select_preview_image':
                $id = $this->getPreviewImageIdByFileUuid($gallery->previewImage);
                break;
        }

        return $id;
    }

    protected function getRandomPreviewImageId(array $imageFiles): int|null
    {
        if (empty($imageFiles)) {
            return null;
        }

        return $imageFiles[random_int(0, \count($imageFiles) - 1)]['id'];
    }

    protected function getFirstPreviewImageId(array $imageFiles): int|null
    {
        if (empty($imageFiles)) {
            return null;
        }

        return current($imageFiles)['id'];
    }

    protected function getPreviewImageIdByFileUuid(string|null $uuid): int|null
    {
        if (null === $uuid) {
            return null;
        }

        $fileModel = FilesModel::findByUuid($uuid);

        if (null === $fileModel) {
            return null;
        }

        $file = new File($fileModel->path);

        if (!$file->isImage) {
            return null;
        }

        return $fileModel->id;
    }
}
