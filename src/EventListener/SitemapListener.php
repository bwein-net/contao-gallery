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

use Bwein\Gallery\Model\GalleryCategoryModel;
use Bwein\Gallery\Model\GalleryModel;
use Contao\CoreBundle\Event\SitemapEvent;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\Database;
use Contao\PageModel;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @internal
 */
#[AsEventListener]
class SitemapListener
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly Security $security,
        private readonly ContentUrlGenerator $urlGenerator,
    ) {
    }

    public function __invoke(SitemapEvent $event): void
    {
        $arrRoot = $this->framework->createInstance(Database::class)->getChildRecords($event->getRootPageIds(), 'tl_page');

        // Early return here in the unlikely case that there are no pages
        if (empty($arrRoot)) {
            return;
        }

        $arrPages = [];
        $time = time();

        if ($isMember = $this->security->isGranted('ROLE_MEMBER')) {
            // Get all gallery categories
            $objCategories = $this->framework->getAdapter(GalleryCategoryModel::class)->findAll();
        } else {
            // Get all unprotected gallery categories
            $objCategories = $this->framework->getAdapter(GalleryCategoryModel::class)->findByProtected('');
        }

        if (null === $objCategories) {
            return;
        }

        // Walk through each gallery category
        foreach ($objCategories as $objCategory) {
            // Skip gallery categories without target page
            if (!$objCategory->jumpTo) {
                continue;
            }

            // Skip gallery categories outside the root nodes
            if (!\in_array($objCategory->jumpTo, $arrRoot, true)) {
                continue;
            }

            if ($isMember && $objCategory->protected && !$this->security->isGranted(ContaoCorePermissions::MEMBER_IN_GROUPS, $objCategory->groups)) {
                continue;
            }

            $objParent = $this->framework->getAdapter(PageModel::class)->findWithDetails($objCategory->jumpTo);

            // The target page does not exist
            if (!$objParent) {
                continue;
            }

            // The target page has not been published
            if (!$objParent->published || ($objParent->start && $objParent->start > $time) || ($objParent->stop && $objParent->stop <= $time)) {
                continue;
            }

            // The target page is protected
            if ($objParent->protected && !$this->security->isGranted(ContaoCorePermissions::MEMBER_IN_GROUPS, $objParent->groups)) {
                continue;
            }

            // The target page is exempt from the sitemap
            if ('noindex,nofollow' === $objParent->robots) {
                continue;
            }

            // Get the galleries
            $objGalleries = $this->framework->getAdapter(GalleryModel::class)->findPublishedDefaultByPid($objCategory->id);

            if (null === $objGalleries) {
                continue;
            }

            foreach ($objGalleries as $objGallery) {
                if ('noindex,nofollow' === $objGallery->robots) {
                    continue;
                }

                try {
                    $arrPages[] = $this->urlGenerator->generate($objGallery, [], UrlGeneratorInterface::ABSOLUTE_URL);
                } catch (ExceptionInterface) {
                }
            }
        }

        foreach ($arrPages as $strUrl) {
            $event->addUrlToDefaultUrlSet($strUrl);
        }
    }
}
