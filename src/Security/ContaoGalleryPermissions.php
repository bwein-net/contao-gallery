<?php

declare(strict_types=1);

/*
 * This file is part of gallery albums for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

namespace Bwein\Gallery\Security;

final class ContaoGalleryPermissions
{
    public const USER_CAN_ACCESS_MODULE = 'contao_user.modules.gallery';

    public const USER_CAN_EDIT_CATEGORY = 'contao_user.gallery';

    public const USER_CAN_CREATE_CATEGORIES = 'contao_user.galleryp.create';

    public const USER_CAN_DELETE_CATEGORIES = 'contao_user.galleryp.delete';
}
