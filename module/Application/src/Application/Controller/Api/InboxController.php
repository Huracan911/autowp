<?php

namespace Application\Controller\Api;

use Zend\Db\Sql;
use Zend\InputFilter\InputFilter;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use ZF\ApiProblem\ApiProblemResponse;

use Autowp\User\Controller\Plugin\User;

use Application\Service\DayPictures;
use Application\Model\Brand;
use Application\Model\Item;
use Application\Model\Picture;

/**
 * Class InboxController
 * @package Application\Controller\Api
 *
 * @method User user($user = null)
 * @method string language()
 * @method ApiProblemResponse inputFilterResponse(InputFilter $inputFilter)
 */
class InboxController extends AbstractRestfulController
{
    const PER_PAGE = 18;
    const BRAND_ALL = 'all';

    /**
     * @var Picture
     */
    private $picture;

    /**
     * @var Brand
     */
    private $brand;

    /**
     * @var InputFilter
     */
    private $inputFilter;

    public function __construct(Picture $picture, Brand $brand, InputFilter $inputFilter)
    {
        $this->picture = $picture;
        $this->brand = $brand;
        $this->inputFilter = $inputFilter;
    }

    private function getBrandControl(): array
    {
        $language = $this->language();

        $brands = $this->brand->getList($language, function (Sql\Select $select) {

            $subSelect = new Sql\Select('item');
            $subSelect->columns(['id'])
                ->join('item_parent_cache', 'item.id = item_parent_cache.parent_id', [])
                ->join('picture_item', 'item_parent_cache.item_id = picture_item.item_id', [])
                ->join('pictures', 'picture_item.picture_id = pictures.id', [])
                ->where([
                    'item.item_type_id' => Item::BRAND,
                    'pictures.status'   => Picture::STATUS_INBOX
                ]);

            $select->where([
                new Sql\Predicate\In('item.id', $subSelect)
            ]);
        });

        $brandOptions = [];
        foreach ($brands as $iBrand) {
            $brandOptions[] = [
                'id'      => (int)$iBrand['id'],
                'name'    => $iBrand['name']
            ];
        }

        return $brandOptions;
    }

    public function indexAction()
    {
        $this->inputFilter->setData($this->params()->fromQuery());

        if (! $this->inputFilter->isValid()) {
            return $this->inputFilterResponse($this->inputFilter);
        }

        $values = $this->inputFilter->getValues();

        $language = $this->language();

        $brand = null;
        if ($values['brand_id']) {
            $brand = $this->brand->getBrandById($values['brand_id'], $language);
        }

        $select = $this->picture->getTable()->getSql()->select()
            ->where(['pictures.status' => Picture::STATUS_INBOX]);
        if ($brand) {
            $select
                ->join('picture_item', 'pictures.id = picture_item.picture_id', [])
                ->join('item_parent_cache', 'picture_item.item_id = item_parent_cache.item_id', [])
                ->where(['item_parent_cache.parent_id' => $brand['id']])
                ->group('pictures.id');
        }

        $service = new DayPictures([
            'picture'      => $this->picture,
            'timezone'     => $this->user()->timezone(), // @phan-suppress-current-line PhanUndeclaredMethod
            'dbTimezone'   => MYSQL_TIMEZONE,
            'select'       => $select,
            'orderColumn'  => 'add_date',
            'currentDate'  => $values['date']
        ]);

        if (! $service->haveCurrentDate() || ! $service->haveCurrentDayPictures()) {
            $lastDate = $service->getLastDateStr();

            if (! $lastDate) {
                return $this->notFoundAction();
            }

            $service->setCurrentDate($lastDate);
        }

        $prevDate = $service->getPrevDate();
        $currentDate = $service->getCurrentDate();
        $nextDate = $service->getNextDate();

        return new JsonModel([
            'brands'    => $this->getBrandControl(),
            'prev'      => [
                'date'  => $prevDate ? $prevDate->format('Y-m-d') : null,
                'count' => $service->getPrevDateCount()
            ],
            'current'   => [
                'date'  => $currentDate ? $currentDate->format('Y-m-d') : null,
                'count' => $service->getCurrentDateCount(),
            ],
            'next'      => [
                'date'  => $nextDate ? $nextDate->format('Y-m-d') : null,
                'count' => $service->getNextDateCount()
            ]
        ]);
    }
}
