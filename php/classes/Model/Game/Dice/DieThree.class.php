<?php
namespace AttOn\Model\Game\Dice;

class DieThree extends AbstractGameDie {

    function roll() {
        return ceil(self::next() * 0.5);
    }
}
