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

use Contao\Date;
use Contao\Model;
use Contao\Model\Collection;

/**
 * Reads and writes gallery.
 *
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property string $title
 * @property bool   $featured
 * @property string $alias
 * @property int    $author
 * @property int    $startDate
 * @property int    $endDate
 * @property string $images
 * @property string $sortBy
 * @property string $previewImageType
 * @property string $previewImage
 * @property string $event
 * @property string $place
 * @property string $photographer
 * @property string $description
 * @property string $metaTitle
 * @property string $robots
 * @property string $metaDescription
 * @property string $cssClass
 * @property bool   $published
 * @property string $start
 * @property string $stop
 *
 * @method static GalleryModel|null                                        findById($id, array $opt=[])
 * @method static GalleryModel|null                                        findByPk($id, array $opt=[])
 * @method static GalleryModel|null                                        findByIdOrAlias($val, array $opt=[])
 * @method static GalleryModel|null                                        findOneBy($col, $val, array $opt=[])
 * @method static GalleryModel|null                                        findOneByPid($val, array $opt=[])
 * @method static GalleryModel|null                                        findOneByTstamp($val, array $opt=[])
 * @method static GalleryModel|null                                        findOneByTitle($val, array $opt=[])
 * @method static GalleryModel|null                                        findOneByAlias($val, array $opt=[])
 * @method static GalleryModel|null                                        findOneByAuthor($val, array $opt=[])
 * @method static GalleryModel|null                                        findOneByStartDate($val, array $opt=[])
 * @method static GalleryModel|null                                        findOneByEndDate($val, array $opt=[])
 * @method static GalleryModel|null                                        findOneByImages($val, array $opt=[])
 * @method static GalleryModel|null                                        findOneByPreviewImage($val, array $opt=[])
 * @method static GalleryModel|null                                        findOneByEvent($val, array $opt=[])
 * @method static GalleryModel|null                                        findOneByPlace($val, array $opt=[])
 * @method static GalleryModel|null                                        findOneByPhotographer($val, array $opt=[])
 * @method static GalleryModel|null                                        findOneByDescription($val, array $opt=[])
 * @method static GalleryModel|null                                        findOneByMetaTitle($val, array $opt=[])
 * @method static GalleryModel|null                                        findOneByMetaDescription($val, array $opt=[])
 * @method static GalleryModel|null                                        findOneByCssClass($val, array $opt=[])
 * @method static GalleryModel|null                                        findOneByFeatured($val, array $opt=[])
 * @method static GalleryModel|null                                        findOneByPublished($val, array $opt=[])
 * @method static GalleryModel|null                                        findOneByStart($val, array $opt=[])
 * @method static GalleryModel|null                                        findOneByStop($val, array $opt=[])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findByPid($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findByTstamp($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findByTitle($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findByAlias($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findByAuthor($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findByStartDate($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findByEndDate($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findByImages($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findByPreviewImage($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findByEvent($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findByPlace($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findByPhotographer($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findByDescription($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findByMetaTitle($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findByRobots($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findByMetaDescription($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findByCssClass($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findByFeatured($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findByPublished($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findByStart($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findByStop($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findMultipleByIds($val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findBy($col, $val, array $opt = [])
 * @method static Collection|array<GalleryCategoryModel>|GalleryModel|null findAll(array $opt = [])
 * @method static integer                                                  countById($id, array $opt=[])
 * @method static integer                                                  countByPid($val, array $opt=[])
 * @method static integer                                                  countByTstamp($val, array $opt=[])
 * @method static integer                                                  countByTitle($val, array $opt=[])
 * @method static integer                                                  countByAlias($val, array $opt=[])
 * @method static integer                                                  countByAuthor($val, array $opt=[])
 * @method static integer                                                  countByStartDate($val, array $opt=[])
 * @method static integer                                                  countByEndDate($val, array $opt=[])
 * @method static integer                                                  countByImages($val, array $opt=[])
 * @method static integer                                                  countByPreviewImage($val, array $opt=[])
 * @method static integer                                                  countByEvent($val, array $opt=[])
 * @method static integer                                                  countByPlace($val, array $opt=[])
 * @method static integer                                                  countByPhotographer($val, array $opt=[])
 * @method static integer                                                  countByDescription($val, array $opt=[])
 * @method static integer                                                  countByMetaTitle($val, array $opt=[])
 * @method static integer                                                  countByRobots($val, array $opt=[])
 * @method static integer                                                  countByMetaDescription($val, array $opt=[])
 * @method static integer                                                  countByCssClass($val, array $opt=[])
 * @method static integer                                                  countByFeatured($val, array $opt=[])
 * @method static integer                                                  countByPublished($val, array $opt=[])
 * @method static integer                                                  countByStart($val, array $opt=[])
 * @method static integer                                                  countByStop($val, array $opt=[])
 */
class GalleryModel extends Model
{
    public const FRONTEND_HELPER_CSS_CLASS_PREFIX = 'rsfh-gallery-';

    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_bwein_gallery';

