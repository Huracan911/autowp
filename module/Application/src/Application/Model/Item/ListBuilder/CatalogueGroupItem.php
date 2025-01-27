<?php

namespace Application\Model\Item\ListBuilder;

use Exception;

use Autowp\TextStorage;

use Application\Model\Item;
use Application\Model\ItemParent;

class CatalogueGroupItem extends CatalogueItem
{
    /**
     * @var string
     */
    private $language;

    /**
     * @var TextStorage\Service
     */
    private $textStorage;

    /**
     * @var array
     */
    private $hasChildSpecs;

    private $type;

    private $itemParentRows = [];

    /**
     * @var Item
     */
    private $itemModel;

    public function setItemModel(Item $model)
    {
        $this->itemModel = $model;
    }

    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    public function setTextStorage(TextStorage\Service $textStorage)
    {
        $this->textStorage = $textStorage;

        return $this;
    }

    public function setHasChildSpecs($hasChildSpecs)
    {
        $this->hasChildSpecs = $hasChildSpecs;

        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    private function getItemParentRow(int $itemId, int $parentId)
    {
        if (! isset($this->itemParentRows[$itemId][$parentId])) {
            $row = $this->itemParent->getRow($parentId, $itemId);

            $this->itemParentRows[$itemId][$parentId] = $row;
        }

        return $this->itemParentRows[$itemId][$parentId];
    }

    public function getDetailsUrl($item)
    {
        $hasChilds = $this->itemParent->hasChildItems($item['id']);

        $hasHtml = $this->itemModel->hasFullText($item['id']);

        if (! $hasChilds && ! $hasHtml) {
            return null;
        }

        // found parent row
        $itemParentRow = $this->getItemParentRow($item['id'], $this->itemId);
        if (! $itemParentRow) {
            return null;
        }

        return $this->router->assemble([
            'action'        => 'brand-item',
            'brand_catname' => $this->brand['catname'],
            'car_catname'   => $this->brandItemCatname,
            'path'          => array_merge($this->path, [
                $itemParentRow['catname']
            ])
        ], [
            'name' => 'catalogue'
        ]);
    }

    public function getPicturesUrl($item)
    {
        //TODO: more than 1 levels diff fails here
        $itemParentRow = $this->getItemParentRow($item['id'], $this->itemId);
        if (! $itemParentRow) {
            return null;
        }

        return $this->router->assemble([
            'action'        => 'brand-item-pictures',
            'brand_catname' => $this->brand['catname'],
            'car_catname'   => $this->brandItemCatname,
            'path'          => array_merge($this->path, [
                $itemParentRow['catname']
            ]),
            'exact'         => false
        ], [
            'name' => 'catalogue'
        ]);
    }

    public function getSpecificationsUrl($item)
    {
        if ($this->hasChildSpecs[$item['id']]) {
            $itemParentRow = $this->getItemParentRow($item['id'], $this->itemId);
            if ($itemParentRow) {
                return $this->router->assemble([
                    'action'        => 'brand-item-specifications',
                    'brand_catname' => $this->brand['catname'],
                    'car_catname'   => $this->brandItemCatname,
                    'path'          => array_merge($this->path, [
                        $itemParentRow['catname']
                    ]),
                ], [
                    'name' => 'catalogue'
                ]);
            }
        }

        if (! $this->specsService->hasSpecs($item['id'])) {
            return false;
        }

        switch ($this->type) {
            case ItemParent::TYPE_TUNING:
                $typeStr = 'tuning';
                break;

            case ItemParent::TYPE_SPORT:
                $typeStr = 'sport';
                break;

            default:
                $typeStr = null;
                break;
        }

        return $this->router->assemble([
            'action'        => 'brand-item-specifications',
            'brand_catname' => $this->brand['catname'],
            'car_catname'   => $this->brandItemCatname,
            'path'          => $this->path,
            'type'          => $typeStr
        ], [
            'name' => 'catalogue'
        ]);
    }

    public function getTypeUrl($item, $type)
    {
        switch ($type) {
            case ItemParent::TYPE_TUNING:
                $catname = 'tuning';
                break;
            case ItemParent::TYPE_SPORT:
                $catname = 'sport';
                break;
            default:
                throw new Exception('Unexpected type');
                break;
        }

        $itemParentRow = $this->getItemParentRow($item['id'], $this->itemId);
        if ($itemParentRow) {
            $currentPath = array_merge($this->path, [
                $itemParentRow['catname']
            ]);
        } else {
            $currentPath = $this->path;
        }

        return $this->router->assemble([
            'action'        => 'brand-item',
            'brand_catname' => $this->brand['catname'],
            'car_catname'   => $this->brandItemCatname,
            'path'          => $currentPath,
            'type'          => $catname,
            'page'          => null,
        ], [
            'name' => 'catalogue'
        ]);
    }

    public function getPictureUrl($item, $picture)
    {
        // found parent row
        $itemParentRow = $this->getItemParentRow($item['id'], $this->itemId);
        if (! $itemParentRow) {
            return $this->picHelper->url($picture['identity']);
        }

        return $this->router->assemble([
            'action'        => 'brand-item-picture',
            'brand_catname' => $this->brand['catname'],
            'car_catname'   => $this->brandItemCatname,
            'path'          => array_merge($this->path, [
                $itemParentRow['catname']
            ]),
            'picture_id'    => $picture['identity']
        ], [
            'name' => 'catalogue'
        ]);
    }
}
