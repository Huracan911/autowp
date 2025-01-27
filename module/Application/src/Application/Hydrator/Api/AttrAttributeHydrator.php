<?php

namespace Application\Hydrator\Api;

use Exception;
use Traversable;

use Zend\Hydrator\Exception\InvalidArgumentException;
use Zend\Stdlib\ArrayUtils;

use Autowp\User\Model\User;

use Application\Hydrator\Api\Filter\PropertyFilter;
use Application\Hydrator\Api\Strategy\HydratorStrategy;
use Application\Model\Item;
use Application\Service\SpecificationsService;

class AttrAttributeHydrator extends RestHydrator
{
    /**
     * @var int|null
     */
    private $userId = null;

    /**
     * @var Item
     */
    private $item;

    /**
     * @var User
     */
    private $userModel;

    /**
     * @var SpecificationsService
     */
    private $specService;

    public function __construct($serviceManager)
    {
        parent::__construct();

        $this->userId = null;

        $this->item = $serviceManager->get(Item::class);
        $this->userModel = $serviceManager->get(User::class);
        $this->specService = $serviceManager->get(SpecificationsService::class);

        $strategy = new Strategy\AttrAttributes($serviceManager);
        $this->addStrategy('childs', $strategy);
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
     * @return AttrAttributeHydrator
     */
    public function setUserId($userId = null)
    {
        $this->userId = $userId;

        return $this;
    }

    public function extract($object)
    {
        $result = [
            'id'          => $object['id'],
            'name'        => $object['name'],
            'description' => $object['description'],
            'type_id'     => $object['typeId'],
            'unit_id'     => $object['unitId'],
            'is_multiple' => (bool)$object['isMultiple'],
            'precision'   => $object['precision']
        ];

        if ($this->filterComposite->filter('unit')) {
            $result['unit'] = $this->specService->getUnit($object['unitId']);
        }

        if ($this->filterComposite->filter('options')) {
            if (in_array($object['typeId'], [6, 7])) {
                $result['options'] = $this->specService->getListOptionsArray($object['id']);
            }
        }

        if (isset($object['childs'])) {
            $result['childs'] = $this->extractValue('childs', $object['childs']);
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

    public function setFields(array $fields)
    {
        $this->getFilter()->addFilter('fields', new PropertyFilter(array_keys($fields)));

        foreach ($fields as $name => $value) {
            if (! is_array($value)) {
                continue;
            }

            if (! isset($this->strategies[$name])) {
                continue;
            }

            $strategy = $this->strategies[$name];

            if ($strategy instanceof HydratorStrategy) {
                $strategy->setFields($value);
            }
        }

        if (isset($fields['childs'])) {
            $strategy = $this->strategies['childs'];

            if ($strategy instanceof HydratorStrategy) {
                $strategy->setFields($fields);
            }
        }

        return $this;
    }
}
