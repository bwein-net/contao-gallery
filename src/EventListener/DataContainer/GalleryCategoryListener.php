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

use Bwein\Gallery\Security\ContaoGalleryPermissions;
use Contao\Backend;
use Contao\BackendUser;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class GalleryCategoryListener.
 */
class GalleryCategoryListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ContaoFramework $framework,
        private readonly Security $security,
    ) {
    }

    /**
     * @throws AccessDeniedException
     */
    #[AsCallback('tl_bwein_gallery_category', target: 'config.onload')]
    public function checkPermission(DataContainer|null $dc = null): void
    {
        /** @var BackendUser $backendUser */
        $backendUser = $this->framework->getAdapter(BackendUser::class)->getInstance();

        if ($backendUser->isAdmin) {
            return;
        }

        // Set root IDs
        if (empty($backendUser->gallery) || !\is_array($backendUser->gallery)) {
            $root = [0];
        } else {
            $root = $backendUser->gallery;
        }

        $GLOBALS['TL_DCA']['tl_bwein_gallery_category']['list']['sorting']['root'] = $root;

        // Check permissions to add category
        if (!$this->security->isGranted(ContaoGalleryPermissions::USER_CAN_CREATE_CATEGORIES)) {
            $GLOBALS['TL_DCA']['tl_bwein_gallery_category']['config']['closed'] = true;
            $GLOBALS['TL_DCA']['tl_bwein_gallery_category']['config']['notCreatable'] = true;
            $GLOBALS['TL_DCA']['tl_bwein_gallery_category']['config']['notCopyable'] = true;
        }

        // Check permissions to delete calendars
        if (!$this->security->isGranted(ContaoGalleryPermissions::USER_CAN_DELETE_CATEGORIES)) {
            $GLOBALS['TL_DCA']['tl_bwein_gallery_category']['config']['notDeletable'] = true;
        }

        // Check current action
        $action = $this->requestStack->getCurrentRequest()->query->get('act');

        switch ($action) {
            case 'select':
                // Allow
                break;

            case 'create':
                if (!$this->security->isGranted(ContaoGalleryPermissions::USER_CAN_CREATE_CATEGORIES)) {
                    throw new AccessDeniedException('Not enough permissions to create gallery category.');
                }
                break;

            case 'edit':
            case 'copy':
            case 'delete':
            case 'show':
                if (
                    !\in_array(Input::get('id'), $root, true)
                    || ('delete' === $action && !$this->security->isGranted(ContaoGalleryPermissions::USER_CAN_DELETE_CATEGORIES))
                ) {
                    throw new AccessDeniedException('Not enough permissions to '.$action.' gallery category ID '.Input::get('id').'.');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'copyAll':
                $objSession = $this->requestStack->getSession();
                $session = $objSession->all();

                if ('deleteAll' === $action && !$this->security->isGranted(ContaoGalleryPermissions::USER_CAN_DELETE_CATEGORIES)) {
                    $session['CURRENT']['IDS'] = [];
                } else {
                    $session['CURRENT']['IDS'] = array_intersect((array) $session['CURRENT']['IDS'], $root);
                }
                $objSession->replace($session);
                break;

            default:
                if ($action) {
                    throw new AccessDeniedException('Not enough permissions to '.$action.' gallery category.');
                }
                break;
        }
    }

    #[AsCallback('tl_bwein_gallery_category', target: 'config.oncreate')]
    public function adjustPermissionsCreate(string $table, int $insertId, array $row, DataContainer $dataContainer): void
    {
        $this->adjustPermissions($insertId, $dataContainer);
    }

    /**
     * @Callback(table="tl_bwein_gallery_category", target="config.oncreate")
     */
    #[AsCallback('tl_bwein_gallery_category', target: 'config.oncreate')]
    public function adjustPermissionsCopy(string $table, int $insertId, array $row, DataContainer $dataContainer): void
    {
        $this->adjustPermissions($insertId, $dataContainer);
    }

    /**
     * Add the new category to the permissions.
     *
     * @Callback(table="tl_bwein_gallery_category", target="config.oncopy")
     */
    public function adjustPermissions(int $insertId, DataContainer $dataContainer): void
    {
        /** @var BackendUser $backendUser */
        $backendUser = $this->framework->getAdapter(BackendUser::class)->getInstance();

        if ($backendUser->isAdmin) {
            return;
        }

        // Set root IDs
        if (empty($backendUser->gallery) || !\is_array($backendUser->gallery)) {
            $root = [0];
        } else {
            $root = $backendUser->gallery;
        }

        $root = array_map('intval', $root);

        // The category is enabled already
        if (\in_array($insertId, $root, true)) {
            return;
        }

        $db = Database::getInstance();

        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $this->requestStack->getSession()->getBag('contao_backend');
        $newRecords = $sessionBag->get('new_records');

        if (\is_array($newRecords['tl_bwein_gallery_category']) && \in_array($insertId, array_map('intval', $newRecords['tl_bwein_gallery_category']), true)) {
            // Add the permissions on group level
            if ('custom' !== $backendUser->inherit) {
                $userGroup = $db->execute('SELECT id, gallery, galleryp FROM tl_user_group WHERE id IN('.implode(',', array_map('\intval', $backendUser->groups)).')');

                while ($userGroup->next()) {
                    $galleryp = StringUtil::deserialize($userGroup->galleryp);

                    if (\is_array($galleryp) && \in_array('create', $galleryp, true)) {
                        $arrgallery = (array) StringUtil::deserialize($userGroup->gallery, true);
                        $arrgallery[] = $insertId;

                        $db->prepare('UPDATE tl_user_group SET gallery=? WHERE id=?')
                            ->execute(serialize($arrgallery), $userGroup->id)
                        ;
                    }
                }
            }

            // Add the permissions on user level
            if ('group' !== $backendUser->inherit) {
                $backendUser = $db->prepare('SELECT gallery, galleryp FROM tl_user WHERE id=?')
                    ->limit(1)
                    ->execute($backendUser->id)
                ;

                $galleryp = StringUtil::deserialize($backendUser->galleryp);

                if (\is_array($galleryp) && \in_array('create', $galleryp, true)) {
                    $arrgallery = (array) StringUtil::deserialize($backendUser->gallery, true);
                    $arrgallery[] = $insertId;

                    $db->prepare('UPDATE tl_user SET gallery=? WHERE id=?')
                        ->execute(serialize($arrgallery), $backendUser->id)
                    ;
                }
            }

            // Add the new element to the user object
            $root[] = $insertId;
            $backendUser->gallery = $root;
        }
    }

    /**
     * @Callback(table="tl_bwein_gallery_category", target="list.operations.editheader.button")
     */
    public function editHeader(array $row, string|null $href, string|null $label, string|null $title, string|null $icon, string|null $attributes): string
    {
        return $this->security->isGranted(ContaoCorePermissions::USER_CAN_EDIT_FIELDS_OF_TABLE, 'tl_bwein_gallery_category') ? '<a href="'.Backend::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    /**
     * @Callback(table="tl_bwein_gallery_category", target="list.operations.copy.button")
     */
    public function copyCategory(array $row, string|null $href, string|null $label, string|null $title, string|null $icon, string|null $attributes): string
    {
        return $this->security->isGranted(ContaoGalleryPermissions::USER_CAN_CREATE_CATEGORIES) ? '<a href="'.Backend::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    /**
     * @Callback(table="tl_bwein_gallery_category", target="list.operations.delete.button")
     */
    public function deleteCategory(array $row, string|null $href, string|null $label, string|null $title, string|null $icon, string|null $attributes): string
    {
        return $this->security->isGranted(ContaoGalleryPermissions::USER_CAN_DELETE_CATEGORIES) ? '<a href="'.Backend::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }
}
