<?php

declare(strict_types=1);

/*
 * This file is part of gallery albums for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

namespace Bwein\Gallery\EventListener\DataContainer;

use Bwein\Gallery\Model\GalleryCategoryModel;
use Bwein\Gallery\Model\GalleryModel;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\ChangeLanguage\Helper\LabelCallback;

class MissingLanguageIconListener
{
    private static array $callbacks = [
        'tl_bwein_gallery' => 'onGalleryRecords',
    ];

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Override core labels to show missing language information.
     */
    public function register(string $table): void
    {
        if (\array_key_exists($table, self::$callbacks)) {
            LabelCallback::createAndRegister(
                $table,
                fn (array $args, $previousResult) => $this->{self::$callbacks[$table]}($args, $previousResult),
            );
        }
    }

    /**
     * Generate missing translation warning for gallery records.
     */
    public function onGalleryRecords(array $args, $previousResult = null): string
    {
        $row = $args[0];
        $label = (string) $previousResult;

        $archive = GalleryCategoryModel::findById($row['pid']);

        if (
            null !== $archive
            && $archive->master
            && (!$row['languageMain'] || null === GalleryModel::findById($row['languageMain']))
        ) {
            return $this->generateLabelWithWarning($label);
        }

        return $label;
    }

    private function generateLabelWithWarning(string $label, string $imgStyle = ''): string
    {
        return $label.\sprintf(
            '<span style="padding-left:3px"><img src="%s" alt="%s" title="%s" style="%s"></span>',
            'bundles/terminal42changelanguage/language-warning.png',
            $this->translator->trans('MSC.noMainLanguage', [], 'contao_default'),
            $this->translator->trans('MSC.noMainLanguage', [], 'contao_default'),
            $imgStyle,
        );
    }
}
