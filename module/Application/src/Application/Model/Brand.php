<?php

namespace Application\Model;

use Collator;
use Transliterator;

use Zend\Db\Sql;

use Autowp\Image;

class Brand
{
    const TOP_COUNT = 150;

    const NEW_DAYS = 7;

    const MAX_NAME = 80;

    const MAX_FULLNAME = 255;

    const ICON_FORMAT = 'brandicon';

    /**
     * @var Item
     */
    private $item;

    private $collators = [];

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    private function getCollator($language)
    {
        if (! isset($this->collators[$language])) {
            $this->collators[$language] = new Collator($language);
        }

        return $this->collators[$language];
    }

    private function compareName($a, $b, $language)
    {
        $coll = $this->getCollator($language);
        switch ($language) {
            case 'zh':
                $aIsHan = (bool)preg_match("/^\p{Han}/u", $a);
                $bIsHan = (bool)preg_match("/^\p{Han}/u", $b);

                if ($aIsHan && ! $bIsHan) {
                    return -1;
                }

                if ($bIsHan && ! $aIsHan) {
                    return 1;
                }

                return $coll->compare($a, $b);
                break;

            default:
                return $coll->compare($a, $b);
                break;
        }
    }

    /**
     * @suppress PhanDeprecatedFunction, PhanUndeclaredMethod, PhanPluginMixedKeyNoKey
     * @param string $language
     * @return array
     */
    public function getTopBrandsList(string $language)
    {
        $subSelect = new Sql\Select(['product' => 'item']);
        $subSelect->columns([new Sql\Expression('count(distinct product.id)')])
            ->join('item_parent_cache', 'product.id = item_parent_cache.item_id', [])
            ->where('item_parent_cache.parent_id = item.id')
            ->limit(1);

        $rows = $this->item->getRows([
            'language'     => $language,
            'columns'      => [
                'id', 'catname', 'name',
                'cars_count' => $subSelect
            ],
            'item_type_id' => Item::BRAND,
            'limit'        => self::TOP_COUNT,
            'order'        => 'cars_count DESC'
        ]);

        $items = [];
        foreach ($rows as $brandRow) {
            $select = new Sql\Select($this->item->getTable()->getTable());
            $select->columns(['count' => new Sql\Expression('count(distinct item.id)')])
                ->join('item_parent_cache', 'item.id = item_parent_cache.item_id', [])
                ->where([
                    'item_parent_cache.parent_id' => $brandRow['id'],
                    'item_parent_cache.item_id <> item_parent_cache.parent_id',
                    new Sql\Predicate\Expression(
                        'item.add_datetime > DATE_SUB(NOW(), INTERVAL ? DAY)',
                        [self::NEW_DAYS]
                    )
                ]);
            $row = $this->item->getTable()->selectWith($select)->current();

            $newCarsCount = $row ? (int)$row['count'] : 0;

            $items[] = [
                'id'             => $brandRow['id'],
                'catname'        => $brandRow['catname'],
                'name'           => $brandRow['name'],
                'cars_count'     => $brandRow['cars_count'],
                'new_cars_count' => $newCarsCount
            ];
        }

        usort($items, function ($a, $b) use ($language) {
            return $this->compareName($a['name'], $b['name'], $language);
        });

        return $items;
    }

    private function utfCharToNumber($char)
    {
        $i = 0;
        $number = '';
        while (isset($char{$i})) {
            $number .= ord($char{$i});
            ++$i;
        }
        return $number;
    }

