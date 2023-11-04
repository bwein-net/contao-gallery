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
use Bwein\Gallery\Renderer\GalleryUrlRenderer;
use Contao\BackendUser;
use Contao\Config;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\CoreBundle\Slug\Slug;
use Contao\Database;
use Contao\DataContainer;
use Contao\Date;
use Contao\Input;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class GalleryListener.
 */
class GalleryListener
{
    private $requestStack;

    private TranslatorInterface $translator;

    private ContaoFramework $framework;

    private Slug $slug;

    private GalleryUrlRenderer $urlRenderer;

    public function __construct(RequestStack $requestStack, TranslatorInterface $translator, ContaoFramework $framework, Slug $slug, GalleryUrlRenderer $urlRenderer)
    {
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->framework = $framework;
        $this->slug = $slug;
        $this->urlRenderer = $urlRenderer;
    }

    /**
     * @Callback(table="tl_bwein_gallery", target="config.onload")
     */
    public function generatePalette(DataContainer|null $dc = null): void
    {
        if (null === $dc || !$dc->id || 'edit' !== $this->requestStack->getCurrentRequest()->query->get('act')) {
            return;
        }

        $gallery = GalleryModel::findById($dc->id);

        if ('select_preview_image' === $gallery->previewImageType) {
            PaletteManipulator::create()
                ->addField(
                    ['previewImage'],
                    'source_legend',
                    PaletteManipulator::POSITION_APPEND,
                )
                ->applyToPalette('default', 'tl_bwein_gallery')
            ;
        }
    }

