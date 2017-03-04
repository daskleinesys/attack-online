<?php
namespace Attack\GameLogic\Battles;

use Attack\Model\Game\Dice\DieSix;
use Attack\Model\Game\ModelGameShip;
use Attack\Model\Units\ModelShip;

class SeaBattle {

    /**
     * [int id_user => [int id_ship => [ModelGameShip, ...]]]
     * @var array
     */
    private $ships_per_user_and_type;

    /**
     * amount of damage dealt to this type of ship by specified user
     *
     * [int id_user => [int id_ship => int count]]
     * @var array
     */
    private $current_hits_per_user_and_type;

    /**
     * [int $id_ship, ... ]
     * @var array
     */
    private $ship_types = [];

    private $user_gets_experience = [];

    public function __construct(array $game_ships) {
        $ships = ModelShip::iterator();
        while ($ships->hasNext()) {
            $id_ship = $ships->next()->getId();
            $this->ship_types[] = $id_ship;
        }

        /** @var ModelGameShip $user_ship */
        foreach ($game_ships as $user_ship) {
            $id_user = $user_ship->getIdUser();
            if (!isset($this->ships_per_user_and_type[$id_user])) {
                foreach ($this->ship_types as $id_ship) {
                    $this->ships_per_user_and_type[$id_user][$id_ship] = [];
                }
            }
            $id_ship = $user_ship->getIdUnit();
            $this->ships_per_user_and_type[$id_user][$id_ship][] = $user_ship;
        }
    }

    public function resolve() {
        if (!$this->shipsLeft()) {
            return;
        }
        while ($this->shipsLeft()) {
            // 1. calculate hits per ship type per user
            foreach ($this->ships_per_user_and_type as $id_user => $user_ships_per_type) {
                foreach ($this->ship_types as $id_ship) {
                    $this->current_hits_per_user_and_type[$id_user][$id_ship] = 0;
                }
                $this->calculateHits($id_user, $user_ships_per_type);
            }

            // 2. apply hits to ships of other users
            foreach ($this->current_hits_per_user_and_type as $id_user => $user_hits_per_type) {
                $this->applyHits($id_user, $user_hits_per_type);
            }
        }
        // add experience to units that survived the battle
        foreach ($this->ships_per_user_and_type as $id_user => $ships) {
            if (!in_array($id_user, $this->user_gets_experience)) {
                continue;
            }
            foreach ($ships as $id_unit => $ships_per_type) {
                /** @var ModelGameShip $ship */
                foreach ($ships_per_type as $ship) {
                    $exp = $ship->getExperience() + 1;
                    $ship->setExperience($exp);
                }
            }
        }
    }

