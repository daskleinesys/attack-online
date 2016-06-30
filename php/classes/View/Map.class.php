<?php
namespace AttOn\View;

use AttOn\Exceptions\MapException;
use AttOn\Model\Atton\InGame\ModelInGameLandUnit;
use AttOn\Model\Atton\ModelLandUnit;
use AttOn\Model\Game\ModelGame;

class Map {

    public function run(array &$data) {
        $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        mysqli_set_charset($dbc, 'utf8');

        $id_game = ModelGame::getCurrentGame()->getId();

        $error_msg = NULL;

        $query = "SELECT status, id_phase FROM games WHERE id = $id_game";
        $gameinfo = mysqli_fetch_array(mysqli_query($dbc, $query));

        // running game (or newly started but countries are already picked)
        if (($gameinfo['status'] === GAME_STATUS_RUNNING) || (($gameinfo['status'] === GAME_STATUS_STARTED) && ((int)$gameinfo['id_phase'] === PHASE_SETSHIPS))) {
            $query = "SELECT areas.id, areas.number AS number, areas.name AS name, areas.coords_small AS coords, areas.x AS posleft, areas.y AS postop, areas.height AS height, areas.width AS width, areas.id_type AS area_type, areas.xres AS xres, areas.yres AS yres, " .
                "resources.name AS resource, resources.label AS res_label, zareas.productivity AS prod, user.id AS id_user, user.user AS user, user.color AS color " .
                "FROM z" . $id_game . "_areas AS zareas " .
                "LEFT JOIN resources AS resources ON (zareas.id_resource = resources.id) " .
                "LEFT JOIN areas AS areas ON (zareas.id_area = areas.id) " .
                "LEFT JOIN (" .
                "SELECT user.id AS id, user.login AS user, colors.color AS color " .
                "FROM is_in_game AS iig " .
                "LEFT JOIN user AS user ON (iig.id_user = user.id) " .
                "LEFT JOIN colors AS colors ON (colors.id = iig.id_color) " .
                "WHERE iig.id_game = $id_game" .
                ") AS user ON (zareas.id_user = user.id)";
        } // newly started game countries have to be picked
        else if (($gameinfo['status'] === GAME_STATUS_STARTED) && ((int)$gameinfo['id_phase'] === PHASE_SELECTSTART)) {
            $query = "SELECT areas.number AS number, areas.name AS name, areas.coords_small AS coords, areas.x AS posleft, areas.y AS postop, areas.height AS height, areas.width AS width, areas.id_type AS area_type, areas.xres AS xres, areas.yres AS yres, " .
                "resources.name AS resource, resources.label AS res_label, zareas.productivity AS prod, " .
                "start.*" .
                "FROM z" . $id_game . "_areas AS zareas " .
                "LEFT JOIN resources AS resources ON (zareas.id_resource = resources.id) " .
                "LEFT JOIN areas AS areas ON (zareas.id_area = areas.id) " .
                "LEFT JOIN (" .
                "SELECT user.login AS user, colors.color AS color, startreg.id_area, startreg.options AS countrySelectOption, optypes.units AS countrySelectUnitCount, optypes.countries AS countrySelectCount " .
                "FROM is_in_game AS iig " .
                "LEFT JOIN user AS user ON (iig.id_user = user.id) " .
                "LEFT JOIN colors AS colors ON (colors.id = iig.id_color) " .
                "INNER JOIN startregions AS startreg ON (startreg.id_set = iig.id_set) " .
                "LEFT JOIN optiontypes AS optypes ON (optypes.id = startreg.id_optiontype) " .
                "WHERE iig.id_game = $id_game" .
                ") AS start ON (zareas.id_area = start.id_area)";
        } // game not in valid phase
        else {
            throw new MapException('invalid game selected: ' . $id_game);
        }

        $result = mysqli_query($dbc, $query);
        $countryData = array();
        while ($country = mysqli_fetch_array($result)) {
            if (!array_key_exists('countrySelectOption', $country)) {
                $unitCount = 0;
                $id_user = (int)$country['id_user'];
                if ($id_user <= 0) {
                    $id_user = NEUTRAL_COUNTRY;
                }
                $units = ModelInGameLandUnit::getUnitsByIdZAreaUser($id_game, (int)$country['id'], $id_user);
                $unitsViewData = array();
                /* @var $unit ModelInGameLandUnit */
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
            }

            $countryData[] = $country;
        }
        $data['countryData'] = $countryData;

        return $data;
    }

}
