<?php

declare(strict_types=1);

/*
 * This file is part of gallery albums for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

namespace Bwein\Gallery\Security\Voter;

use Bwein\Gallery\Security\ContaoGalleryPermissions;
use Contao\CoreBundle\Security\DataContainer\CreateAction;
use Contao\CoreBundle\Security\DataContainer\DeleteAction;
use Contao\CoreBundle\Security\DataContainer\ReadAction;
use Contao\CoreBundle\Security\DataContainer\UpdateAction;
use Contao\CoreBundle\Security\Voter\DataContainer\AbstractDataContainerVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * @internal
 */
class GalleryCategoryAccessVoter extends AbstractDataContainerVoter
{
    public function __construct(private readonly AccessDecisionManagerInterface $accessDecisionManager)
    {
    }

    protected function getTable(): string
    {
        return 'tl_bwein_gallery_category';
    }

    protected function hasAccess(TokenInterface $token, CreateAction|DeleteAction|ReadAction|UpdateAction $action): bool
    {
        if (!$this->accessDecisionManager->decide($token, [ContaoGalleryPermissions::USER_CAN_ACCESS_MODULE])) {
            return false;
        }

        return match (true) {
            $action instanceof CreateAction => $this->accessDecisionManager->decide($token, [ContaoGalleryPermissions::USER_CAN_CREATE_CATEGORIES]),
            $action instanceof ReadAction,
            $action instanceof UpdateAction => $this->accessDecisionManager->decide($token, [ContaoGalleryPermissions::USER_CAN_EDIT_CATEGORY], $action->getCurrentId()),
            $action instanceof DeleteAction => $this->accessDecisionManager->decide($token, [ContaoGalleryPermissions::USER_CAN_EDIT_CATEGORY], $action->getCurrentId())
                && $this->accessDecisionManager->decide($token, [ContaoGalleryPermissions::USER_CAN_DELETE_CATEGORIES]),
        };
    }
}