    /**
     * @Callback(table="tl_bwein_gallery", target="config.onload")
     *
     * @throws AccessDeniedException
     */
    public function checkPermission(DataContainer|null $dc = null): void
    {
        /** @var BackendUser $backendUser */
        $backendUser = $this->framework->getAdapter(BackendUser::class)->getInstance();

        if ($backendUser->isAdmin) {
            return;
        }

        // Set the root IDs
        if (empty($backendUser->gallery) || !\is_array($backendUser->gallery)) {
            $root = [0];
        } else {
            $root = $backendUser->gallery;
        }

        $root = array_map('intval', $root);
        $id = \strlen(Input::get('id')) ? Input::get('id') : $dc->currentPid;

        // Check current action
        $action = $this->requestStack->getCurrentRequest()->query->get('act');

        switch ($action) {
            case 'paste':
            case 'select':
                if (!\in_array($dc->currentPid, $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to access gallery category ID '.$id.'.');
                }
                break;

            case 'create':
                if (!Input::get('pid') || !\in_array((int) Input::get('pid'), $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to create galleries in gallery category ID '.Input::get('pid').'.');
                }
                break;

            case 'cut':
            case 'copy':
                if ('cut' === $action && 1 === Input::get('mode')) {
                    $category = Database::getInstance()->prepare('SELECT pid FROM tl_bwein_gallery WHERE id=?')
                        ->limit(1)
                        ->execute(Input::get('pid'))
                    ;

                    if ($category->numRows < 1) {
                        throw new AccessDeniedException('Invalid gallery ID '.Input::get('pid').'.');
                    }

                    $pid = $category->pid;
                } else {
                    $pid = Input::get('pid');
                }

                if (!\in_array((int) $pid, $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to '.$action.' gallery item ID '.$id.' to gallery category ID '.$pid.'.');
                }
                // no break

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
                $category = Database::getInstance()->prepare('SELECT pid FROM tl_bwein_gallery WHERE id=?')
                    ->limit(1)
                    ->execute($id)
                ;

                if ($category->numRows < 1) {
                    throw new AccessDeniedException('Invalid gallery ID '.$id.'.');
                }

                if (!\in_array((int) $category->pid, $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to '.$action.' gallery ID '.$id.' of gallery category ID '.$category->pid.'.');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!\in_array((int) $id, $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to access gallery category ID '.$id.'.');
                }

                $category = Database::getInstance()->prepare('SELECT id FROM tl_bwein_gallery WHERE pid=?')
                    ->execute($id)
                ;

                $objSession = $this->requestStack->getSession();
                $session = $objSession->all();
                $session['CURRENT']['IDS'] = array_intersect((array) $session['CURRENT']['IDS'], $category->fetchEach('id'));
                $objSession->replace($session);
                break;

            default:
                if ($action) {
                    throw new AccessDeniedException('Invalid command "'.$action.'".');
                }

                if (!\in_array((int) $id, $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to access gallery category ID '.$id.'.');
                }
                break;
        }
    }

    /**
     * @Callback(table="tl_bwein_gallery", target="fields.alias.save")
     */
    public function generateAlias($value, DataContainer $dc): string
    {
        $aliasExists = static fn (string $alias): bool => Database::getInstance()->prepare('SELECT id FROM tl_bwein_gallery WHERE alias=? AND id!=?')->execute($alias, $dc->id)->numRows > 0;

        // Generate alias if there is none
        if ('' === (string) $value) {
            $value = $this->slug->generate($dc->activeRecord->title, GalleryCategoryModel::findByPk($dc->activeRecord->pid)->jumpTo, $aliasExists);
        } elseif ($aliasExists($value)) {
            throw new \Exception(sprintf($this->translator->trans('ERR.aliasExists', [], 'contao_default'), $value));
        }

        return (string) $value;
    }

    /**
     * @Callback(table="tl_bwein_gallery", target="fields.startDate.load")
     * @Callback(table="tl_bwein_gallery", target="fields.endDate.load")
     */
    public function loadDate($value, DataContainer $dc): int|null
    {
        if (null === $value) {
            return $value;
        }

        return strtotime(date('Y-m-d', (int) $value).' 00:00:00');
    }

    /**
     * Adjust start end end time of the gallery.
     *
     * @Callback(table="tl_bwein_gallery", target="config.onsubmit")
     */
    public function adjustTime(DataContainer $dataContainer): void
    {
        // Return if there is no active record (override all) or no start date has been set yet
        if (!$dataContainer->activeRecord || !$dataContainer->activeRecord->startDate) {
            return;
        }

        $where['endDate'] = null;

        // Set end date
        if ($dataContainer->activeRecord->endDate) {
            if ($dataContainer->activeRecord->endDate > $dataContainer->activeRecord->startDate) {
                $where['endDate'] = $dataContainer->activeRecord->endDate;
            } else {
                $where['endDate'] = $dataContainer->activeRecord->startDate;
            }
        }

        Database::getInstance()->prepare('UPDATE tl_bwein_gallery %s WHERE id=?')->set($where)->execute($dataContainer->id);
    }

    /**
     * @Callback(table="tl_bwein_gallery", target="fields.serpPreview.eval.url")
     */
    public function getSerpUrl(GalleryModel $model): string
    {
        return $this->urlRenderer->generateGalleryUrl($model, true);
    }

    /**
     * @Callback(table="tl_bwein_gallery", target="list.sorting.child_record")
     */
    public function listgalleryGalleries(array $row): string
    {
        return '<div class="tl_content_left">'.$row['title'].' <span style="color:#999;padding-left:3px">['.Date::parse(Config::get('dateFormat'), $row['startDate']).']</span></div>';
    }

    /**
     * @Callback(table="tl_bwein_gallery", target="config.oninvalidate_cache_tags")
     */
    public function addSitemapCacheInvalidationTag(DataContainer $dataContainer, array $tags)
    {
        $category = GalleryCategoryModel::findByPk($dataContainer->activeRecord->pid);

        if (null === $category) {
            return $tags;
        }

        $pageModel = PageModel::findWithDetails($category->jumpTo);

        if (null === $pageModel) {
            return $tags;
        }

        return array_merge($tags, ['contao.sitemap.'.$pageModel->rootId]);
    }
}
