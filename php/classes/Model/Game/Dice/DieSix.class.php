<?php
namespace Attack\Model\Game\Dice;

class DieSix extends AbstractGameDie {

    function roll() {
        return self::next();
    }
}
