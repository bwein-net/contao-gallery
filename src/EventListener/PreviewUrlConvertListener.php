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
use Bwein\Gallery\Renderer\GalleryUrlRenderer;
use Contao\CoreBundle\Event\PreviewUrlConvertEvent;
use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\HttpFoundation\Request;
use Terminal42\ServiceAnnotationBundle\Annotation\ServiceTag;

/**
 * @internal
 *
 * @ServiceTag("kernel.event_listener")
 */
class PreviewUrlConvertListener
{
    private ContaoFramework $framework;

    private GalleryUrlRenderer $urlRenderer;

    public function __construct(ContaoFramework $framework, GalleryUrlRenderer $urlRenderer)
    {
        $this->framework = $framework;
        $this->urlRenderer = $urlRenderer;
    }

    /**
     * Adds the front end preview URL to the event.
     *
     * @throws \Exception
     */
    public function __invoke(PreviewUrlConvertEvent $event): void
    {
        if (!$this->framework->isInitialized()) {
            return;
        }

        $request = $event->getRequest();

        if (null === $request || null === ($gallery = $this->getGalleryModel($request))) {
            return;
        }

        $event->setUrl($request->getSchemeAndHttpHost().'/'.$this->urlRenderer->generateGalleryUrl($gallery));
    }

    private function getGalleryModel(Request $request): GalleryModel|null
    {
        if (!$request->query->has('gallery')) {
            return null;
        }

        /** @var GalleryModel $adapter */
        $adapter = $this->framework->getAdapter(GalleryModel::class);

        return $adapter->findByPk($request->query->get('gallery'));
    }
}
