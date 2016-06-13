<?php
namespace AttOn\Model\Iterator;

use AttOn\Model\Iterator\Interfaces\AttonIterator;
use AttOn\Exceptions\NullPointerException;

class ModelIterator implements AttonIterator {

    private $models;
    private $position = 0;

    public function __construct($models) {
        $this->models = $models;
    }

    /**
     * @return bool
     */
    public function hasNext() {
        if (isset($this->models[$this->position])) {
            return true;
        }
        return false;
    }

    /**
     * @return object
     * @throws NullPointerException
     */
    public function next() {
        if (!$this->hasNext()) {
            throw new NullPointerException('no such element found!');
        }
        return $this->models[$this->position++];
    }

    /**
     * @return number
     */
    public function size() {
        return count($this->models);
    }
}
