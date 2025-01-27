<?php

namespace Application\Controller\Api;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

use Autowp\User\Controller\Plugin\User;

use Application\Controller\Plugin\ForbiddenAction;
use Application\Model\Item;
use Application\Model\Picture;

/**
 * Class StatController
 * @package Application\Controller\Api
 *
 * @method User user($user = null)
 * @method ForbiddenAction forbiddenAction()
 */
class StatController extends AbstractActionController
{
    /**
     * @var Item
     */
    private $item;

    /**
     * @var Picture
     */
    private $picture;

    public function __construct(Item $item, Picture $picture)
    {
        $this->item = $item;
        $this->picture = $picture;
    }

    public function globalSummaryAction()
    {
        if (! $this->user()->inheritsRole('moder')) {
            return $this->forbiddenAction();
        }

        $totalPictures = $this->picture->getCount([]);

        $totalBrands = $this->item->getCount([
            'item_type_id' => Item::BRAND
        ]);

        $totalCars = $this->item->getCount([
            'item_type_id' => Item::VEHICLE
        ]);

        $db = $this->item->getTable()->getAdapter();

        /* @phan-suppress-next-line PhanUndeclaredMethod */
        $row = $db->query('
            select count(1) as count
            from attrs_attributes
                join attrs_zone_attributes on attrs_attributes.id=attrs_zone_attributes.attribute_id
            where attrs_zone_attributes.zone_id = 1
        ')->execute()->current();
        $totalCarAttrs = $row ? (int)$row['count'] : null;

        /* @phan-suppress-next-line PhanUndeclaredMethod */
        $row = $db->query('
            select count(1) as count
            from attrs_values
        ')->execute()->current();
        $carAttrsValues = $row ? (int)$row['count'] : null;

        /* @phan-suppress-next-line PhanUndeclaredMethod */
        $row = $db->query('
            select count(1) as count from (
                select item.id, count(pictures.id) as c
                from item
                    inner join picture_item on item.id = picture_item.item_id
                    inner join pictures on picture_item.picture_id = pictures.id
                group by item.id
                having c >= 4
            ) as T1
        ')->execute()->current();
        $carsWith4OrMorePictures = $row ? (int)$row['count'] : null;

        $data = [
            [
                'name'    => 'moder/statistics/photos-with-copyrights',
                'total'    => $totalPictures,
                'value'    => $this->picture->getCount(['has_copyrights' => true])
            ],
            [
                'name'     => 'moder/statistics/vehicles-with-4-or-more-photos',
                'total'    => $totalCars,
                'value'    => $carsWith4OrMorePictures
            ],
            [
                'name'     => 'moder/statistics/specifications-values',
                'total'    => $totalCars * $totalCarAttrs,
                'value'    => $carAttrsValues,
            ],
            [
                'name'     => 'moder/statistics/brand-logos',
                'total'    => $totalBrands,
                'value'    => $this->item->getCount([
                    'has_logo'     => true,
                    'item_type_id' => Item::BRAND
                ])
            ],
            [
                'name'    => 'moder/statistics/from-years',
                'total'    => $totalCars,
                'value'    => $this->item->getCount([
                    'has_begin_year' => true
                ])
            ],
            [
                'name'    => 'moder/statistics/from-and-to-years',
                'total'    => $totalCars,
                'value'    => $this->item->getCount([
                    'has_begin_year' => true,
                    'has_end_year'   => true,
                ])
            ],
            [
                'name'    => 'moder/statistics/from-and-to-years-and-months',
                'total'    => $totalCars,
                'value'    => $this->item->getCount([
                    'has_begin_year ' => true,
                    'has_end_year'    => true,
                    'has_begin_month' => true,
                    'has_end_month'   => true,
                ])
            ],
        ];

        return new JsonModel([
            'items' => $data,
        ]);
    }
}
