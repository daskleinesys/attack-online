<?php
namespace AttOn\Model\Iterator\Interfaces;

interface AttonIterator {

    /**
     * @return bool
     */
    public function hasNext();

    /**
     * @return object
     * @throws Exception
     */
    public function next();

    /**
     * @return number
     */
    public function size();

}