    /**
     * @suppress PhanDeprecatedFunction, PhanPluginMixedKeyNoKey
     * @param string $language
     * @return array
     */
    public function getFullBrandsList(string $language)
    {
        $select = new Sql\Select(['ipc_all' => 'item_parent_cache']);
        $select->columns([new Sql\Expression('COUNT(DISTINCT pictures.id)')])
            ->join('picture_item', 'ipc_all.item_id = picture_item.item_id', [])
            ->join('pictures', 'picture_item.picture_id = pictures.id', [])
            ->where([
                'item.id = ipc_all.parent_id',
                'pictures.status' => Picture::STATUS_ACCEPTED
            ]);

        $rows = $this->getList([
            'language' => $language,
            'columns'  => [
                'logo_id',
                'cars_count' => new Sql\Expression(
                    'COUNT(subitem.id)'
                ),
                'new_cars_count' => new Sql\Expression(
                    'COUNT(IF(subitem.add_datetime > DATE_SUB(NOW(), INTERVAL ? DAY), 1, NULL))',
                    [self::NEW_DAYS]
                ),
                'pictures_count' => $select
            ]
        ], function (Sql\Select $select) use ($language) {
            $select
                ->join('item_parent_cache', 'item.id = item_parent_cache.parent_id', [])
                ->where(['item_parent_cache.item_id <> item_parent_cache.parent_id'])
                ->join(['subitem' => 'item'], 'item_parent_cache.item_id = subitem.id', [])
                ->group('item.id');
        });

        $result = [
            'numbers'  => [],
            'cyrillic' => [],
            'latin'    => [],
            'other'    => []
        ];

        $tr = Transliterator::create('Any-Latin;Latin-ASCII;');

        /*foreach ($rows as $row) {
            print $row['name'] . PHP_EOL;
        }*/

        foreach ($rows as $row) {
            $name = $row['name'];

            $char = mb_substr($name, 0, 1);

            $isNumber = preg_match("/^[0-9]$/u", $char);
            $isCyrillic = false;
            $isLatin = false;

            if (! $isNumber) {
                $isHan = preg_match("/^\p{Han}$/u", $char);
                if ($isHan) {
                    $char = mb_substr($tr->transliterate($char), 0, 1);
                    $isLatin = true;
                }

                if (! $isHan) {
                    $isCyrillic = preg_match("/^\p{Cyrillic}$/u", $char);

                    if (! $isCyrillic) {
                        $char = $tr->transliterate($char);

                        $isLatin = preg_match("/^[A-Za-z]$/u", $char);
                    }
                }
                $char = mb_strtoupper($char);
            }

            if ($isNumber) {
                $line = 'numbers';
            } elseif ($isCyrillic) {
                $line = 'cyrillic';
            } elseif ($isLatin) {
                $line = 'latin';
            } else {
                $line = 'other';
            }

            if (! isset($result[$line][$char])) {
                $result[$line][$char] = [
                    'id'     => $this->utfCharToNumber($char),
                    'char'   => $char,
                    'brands' => []
                ];
            }

            /*$picturesCount = $row['carpictures_count'] + $row['enginepictures_count'] +
                $row['logopictures_count'] + $row['mixedpictures_count'] +
                $row['unsortedpictures_count'];*/

            $result[$line][$char]['brands'][] = [
                'id'             => (int)$row['id'],
                'name'           => $name,
                'catname'        => $row['catname'],
                'logo_id'        => $row['logo_id'],
                'totalPictures'  => (int)$row['pictures_count'],
                'newCars'        => (int)$row['new_cars_count'],
                'totalCars'      => (int)$row['cars_count']
            ];
        }

        foreach ($result as &$line) {
            uksort($line, function ($a, $b) use ($language) {
                return $this->compareName($a, $b, $language);
            });

            $line = array_values($line);
        }
        unset($line);

        $result = array_values($result);

        return $result;
    }

    /**
     * @suppress PhanUndeclaredMethod
     * @param string $language
     * @param $callback
     * @return array|null
     * @throws \Exception
     */
    private function fetchBrand(string $language, $callback)
    {
        $select = $this->item->getSelect([
            'language'     => $language,
            'columns'      => ['id', 'catname', 'name', 'full_name', 'logo_id'],
            'item_type_id' => Item::BRAND
        ]);

        $callback($select);

        $brand = $this->item->getTable()->selectWith($select)->current();

        if (! $brand) {
            return null;
        }

        return [
            'id'        => $brand['id'],
            'name'      => $brand['name'],
            'catname'   => $brand['catname'],
            'full_name' => $brand['full_name'],
            'logo_id'   => $brand['logo_id'],
        ];
    }

