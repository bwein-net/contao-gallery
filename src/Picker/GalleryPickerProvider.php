<?php

declare(strict_types=1);

/*
 * This file is part of gallery albums for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

namespace Bwein\Gallery\Picker;

use Bwein\Gallery\Model\GalleryCategoryModel;
use Bwein\Gallery\Model\GalleryModel;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\CoreBundle\Picker\AbstractInsertTagPickerProvider;
use Contao\CoreBundle\Picker\DcaPickerProviderInterface;
use Contao\CoreBundle\Picker\PickerConfig;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class GalleryPickerProvider.
 */
class GalleryPickerProvider extends AbstractInsertTagPickerProvider implements DcaPickerProviderInterface, FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @var Security
     */
    private $security;

    /**
     * @internal Do not inherit from this class; decorate the "contao_gallery.picker.gallery_provider" service instead
     */
    public function __construct(FactoryInterface $menuFactory, RouterInterface $router, TranslatorInterface|null $translator, Security $security)
    {
        parent::__construct($menuFactory, $router, $translator);

        $this->security = $security;
    }

    public function getName(): string
    {
        return 'galleryPicker';
    }

    public function supportsContext($context): bool
    {
        return 'link' === $context && $this->security->isGranted('contao_user.modules', 'gallery');
    }

    public function supportsValue(PickerConfig $config): bool
    {
        return $this->isMatchingInsertTag($config);
    }

    public function getDcaTable(PickerConfig|null $config = null): string
    {
        return 'tl_bwein_gallery';
    }

    public function getDcaAttributes(PickerConfig $config): array
    {
        $attributes = ['fieldType' => 'radio'];

        if ($source = $config->getExtra('source')) {
            $attributes['preserveRecord'] = $source;
        }

        if ($this->supportsValue($config)) {
            $attributes['value'] = $this->getInsertTagValue($config);
        }

        return $attributes;
    }

    public function convertDcaValue(PickerConfig $config, $value): string
    {
        return sprintf($this->getInsertTag($config), $value);
    }

    protected function getRouteParameters(PickerConfig|null $config = null): array
    {
        $params = ['do' => 'gallery'];

        if (null === $config || !$config->getValue() || !$this->supportsValue($config)) {
            return $params;
        }

        if (null !== ($gallerycategoryId = $this->getGalleryCategoryId($this->getInsertTagValue($config)))) {
            $params['table'] = 'tl_bwein_gallery';
            $params['id'] = $gallerycategoryId;
        }

        return $params;
    }

    protected function getDefaultInsertTag(): string
    {
        return '{{gallery_url::%s}}';
    }

    /**
     * @param int|string $id
     *
     * @throws \Exception
     */
    private function getGalleryCategoryId($id): int|null
    {
        /** @var GalleryModel $galleryAdapter */
        $galleryAdapter = $this->framework->getAdapter(GalleryModel::class);

        if (!($galleryModel = $galleryAdapter->findById($id)) instanceof GalleryModel) {
            return null;
        }

        if (!($gallerycategory = $galleryModel->getRelated('pid')) instanceof GalleryCategoryModel) {
            return null;
        }

        return (int) $gallerycategory->id;
    }
}
