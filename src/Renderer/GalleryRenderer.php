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
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\Date;
use Contao\File;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\Model\Collection;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Template;
use Contao\UserModel;
use FOS\HttpCache\ResponseTagger;
use MadeYourDay\RockSolidFrontendHelper\FrontendHooks;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class GalleryRenderer
{
    protected bool $isUnsearchable = false;

    public function __construct(
        protected readonly TranslatorInterface $translator,
        protected readonly ContaoFramework $framework,
        protected readonly ParameterBagInterface $parameterBag,
        protected readonly GalleryBodyRenderer $bodyRenderer,
        protected readonly GalleryPreviewRenderer $previewRenderer,
        protected readonly ContentUrlGenerator $urlGenerator,
        protected readonly ResponseTagger|null $responseTagger = null,
    ) {
    }

    /**
     * Parse an gallery and return it as string.
     *
     * @throws \Exception
     */
    public function renderGallery(ModuleModel $model, GalleryModel $gallery, int $count = 0): string
    {
        $this->framework->initialize();
        $template = new FrontendTemplate($model->bweinGalleryTemplate ?: 'album_full');
        $this->addGalleryToTemplate($template, $gallery);
        $this->addParamsToTemplate($template, $model, $gallery, $count);

        $this->bodyRenderer->renderGalleryBody($template, $model, $gallery);
        $this->previewRenderer->renderPreview($template, $model, $gallery);

        return $template->parse();
    }

    /**
     * Parse one or more galleries and return them as array.
     *
     * @param array<GalleryModel> $galleries
     */
    public function renderGalleries(ModuleModel $model, Collection $galleries): array
    {
        if (empty($galleries)) {
            return [];
        }

        $count = 0;
        $return = [];

        foreach ($galleries as $gallery) {
            $album = $this->renderGallery(
                $model,
                $gallery,
                $count,
            );

            if (null === $album) {
                continue;
            }

            $return[] = $album;
            ++$count;
        }

        return $return;
    }

    public function isUnsearchable()
    {
        return $this->isUnsearchable;
    }

    protected function addGalleryToTemplate(Template $template, GalleryModel $gallery): void
    {
        $template->setData($gallery->row());
        $template->galleryModel = $gallery;

        $template->fileIds = (array) StringUtil::deserialize($gallery->images, true);
        $imageFiles = $this->getAllImageFiles($template->fileIds);
        $imageFiles = $this->sortImageFiles($imageFiles, $gallery);
        $template->imageFiles = $imageFiles;
        $template->total = \count($imageFiles);

        // Tag the response
        if (null !== $this->responseTagger) {
            $this->responseTagger->addTags(['contao.db.tl_bwein_gallery.'.$gallery->id]);
            $this->responseTagger->addTags(['contao.db.tl_bwein_gallery_category.'.$gallery->pid]);
        }
    }

    protected function addParamsToTemplate(Template $template, ModuleModel $model, GalleryModel $gallery, int $count = 0): void
    {
        /** @var PageModel $objPage */
        global $objPage;

        $template->class = implode(' ', $this->getCssClasses($template, $model, $gallery));

        $template->linkTitle = $this->generateLink($gallery->title, $gallery);
        $template->more = $this->generateLink($this->translator->trans('MSC.more', [], 'contao_default'), $gallery, true);
        $template->link = $this->urlGenerator->generate($gallery, []);
        $template->category = $gallery->getRelated('pid');
        $template->startDateParsed = Date::parse($objPage->dateFormat, $gallery->startDate);
        $template->endDateParsed = Date::parse($objPage->dateFormat, $gallery->endDate);
        $template->description = !empty($gallery->description) ? StringUtil::encodeEmail($template->description) : '';
        $template->count = $count;

        /** @var UserModel $author */
        $template->author = '';

        if (($author = $gallery->getRelated('author')) instanceof UserModel) {
            $template->author = $this->translator->trans('MSC.by', [], 'contao_default').' '.$author->name.'';
        }
    }

    protected function getCssClasses(Template $template, ModuleModel $model, GalleryModel $gallery): array
    {
        $classes = [];

        $gridClasses = StringUtil::deserialize($model->grid_columns, true);

        foreach ($gridClasses as $gridClass) {
            $classes[] = $gridClass;
        }

        if (!empty($gallery->cssClass)) {
            $classes[] = $gallery->cssClass;
        }

        if ($gallery->featured) {
            $classes[] = 'featured';
        }

        if (class_exists(FrontendHooks::class) && ($permissions = FrontendHooks::checkLogin()) && \in_array('beModules', $permissions, true)) {
            $classes[] = $gallery::FRONTEND_HELPER_CSS_CLASS_PREFIX.$gallery->id;
        }

        return $classes;
    }

    protected function getAllImageFiles(array $fileIds): array
    {
        $imageFiles = [];
        $projectDir = $this->parameterBag->get('kernel.project_dir');

        // Get the file entries from the database
        $fileModels = FilesModel::findMultipleByUuids($fileIds);

        if (null === $fileModels) {
            return $imageFiles;
        }

        // Get all images
        foreach ($fileModels as $fileModel) {
            // Continue if the files has been processed or does not exist
            if (isset($imageFiles[$fileModel->path]) || !file_exists($projectDir.'/'.$fileModel->path)) {
                continue;
            }

            // Single files
            if ('file' === $fileModel->type) {
                $file = new File($fileModel->path);

                if (!$file->isImage) {
                    continue;
                }

                $row = $fileModel->row();
                $row['mtime'] = $file->mtime;

                // Add the image
                $imageFiles[$fileModel->path] = $row;
            }

            // Folders
            else {
                $subfileModels = FilesModel::findByPid($fileModel->uuid, ['order' => 'name']);

                if (null === $subfileModels) {
                    continue;
                }

                foreach ($subfileModels as $subfileModel) {
                    // Skip subfolders
                    if ('folder' === $subfileModel->type) {
                        continue;
                    }

                    $file = new File($subfileModel->path);

                    if (!$file->isImage) {
                        continue;
                    }

                    $row = $subfileModel->row();
                    $row['mtime'] = $file->mtime;

                    // Add the image
                    $imageFiles[$subfileModel->path] = $row;
                }
            }
        }

        return $imageFiles;
    }

    protected function sortImageFiles(array $imageFiles, GalleryModel $gallery): array
    {
        switch ($gallery->sortBy) {
            default:
            case 'name_asc':
                uksort($imageFiles, static fn ($a, $b): int => strnatcasecmp(basename($a), basename($b)));
                break;

            case 'name_desc':
                uksort($imageFiles, static fn ($a, $b): int => -strnatcasecmp(basename($a), basename($b)));
                break;

            case 'date_asc':
                uasort($imageFiles, static fn (array $a, array $b) => $a['mtime'] <=> $b['mtime']);
                break;

            case 'date_desc':
                uasort($imageFiles, static fn (array $a, array $b) => $b['mtime'] <=> $a['mtime']);
                break;

            case 'custom':
                break;

            case 'random':
                shuffle($imageFiles);
                $this->isUnsearchable = true;
                break;
        }

        $imageFiles = array_values($imageFiles);

        return $imageFiles;
    }

    /**
     * Generate a link and return it as string.
     *
     * @throws \Exception
     */
    protected function generateLink(string $link, GalleryModel $gallery, bool $isReadMore = false): string
    {
        return \sprintf(
            '<a href="%s" title="%s">%s%s</a>',
            $this->urlGenerator->generate($gallery, []),
            StringUtil::specialchars(\sprintf($this->translator->trans('MSC.readMore', [], 'contao_default'), $gallery->title), true),
            $link,
            $isReadMore ? '<span class="invisible"> '.$gallery->title.'</span>' : '',
        );
    }
}
