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
use Contao\CoreBundle\Exception\InternalServerErrorException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Routing\ResponseContext\HtmlHeadBag\HtmlHeadBag;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\CoreBundle\String\HtmlDecoder;
use Contao\Environment;
use Contao\Input;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule('galleryreader', category: 'gallery', template: 'mod_galleryreader')]
class GalleryReaderFrontendModuleController extends AbstractGalleryFrontendModuleController
{
    protected ResponseContextAccessor $responseContextAccessor;

    protected HtmlDecoder $htmlDecoder;

    protected GalleryModel|null $gallery = null;

    /**
     * @required
     */
    public function setResponseContextAccessor(ResponseContextAccessor $responseContextAccessor): void
    {
        $this->responseContextAccessor = $responseContextAccessor;
    }

    /**
     * @required
     */
    public function setHtmlDecoder(HtmlDecoder $htmlDecoder): void
    {
        $this->htmlDecoder = $htmlDecoder;
    }

    /**
     * @throws \Exception
     */
    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $this->framework->initialize();

        // Return an empty string if "auto_item" is not set (to combine list and reader
        // on same page)
        if (null === Input::get('auto_item')) {
            return new Response();
        }

        $model->bweinGalleryCategories = $this->sortOutProtected(StringUtil::deserialize($model->bweinGalleryCategories));

        if (empty($model->bweinGalleryCategories) || !\is_array($model->bweinGalleryCategories)) {
            throw new InternalServerErrorException('The gallery reader ID '.$model->id.' has no categories specified.', $model->id);
        }

        $this->addParamsToTemplate($template, $model, $request);

        return $template->getResponse();
    }

    /**
     * @throws \Exception
     */
    protected function addParamsToTemplate(Template $template, ModuleModel $model, Request $request): void
    {
        $template->galleries = '';

        if ($model->overviewPage) {
            $template->referer = PageModel::findById($model->overviewPage)->getFrontendUrl();
            $template->back = $model->customLabel ?: $this->translator->trans('MSC.galleryOverview', [], 'contao_default');
        }

        // Get the gallery
        $this->gallery = GalleryModel::findPublishedByParentAndIdOrAlias(Input::get('auto_item'), $model->bweinGalleryCategories);

        // The gallery does not exist
        if (null === $this->gallery) {
            throw new PageNotFoundException('Page not found: '.Environment::get('uri'));
        }
        $template->galleries = $this->renderer->renderGallery($model, $this->gallery);
        $template->isUnsearchable = $this->renderer->isUnsearchable();

        $this->overwriteMetaData($this->gallery);
    }

    protected function overwriteMetaData(GalleryModel $gallery): void
    {
        $responseContext = $this->responseContextAccessor->getResponseContext();

        if ($responseContext && $responseContext->has(HtmlHeadBag::class)) {
            /** @var HtmlHeadBag $htmlHeadBag */
            $htmlHeadBag = $responseContext->get(HtmlHeadBag::class);

            if ($gallery->metaTitle) {
                $htmlHeadBag->setTitle($gallery->metaTitle);
            } elseif ($gallery->title) {
                $htmlHeadBag->setTitle($this->htmlDecoder->inputEncodedToPlainText($gallery->title));
            }

            if ($gallery->metaDescription) {
                $htmlHeadBag->setMetaDescription($this->htmlDecoder->inputEncodedToPlainText($gallery->metaDescription));
            } elseif ($gallery->description) {
                $htmlHeadBag->setMetaDescription($this->htmlDecoder->htmlToPlainText($gallery->description));
            }

            if ($gallery->robots) {
                $htmlHeadBag->setMetaRobots($gallery->robots);
            }
        }
    }
}
