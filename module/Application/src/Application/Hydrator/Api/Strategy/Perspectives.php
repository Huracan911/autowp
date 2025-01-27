<?php

namespace Application\Hydrator\Api\Strategy;

use Application\Hydrator\Api\PerspectiveHydrator as Hydrator;

class Perspectives extends HydratorStrategy
{
    /**
     * @return Hydrator
     */
    protected function getHydrator()
    {
        if (! $this->hydrator) {
            $this->hydrator = new Hydrator();
        }

        return $this->hydrator;
    }

    public function extract($value)
    {
        $hydrator = $this->getHydrator();

        $hydrator->setFields($this->fields);

        $result = [];
        foreach ($value as $row) {
            $result[] = $hydrator->extract($row);
        }
        return $result;
    }
}
