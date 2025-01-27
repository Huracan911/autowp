<?php

namespace Application\Hydrator\Api;

use Exception;
use Traversable;

use Zend\Hydrator\Exception\InvalidArgumentException;
use Zend\Hydrator\Strategy\DateTimeFormatterStrategy;
use Zend\Stdlib\ArrayUtils;

class LogHydrator extends RestHydrator
{
    /**
     * @var int|null
     */
    private $userId = null;

    public function __construct(
        $serviceManager
    ) {
        parent::__construct();

        $strategy = new Strategy\User($serviceManager);
        $this->addStrategy('user', $strategy);

        $strategy = new DateTimeFormatterStrategy();
        $this->addStrategy('date', $strategy);

        $strategy = new Strategy\Pictures($serviceManager);
        $this->addStrategy('pictures', $strategy);

        $strategy = new Strategy\Items($serviceManager);
        $this->addStrategy('items', $strategy);
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
     * @return LogHydrator
     */
    public function setUserId($userId = null)
    {
        $this->userId = $userId;

        $this->getStrategy('user')->setUserId($userId);
        $this->getStrategy('pictures')->setUserId($userId);
        $this->getStrategy('items')->setUserId($userId);

        return $this;
    }

    public function extract($object)
    {
        $result = [
            'date' => $this->extractValue('date', $object['date']),
            'desc' => $object['desc'],
        ];

        if ($this->filterComposite->filter('user')) {
            $result['user'] = $object['user'] ? $this->extractValue('user', $object['user']) : null;
        }

        if ($this->filterComposite->filter('pictures')) {
            $result['pictures'] = $this->extractValue('pictures', $object['pictures']);
        }

        if ($this->filterComposite->filter('items')) {
            $result['items'] = $this->extractValue('items', $object['items']);
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
}
