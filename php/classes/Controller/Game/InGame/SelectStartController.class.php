<?php
namespace AttOn\Controller\Game\InGame;

use AttOn\Controller\Interfaces\PhaseController;
use AttOn\Exceptions\ControllerException;
use AttOn\Model\Atton\InGame\ModelGameArea;
use AttOn\Model\Atton\InGame\Moves\ModelSelectStartMove;
use AttOn\Model\Atton\ModelOptionType;
use AttOn\Model\Atton\ModelStartRegion;
use AttOn\Model\User\ModelIsInGameInfo;

class SelectStartController extends PhaseController {

    private $error = false;

    /**
     * @param int $id_user - id of the user accessing the moderation actions
     * @param int $id_game - id of currently selected game
     * @return SelectStartController
     */
    public function __construct($id_user, $id_game) {
        parent::__construct($id_user, $id_game, PHASE_SELECTSTART);
    }

    /**
     * user selected areas
     *
     * @param $id_set int
     * @param $option_number int
     * @param $areas array(int id_area)
     * @throws ControllerException
     * @return void
     */
    public function selectStartAreas($id_set, $option_number, $areas) {
        if ($this->error) {
            return;
        }

        // check if user has already finished this move
        if ($this->checkIfDone()) {
            $this->error = true;
            throw new ControllerException('You have already finished this move.');
        }

        // check if regions are in the set option
        $supported_areas = ModelStartRegion::getRegionsForSetAndOption($id_set, $option_number);
        foreach ($areas as $id_area) {
            if (!isset($supported_areas[$id_area])) {
                $this->error = true;
                throw new ControllerException('The area ' . $id_area . ' is not in your set and option-number.');
            }
        }

        // check if correct number of areas selected
        $startRegion = array_shift($supported_areas);
        $id_option_type = $startRegion->getIdOptionType();
        $optionType = ModelOptionType::getOptionType($id_option_type);
        if ($optionType->getCountries() !== count($areas)) {
            $this->error = true;
            throw new ControllerException('Please choose the correct amount of areas.');
        }

        // insert
        $move = ModelSelectStartMove::getSelectStartMoveForUser($this->id_user, $this->id_game);
        $zareas = array();
        foreach ($areas as $id_area) {
            $gameArea = ModelGameArea::getGameAreaForArea($this->id_game, $id_area);
            $zareas[] = $gameArea->getId();
        }
        $move->setRegions($option_number, $zareas);
    }

    /**
     * fixates the move if no error occured
     *
     * @throws ControllerException
     * @return void
     */
    public function finishMove() {
        if ($this->error) {
            return;
        }
        // check if every option has taken countries
        $move = ModelSelectStartMove::getSelectStartMoveForUser($this->id_user, $this->id_game);
        $regions_selected = $move->getRegions(); // array(int option_number => array(int id_zarea))

        // get Model Data
        /** @var $iig ModelIsInGameInfo */
        $iig = ModelIsInGameInfo::getIsInGameInfo($this->id_user, $this->id_game);
        $id_set = $iig->getIdStartingSet();
        $regions = ModelStartRegion::getRegionsForSet($id_set); // array(int id_opttype => array(int option_number => array(int id_area => ModelStartRegion)))

        foreach ($regions as $opttype) {
            foreach ($opttype as $opt_number => $areas) {
                if (!isset($regions_selected[$opt_number])) {
                    throw new ControllerException('Choose countries first.');
                }
            }
        }

        $this->fixatePhase(true);
    }

}
