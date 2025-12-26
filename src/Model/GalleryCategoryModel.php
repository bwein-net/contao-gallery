<?php

declare(strict_types=1);

/*
 * This file is part of gallery albums for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

namespace Bwein\Gallery\Model;

use Contao\Model;
use Contao\Model\Collection;

/**
 * Reads and writes gallery categories.
 *
 * @property int    $id
 * @property string $title
 * @property int    $tstamp
 * @property int    $jumpTo
 * @property bool   $protected
 * @property string $groups
 *
 * @method static GalleryCategoryModel|null                                        findById($id, array $opt=[])
 * @method static GalleryCategoryModel|null                                        findByIdOrAlias($val, array $opt=[])
 * @method static GalleryCategoryModel|null                                        findOneBy($col, $val, array $opt=[])
 * @method static GalleryCategoryModel|null                                        findOneByTitle($val, array $opt=[])
 * @method static GalleryCategoryModel|null                                        findByPk($id, array $opt=[])
 * @method static GalleryCategoryModel|null                                        findOneByTstamp($val, array $opt=[])
 * @method static GalleryCategoryModel|null                                        findOneByJumpTo($val, array $opt=[])
 * @method static GalleryCategoryModel|null                                        findOneByProtected($val, array $opt=[])
 * @method static GalleryCategoryModel|null                                        findOneByGroups($val, array $opt=[])
 * @method static Collection|array<GalleryCategoryModel>|GalleryCategoryModel|null findByTstamp($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryCategoryModel|null findByTitle($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryCategoryModel|null findByJumpTo($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryCategoryModel|null findByProtected($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryCategoryModel|null findByGroups($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryCategoryModel|null findMultipleByIds($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryCategoryModel|null findBy($col, $val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryCategoryModel|null findAll(array $opt = [])
 * @method static integer                                                          countById($id, array $opt=[])
 * @method static integer                                                          countByTstamp($val, array $opt=[])
 * @method static integer                                                          countByTitle($val, array $opt=[])
 * @method static integer                                                          countByJumpTo($val, array $opt=[])
 * @method static integer                                                          countByProtected($val, array $opt=[])
 * @method static integer                                                          countByGroups($val, array $opt=[])
 */
class GalleryCategoryModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_bwein_gallery_category';
}
