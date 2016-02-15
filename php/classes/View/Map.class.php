<?php
namespace AttOn\View;
use AttOn\Exceptions\MapException;
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
        if (($gameinfo['status'] === GAME_STATUS_RUNNING) || (($gameinfo['status'] === GAME_STATUS_STARTED) && ((int) $gameinfo['id_phase'] === PHASE_SETSHIPS))) {
            die('todo: adapt this to map.twig');
            $query = "SELECT areas.id AS ctrnr, areas.name AS country, areas.coords_small AS coords, areas.x AS posleft, areas.y AS postop, areas.height AS height, areas.width AS width, user.login AS user, user.id AS id_area_user, " .
            "resources.name AS resource, zareas.productivity AS prod, zareas.tank AS tank, units.name AS unit, zunits_land.count AS unr, zunits_sea.name AS shipname, zunits_harbor.id_zarea AS coastnr, " .
            "coasts.name AS coast, colors.name AS color, units.id_type AS unit_type, iig.id_game AS id_game, units.id AS id_unit, areas.id_type AS areas_type, " .
            "areas.xres AS xres, areas.yres AS yres, TR.id AS id_TR, TR.id_area1 AS TR_area1, TR_area1.name AS TR_area1_name, TR.id_area2 AS TR_area2, TR_area2.name AS TR_area2_name, " .
            "resources.label AS res_label, " .
            "a2a.id_area2 AS id_adjacent " .
            "FROM z" . $id_game . "_areas AS zareas " .
            "LEFT JOIN resources AS resources ON (zareas.id_resource=resources.id) " .
            "LEFT JOIN z" . $id_game . "_units AS zunits ON (zunits.id_zarea=zareas.id) " .
            "LEFT JOIN z" . $id_game . "_units_land AS zunits_land ON (zunits.id=zunits_land.id_zunit) " .
            "LEFT JOIN z" . $id_game . "_units_sea AS zunits_sea ON (zunits.id=zunits_sea.id_zunit) " .
            "LEFT JOIN z" . $id_game . "_units_in_harbor AS zunits_harbor ON (zunits.id=zunits_harbor.id_zunit) " .
            "LEFT JOIN areas AS areas ON (zareas.id_area=areas.id) " .
            "LEFT JOIN areas AS coasts ON (zunits_harbor.id_zarea=coasts.id) " .
            "LEFT JOIN units AS units ON (zunits.id_unit=units.id) " .
            "LEFT JOIN user AS user ON (zareas.id_user=user.id) " .
            "LEFT JOIN is_in_game AS iig ON (zareas.id_user=iig.id_user) " .
            "LEFT JOIN colors AS colors ON (colors.id=iig.id_color) " .
            "LEFT JOIN z" . $id_game . "_traderoutes AS zTR ON (zareas.id_user=zTR.id_user) " .
            "LEFT JOIN traderoutes AS TR ON (TR.id=zTR.id_traderoute1) " .
            "LEFT JOIN areas AS TR_area1 ON (TR.id_area1=TR_area1.id) " .
            "LEFT JOIN areas AS TR_area2 ON (TR.id_area2=TR_area2.id) " .
            "LEFT JOIN a2a AS a2a ON (areas.id=a2a.id_area1) " .
            "WHERE iig.id_game = $id_game OR iig.id_game IS NULL " .
            "ORDER BY areas.id, coasts.name, zunits.id_unit ASC, zunits_sea.id_zunit ASC, zunits.id_user ASC";
        }
        // newly started game countries have to be picked
        else if (($gameinfo['status'] === GAME_STATUS_STARTED) && ((int) $gameinfo['id_phase'] === PHASE_SELECTSTART)) {
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
        }
        // game not in valid phase
        else {
            throw new MapException('invalid game selected: ' . $id_game);
        }

        $result = mysqli_query($dbc, $query);
        $countryData = array();
        while ($country = mysqli_fetch_array($result)) {
            $countryData[] = $country;
        }
        $data['countryData'] = $countryData;

        return $data;
    }

}