    public function getBrandById(int $id, string $language)
    {
        return $this->fetchBrand($language, function (Sql\Select $select) use ($id) {
            $select->where(['item.id' => $id]);
        });
    }

    public function getBrandByCatname(string $catname, string $language)
    {
        return $this->fetchBrand($language, function (Sql\Select $select) use ($catname) {
            $select->where(['item.catname' => $catname]);
        });
    }

    public function getList($options, callable $callback = null): array
    {
        if (is_string($options)) {
            $options = [
                'language' => $options
            ];
        }

        $defaults = [
            'language' => 'en',
            'columns'  => []
        ];
        $options = array_replace($defaults, $options);

        $columns = [
            'id',
            'catname',
            'position',
            'name'
        ];
        foreach ($options['columns'] as $column => $expr) {
            switch ($expr) {
                case 'id':
                case 'name':
                    break;
                case 'logo_id':
                    $columns[] = 'logo_id';
                    break;
                default:
                    if (is_numeric($column)) {
                        $columns[] = $expr;
                    } else {
                        $columns[$column] = $expr;
                    }
            }
        }

        $select = $this->item->getSelect([
            'language'     => (string)$options['language'],
            'columns'      => $columns,
            'item_type_id' => Item::BRAND,
            'order'        => 'item.position'
        ]);

        if ($callback) {
            $callback($select);
        }

        $items = [];
        foreach ($this->item->getTable()->selectWith($select) as $row) {
            $items[] = $row;
        }

        usort($items, function ($a, $b) use ($options) {

            if ($a['position'] != $b['position']) {
                return ($a['position'] < $b['position']) ? -1 : 1;
            }

            return $this->compareName($a['name'], $b['name'], $options['language']);
        });

        return $items;
    }

    public function createIconsSprite(Image\Storage $imageStorage, $destImg, $destCss)
    {
        $list = $this->getList([
            'language' => 'en',
            'columns'  => [
                'logo_id'
            ]
        ], function ($select) {
            $select->where(['logo_id']);
        });

        $images = [];

        $format = $imageStorage->getFormat(self::ICON_FORMAT);

        $background = $format->getBackground();

        foreach ($list as $brand) {
            $img = false;
            if ($brand['logo_id']) {
                $img = $imageStorage->getFormatedImagePath($brand['logo_id'], self::ICON_FORMAT);
            }

            if ($img) {
                $img = str_replace('http://i.wheelsage.org/', PUBLIC_DIR . '/', $img);
                $catname = str_replace('.', '_', $brand['catname']);
                $images[$catname] = escapeshellarg($img);
            }
        }

        $count = count($images);
        $width = (int)ceil(sqrt($count));
        if ($width <= 0) {
            $width = 1;
        }

        $cmd = sprintf(
            'montage ' . implode(' ', $images) . ' -background %s -geometry +1+1 -tile %dx %s',
            escapeshellarg($background ? $background : 'none'),
            $width,
            escapeshellarg($destImg)
        );

        $cmdFilename = tempnam(sys_get_temp_dir(), 'brandicons');
        file_put_contents($cmdFilename, $cmd);
        chmod($cmdFilename, 0700);
        exec($cmdFilename);

        $css = [];
        $index = 0;
        foreach ($images as $catname => $img) {
            $top = floor($index / $width);
            $left = $index - $top * $width;
            $css[] = sprintf(
                '.brandicon.brandicon-%s {background-position: -%dpx -%dpx}',
                $catname,
                1 + ($format->getWidth() + 1 + 1) * $left,
                1 + ($format->getHeight() + 1 + 1) * $top
            );
            $index++;
        }

        file_put_contents($destCss, implode(' ', $css));
    }
}
