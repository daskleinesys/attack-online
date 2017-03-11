<?php

namespace Attack\View;

use Attack\Exceptions\MapException;
use Attack\Model\Game\ModelGameArea;
use Attack\Model\Game\ModelGameLandUnit;
use Attack\Model\Game\ModelGameShip;
use Attack\Model\Game\ModelTradeRoute;
use Attack\Model\Units\ModelLandUnit;
use Attack\Model\Units\ModelShip;
use Attack\Database\SQLConnector;
use Attack\Model\Game\ModelGame;
use Attack\Model\User\ModelUser;

class Map {

    public function run(array &$data) {
        $game = ModelGame::getCurrentGame();
        $id_game = $game->getId();

        // running game (or newly started but countries are already picked)
        if (($game->getStatus() === GAME_STATUS_RUNNING) || (($game->getStatus() === GAME_STATUS_STARTED) && ($game->getIdPhase() === PHASE_SETSHIPS))) {
            $query = 'get_map_for_running_game';
        } // newly started game countries have to be picked
        else if (($game->getStatus() === GAME_STATUS_STARTED) && ($game->getIdPhase() === PHASE_SELECTSTART)) {
            $query = 'get_map_for_new_game';
        } // game not in valid phase
        else {
            throw new MapException('invalid game selected: ' . $id_game);
        }
        $result = SQLConnector::getInstance()->epp($query, [':id_game' => $id_game]);

        $countryData = array();
        foreach ($result as $country) {
            // newly started game countries have to be picked -> no landunits/ships available
            if (array_key_exists('countrySelectOption', $country)) {
                $countryData[] = $country;
                continue;
            }

            // running game (or newly started but countries are already picked)
            // check landunits
            $unitCount = 0;
            $id_user = (int)$country['id_user'];
            if ($id_user === 0) {
                $id_user = NEUTRAL_COUNTRY;
            }
            $units = ModelGameLandUnit::getUnitsByIdGameAreaUser($id_game, (int)$country['id_game_area'], $id_user);
            $unitsViewData = array();
            /* @var $unit ModelGameLandUnit */
            foreach ($units as $unit) {
                $unitCount += $unit->getCount();
                $landUnit = ModelLandUnit::getModelById($unit->getIdUnit());
                $unitViewData = array(
                    'name' => $landUnit->getName(),
                    'count' => $unit->getCount()
                );
                $unitsViewData[] = $unitViewData;
            }
            if ($unitCount > 0) {
                $country['units'] = $unitsViewData;
            }
            $country['unitCount'] = $unitCount;

            // check ships
            $shipCount = 0;
            $shipViewData = array();
            if ((int)$country['area_type'] === TYPE_LAND) {
                $ships = ModelGameShip::getShipsInPort($id_game, (int)$country['id_game_area']);
            } else {
                $ships = ModelGameShip::getShipsInAreaNotInPort($id_game, (int)$country['id_game_area']);
            }
            while ($ships->hasNext()) {
                /* @var $ship ModelGameShip */
                $ship = $ships->next();
                $id_ship_owner = $ship->getIdUser();
                if (!isset($shipViewData[$id_ship_owner])) {
                    $shipViewData[$id_ship_owner] = array(
                        'username' => ModelUser::getUser($id_ship_owner)->getLogin(),
                        'ships' => array()
                    );
                }
                $shipType = ModelShip::getModelById($ship->getIdUnit());
                $currShipViewData = array(
                    'name' => $ship->getName(),
                    'type' => $shipType->getName(),
                    'diveStatus' => $ship->getDiveStatus(),
                    'experience' => $ship->getExperience()
                );
                if ((int)$country['area_type'] === TYPE_LAND) {
                    $portToArea = ModelGameArea::getGameArea($id_game, $ship->getIdGameArea());
                    $currShipViewData['port'] = $portToArea->getName();
                    $currShipViewData['portNumber'] = $portToArea->getNumber();
                }
                $shipViewData[$id_ship_owner]['ships'][] = $currShipViewData;
                ++$shipCount;
            }
            if ($shipCount > 0) {
                $country['ships'] = $shipViewData;
            }
            $country['shipCount'] = $shipCount;

            $countryData[$country['id_game_area']] = $country;
        }

        // check traderoutes
        if ($game->getStatus() === GAME_STATUS_RUNNING) {
            $iterator = ModelTradeRoute::iterator(null, $id_game);
            while ($iterator->hasNext()) {
                /** @var ModelTradeRoute $traderoute */
                $traderoute = $iterator->next();
                $steps = $traderoute->getSteps();
                $startArea = ModelGameArea::getGameArea($id_game, $steps[0]);
                $targetArea = ModelGameArea::getGameArea($id_game, end($steps));
                foreach ($traderoute->getSteps() as $id_game_area) {
                    $countryData[$id_game_area]['traderoute'] = [
                        'start_area' => [
                            'number' => $startArea->getNumber(),
                            'name' => $startArea->getName()
                        ],
                        'target_area' => [
                            'number' => $targetArea->getNumber(),
                            'name' => $targetArea->getName()
                        ],
                        'current_value' => $traderoute->getCurrentValue()
                    ];
                }
            }
        }


        $data['countryData'] = $countryData;

        return $data;
    }

}