    private function calculateHits($id_user, $ships_per_type) {
        $die = new DieSix();
        /*
         * 1. for each ship type calculate the damage it does to all other ship types, in spite of ships by other users
         * 2. use max 2 units per type when calculating hits
         */

        // 1. destroyer (can hit destroyer or submarines twice as good as carriers and battleships)
        // can do max 2 dmg do destroyer and sub, max 1 dmg to carrier and battleships
        $destroyer = min(count($ships_per_type[ID_DESTROYER]), 2);
        $roll = $die->roll();
        if ($destroyer >= $roll) {
            $this->current_hits_per_user_and_type[$id_user][ID_DESTROYER] += 2;
        } else if ($destroyer >= $roll / 2) {
            $this->current_hits_per_user_and_type[$id_user][ID_DESTROYER] += 1;
        }
        $roll = $die->roll();
        if ($destroyer >= $roll / 2) {
            $this->current_hits_per_user_and_type[$id_user][ID_SUBMARINE] += 2;
        } else if ($destroyer >= $roll / 3) {
            $this->current_hits_per_user_and_type[$id_user][ID_SUBMARINE] += 1;
        }
        $roll = $die->roll();
        if ($destroyer >= $roll) {
            $this->current_hits_per_user_and_type[$id_user][ID_BATTLESHIP] += 1;
        }
        $roll = $die->roll();
        if ($destroyer >= $roll) {
            $this->current_hits_per_user_and_type[$id_user][ID_CARRIER] += 1;
        }

        // 2. submarines (can hit carriers and battleships twice as good as destroyers, cant hit submarines)
        // can have one-shots for carriers and battleships, can do max 2 dmg to desroyer
        $subs = min(count($ships_per_type[ID_SUBMARINE]), 2);
        $roll = $die->roll();
        if ($subs >= $roll) {
            $this->current_hits_per_user_and_type[$id_user][ID_DESTROYER] += 2;
        } else if ($subs >= $roll / 2) {
            $this->current_hits_per_user_and_type[$id_user][ID_DESTROYER] += 1;
        }
        $roll = $die->roll();
        if ($subs >= $roll) {
            $this->current_hits_per_user_and_type[$id_user][ID_BATTLESHIP] += 100;
        } else if ($subs >= $roll / 2) {
            $this->current_hits_per_user_and_type[$id_user][ID_BATTLESHIP] += 4;
        } else if ($subs >= $roll / 3) {
            $this->current_hits_per_user_and_type[$id_user][ID_BATTLESHIP] += 2;
        } else if ($subs >= $roll / 4) {
            $this->current_hits_per_user_and_type[$id_user][ID_BATTLESHIP] += 1;
        }
        $roll = $die->roll();
        if ($subs >= $roll) {
            $this->current_hits_per_user_and_type[$id_user][ID_CARRIER] += 100;
        } else if ($subs >= $roll / 2) {
            $this->current_hits_per_user_and_type[$id_user][ID_CARRIER] += 4;
        } else if ($subs >= $roll / 3) {
            $this->current_hits_per_user_and_type[$id_user][ID_CARRIER] += 2;
        } else if ($subs >= $roll / 4) {
            $this->current_hits_per_user_and_type[$id_user][ID_CARRIER] += 1;
        }

        // 3. battleships (can hit destroyers better than carrier and battleships, cant hit submarines)
        // can do max 3 dmg per ship
        $battleships = min(count($ships_per_type[ID_BATTLESHIP]), 2);
        $roll = $die->roll();
        if ($battleships >= $roll) {
            $this->current_hits_per_user_and_type[$id_user][ID_DESTROYER] += 4;
        } else if ($battleships >= $roll / 2) {
            $this->current_hits_per_user_and_type[$id_user][ID_DESTROYER] += 2;
        } else if ($battleships >= $roll / 3) {
            $this->current_hits_per_user_and_type[$id_user][ID_DESTROYER] += 1;
        }
        $roll = $die->roll();
        if ($battleships >= $roll) {
            $this->current_hits_per_user_and_type[$id_user][ID_BATTLESHIP] += 3;
        } else if ($battleships >= $roll / 2) {
            $this->current_hits_per_user_and_type[$id_user][ID_BATTLESHIP] += 2;
        } else if ($battleships >= $roll / 3) {
            $this->current_hits_per_user_and_type[$id_user][ID_BATTLESHIP] += 1;
        }
        $roll = $die->roll();
        if ($battleships >= $roll) {
            $this->current_hits_per_user_and_type[$id_user][ID_CARRIER] += 3;
        } else if ($battleships >= $roll / 2) {
            $this->current_hits_per_user_and_type[$id_user][ID_CARRIER] += 2;
        } else if ($battleships >= $roll / 3) {
            $this->current_hits_per_user_and_type[$id_user][ID_CARRIER] += 1;
        }

        // 4. carriers (can hit carriers, battleships and destroyers twice as good as submarines)
        // can do max 2 dmg to sub, 4 dmg per other ships
        $carrier = min(count($ships_per_type[ID_CARRIER]), 2);
        $roll = $die->roll();
        if ($carrier >= $roll) {
            $this->current_hits_per_user_and_type[$id_user][ID_DESTROYER] += 4;
        } else if ($carrier >= $roll / 2) {
            $this->current_hits_per_user_and_type[$id_user][ID_DESTROYER] += 2;
        } else if ($carrier >= $roll / 3) {
            $this->current_hits_per_user_and_type[$id_user][ID_DESTROYER] += 1;
        }
        $roll = $die->roll();
        if ($carrier >= $roll) {
            $this->current_hits_per_user_and_type[$id_user][ID_SUBMARINE] += 2;
        } else if ($carrier >= $roll) {
            $this->current_hits_per_user_and_type[$id_user][ID_SUBMARINE] += 1;
        }
        $roll = $die->roll();
        if ($carrier >= $roll) {
            $this->current_hits_per_user_and_type[$id_user][ID_BATTLESHIP] += 4;
        } else if ($carrier >= $roll / 2) {
            $this->current_hits_per_user_and_type[$id_user][ID_BATTLESHIP] += 2;
        } else if ($carrier >= $roll / 3) {
            $this->current_hits_per_user_and_type[$id_user][ID_BATTLESHIP] += 1;
        }
        $roll = $die->roll();
        if ($carrier >= $roll) {
            $this->current_hits_per_user_and_type[$id_user][ID_CARRIER] += 4;
        } else if ($carrier >= $roll / 2) {
            $this->current_hits_per_user_and_type[$id_user][ID_CARRIER] += 2;
        } else if ($carrier >= $roll / 3) {
            $this->current_hits_per_user_and_type[$id_user][ID_CARRIER] += 1;
        }
    }

