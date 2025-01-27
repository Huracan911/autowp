<?php

namespace Application\Controller\Plugin;

use Exception;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Router\Http\TreeRouteStack;

use Application\ItemNameFormatter;
use Application\Model\Item;
use Application\Model\ItemParent;
use Application\Model\Item\PictureFetcher;
use Application\Model\Picture;
use Application\Model\Twins;
use Application\Service\SpecificationsService;

class Car extends AbstractPlugin
{
    /**
     * @var Twins
     */
    private $twins;

    /**
     * @var SpecificationsService
     */
    private $specsService = null;

    /**
     * @var ItemNameFormatter
     */
    private $itemNameFormatter;

    private $categoryPictureFetcher;

    /**
     * @var Item
     */
    private $itemModel;

    /**
     * @var ItemParent
     */
    private $itemParent;

    /**
     * @var Picture
     */
    private $picture;

    /**
     * @var TreeRouteStack
     */
    private $router;

    public function __construct(
        SpecificationsService $specsService,
        ItemNameFormatter $itemNameFormatter,
        Item $itemModel,
        ItemParent $itemParent,
        Picture $picture,
        Twins $twins,
        TreeRouteStack $router
    ) {
        $this->specsService = $specsService;
        $this->itemNameFormatter = $itemNameFormatter;
        $this->itemModel = $itemModel;
        $this->itemParent = $itemParent;
        $this->picture = $picture;
        $this->twins = $twins;
        $this->router = $router;
    }

    /**
     * @return Car
     */
    public function __invoke()
    {
        return $this;
    }

    private function getCategoryPictureFetcher()
    {
        return $this->categoryPictureFetcher
            ? $this->categoryPictureFetcher
            : $this->categoryPictureFetcher = new Item\DistinctItemPictureFetcher([
                'pictureModel' => $this->picture,
                'itemModel'    => $this->itemModel,
                'dateSort'     => false
            ]);
    }

