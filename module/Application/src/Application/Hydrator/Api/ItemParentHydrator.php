<?php

namespace Application\Hydrator\Api;

use Exception;
use Traversable;

use Zend\Hydrator\Exception\InvalidArgumentException;
use Zend\Permissions\Acl\Acl;
use Zend\Stdlib\ArrayUtils;

use Autowp\User\Model\User;

use Application\Model\Item;
use Application\Model\ItemParent;

class ItemParentHydrator extends RestHydrator
{
    /**
     * @var int|null
     */
    private $userId = null;

    private $userRole = null;

    private $router;

    /**
     * @var Item
     */
    private $item;

    /**
     * @var ItemParent
     */
    private $itemParent;

    /**
     * @var Acl
     */
    private $acl;

    /**
     * @var User
     */
    private $userModel;

    public function __construct(
        $serviceManager
    ) {
        parent::__construct();

        $this->router = $serviceManager->get('HttpRouter');
        $this->itemParent = $serviceManager->get(ItemParent::class);

        $this->item = $serviceManager->get(Item::class);

        $this->acl = $serviceManager->get(Acl::class);
        $this->userModel = $serviceManager->get(User::class);

        $strategy = new Strategy\Item($serviceManager);
        $this->addStrategy('item', $strategy);

        $strategy = new Strategy\Item($serviceManager);
        $this->addStrategy('parent', $strategy);

        $strategy = new Strategy\Item($serviceManager);
        $this->addStrategy('duplicate_parent', $strategy);

        $strategy = new Strategy\Item($serviceManager);
        $this->addStrategy('duplicate_child', $strategy);
    }

    /**
     * @param  array|Traversable $options
     * @return RestHydrator
     * @throws InvalidArgumentException
     */
    public function setOptions($options)
    {
        parent::setOptions($options);

        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (! is_array($options)) {
            throw new InvalidArgumentException(
                'The options parameter must be an array or a Traversable'
            );
        }

        if (isset($options['user_id'])) {
            $this->setUserId($options['user_id']);
        }

        return $this;
    }

    /**
     * @param int|null $userId
     * @return ItemParentHydrator
     */
    public function setUserId($userId = null)
    {
        $this->userId = $userId;

        $this->getStrategy('item')->setUserId($userId);
        $this->getStrategy('parent')->setUserId($userId);
        $this->getStrategy('duplicate_parent')->setUserId($userId);
        $this->getStrategy('duplicate_child')->setUserId($userId);

        return $this;
    }

    public function extract($object)
    {
        $result = [
            'item_id'   => (int)$object['item_id'],
            'parent_id' => (int)$object['parent_id'],
            'type_id'   => (int)$object['type'],
            'catname'   => $object['catname'],
        ];

        $isModer = false;
        $role = $this->getUserRole();
        if ($role) {
            $isModer = $this->acl->inheritsRole($role, 'moder');
        }

        if ($this->filterComposite->filter('item')) {
            $item = $this->item->getRow(['id' => $object['item_id']]);
            $result['item'] = $item ? $this->extractValue('item', $item) : null;
        }

        if ($isModer) {
            if ($this->filterComposite->filter('parent')) {
                $item = $this->item->getRow(['id' => $object['parent_id']]);
                $result['parent'] = $item ? $this->extractValue('parent', $item) : null;
            }

            if ($this->filterComposite->filter('name')) {
                $result['name'] = $this->itemParent->getNamePreferLanguage(
                    $object['parent_id'],
                    $object['item_id'],
                    $this->language
                );
            }

            if ($this->filterComposite->filter('duplicate_parent')) {
                $duplicateRow = $this->item->getRow([
                    'exclude_id' => $object['parent_id'],
                    'child' => [
                        'id' => $object['item_id'],
                        'link_type' => ItemParent::TYPE_DEFAULT
                    ],
                    'ancestor_or_self' => [
                        'id'         => $object['parent_id'],
                        'stock_only' => true
                    ]
                ]);

                $result['duplicate_parent'] = $duplicateRow
                    ? $this->extractValue('duplicate_parent', $duplicateRow) : null;
            }

            if ($this->filterComposite->filter('duplicate_child')) {
                $duplicateRow = $this->item->getRow([
                    'exclude_id' => $object['item_id'],
                    'parent' => [
                        'id' => $object['parent_id'],
                        'link_type' => $object['type']
                    ],
                    'descendant_or_self' => $object['item_id']
                ]);

                $result['duplicate_child'] = $duplicateRow
                    ? $this->extractValue('duplicate_child', $duplicateRow) : null;
            }
        }

        return $result;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param array $data
     * @param $object
     * @throws Exception
     */
    public function hydrate(array $data, $object)
    {
        throw new Exception("Not supported");
    }

    private function getUserRole()
    {
        if (! $this->userId) {
            return null;
        }

        if (! $this->userRole) {
            $this->userRole = $this->userModel->getUserRole($this->userId);
        }

        return $this->userRole;
    }
}
