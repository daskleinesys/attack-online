<?php
namespace AttOn\Model\Game\Dice;

abstract class AbstractGameDie {

    private static $rolls = array();

    protected static function next() {
        if (empty(self::$rolls)) {
            // add quota check - if necessary
            // curl_setopt($ch, CURLOPT_URL, 'https://www.random.org/quota/?ip=CURRENT_IP&format=plain');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://www.random.org/integers/?num=200&min=1&max=6&col=1&base=10&format=plain&rnd=new');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($ch, CURLOPT_USERAGENT, 'phpRandDotOrg ' . '1.1.0' . ' : ' . 'thomas.schagerl@gmx.net');

            try {
                $rolls = curl_exec($ch);
                curl_close($ch);
                self::$rolls = explode("\n", $rolls);
                array_pop(self::$rolls);
            } catch (\Exception $e) {
                // probably no i-net connection, create die-rolls via php random
                self::$rolls = array();
                for ($x = 0; $x < 200; ++$x) {
                    self::$rolls[] = rand(1, 6);
                }
            }
        }
        return array_pop(self::$rolls);
    }

    abstract function roll();

}