    private function applyHits($id_user, $user_hits_per_type) {
        foreach ($user_hits_per_type as $id_ship => $hits) {
            $enemyShips = $this->getAllEnemyShipsPerType($id_user, $id_ship);
            if (empty($enemyShips)) {
                continue;
            }
            $oneShots = floor($hits / 100);
            $dmg_per_round = floor(($hits % 100) / count($enemyShips));
            while ($hits > 0 && !empty($enemyShips)) {
                $dmg_to_allocate = $dmg_per_round;
                $currEnemyShips = array_shift($enemyShips);
                while (($dmg_to_allocate > 0 || $oneShots > 0) && !empty($currEnemyShips)) {
                    $ship = array_shift($currEnemyShips);
                    /** @var ModelGameShip $ship */
                    $ship_hitpoints = $ship->getHitpoints();
                    if ($oneShots > 0) {
                        $this->removeShipFromBattle($id_user, $ship);
                        --$oneShots;
                        $hits -= 100;
                        break; // do not apply further damage if one-shot applied
                    } else if ($ship_hitpoints === $dmg_to_allocate) {
                        $this->removeShipFromBattle($id_user, $ship);
                        $dmg_to_allocate = 0;
                    } else if ($ship_hitpoints > $dmg_to_allocate) {
                        $ship->setHitpoints($ship_hitpoints - $dmg_to_allocate);
                        $dmg_to_allocate = 0;
                        array_unshift($currEnemyShips, $ship);
                    } else {
                        $this->removeShipFromBattle($id_user, $ship);
                        $dmg_to_allocate -= $ship_hitpoints;
                    }
                }
                if (!empty($currEnemyShips)) {
                    $enemyShips[] = $currEnemyShips;
                }
                $hits -= ($dmg_per_round - $dmg_to_allocate);
            }
        }
    }

    private function removeShipFromBattle($id_user, ModelGameShip $ship) {
        $this->user_gets_experience[] = $id_user;
        $ship->setHitpoints(0);
        $id_user = $ship->getIdUser();
        $id_unit = $ship->getIdUnit();
        foreach ($this->ships_per_user_and_type[$id_user][$id_unit] as $key => $value) {
            if ($ship === $value) {
                unset($this->ships_per_user_and_type[$id_user][$id_unit][$key]);
            }
        }
        // check if user has at least one ship remaining in battle
        foreach ($this->ships_per_user_and_type[$id_user] as $id_unit => $array) {
            if (!empty($array)) {
                return;
            }
        }
        unset($this->ships_per_user_and_type[$id_user]);
    }

    private function getAllEnemyShipsPerType($id_user, $id_ship) {
        $ships = [];
        // 1. run through $this->ships_per_user_and_type
        foreach ($this->ships_per_user_and_type as $id_enemy_user => $user_ships) {
            if ($id_enemy_user === $id_user) {
                continue;
            }
            if (empty($user_ships[$id_ship])) {
                continue;
            }
            shuffle($user_ships[$id_ship]);
            $ships[] = $user_ships[$id_ship];
        }

        // 2. shuffle output
        shuffle($ships);
        return $ships;
    }

    private function shipsLeft() {
        // 1. check if enough user left
        if (count($this->ships_per_user_and_type) < 2) {
            return false;
        }

        // 2. check if other ships than subs available
        foreach ($this->ships_per_user_and_type as $id_user => $user_ships_per_type) {
            foreach ($user_ships_per_type as $id_ship => $user_ships) {
                if ($id_ship === ID_SUBMARINE) {
                    continue;
                }
                if (count($user_ships) > 0) {
                    return true;
                }
            }
        }

        return false;
    }

}
