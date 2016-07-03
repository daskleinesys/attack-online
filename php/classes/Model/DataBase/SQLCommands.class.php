<?php
namespace AttOn\Model\DataBase;

class SQLCommands {

    /**
     * @var $DataSource DataSource
     */
    private static $DataSource = null;
    private static $id_game = null;

    /**
     * load predefined SQL commands into DataSource class
     *
     * @param int $id_game
     * @return void
     */
    public static function init($id_game = null) {

        if (self::$DataSource === null) {
            self::$DataSource = DataSource::getInstance();
            self::LoadUserQueries();
            self::LoadGameQueries();
        }

        // clear all prepared statements (reset PDO object) if a new game_id is given
        if ($id_game !== null && $id_game !== self::$id_game && is_int($id_game)) {
            DataSource::getInstance()->reset_game_specific_queries();
            self::$id_game = $id_game;
            self::LoadGameSpecificQueries(self::$id_game);
        }

    }

    private static function LoadUserQueries() {

        // user
        self::$DataSource->load_query('check_user_password', "SELECT id FROM user WHERE id = :id_user AND password = SHA(:password)");
        self::$DataSource->load_query('get_user_login', "SELECT id,login FROM user WHERE id = :id_user LIMIT 1");
        self::$DataSource->load_query('check_user_login', "SELECT id, login, status FROM user WHERE login = :username AND password = SHA(:password)");
        self::$DataSource->load_query('check_user_token', "SELECT id, login, status FROM user WHERE token = :token LIMIT 1");
        self::$DataSource->load_query('get_all_user_data', "SELECT id, name, lastname, login, email, status, verify, token FROM user WHERE id = :id_user");
        self::$DataSource->load_query('get_all_users', "SELECT id FROM user ORDER BY id ASC");
        self::$DataSource->load_query('get_all_users_asc', "SELECT id FROM user ORDER BY id ASC");
        self::$DataSource->load_query('get_all_users_desc', "SELECT id FROM user ORDER BY id DESC");
        self::$DataSource->load_query('get_all_users_ordered_login_asc', "SELECT id FROM user ORDER BY login ASC");
        self::$DataSource->load_query('get_all_users_ordered_login_desc', "SELECT id FROM user ORDER BY login DESC");
        self::$DataSource->load_query('get_all_users_ordered_name_asc', "SELECT id FROM user ORDER BY name ASC");
        self::$DataSource->load_query('get_all_users_ordered_name_desc', "SELECT id FROM user ORDER BY name DESC");
        self::$DataSource->load_query('get_all_users_ordered_lastname_asc', "SELECT id FROM user ORDER BY lastname ASC");
        self::$DataSource->load_query('get_all_users_ordered_lastname_desc', "SELECT id FROM user ORDER BY lastname DESC");
        self::$DataSource->load_query('get_all_users_ordered_email_asc', "SELECT id FROM user ORDER BY email ASC");
        self::$DataSource->load_query('get_all_users_ordered_email_desc', "SELECT id FROM user ORDER BY email DESC");
        self::$DataSource->load_query('get_all_users_ordered_status_asc', "SELECT id FROM user ORDER BY status ASC");
        self::$DataSource->load_query('get_all_users_ordered_status_desc', "SELECT id FROM user ORDER BY status DESC");

        // iterator
        $ordered = array(SORT_BY_ID, SORT_BY_NAME, SORT_BY_LASTNAME, SORT_BY_LOGIN, SORT_BY_EMAIL, SORT_BY_STATUS);
        foreach ($ordered as $key) {
            self::$DataSource->load_query('get_users_ord_' . $key . '_asc', 'SELECT id FROM user WHERE status LIKE :status ORDER BY ' . $key . ' ASC');
            self::$DataSource->load_query('get_users_ord_' . $key . '_desc', 'SELECT id FROM user WHERE status LIKE :status ORDER BY ' . $key . ' DESC');
            self::$DataSource->load_query('get_users_for_game_ord_' . $key . '_asc', 'SELECT u.id FROM user u, is_in_game iig WHERE u.id = iig.id_user AND iig.id_game = :id_game AND status LIKE :status ORDER BY u.' . $key . ' ASC');
            self::$DataSource->load_query('get_users_for_game_ord_' . $key . '_desc', 'SELECT u.id FROM user u, is_in_game iig WHERE u.id = iig.id_user AND iig.id_game = :id_game AND status LIKE :status ORDER BY u.' . $key . ' DESC');
        }

        // manipulation
        self::$DataSource->load_query('activate_user', "UPDATE user SET status = 'active' WHERE id = :id_user");
        self::$DataSource->load_query('deactivate_user', "UPDATE user SET status = 'inactive' WHERE id = :id_user");
        self::$DataSource->load_query('set_user_to_moderator', "UPDATE user SET status = 'moderator' WHERE id = :id_user");
        self::$DataSource->load_query('set_user_to_admin', "UPDATE user SET status = 'admin' WHERE id = :id_user");
        self::$DataSource->load_query('set_user_to_deleted', "UPDATE user SET status = 'deleted' WHERE id = :id_user");
        self::$DataSource->load_query('update_email', "UPDATE user SET email = :email WHERE id = :id_user");
        self::$DataSource->load_query('update_password', "UPDATE user SET password = SHA(:password) WHERE id = :id_user");
        self::$DataSource->load_query('update_token', "UPDATE user SET token = :token WHERE id = :id_user");

        // register new user
        self::$DataSource->load_query('check_if_login_exists', 'SELECT id FROM user WHERE login = :login');
        self::$DataSource->load_query('check_if_email_exists', 'SELECT id FROM user WHERE email = :email');
        self::$DataSource->load_query('get_id_for_login', 'SELECT id FROM user WHERE login = :login');
        self::$DataSource->load_query('create_new_user', "INSERT INTO user (name, lastname, login, password, email, verify) VALUES (:name, :lastname, :login, SHA(:password), :email, SHA(:verify))");
        self::$DataSource->load_query('get_verification_code_for_login', "SELECT id, verify FROM user WHERE login = :login");
        self::$DataSource->load_query('get_verification_code_for_id', "SELECT id,verify FROM user WHERE id = :id_user");

        // is_in_game
        // query infos
        self::$DataSource->load_query('get_iig_ids', "SELECT id, id_user, id_game FROM is_in_game WHERE id_user LIKE :id_user AND id_game LIKE :id_game ORDER BY id_game, id_user ASC");
        self::$DataSource->load_query('get_iig_info_for_user_in_game', "SELECT id, id_user, id_game, id_color, money, id_set FROM is_in_game WHERE id_game = :id_game AND id_user = :id_user");
        //query user
        self::$DataSource->load_query('get_all_user_ids_for_game', "SELECT id_user FROM is_in_game WHERE id_game = :id_game");
        self::$DataSource->load_query('get_all_games_for_user', "SELECT games.id, games.name, games.players, games.id_creator, games.status, games.id_phase, games.round, games.processing FROM games RIGHT JOIN is_in_game AS iig ON(games.id=iig.id_game) WHERE iig.id_user = :id_user");
        self::$DataSource->load_query('get_participating_user', "SELECT id_user FROM is_in_game WHERE id_game = :id_game");
        self::$DataSource->load_query('get_money_for_user', "SELECT money FROM is_in_game AS iig WHERE id_user = :id_user AND id_game = :id_game");
        self::$DataSource->load_query('get_user_status', 'SELECT status FROM user WHERE id = :id_user');
        self::$DataSource->load_query('check_if_user_created_a_game', 'SELECT id FROM games WHERE id_creator = :id_user');
        self::$DataSource->load_query('check_if_user_is_in_a_game', 'SELECT id FROM is_in_game WHERE id_user = :id_user');
        self::$DataSource->load_query('get_iig_info_for_user', "SELECT id_game, id_color, money, id_set FROM is_in_game WHERE id_user = :id_user");
        // update user
        self::$DataSource->load_query('update_user_color_for_game', "UPDATE is_in_game SET id_color = :id_color WHERE id_user = :id_user AND id_game = :id_game LIMIT 1");
        self::$DataSource->load_query('set_money_for_user', "UPDATE is_in_game SET money = :money WHERE id_user = :id_user AND id_game = :id_game");
        self::$DataSource->load_query('set_starting_set_for_user', "UPDATE is_in_game SET id_set = :id_set WHERE id_user = :id_user AND id_game = :id_game");
        // in-game-phase-info
        self::$DataSource->load_query('update_ingame_notification_rule', "UPDATE in_game_phase_info SET notif_rule = :rule WHERE id_user = :id_user AND id_phase = :id_phase AND id_game = :id_game");
        self::$DataSource->load_query('update_ingame_is_ready', "UPDATE in_game_phase_info SET is_ready = :is_ready WHERE id_user = :id_user AND id_phase = :id_phase AND id_game = :id_game");
        self::$DataSource->load_query('get_game_phase_info', "SELECT id_user, id_game, id_phase, notif_rule, is_ready FROM in_game_phase_info WHERE id_user = :id_user AND id_game = :id_game AND id_phase = :id_phase LIMIT 1");
        self::$DataSource->load_query('set_new_game_phase_info', "INSERT INTO in_game_phase_info (id_user, id_phase, id_game, notif_rule, is_ready) VALUES (:id_user, :id_phase, :id_game, :rule, :is_ready)");
        self::$DataSource->load_query('delete_game_phase_info_for_game', "DELETE FROM in_game_phase_info WHERE id_game = :id_game");
        self::$DataSource->load_query('delete_game_phase_info_for_user', "DELETE FROM in_game_phase_info WHERE id_game = :id_game AND id_user = :id_user");

        // join/leave game
        self::$DataSource->load_query('leave_game_for_user', "DELETE FROM is_in_game WHERE id_user = :id_user AND id_game = :id_game");
        self::$DataSource->load_query('join_game', "INSERT INTO is_in_game (id_user, id_game, id_color) VALUES (:id_user, :id_game, :id_color)");
        self::$DataSource->load_query('delete_iig_info_for_game', "DELETE FROM is_in_game WHERE id_game = :id_game");
        self::$DataSource->load_query('delete_iig_info_for_user', "DELETE FROM is_in_game WHERE id_game = :id_game AND id_user = :id_user");
    }

