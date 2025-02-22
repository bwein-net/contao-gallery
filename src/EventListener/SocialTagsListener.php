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
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Input;
use Contao\Model;
use Contao\ModuleModel;
use Contao\StringUtil;
use Hofff\Contao\SocialTags\Data\SocialTagsFactory;
use Hofff\Contao\SocialTags\EventListener\Hook\SocialTagsDataAwareListener;
use Symfony\Component\HttpFoundation\RequestStack;

final class SocialTagsListener extends SocialTagsDataAwareListener
{
    public function __construct(
        RequestStack $requestStack,
        private readonly SocialTagsFactory $factory,
        private readonly ScopeMatcher $scopeMatcher,
        private readonly ContaoFramework $framework,
    ) {
        parent::__construct($requestStack);
    }

    #[AsHook('getContentElement')]
    public function onGetContentElement(Model $model, string $result): string
    {
        if ('module' !== $model->type) {
            return $result;
        }

        $module = ModuleModel::findById($model->module);

        if (!$module) {
            return $result;
        }

        return $this->onGetFrontendModule($module, $result);
    }

    #[AsHook('getFrontendModule')]
    public function onGetFrontendModule(ModuleModel $model, string $result): string
    {
        $request = $this->requestStack->getMainRequest();

        if (!$request || !$this->scopeMatcher->isFrontendRequest($request)) {
            return $result;
        }

        $model = $this->determineModuleModel($model);

        if (!$this->supports($model) || $this->getSocialTagsData()) {
            return $result;
        }

        $galleryModel = $this->getGalleryModel($model);

        if ($galleryModel) {
            $this->setSocialTagsData($this->factory->generate($galleryModel));
        }

        return $result;
    }

    private function supports(ModuleModel $model): bool
    {
        return 'galleryreader' === $model->type;
    }

    private function getGalleryModel(ModuleModel $model): GalleryModel|null
    {
        return GalleryModel::findPublishedByParentAndIdOrAlias(
            $this->framework->getAdapter(Input::class)->get('auto_item'),
            StringUtil::deserialize($model->bweinGalleryCategories, true),
        );
    }

    private function determineModuleModel(ModuleModel $model): ModuleModel
    {
        if (
            ('gallerylist' === $model->type)
            && $model->bweinGalleryReaderModule > 0
            && $this->framework->getAdapter(Input::class)->get('auto_item')
        ) {
            $readerModel = ModuleModel::findById($model->bweinGalleryReaderModule);
            if ($readerModel) {
                return $readerModel;
            }
        }

        return $model;
    }
}