    /**
     * Find a published gallery from one or more gallery archives by its ID or alias.
     *
     * @param mixed $value   The numeric ID or alias name
     * @param array $pids    An array of parent IDs
     * @param array $options An optional options array
     *
     * @return GalleryModel|null The model or null if there are no gallery
     */
    public static function findPublishedByParentAndIdOrAlias($value, array|null $pids, array $options = [])
    {
        if (empty($pids) || !\is_array($pids)) {
            return null;
        }

        if (!\is_string($value) && !is_numeric($value)) {
            return null;
        }

        $t = static::$strTable;
        $columns = !preg_match('/^[1-9]\d*$/', $value) ? ["$t.alias=?"] : ["$t.id=?"];
        $columns[] = "$t.pid IN(".implode(',', array_map(\intval(...), $pids)).')';

        if (!static::isPreviewMode($options)) {
            $time = Date::floorToMinute();
            $columns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'".($time + 60)."') AND $t.published='1'";
        }

        return static::findOneBy($columns, $value, $options);
    }

    /**
     * Find published galleries by their parent ID.
     *
     * @param array $pids     An array of gallery archive IDs
     * @param bool  $featured If true, return only featured gallery, if false, return only unfeatured gallery
     * @param int   $limit    An optional limit
     * @param int   $offset   An optional offset
     * @param array $options  An optional options array
     *
     * @return Collection|array<GalleryModel>|GalleryModel|null A collection of models or null if there are no gallery
     */
    public static function findPublishedByPids(array|null $pids, bool|null $featured = null, int $limit = 0, int $offset = 0, array $options = [])
    {
        if (empty($pids) || !\is_array($pids)) {
            return null;
        }

        $t = static::$strTable;
        $columns = ["$t.pid IN(".implode(',', array_map(\intval(...), $pids)).')'];

        if (true === $featured) {
            $columns[] = "$t.featured='1'";
        } elseif (false === $featured) {
            $columns[] = "$t.featured=''";
        }

        if (!static::isPreviewMode($options)) {
            $time = Date::floorToMinute();
            $columns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'".($time + 60)."') AND $t.published='1'";
        }

        if (!isset($options['order'])) {
            $options['order'] = "$t.startDate DESC";
        }

        $options['limit'] = $limit;
        $options['offset'] = $offset;

        return static::findBy($columns, null, $options);
    }

    /**
     * Count published galleries by their parent ID.
     *
     * @param array $pids     An array of gallery archive IDs
     * @param bool  $featured If true, return only featured gallery, if false, return only unfeatured gallery
     * @param array $options  An optional options array
     *
     * @return int The number of galleries
     */
    public static function countPublishedByPids(array|null $pids, bool|null $featured = null, array $options = []): int|null
    {
        if (empty($pids) || !\is_array($pids)) {
            return 0;
        }

        $t = static::$strTable;
        $columns = ["$t.pid IN(".implode(',', array_map(\intval(...), $pids)).')'];

        if (true === $featured) {
            $columns[] = "$t.featured='1'";
        } elseif (false === $featured) {
            $columns[] = "$t.featured=''";
        }

        if (!static::isPreviewMode($options)) {
            $time = Date::floorToMinute();
            $columns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'".($time + 60)."') AND $t.published='1'";
        }

        return static::countBy($columns, null, $options);
    }

    /**
     * Find published galleries with the default redirect target by their parent ID.
     *
     * @param int   $pid     The gallery archive ID
     * @param array $options An optional options array
     *
     * @return Collection|array<GalleryModel>|GalleryModel|null A collection of models or null if there are no gallery
     */
    public static function findPublishedDefaultByPid(int $pid, array $options = [])
    {
        $t = static::$strTable;
        $columns = ["$t.pid=?"];

        if (!static::isPreviewMode($options)) {
            $time = Date::floorToMinute();
            $columns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'".($time + 60)."') AND $t.published='1'";
        }

        if (!isset($options['order'])) {
            $options['order'] = "$t.startDate DESC";
        }

        return static::findBy($columns, $pid, $options);
    }

    /**
     * Find published galleries by their parent ID.
     *
     * @param int   $id      The gallery archive ID
     * @param int   $limit   An optional limit
     * @param array $options An optional options array
     *
     * @return Collection|array<GalleryModel>|GalleryModel|null A collection of models or null if there are no gallery
     */
    public static function findPublishedByPid(int $id, int $limit = 0, array $options = [])
    {
        $t = static::$strTable;
        $columns = ["$t.pid=?"];

        if (!static::isPreviewMode($options)) {
            $time = Date::floorToMinute();
            $columns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'".($time + 60)."') AND $t.published='1'";
        }

        if (!isset($options['order'])) {
            $options['order'] = "$t.startDate DESC";
        }

        if ($limit > 0) {
            $options['limit'] = $limit;
        }

        return static::findBy($columns, $id, $options);
    }
}
