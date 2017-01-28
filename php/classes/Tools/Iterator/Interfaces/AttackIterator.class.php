<?php
namespace Attack\Tools\Iterator\Interfaces;

interface AttackIterator {

    /**
     * @return bool
     */
    public function hasNext();

    /**
     * @return object
     * @throws \Exception
     */
    public function next();

    /**
     * @return number
     */
    public function size();

}
