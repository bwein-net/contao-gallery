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
use Contao\CoreBundle\DependencyInjection\Attribute\AsInsertTag;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagResult;
use Contao\CoreBundle\InsertTag\OutputType;
use Contao\CoreBundle\InsertTag\ResolvedInsertTag;
use Contao\CoreBundle\InsertTag\Resolver\InsertTagResolverNestedResolvedInterface;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\StringUtil;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsInsertTag('gallery')]
#[AsInsertTag('gallery_open')]
#[AsInsertTag('gallery_url')]
#[AsInsertTag('gallery_title')]
class InsertTagsListener implements InsertTagResolverNestedResolvedInterface
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly ContentUrlGenerator $urlGenerator,
    ) {
    }

    public function __invoke(ResolvedInsertTag $insertTag): InsertTagResult
    {
        $this->framework->initialize();

        $arguments = \array_slice($insertTag->getParameters()->all(), 1);
        /** @var GalleryModel $adapter */
        $adapter = $this->framework->getAdapter(GalleryModel::class);

        if (!$model = $adapter->findByIdOrAlias($insertTag->getParameters()->get(0))) {
            return new InsertTagResult('');
        }

        return match ($insertTag->getName()) {
            'gallery' => new InsertTagResult(
                \sprintf(
                    '<a href="%s" title="%s"%s>%s</a>',
                    StringUtil::specialcharsAttribute($this->generateGalleryUrl($model, $arguments)),
                    StringUtil::specialcharsAttribute($model->title),
                    \in_array('blank', $arguments, true) ? ' target="_blank" rel="noreferrer noopener"' : '',
                    $model->title,
                ),
                OutputType::html,
            ),
            'gallery_open' => new InsertTagResult(
                \sprintf(
                    '<a href="%s" title="%s"%s>',
                    StringUtil::specialcharsAttribute($this->generateGalleryUrl($model, $arguments)),
                    StringUtil::specialcharsAttribute($model->title),
                    \in_array('blank', $arguments, true) ? ' target="_blank" rel="noreferrer noopener"' : '',
                ),
                OutputType::html,
            ),
            'gallery_url' => new InsertTagResult($this->generateGalleryUrl($model, $arguments), OutputType::url),
            'gallery_title' => new InsertTagResult($model->title),
            default => new InsertTagResult(''),
        };
    }

    private function generateGalleryUrl(GalleryModel $model, array $arguments): string
    {
        try {
            return $this->urlGenerator->generate($model, [], \in_array('absolute', $arguments, true) ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH);
        } catch (ForwardPageNotFoundException) {
            return '';
        }
    }
}