    public function listData($cars, array $options = [])
    {
        $listBuilder          = $options['listBuilder'];
        $pictureFetcher       = $options['pictureFetcher'];
        if (! $pictureFetcher instanceof PictureFetcher) {
            throw new Exception("Invalid picture fetcher provided");
        }
        $disableTitle         = isset($options['disableTitle']) && $options['disableTitle'];
        $disableDescription   = isset($options['disableDescription']) && $options['disableDescription'];
        $disableDetailsLink   = isset($options['disableDetailsLink']) && $options['disableDetailsLink'];
        $onlyExactlyPictures  = isset($options['onlyExactlyPictures']) && $options['onlyExactlyPictures'];
        $hideEmpty            = isset($options['hideEmpty']) && $options['hideEmpty'];
        $disableTwins         = isset($options['disableTwins']) && $options['disableTwins'];
        $disableSpecs         = isset($options['disableSpecs']) && $options['disableSpecs'];
        $disableCategories    = isset($options['disableCategories']) && $options['disableCategories'];
        $callback             = isset($options['callback']) && $options['callback'] ? $options['callback'] : null;
        $thumbColumns         = isset($options['thumbColumns']) && $options['thumbColumns']
                                    ? $options['thumbColumns'] : 4;

        $controller = $this->getController();
        $pluginManager = $controller->getPluginManager();
        $picHelper = $pluginManager->get('pic');
        /* @phan-suppress-next-line PhanUndeclaredMethod */
        $userHelper = $controller->user();
        $imageStorage = $controller->imageStorage();

        $specEditor = $userHelper->isAllowed('specifications', 'edit');
        $isCarModer = $userHelper->inheritsRole('cars-moder');
        $language = $controller->language();

        $carIds = [];
        foreach ($cars as $car) {
            $carIds[] = (int)$car['id'];
        }

        if ($carIds) {
            $childsCounts = $this->itemParent->getChildItemsCountArray($carIds);
        } else {
            $childsCounts = [];
        }

        // categories
        $carsCategories = [];
        if ($carIds && ! $disableCategories) {
            $categoryRows = $this->itemModel->getRows([
                'language'     => $language,
                'columns'      => ['catname', 'name'],
                'item_type_id' => Item::CATEGORY,
                'child'        => [
                    'item_type_id'       => [Item::VEHICLE, Item::ENGINE],
                    'descendant_or_self' => [
                        'id'      => $carIds,
                        'columns' => [
                            'item_id' => 'id'
                        ]
                    ]
                ]
            ]);

            foreach ($categoryRows as $category) {
                $carId = (int)$category['item_id'];
                if (! isset($carsCategories[$carId])) {
                    $carsCategories[$carId] = [];
                }
                $carsCategories[$carId][] = [
                    'name' => $this->itemNameFormatter->format(
                        $category,
                        $language
                    ),
                    'url'  => '/ng/category/' . urlencode($category['catname']),
                ];
            }
        }

        // twins
        $carsTwinsGroups = [];
        if ($carIds && ! $disableTwins) {
            $carsTwinsGroups = [];

            foreach ($this->twins->getCarsGroups($carIds, $language) as $carId => $twinsGroups) {
                $carsTwinsGroups[$carId] = [];
                foreach ($twinsGroups as $twinsGroup) {
                    $carsTwinsGroups[$carId][] = [
                        'name' => $twinsGroup['name'],
                        'url'  => '/ng/twins/group/' . $twinsGroup['id'],
                    ];
                }
            }
        }

        // typecount
        $carsTypeCounts = [];
        if ($carIds && $listBuilder->isTypeUrlEnabled()) {
            $carsTypeCounts = $this->itemParent->getChildItemsCountArrayByTypes($carIds, [
                ItemParent::TYPE_TUNING,
                ItemParent::TYPE_SPORT
            ]);
        }

        // lang names
        $carsLangName = $this->itemModel->getLanguageNamesOfItems($carIds, $language);

        // total pictures
        $carsTotalPictures = $pictureFetcher->getTotalPictures($carIds, $onlyExactlyPictures);
        $items = [];
        foreach ($cars as $car) {
            $totalPictures = isset($carsTotalPictures[$car['id']]) ? $carsTotalPictures[$car['id']] : null;

            $designProjectData = $this->itemModel->getDesignInfo($this->router, $car['id'], $language);

            $categories = [];
            if (! $disableCategories) {
                $categories = isset($carsCategories[$car['id']]) ? $carsCategories[$car['id']] : [];
            }

            $cFetcher = $pictureFetcher;
            if ($car['item_type_id'] == Item::CATEGORY) {
                $cFetcher = $this->getCategoryPictureFetcher();
            }

            $pictures = $cFetcher->fetch($car, [
                'totalPictures' => $totalPictures
            ]);
            $largeFormat = count($pictures) > 4;
            foreach ($pictures as &$picture) {
                if ($picture) {
                    if (isset($picture['isVehicleHood']) && $picture['isVehicleHood']) {
                        $url = $picHelper->href($picture['row']);
                    } else {
                        $url = $listBuilder->getPictureUrl($car, $picture['row']);
                    }
                    $picture['url'] = $url;
                }
            }
            unset($picture);

            if ($hideEmpty) {
                $hasPictures = false;
                foreach ($pictures as $picture) {
                    if ($picture) {
                        $hasPictures = true;
                        break;
                    }
                }

                if (! $hasPictures) {
                    continue;
                }
            }

            $texts = $this->itemModel->getTextsOfItem($car['id'], $language);

            $description = $texts['text'];
            $text = $texts['full_text'];

            $hasHtml = (bool)$text;

            $specsLinks = [];
            if (! $disableSpecs) {
                $url = $listBuilder->getSpecificationsUrl($car);
                if ($url) {
                    $specsLinks[] = [
                        'name' => null,
                        'url'  => $url
                    ];
                }
            }

            $childsCount = isset($childsCounts[$car['id']]) ? $childsCounts[$car['id']] : 0;

            $vehiclesOnEngine = [];
            if ($car['item_type_id'] == Item::ENGINE) {
                $vehiclesOnEngine = $this->getVehiclesOnEngine($car);
            }

            $item = [
                'id'               => $car['id'],
                'itemTypeId'       => $car['item_type_id'],
                'name'             => $car['name'],
                'nameData'         => $this->itemModel->getNameData($car, $language),
                'langName'         => isset($carsLangName[$car['id']]) ? $carsLangName[$car['id']] : null,
                'produced'         => $car['produced'],
                'produced_exactly' => $car['produced_exactly'],
                'designProject'    => $designProjectData,
                'totalPictures'    => $totalPictures,
                'categories'       => $categories,
                'pictures'         => $pictures,
                'hasHtml'          => $hasHtml,
                'hasChilds'        => $childsCount > 0,
                'childsCount'      => $childsCount,
                'specsLinks'       => $specsLinks,
                'largeFormat'      => $largeFormat,
                'vehiclesOnEngine' => $vehiclesOnEngine
            ];

            if (! $disableTwins) {
                $item['twinsGroups'] = isset($carsTwinsGroups[$car['id']]) ? $carsTwinsGroups[$car['id']] : [];
            }

            if (count($item['pictures']) < $item['totalPictures']) {
                $item['allPicturesUrl'] = $listBuilder->getPicturesUrl($car);
            }

            $item['uploadUrl'] = '/ng/upload?item_id=' . $car['id'];

            if (! $disableDetailsLink && ($hasHtml || $childsCount > 0)) {
                $url = $listBuilder->getDetailsUrl($car);

                if ($url) {
                    $item['details'] = [
                        'url' => $url
                    ];
                }
            }

            if (! $disableDescription) {
                $item['description'] = $description;
            }

            if ($specEditor) {
                $item['specEditorUrl'] = '/ng/cars/specifications-editor?item_id=' . $car['id'];
            }

            if ($isCarModer) {
                $item['moderUrl'] = '/ng/moder/items/item/' . $car['id'];
            }

            if ($listBuilder->isTypeUrlEnabled()) {
                $tuningCount = isset($carsTypeCounts[$car['id']][ItemParent::TYPE_TUNING])
                    ? $carsTypeCounts[$car['id']][ItemParent::TYPE_TUNING]
                    : 0;
                if ($tuningCount) {
                    $url = $listBuilder->getTypeUrl($car, ItemParent::TYPE_TUNING);
                    $item['tuning'] = [
                        'count' => $tuningCount,
                        'url'   => $url
                    ];
                }

                $sportCount = isset($carsTypeCounts[$car['id']][ItemParent::TYPE_SPORT])
                    ? $carsTypeCounts[$car['id']][ItemParent::TYPE_SPORT]
                    : 0;
                if ($sportCount) {
                    $url = $listBuilder->getTypeUrl($car, ItemParent::TYPE_SPORT);
                    $item['sport'] = [
                        'count' => $sportCount,
                        'url'   => $url
                    ];
                }
            }

            if ($callback) {
                $callback($item);
            }

            $items[] = $item;
        }

        // collect all pictures
        $allPictures = [];
        $allFormatRequests = [];
        foreach ($items as $item) {
            foreach ($item['pictures'] as $idx => $picture) {
                if ($picture) {
                    $row = $picture['row'];
                    $allPictures[] = $row;
                    if ($item['largeFormat'] && $idx == 0) {
                        $allFormatRequests['picture-thumb-large'][$row['id']] = $row['image_id'];
                    } else {
                        $allFormatRequests['picture-thumb-medium'][$row['id']] = $row['image_id'];
                    }
                }
            }
        }


        // prefetch names
        $pictureNames = $this->picture->getNameData($allPictures, [
            'language' => $language
        ]);

        // prefetch images
        $imagesInfo = [];
        foreach ($allFormatRequests as $format => $requests) {
            $imagesInfo[$format] = $imageStorage->getFormatedImages($requests, $format);
        }

        // populate prefetched
        foreach ($items as &$item) {
            foreach ($item['pictures'] as $idx => &$picture) {
                if ($picture) {
                    $id = $picture['row']['id'];

                    $picture['name'] = isset($pictureNames[$id]) ? $pictureNames[$id] : null;
                    if ($item['largeFormat'] && $idx == 0) {
                        $large = isset($imagesInfo['picture-thumb-large'][$id])
                            ? $imagesInfo['picture-thumb-large'][$id]
                            : null;
                        $picture['large'] = $large ? [
                            'src'    => $large->getSrc(),
                            'width'  => $large->getWidth(),
                            'height' => $large->getHeight()
                        ] : null;
                    } else {
                        $medium = isset($imagesInfo['picture-thumb-medium'][$id])
                            ? $imagesInfo['picture-thumb-medium'][$id]
                            : null;
                        $picture['medium'] = $medium ? [
                            'src'    => $medium->getSrc(),
                            'width'  => $medium->getWidth(),
                            'height' => $medium->getHeight()
                        ] : null;
                    }
                    unset($picture['row'], $picture['format']);
                }
            }
        }
        unset($item, $picture);

        return [
            'specEditor'         => $specEditor,
            'isCarModer'         => $isCarModer,
            'disableDescription' => $disableDescription,
            'disableDetailsLink' => $disableDetailsLink,
            'disableTitle'       => $disableTitle,
            'items'              => $items,
            'thumbColumns'       => $thumbColumns
        ];
    }

    private function getVehiclesOnEngine($engine)
    {
        $result = [];

        $ids = $this->itemModel->getEngineVehiclesGroups($engine['id'], [
            'groupJoinLimit' => 3
        ]);

        if ($ids) {
            $controller = $this->getController();
            $language = $controller->language();
            $catalogue = $controller->catalogue();

            $rows = $this->itemModel->getRows([
                'id'    => $ids,
                'order' => $catalogue->itemOrdering()
            ]);

            foreach ($rows as $row) {
                $cataloguePaths = $catalogue->getCataloguePaths($row['id']);
                foreach ($cataloguePaths as $cPath) {
                    $result[] = [
                        'name' => $this->itemModel->getNameData($row, $language),
                        'url'  => $controller->url()->fromRoute('catalogue', [
                            'action'        => 'brand-item',
                            'brand_catname' => $cPath['brand_catname'],
                            'car_catname'   => $cPath['car_catname'],
                            'path'          => $cPath['path']
                        ])
                    ];
                    break;
                }
            }
        }

        return $result;
    }

    public function formatName($vehicle, $language)
    {
        return $this->itemNameFormatter->format(
            $this->itemModel->getNameData($vehicle, $language),
            $language
        );
    }
}
