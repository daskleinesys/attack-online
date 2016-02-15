<?php
namespace AttOn\View\Content\Operations;
use AttOn\Exceptions\ControllerException;
use AttOn\Model\Atton\InGame\ModelGameArea;
use AttOn\Model\Game\ModelGame;
use AttOn\Model\User\ModelInGamePhaseInfo;
use AttOn\Model\User\ModelIsInGameInfo;
use AttOn\Model\User\ModelUser;

class ContentSelectStart extends Interfaces\ContentOperation {

    private $id_set;
    private $regions;

    public function getTemplate() {
        return 'selectstart';
    }

    public function run(array &$data) {
        $data['template'] = $this->getTemplate();
        $this->addCurrentGameInfo($data);
        return;

        // get Model Data
        /** @var $iig ModelIsInGameInfo */
        $iig = ModelIsInGameInfo::getIsInGameInfo(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());
        $this->id_set = $iig->getIdStartingSet();
        $this->regions = ModelStartRegion::getRegionsForSet($this->id_set); // array(int id_opttype => array(int option_number => array(int id_area => ModelStartRegion)))

        // update moves
        if (isset($_POST['selectstart'])) {
            $this->selectOption($data, false);
        } elseif (isset($_POST['fixate_start'])) {
            $this->selectOption($data, true);
        }

        // parse moves
        $this->checkFixate($data);
        $this->parseOptions();
    }

    private function selectOption(array &$data, $fixate) {
        $moveController = new SelectStartController(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());

        foreach ($this->regions as $option_types) {
            foreach ($option_types as $option_number => $options) {
                if (isset($_POST['countries_' . $option_number])) {
                    try {
                        $moveController->selectStartAreas($this->id_set, $option_number, $_POST['countries_' . $option_number]);
                    } catch (ControllerException $ex) {
                        $data['errors'] = array(
                            'message' => $ex->getMessage()
                        );
                        return;
                    }
                }
            }
        }

        if ($fixate) {
            try {
                $moveController->finishMove();
            } catch (ControllerException $ex) {
                $data['errors'] = array(
                    'message' => $ex->getMessage()
                );
            }
        }
    }

    private function parseOptions() {
        foreach ($this->regions as $id_option_type => $option_types) {
            $optionType = ModelOptionType::getOptionType($id_option_type);
            $opttype = array();
            $opttype['units'] = $optionType->getUnits();
            $opttype['countries'] = $optionType->getCountries();

            foreach ($option_types as $option_number => $options) {
                $count = 0;
                foreach ($options as $id_area => $_StartRegion) {
                    $count++;

                    // get area infos
                    $area = ModelArea::getArea($id_area);
                    $country = array();
                    $country['id_area'] = $id_area;
                    $country['nr'] = $area->getNumber();
                    $country['name'] = $area->getName();

                    // check if country already selected
                    $gameArea = ModelGameArea::getGameAreaForArea(ModelGame::getCurrentGame()->getId(), $id_area);
                    $id_zarea = $gameArea->getId();
                    $modelMove = ModelSelectStartMove::getSelectStartMoveForUser(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());
                    $checked = ($modelMove->checkIfAreaIsSelected($option_number, $id_zarea)) ? 'checked' : '';
                }
            }
        }
    }

    private function checkFixate(array &$data) {
        $igpi = ModelInGamePhaseInfo::getInGamePhaseInfo(ModelUser::getCurrentUser()->getId(), ModelGame::getCurrentGame()->getId());
        $data['turnFixated'] = ($igpi->getIsReadyForPhase(PHASE_SELECTSTART) === 1) ? true : false;
    }

}
