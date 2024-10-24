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
use Contao\CoreBundle\Event\PreviewUrlConvertEvent;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @internal
 */
#[AsEventListener]
class PreviewUrlConvertListener
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly ContentUrlGenerator $urlGenerator,
    ) {
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

        if (!$gallery = $this->getGalleryModel($event->getRequest())) {
            return;
        }

        $event->setUrl($this->urlGenerator->generate($gallery, [], UrlGeneratorInterface::ABSOLUTE_URL));
    }

    private function getGalleryModel(Request $request): GalleryModel|null
    {
        if (!$request->query->has('gallery')) {
            return null;
        }

        /** @var GalleryModel $adapter */
        $adapter = $this->framework->getAdapter(GalleryModel::class);

        return $adapter->findById($request->query->get('gallery'));
    }
}