    private static function LoadGameQueries() {
        // check which games are ready for processing
        self::$DataSource->load_query('get_games_rdy', "SELECT g.id FROM games g
			WHERE (g.status = 'running' OR g.status = 'started')
			AND 0 NOT IN (SELECT is_ready FROM in_game_phase_info WHERE id_game = g.id AND id_phase = g.id_phase)");
        self::$DataSource->load_query('get_games_rdy_v2', "SELECT g.id FROM games g
			JOIN (SELECT id_game, count(id_user) AS players FROM is_in_game GROUP BY id_game) pl ON (pl.id_game = g.id)
			JOIN (SELECT id_game, id_phase, count(id_user) AS players_done FROM in_game_phase_info WHERE is_ready = 1 GROUP BY id_game, id_phase) pl_dn ON (pl_dn.id_game = g.id AND pl_dn.id_phase = g.id_phase)
			WHERE pl.players = pl_dn.players_done AND g.processing = 0");

        // update general game inf
        self::$DataSource->load_query('delete_game_password', "UPDATE games SET password = NULL WHERE id = :id_game");
        self::$DataSource->load_query('update_game_password', "UPDATE games SET password = SHA(:password) WHERE id = :id_game");
        self::$DataSource->load_query('set_game_status', "UPDATE games SET status = :status WHERE id = :id_game");

        // delete game
        self::$DataSource->load_query('delete_game_iig', "DELETE FROM is_in_game WHERE id_game = :id_game");
        self::$DataSource->load_query('delete_game', "DELETE FROM games WHERE id = :id_game");

        // join game
        self::$DataSource->load_query('check_game_password', "SELECT id FROM games WHERE id = :id_game AND password = SHA(:password)");

        // new game
        self::$DataSource->load_query('create_game_without_pw', "INSERT INTO games (name, players, id_creator) VALUES (:game_name, :players, :id_creator)");
        self::$DataSource->load_query('create_game_with_pw', "INSERT INTO games (name, players, id_creator, password) VALUES (:game_name, :players, :id_creator, SHA(:password))");
        self::$DataSource->load_query('get_starting_sets', "SELECT id,name,players FROM startsets WHERE players LIKE :players");
        self::$DataSource->load_query('get_starting_set', "SELECT id,name,players FROM startsets WHERE id = :id_set");

        // games
        self::$DataSource->load_query('get_all_game_ids', "SELECT id FROM games ORDER BY id ASC");
        self::$DataSource->load_query('get_done_game_ids', "SELECT id FROM games WHERE status = '" . GAME_STATUS_DONE . "' ORDER BY id ASC");
        self::$DataSource->load_query('get_new_game_ids', "SELECT id FROM games WHERE status = '" . GAME_STATUS_NEW . "' ORDER BY id ASC");
        self::$DataSource->load_query('get_started_game_ids', "SELECT id FROM games WHERE status = '" . GAME_STATUS_STARTED . "' ORDER BY id ASC");
        self::$DataSource->load_query('get_running_game_ids', "SELECT id FROM games WHERE status = '" . GAME_STATUS_RUNNING . "' ORDER BY id ASC");

        self::$DataSource->load_query('get_all_game_ids_for_user', "SELECT id_game AS id FROM is_in_game WHERE id_user = :id_user ORDER BY id_game ASC");
        self::$DataSource->load_query('get_done_game_ids_for_user', "SELECT iig.id_game AS id FROM is_in_game AS iig LEFT JOIN games ON (games.id = iig.id_game) WHERE games.status = '" . GAME_STATUS_DONE . "' AND iig.id_user = :id_user ORDER BY iig.id_game ASC");
        self::$DataSource->load_query('get_new_game_ids_for_user', "SELECT iig.id_game AS id FROM is_in_game AS iig LEFT JOIN games ON (games.id = iig.id_game) WHERE games.status = '" . GAME_STATUS_NEW . "' AND iig.id_user = :id_user ORDER BY iig.id_game ASC");
        self::$DataSource->load_query('get_started_game_ids_for_user', "SELECT iig.id_game AS id FROM is_in_game AS iig LEFT JOIN games ON (games.id = iig.id_game) WHERE games.status = '" . GAME_STATUS_STARTED . "' AND iig.id_user = :id_user ORDER BY iig.id_game ASC");
        self::$DataSource->load_query('get_running_game_ids_for_user', "SELECT iig.id_game AS id FROM is_in_game AS iig LEFT JOIN games ON (games.id = iig.id_game) WHERE games.status = '" . GAME_STATUS_RUNNING . "' AND iig.id_user = :id_user ORDER BY iig.id_game ASC");

        // traderoute info
        //query
        self::$DataSource->load_query('get_traderoute_production_for_value', "SELECT production FROM traderoutes_production WHERE value = :value LIMIT 1");

        // game info
        // query
        self::$DataSource->load_query('check_game_name', 'SELECT id FROM games WHERE name = :name');
        self::$DataSource->load_query('get_full_game_info', 'SELECT id,name,players,id_creator,password,status,id_phase,round,processing FROM games WHERE id = :id_game');
        self::$DataSource->load_query('get_game_status', 'SELECT status FROM games WHERE id = :id_game');
        self::$DataSource->load_query('get_processing_state', "SELECT processing FROM games WHERE id = :id_game LIMIT 1");
        self::$DataSource->load_query('get_round_phase_info_id_game', "SELECT games.id_phase AS id_phase, games.round, phases.name AS phase, phases.label, games.name AS name, games.status FROM games " .
            "LEFT JOIN phases AS phases ON (games.id_phase=phases.id) " .
            "WHERE games.id = :id_game");
        // update
        self::$DataSource->load_query('set_game_phase', "UPDATE games SET id_phase = :id_phase WHERE id = :id_game");
        self::$DataSource->load_query('set_game_round', "UPDATE games SET round = :round WHERE id = :id_game");
        self::$DataSource->load_query('set_game_processing', "UPDATE games SET processing = 1 WHERE id = :id_game");
        self::$DataSource->load_query('set_game_processing_done', "UPDATE games SET processing = 0 WHERE id = :id_game");

        // phase info
        self::$DataSource->load_query('get_phase_info', "SELECT id,name,label,id_type FROM phases WHERE id = :id_phase LIMIT 1");
        self::$DataSource->load_query('get_all_phases', "SELECT id,name,label FROM phases ORDER BY id ASC");
        self::$DataSource->load_query('get_phase_name', "SELECT name FROM phases WHERE id = :id_phase");

        // a2a
        self::$DataSource->load_query('get_a2a', "SELECT id_area2 AS id_adjacent_area FROM a2a WHERE id_area1 = :id_area");

        // area info
        self::$DataSource->load_query('get_all_area_ids', "SELECT id FROM areas");
        self::$DataSource->load_query('get_areas_for_type', "SELECT id FROM areas WHERE id_type LIKE :id_type");
        self::$DataSource->load_query('get_all_area_info', "SELECT id,name,number,coords_small,x,y,x2,y2,xres,yres,height,width,tanksize,id_type,zone,economy FROM areas WHERE id = :id_area");
        self::$DataSource->load_query('get_area_name', "SELECT id,name FROM areas WHERE id = :id_area LIMIT 1");
        self::$DataSource->load_query('get_area_infos', 'SELECT id AS id, number, tanksize, id_type FROM areas WHERE id = :id_area LIMIT 1');
        self::$DataSource->load_query('get_area_type', "SELECT id_type FROM areas WHERE id = :id_area LIMIT 1");
        self::$DataSource->load_query('get_adjacent_areas', "SELECT id_area2 AS id_area FROM a2a WHERE id_area1 = :id_area");

        // color info
        self::$DataSource->load_query('get_color', "SELECT id,name,color FROM colors WHERE id = :id_color");
        self::$DataSource->load_query('get_colors', "SELECT id,name,color FROM colors");
        self::$DataSource->load_query('get_all_colors', "SELECT id FROM colors");
        self::$DataSource->load_query('check_free_color', "SELECT id FROM is_in_game WHERE id_game = :id_game AND id_color = :id_color");
        self::$DataSource->load_query('get_free_colors_for_game', "SELECT id AS id, color AS color FROM colors WHERE id NOT IN (SELECT id_color FROM is_in_game WHERE id_game= :id_game)");

        // ressource info
        self::$DataSource->load_query('get_resources', "SELECT id FROM resources");
        self::$DataSource->load_query('get_resource_label', "SELECT label FROM resources WHERE id = :id_resource");
        self::$DataSource->load_query('get_resource_info', "SELECT id,name,label,id_type WHERE id = :id_resource");
        self::$DataSource->load_query('get_resource_allocation', "SELECT id, id_resource, res_power, economy, count FROM resource_allocation WHERE economy = :economy");

        // select-start
        self::$DataSource->load_query('get_startregions_for_set', "SELECT id,id_area,id_optiontype,id_set,options FROM startregions WHERE id_set = :id_set ORDER BY options ASC");
        self::$DataSource->load_query('get_option_types', "SELECT id,units,countries FROM optiontypes");

        // unit info
        self::$DataSource->load_query('get_land_unit', "SELECT id, name, abbreviation, price, speed, killing_sequence, kill_sequence_offset, ship_takeover, id_type FROM units WHERE id = :id_unit");
        self::$DataSource->load_query('get_all_land_units', "SELECT id FROM units WHERE id_type = " . TYPE_LAND . " OR id_type = " . TYPE_AIR);
        self::$DataSource->load_query('get_ship', "SELECT id, name, abbreviation, price, speed, tanksize, hitpoints, id_type FROM units WHERE id = :id_unit");
        self::$DataSource->load_query('get_all_ships', "SELECT id FROM units WHERE id_type = " . TYPE_SEA);
    }

    private static function LoadGameSpecificQueries($id_game) {

        // create tables
        self::$DataSource->load_query('create_areas_table', "CREATE TABLE z" . $id_game . "_areas LIKE z_areas", true);
        self::$DataSource->load_query('create_moves_table', "CREATE TABLE z" . $id_game . "_moves LIKE z_moves", true);
        self::$DataSource->load_query('create_moves_areas_table', "CREATE TABLE z" . $id_game . "_moves_areas LIKE z_moves_areas", true);
        self::$DataSource->load_query('create_moves_units_table', "CREATE TABLE z" . $id_game . "_moves_units LIKE z_moves_units", true);
        self::$DataSource->load_query('create_units_table', "CREATE TABLE z" . $id_game . "_units LIKE z_units", true);

        // drop tables
        self::$DataSource->load_query('drop_areas_table', "DROP TABLE z" . $id_game . "_areas", true);
        self::$DataSource->load_query('drop_moves_table', "DROP TABLE z" . $id_game . "_moves", true);
        self::$DataSource->load_query('drop_moves_areas_table', "DROP TABLE z" . $id_game . "_moves_areas", true);
        self::$DataSource->load_query('drop_moves_units_table', "DROP TABLE z" . $id_game . "_moves_units", true);
        self::$DataSource->load_query('drop_units_table', "DROP TABLE z" . $id_game . "_units", true);

        // define table-name
        $areas_table = "z" . $id_game . "_areas";
        $moves_table = "z" . $id_game . "_moves";
        $moves_areas_table = "z" . $id_game . "_moves_areas";
        $moves_units_table = "z" . $id_game . "_moves_units";
        $units_table = "z" . $id_game . "_units";

        // map
        self::$DataSource->load_query('get_map_for_running_game', "SELECT zareas.id AS id_zarea, areas.id, areas.number AS number, areas.name AS name, areas.coords_small AS coords, areas.x AS posleft, areas.y AS postop, areas.height AS height, areas.width AS width, areas.id_type AS area_type, areas.xres AS xres, areas.yres AS yres, " .
            "resources.name AS resource, resources.label AS res_label, zareas.productivity AS prod, user.id AS id_user, user.user AS user, user.color AS color " .
            "FROM $areas_table AS zareas " .
            "LEFT JOIN resources AS resources ON (zareas.id_resource = resources.id) " .
            "LEFT JOIN areas AS areas ON (zareas.id_area = areas.id) " .
            "LEFT JOIN (" .
            "SELECT user.id AS id, user.login AS user, colors.color AS color " .
            "FROM is_in_game AS iig " .
            "LEFT JOIN user AS user ON (iig.id_user = user.id) " .
            "LEFT JOIN colors AS colors ON (colors.id = iig.id_color) " .
            "WHERE iig.id_game = $id_game" .
            ") AS user ON (zareas.id_user = user.id)");
        self::$DataSource->load_query('get_map_for_new_game', "SELECT zareas.id AS id_zarea, areas.number AS number, areas.name AS name, areas.coords_small AS coords, areas.x AS posleft, areas.y AS postop, areas.height AS height, areas.width AS width, areas.id_type AS area_type, areas.xres AS xres, areas.yres AS yres, " .
            "resources.name AS resource, resources.label AS res_label, zareas.productivity AS prod, " .
            "start.*" .
            "FROM $areas_table AS zareas " .
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
            ") AS start ON (zareas.id_area = start.id_area)");

        // start moves
        self::$DataSource->load_query('get_start_move', "SELECT moves.id, moves.id_user, moves.id_phase, moves.round, moves.deleted, areas.id_zarea, areas.step
				FROM $moves_table as moves
				LEFT JOIN $moves_areas_table as areas ON (moves.id = areas.id_zmove)
				WHERE moves.id = :id_move", true);
        self::$DataSource->load_query('get_start_move_for_user', "SELECT moves.id FROM $moves_table AS moves WHERE moves.id_user = :id_user AND id_phase = :id_phase AND round = :round AND deleted = 0 LIMIT 1", true);

        // set ship moves
        self::$DataSource->load_query('get_set_ships_move', "
            SELECT moves.id, moves.id_user, moves.id_phase, moves.round, moves.deleted, in_port.id_zarea AS id_zarea_in_port, at_sea.id_zarea AS id_zarea, zship.id_zunit
            FROM $moves_table AS moves
            LEFT JOIN $moves_areas_table AS in_port ON (moves.id = in_port.id_zmove AND in_port.step = 0) 
            LEFT JOIN $moves_areas_table AS at_sea ON (moves.id = at_sea.id_zmove AND at_sea.step = 1)
            LEFT JOIN $moves_units_table AS zship ON (moves.id = zship.id_zmove)
            WHERE moves.id = :id_move
        ");

        // in-game moves
        self::$DataSource->load_query('get_land_move', "
            SELECT moves.id, moves.id_user, moves.id_phase, moves.round, moves.deleted,
                move_areas.id_zarea, move_areas.step,
                move_units.id_zunit, move_units.numberof,
                units.id AS id_unit, units.abbreviation, units.name
            FROM $moves_table AS moves
            LEFT JOIN $moves_areas_table AS move_areas ON (moves.id = move_areas.id_zmove)
            LEFT JOIN $moves_units_table AS move_units ON (moves.id = move_units.id_zmove)
            LEFT JOIN $units_table AS zunits ON (move_units.id_zunit = zunits.id)
            LEFT JOIN units ON (zunits.id_unit = units.id)
            WHERE moves.id = :id_move AND moves.id_phase = :id_phase", true);
        self::$DataSource->load_query('get_production_move', "
            SELECT moves.id, moves.id_user, moves.id_phase, moves.round, moves.deleted,
                move_areas.id_zarea, move_areas.step,
                move_units.id_zunit, move_units.numberof,
                units.id AS id_unit, units.abbreviation, units.name
            FROM $moves_table AS moves
            LEFT JOIN $moves_areas_table AS move_areas ON (moves.id = move_areas.id_zmove)
            LEFT JOIN $moves_units_table AS move_units ON (moves.id = move_units.id_zmove)
            LEFT JOIN $units_table AS zunits ON (move_units.id_zunit = zunits.id)
            LEFT JOIN units ON (zunits.id_unit = units.id)
            WHERE moves.id = :id_move AND moves.id_phase = :id_phase", true);

        // new moves
        self::$DataSource->load_query('create_move', "INSERT INTO $moves_table (id_user, id_phase, round) VALUES (:id_user, :id_phase, :round)", true);
        self::$DataSource->load_query('insert_area_for_move', "INSERT INTO $moves_areas_table (id_zmove, id_zarea, step) VALUES (:id_move, :id_zarea, :step)", true);
        self::$DataSource->load_query('insert_land_units_for_move', "INSERT INTO $moves_units_table (id_zmove, id_zunit, numberof) VALUES (:id_move, :id_zunit, :count)", true);
        self::$DataSource->load_query('insert_ship_for_move', "INSERT INTO $moves_units_table (id_zmove, id_zunit) VALUES (:id_move, :id_zunit)", true);

        // select moves
        self::$DataSource->load_query('get_all_moves_for_phase_and_round', "SELECT id FROM $moves_table WHERE id_phase = :id_phase AND round = :round AND deleted = 0", true);
        self::$DataSource->load_query('get_specific_moves', "SELECT id FROM $moves_table WHERE id_phase = :id_phase AND round = :round AND id_user = :id_user AND deleted = 0", true);

        // delete moves
        self::$DataSource->load_query('delete_move_areas_for_step', "DELETE FROM $moves_areas_table WHERE id_zmove = :id_move AND step = :step", true);
        self::$DataSource->load_query('delete_move_areas_for_move', "DELETE FROM $moves_areas_table WHERE id_zmove = :id_move", true);
        self::$DataSource->load_query('delete_units_for_move', "DELETE FROM $moves_units_table WHERE id_zmove = :id_move", true);
        self::$DataSource->load_query('delete_move', "DELETE FROM $moves_table WHERE id = :id_move", true);
        self::$DataSource->load_query('flag_move_deleted', "UPDATE $moves_table SET deleted = 1 WHERE id = :id_move");

        // area info
        // query
        self::$DataSource->load_query('get_zareas', "SELECT id FROM $areas_table WHERE id_user LIKE :id_user", true);
        self::$DataSource->load_query('get_zarea_for_area', "SELECT id FROM $areas_table WHERE id_area = :id_area", true);
        self::$DataSource->load_query('get_all_zarea_info', "SELECT id,tank,id_user,id_area,id_resource,productivity FROM $areas_table WHERE id = :id_zarea", true);
        self::$DataSource->load_query('get_zarea_infos', "SELECT id_user, id_area, id_resource, productivity FROM $areas_table WHERE id_area = :id_area", true);
        self::$DataSource->load_query('get_zarea_user', "SELECT id_user FROM $areas_table WHERE id = :id_area", true);
        self::$DataSource->load_query('get_zarea_ressource_productivity', "SELECT id_ressource,productivity FROM $areas_table WHERE id = :id_area LIMIT 1", true);
        self::$DataSource->load_query('get_all_empty_zareas_id_ress_prod', "SELECT zareas.id,zareas.id_ressource,zareas.productivity FROM $areas_table AS zareas " .
            "LEFT JOIN areas AS areas ON (areas.id = zareas.id_area) WHERE zareas.id_user IS NULL AND areas.id_type = " . TYPE_LAND, true);
        self::$DataSource->load_query('get_area_production_for_user', "SELECT id_ressource, productivity FROM $areas_table WHERE id_user = :id_user", true);
        // update
        self::$DataSource->load_query('update_area_user_by_area', "UPDATE $areas_table SET id_user = :id_user WHERE id = :id_area", true);
        self::$DataSource->load_query('update_zarea_tank', "UPDATE $areas_table SET tank = :tank WHERE id = :id_zarea", true);
        self::$DataSource->load_query('update_zarea_id_user', "UPDATE $areas_table SET id_user = :id_user WHERE id = :id_zarea", true);
        self::$DataSource->load_query('update_zarea_id_resource', "UPDATE $areas_table SET id_resource = :id_resource WHERE id = :id_zarea", true);
        self::$DataSource->load_query('update_zarea_productivity', "UPDATE $areas_table SET productivity = :productivity WHERE id = :id_zarea", true);
        // create
        self::$DataSource->load_query('create_zarea', "INSERT INTO $areas_table (tank,id_user,id_area,id_resource,productivity) VALUES (:tank,:id_user,:id_area,:id_resource,:productivity)", true);

        // units info - land units
        // query
        self::$DataSource->load_query('get_land_units_for_zarea_user_unit', "SELECT id, id_unit, id_user, id_zarea, numberof AS count FROM $units_table WHERE id_zarea = :id_zarea AND id_user = :id_user AND id_unit = :id_unit");
        // update
        self::$DataSource->load_query('set_land_unit_count', "UPDATE $units_table SET numberof = :count WHERE id = :id_zunit");
        // create
        self::$DataSource->load_query('create_unit_for_zarea_user', "INSERT INTO $units_table (id_unit, id_user, id_zarea, numberof) VALUES (:id_unit, :id_user, :id_zarea, :count)");

        // units info - ships
        // query
        self::$DataSource->load_query('get_ingame_ship_by_id',
            "SELECT id, id_user, id_unit, id_zarea, tank, hitpoints, name, experience, dive_status, id_zarea_in_port FROM $units_table WHERE id = :id_zunit");
        self::$DataSource->load_query('get_ingame_ship_by_name',
            "SELECT id, id_user, id_unit, id_zarea, tank, hitpoints, name, experience, dive_status, id_zarea_in_port FROM $units_table WHERE name = :name");
        self::$DataSource->load_query('get_all_ships_in_area_not_in_port_by_user',
            "SELECT id FROM $units_table WHERE id_user = :id_user AND id_zarea = :id_zarea AND id_zarea_in_port IS NULL AND name IS NOT NULL");
        self::$DataSource->load_query('get_all_ships_in_port_by_user',
            "SELECT id FROM $units_table WHERE id_user = :id_user AND id_zarea_in_port = :id_zarea_in_port AND name IS NOT NULL");
        self::$DataSource->load_query('get_all_ships_in_area_not_in_port',
            "SELECT id FROM $units_table WHERE id_zarea = :id_zarea AND id_zarea_in_port IS NULL AND name IS NOT NULL");
        self::$DataSource->load_query('get_all_ships_in_port',
            "SELECT id FROM $units_table WHERE id_zarea_in_port = :id_zarea_in_port AND name IS NOT NULL");
        // update
        self::$DataSource->load_query('set_ship_user',
            "UPDATE $units_table SET id_user = :id_user WHERE id = :id_zunit");
        self::$DataSource->load_query('set_ship_zarea',
            "UPDATE $units_table SET id_zarea = :id_zarea WHERE id = :id_zunit");
        self::$DataSource->load_query('set_ship_tank',
            "UPDATE $units_table SET tank = :tank WHERE id = :id_zunit");
        self::$DataSource->load_query('set_ship_hitpoints',
            "UPDATE $units_table SET hitpoints = :hitpoints WHERE id = :id_zunit");
        self::$DataSource->load_query('set_ship_experience',
            "UPDATE $units_table SET experience = :experience WHERE id = :id_zunit");
        self::$DataSource->load_query('set_ship_dive_status',
            "UPDATE $units_table SET dive_status = :dive_status WHERE id = :id_zunit");
        self::$DataSource->load_query('set_ship_in_port',
            "UPDATE $units_table SET id_zarea_in_port = :id_zarea_in_port WHERE id = :id_zunit");
        // create
        self::$DataSource->load_query('create_ship',
            "INSERT INTO $units_table (tank, hitpoints, name, experience, dive_status, id_user, id_zarea, id_zarea_in_port, id_unit) 
             VALUES (:tank, :hitpoints, :name, :experience, :dive_status, :id_user, :id_zarea, :id_zarea_in_port, :id_unit)"
        );
        // delete
        self::$DataSource->load_query('delete_ship',
            "DELETE FROM $units_table WHERE id = :id_zunit"
        );
    }

}
