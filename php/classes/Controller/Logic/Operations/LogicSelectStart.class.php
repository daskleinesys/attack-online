<?php
namespace Attack\Controller\Logic\Operations;

use Attack\Controller\Logic\Operations\Interfaces\PhaseLogic;
use Attack\Exceptions\LogicException;
use Attack\Model\Atton\InGame\ModelGameArea;
use Attack\Model\Atton\InGame\ModelInGameLandUnit;
use Attack\Model\Atton\InGame\Moves\ModelSelectStartMove;
use Attack\Model\Atton\ModelLandUnit;
use Attack\Model\Atton\ModelOptionType;
use Attack\Model\Atton\ModelStartRegion;
use Attack\Model\User\ModelIsInGameInfo;

class LogicSelectStart extends PhaseLogic {

	private $logger;

	/**
	 * returns object to run game logic -> should only be called by factory
	 * @param $id_game int
	 * @return LogicSelectStart
	 */
	public function __construct($id_game) {
		parent::__construct($id_game, PHASE_SELECTSTART);
		$this->logger = \Logger::getLogger('LogicSelectStart');
	}

	/**
	 * run the game logic
     *
     * @throws LogicException
	 * @return void
	 */
	public function run() {
		if (!$this->checkIfValid()) {
            throw new LogicException('Game '.$this->id_game.' not valid for processing.');
        }
		$this->startProcessing();

		try {
			// run through moves for each user
			$iter = ModelSelectStartMove::iterator($this->id_game);
			while ($iter->hasNext()) {
				// areas to select for user
				$selectStartMove = $iter->next();
				$regions_selected = $selectStartMove->getRegions(); // array(int option_number => array(int id_zarea))
				$id_user = $selectStartMove->getIdUser();
                /* @var $iigi ModelIsInGameInfo */
                $iigi = ModelIsInGameInfo::getIsInGameInfo($id_user, $this->id_game);
				$id_set = $iigi->getIdStartingSet();

				foreach ($regions_selected as $option_number => $areas) {
					$regions = ModelStartRegion::getRegionsForSetAndOption($id_set, $option_number); // array(int id_area => ModelStartRegion)

					foreach ($areas as $id_zarea) {
						$gameArea = ModelGameArea::getGameArea($this->id_game, $id_zarea);
						$id_area = $gameArea->getIdArea();
                        /* @var $region ModelStartRegion */
                        $region = $regions[$id_area];
                        $id_option = $region->getIdOptionType();
						$unit_count = ModelOptionType::getOptionType($id_option)->getUnits();

						// set user for game area
						$gameArea->setIdUser($id_user);

						// create units for user
						$iterUnits = ModelLandUnit::iterator();
						while ($iterUnits->hasNext()) {
							$landUnit = $iterUnits->next();
							$id_unit = $landUnit->getId();
							$inGameLandUnit = ModelInGameLandUnit::getModelByIdZAreaUserUnit($this->id_game, $id_zarea, $id_user, $id_unit);
							$inGameLandUnit->setCount($unit_count);
						}
					}
				}
			}

            // add units to all empty game-areas
            $iter = ModelGameArea::iterator(NEUTRAL_COUNTRY, $this->id_game);
            while ($iter->hasNext()) {
                /* @var $gameArea ModelGameArea */
                $gameArea = $iter->next();
                if ($gameArea->getIdType() !== TYPE_LAND) {
                    continue;
                }

                $count = $gameArea->getProductivity();
                if ($gameArea->getIdResource() === RESOURCE_OIL) {
                    ++$count;
                }
                $iterUnits = ModelLandUnit::iterator();
                while ($iterUnits->hasNext()) {
                    $landUnit = $iterUnits->next();
                    $id_unit = $landUnit->getId();
                    $inGameLandUnit = ModelInGameLandUnit::getModelByIdZAreaUserUnit($this->id_game, $gameArea->getId(), NEUTRAL_COUNTRY, $id_unit);
                    $inGameLandUnit->setCount($count);
                }
            }

			$this->finishProcessing();
		} catch (\Exception $ex) {
			$this->logger->fatal($ex);
			$this->rollback();
		}
	}

}
