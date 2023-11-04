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

use Bwein\Gallery\Model\GalleryCategoryModel;
use Bwein\Gallery\Renderer\GalleryRenderer;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\FrontendUser;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AbstractGalleryFrontendModuleController.
 */
abstract class AbstractGalleryFrontendModuleController extends AbstractFrontendModuleController
{
    protected TranslatorInterface $translator;

    protected TokenChecker $tokenChecker;

    protected ContaoFramework $framework;

    protected GalleryRenderer $renderer;

    public function __construct(TranslatorInterface $translator, TokenChecker $tokenChecker, ContaoFramework $framework, GalleryRenderer $renderer)
    {
        $this->translator = $translator;
        $this->tokenChecker = $tokenChecker;
        $this->framework = $framework;
        $this->renderer = $renderer;
    }

    abstract protected function addParamsToTemplate(Template $template, ModuleModel $model, Request $request): void;

    /**
     * Sort out protected categories.
     */
    protected function sortOutProtected(array $categories): array
    {
        if (empty($categories)) {
            return $categories;
        }

        /** @var FrontendUser $frontendUser */
        $frontendUser = $this->framework->getAdapter(FrontendUser::class)->getInstance();

        $category = GalleryCategoryModel::findMultipleByIds($categories);
        $categories = [];

        if (null !== $category) {
            while ($category->next()) {
                if ($category->protected) {
                    if (!$this->tokenChecker->hasFrontendUser() || !\is_array($frontendUser->groups)) {
                        continue;
                    }

                    $groups = StringUtil::deserialize($category->groups);

                    if (
                        empty($groups) || !\is_array($groups) || !\count(
                            array_intersect($groups, $frontendUser->groups),
                        )
                    ) {
                        continue;
                    }
                }

                $categories[] = $category->id;
            }
        }

        return $categories;
    }
}
