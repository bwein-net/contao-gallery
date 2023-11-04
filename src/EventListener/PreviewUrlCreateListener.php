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
use Contao\CoreBundle\Event\PreviewUrlCreateEvent;
use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\ServiceAnnotationBundle\Annotation\ServiceTag;

/**
 * @internal
 *
 * @ServiceTag("kernel.event_listener")
 */
class PreviewUrlCreateListener
{
    private RequestStack $requestStack;

    private ContaoFramework $framework;

    public function __construct(RequestStack $requestStack, ContaoFramework $framework)
    {
        $this->requestStack = $requestStack;
        $this->framework = $framework;
    }

    /**
     * Adds the gallery ID to the front end preview URL.
     *
     * @throws \RuntimeException
     */
    public function __invoke(PreviewUrlCreateEvent $event): void
    {
        if (!$this->framework->isInitialized() || 'gallery' !== $event->getKey()) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            throw new \RuntimeException('The request stack did not contain a request');
        }

        // Return on the gallery categories list page
        if ('tl_bwein_gallery' === $request->query->get('table') && !$request->query->has('act')) {
            return;
        }

        if (null === ($galleryModel = $this->getGalleryModel($this->getId($event, $request)))) {
            return;
        }

        $event->setQuery('gallery='.$galleryModel->id);
    }

    /**
     * @return int|string
     */
    private function getId(PreviewUrlCreateEvent $event, Request $request)
    {
        // Overwrite the ID if the gallery settings are edited
        if ('tl_bwein_gallery' === $request->query->get('table') && 'edit' === $request->query->get('act')) {
            return $request->query->get('id');
        }

        return $event->getId();
    }

    /**
     * @param int|string $id
     */
    private function getGalleryModel($id): GalleryModel|null
    {
        /** @var GalleryModel $adapter */
        $adapter = $this->framework->getAdapter(GalleryModel::class);

        return $adapter->findByPk($id);
    }
}
