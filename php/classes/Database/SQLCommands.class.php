<?php
namespace Attack\Database;

use Attack\Exceptions\DatabaseException;

class SQLCommands {

    /**
     * @var $initialized bool
     */
    private static $initialized = false;

    /**
     * @var $SQLQueries array
     */
    private static $SQLQueries = [];

    /**
     * load predefined SQL commands into SQLConnector class
     *
     * @return void
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }
        self::LoadQueries();
        self::$initialized = true;
    }

    /**
     * returns the SQL query string corresponding to the given key.
     * null if no query found
     *
     * @param $key string
     * @return string|null
     */
    public static function getQuery($key) {
        if (!isset(self::$SQLQueries[$key])) {
            return null;
        }
        return self::$SQLQueries[$key];
    }

    /**
     * set SQL query with given key
     *
     * @param $key string
     * @param $SQLquery string
     * @throws \Exception
     */
    public static function setQuery($key, $SQLquery) {
        if (isset(self::$SQLQueries[$key])) {
            throw new DatabaseException('duplicate query declaration');
        }
        self::$SQLQueries[$key] = $SQLquery;
    }

    private static function LoadQueries() {
        $table_areas = 'areas';
        $table_areas_get_resources = 'areas_get_resources';
        $table_area_is_adjacent = 'area_is_adjacent';
        $table_colors = 'colors';
        $table_games = 'games';
        $table_game_areas = 'game_areas';
        $table_game_moves = 'game_moves';
        $table_game_move_has_areas = 'game_move_has_areas';
        $table_game_move_has_units = 'game_move_has_units';
        $table_game_units = 'game_units';
        $table_option_types = 'option_types';
        $table_phases = 'phases';
        $table_resources = 'resources';
        $table_start_sets = 'start_sets';
        $table_start_set_has_areas = 'start_set_has_areas';
        $table_start_ships = 'start_ships';
        $table_units = 'units';
        $table_user = 'user';
        $table_user_in_game_phase_info = 'user_in_game_phase_info';
        $table_user_is_in_game = 'user_is_in_game';


        /************************
         * user queries general *
         ************************/

        // select user
        self::setQuery('get_user_by_id', "SELECT * FROM $table_user WHERE id = :id_user");
        self::setQuery('check_user_password', "SELECT id FROM $table_user WHERE id = :id_user AND password = SHA(:password)");
        self::setQuery('check_user_login', "SELECT id FROM $table_user WHERE login = :username AND password = SHA(:password)");
        self::setQuery('check_user_token', "SELECT id FROM $table_user WHERE token = :token");
        self::setQuery('get_all_users', "SELECT * FROM $table_user ORDER BY id ASC");

        // update user
        self::setQuery('set_user_status', "UPDATE $table_user SET status = :status WHERE id = :id_user");
        self::setQuery('set_user_email', "UPDATE $table_user SET email = :email WHERE id = :id_user");
        self::setQuery('set_user_password', "UPDATE $table_user SET password = SHA(:password) WHERE id = :id_user");
        self::setQuery('set_user_token', "UPDATE $table_user SET token = :token WHERE id = :id_user");

        // register new user
        self::setQuery('check_if_login_exists', "SELECT id FROM $table_user WHERE login = :login");
        self::setQuery('check_if_email_exists', "SELECT id FROM $table_user WHERE email = :email");
        self::setQuery('create_new_user', "INSERT INTO $table_user (name, lastname, login, password, email, verify) VALUES (:name, :lastname, :login, SHA(:password), :email, SHA(:verify))");


        /************************
         * user queries in-game *
         ************************/

        // query infos
        self::setQuery('get_user_is_in_game', "SELECT * FROM $table_user_is_in_game WHERE id_game = :id_game AND id_user = :id_user");
        self::setQuery('get_all_user_is_in_game_by_user', "SELECT * FROM $table_user_is_in_game WHERE id_user = :id_user");
        self::setQuery('get_all_user_is_in_game_by_game', "SELECT * FROM $table_user_is_in_game WHERE id_game = :id_game");

        // query user
        self::setQuery('get_all_user_ids_for_game', "SELECT id_user FROM $table_user_is_in_game WHERE id_game = :id_game");
        self::setQuery('get_all_games_for_user', "
            SELECT games.* 
            FROM $table_games 
                RIGHT JOIN $table_user_is_in_game AS iig ON(games.id = iig.id_game) 
            WHERE iig.id_user = :id_user
        ");
        // TODO : continue query streamlining
        self::setQuery('get_participating_user', "SELECT id_user FROM $table_user_is_in_game WHERE id_game = :id_game");
        self::setQuery('get_money_for_user', "SELECT money FROM $table_user_is_in_game AS iig WHERE id_user = :id_user AND id_game = :id_game");
        self::setQuery('get_user_status', "SELECT status FROM $table_user WHERE id = :id_user");
        self::setQuery('check_if_user_created_a_game', "SELECT id FROM $table_games WHERE id_creator = :id_user");
        self::setQuery('check_if_user_is_in_a_game', "SELECT id FROM $table_user_is_in_game WHERE id_user = :id_user");
        self::setQuery('get_iig_info_for_user', "SELECT id_game, id_color, money, id_set FROM $table_user_is_in_game WHERE id_user = :id_user");

        // update user
        self::setQuery('update_user_color_for_game', "UPDATE $table_user_is_in_game SET id_color = :id_color WHERE id_user = :id_user AND id_game = :id_game LIMIT 1");
        self::setQuery('set_money_for_user', "UPDATE $table_user_is_in_game SET money = :money WHERE id_user = :id_user AND id_game = :id_game");
        self::setQuery('set_starting_set_for_user', "UPDATE $table_user_is_in_game SET id_set = :id_set WHERE id_user = :id_user AND id_game = :id_game");

        // in-game-phase-info
        self::setQuery('get_game_phase_info', "SELECT id_user, id_game, id_phase, is_ready FROM $table_user_in_game_phase_info WHERE id_user = :id_user AND id_game = :id_game AND id_phase = :id_phase LIMIT 1");
        self::setQuery('update_ingame_is_ready', "UPDATE $table_user_in_game_phase_info SET is_ready = :is_ready WHERE id_user = :id_user AND id_phase = :id_phase AND id_game = :id_game");
        self::setQuery('set_new_game_phase_info', "INSERT INTO $table_user_in_game_phase_info (id_user, id_phase, id_game, is_ready) VALUES (:id_user, :id_phase, :id_game, :is_ready)");
        self::setQuery('delete_game_phase_info_for_game', "DELETE FROM $table_user_in_game_phase_info WHERE id_game = :id_game");
        self::setQuery('delete_game_phase_info_for_user', "DELETE FROM $table_user_in_game_phase_info WHERE id_game = :id_game AND id_user = :id_user");

        // join/leave game
        self::setQuery('leave_game_for_user', "DELETE FROM $table_user_is_in_game WHERE id_user = :id_user AND id_game = :id_game");
        self::setQuery('join_game', "INSERT INTO $table_user_is_in_game (id_user, id_game, id_color) VALUES (:id_user, :id_game, :id_color)");
        self::setQuery('delete_iig_info_for_game', "DELETE FROM $table_user_is_in_game WHERE id_game = :id_game");
        self::setQuery('delete_iig_info_for_user', "DELETE FROM $table_user_is_in_game WHERE id_game = :id_game AND id_user = :id_user");

        // check which games are ready for processing
        self::setQuery('get_games_rdy', "
            SELECT g.id FROM $table_games g
            WHERE (g.status = 'running' OR g.status = 'started')
            AND 0 NOT IN (SELECT is_ready FROM $table_user_in_game_phase_info WHERE id_game = g.id AND id_phase = g.id_phase)
        ");
        self::setQuery('get_games_rdy_v2', "
            SELECT g.id FROM $table_games g
                JOIN (SELECT id_game, count(id_user) AS players FROM $table_user_is_in_game GROUP BY id_game) pl ON (pl.id_game = g.id)
                JOIN (SELECT id_game, id_phase, count(id_user) AS players_done FROM $table_user_in_game_phase_info WHERE is_ready = 1 GROUP BY id_game, id_phase) pl_dn ON (pl_dn.id_game = g.id AND pl_dn.id_phase = g.id_phase)
            WHERE pl.players = pl_dn.players_done AND g.processing = 0
        ");


        /****************
         * game queries *
         ****************/

        // update general game info
        self::setQuery('delete_game_password', "UPDATE $table_games SET password = NULL WHERE id = :id_game");
        self::setQuery('update_game_password', "UPDATE $table_games SET password = SHA(:password) WHERE id = :id_game");
        self::setQuery('set_game_status', "UPDATE $table_games SET status = :status WHERE id = :id_game");

        // delete game
        self::setQuery('delete_game_iig', "DELETE FROM $table_user_is_in_game WHERE id_game = :id_game");
        self::setQuery('delete_game', "DELETE FROM $table_games WHERE id = :id_game");

        // join game
        self::setQuery('check_game_password', "SELECT id FROM $table_games WHERE id = :id_game AND password = SHA(:password)");

        // new game
        self::setQuery('create_game_without_pw', "INSERT INTO $table_games (name, players, id_creator) VALUES (:game_name, :players, :id_creator)");
        self::setQuery('create_game_with_pw', "INSERT INTO $table_games (name, players, id_creator, password) VALUES (:game_name, :players, :id_creator, SHA(:password))");
        self::setQuery('get_starting_sets', "SELECT id,name,players FROM $table_start_sets WHERE players LIKE :players");
        self::setQuery('get_starting_set', "SELECT id,name,players FROM $table_start_sets WHERE id = :id_set");

        // games
        self::setQuery('get_all_game_ids', "SELECT id FROM $table_games ORDER BY id ASC");
        self::setQuery('get_done_game_ids', "SELECT id FROM $table_games WHERE status = '" . GAME_STATUS_DONE . "' ORDER BY id ASC");
        self::setQuery('get_new_game_ids', "SELECT id FROM $table_games WHERE status = '" . GAME_STATUS_NEW . "' ORDER BY id ASC");
        self::setQuery('get_started_game_ids', "SELECT id FROM $table_games WHERE status = '" . GAME_STATUS_STARTED . "' ORDER BY id ASC");
        self::setQuery('get_running_game_ids', "SELECT id FROM $table_games WHERE status = '" . GAME_STATUS_RUNNING . "' ORDER BY id ASC");

        self::setQuery('get_all_game_ids_for_user', "SELECT id_game AS id FROM $table_user_is_in_game WHERE id_user = :id_user ORDER BY id_game ASC");
        self::setQuery('get_done_game_ids_for_user', "
            SELECT iig.id_game AS id
            FROM $table_user_is_in_game AS iig
                LEFT JOIN $table_games ON (games.id = iig.id_game)
            WHERE games.status = '" . GAME_STATUS_DONE . "' AND iig.id_user = :id_user
            ORDER BY iig.id_game ASC
        ");
        self::setQuery('get_new_game_ids_for_user', "
            SELECT iig.id_game AS id 
            FROM $table_user_is_in_game AS iig 
                LEFT JOIN $table_games ON (games.id = iig.id_game) 
            WHERE games.status = '" . GAME_STATUS_NEW . "' AND iig.id_user = :id_user 
            ORDER BY iig.id_game ASC
        ");
        self::setQuery('get_started_game_ids_for_user', "
            SELECT iig.id_game AS id 
            FROM $table_user_is_in_game AS iig 
                LEFT JOIN $table_games ON (games.id = iig.id_game) 
            WHERE games.status = '" . GAME_STATUS_STARTED . "' AND iig.id_user = :id_user 
            ORDER BY iig.id_game ASC
        ");
        self::setQuery('get_running_game_ids_for_user', "
            SELECT iig.id_game AS id 
            FROM $table_user_is_in_game AS iig 
                LEFT JOIN $table_games ON (games.id = iig.id_game) 
            WHERE games.status = '" . GAME_STATUS_RUNNING . "' AND iig.id_user = :id_user 
            ORDER BY iig.id_game ASC
        ");


        /*************
         * game info *
         *************/

        // query
        self::setQuery('check_game_name', "SELECT id FROM $table_games WHERE name = :name");
        self::setQuery('get_full_game_info', "SELECT id, name, players, id_creator, password, status, id_phase, round, processing FROM $table_games WHERE id = :id_game");
        self::setQuery('get_game_status', "SELECT status FROM $table_games WHERE id = :id_game");
        self::setQuery('get_processing_state', "SELECT processing FROM $table_games WHERE id = :id_game LIMIT 1");
        self::setQuery('get_round_phase_info_id_game', "
            SELECT games.id_phase AS id_phase, games.round, phases.name AS phase, phases.label, games.name AS name, games.status FROM $table_games 
                LEFT JOIN phases AS phases ON (games.id_phase=phases.id)
            WHERE games.id = :id_game
        ");

        // update
        self::setQuery('set_game_phase', "UPDATE $table_games SET id_phase = :id_phase WHERE id = :id_game");
        self::setQuery('set_game_round', "UPDATE $table_games SET round = :round WHERE id = :id_game");
        self::setQuery('set_game_processing', "UPDATE $table_games SET processing = 1 WHERE id = :id_game");
        self::setQuery('set_game_processing_done', "UPDATE $table_games SET processing = 0 WHERE id = :id_game");

        // phase info
        self::setQuery('get_phase_info', "SELECT id, name, label, id_type FROM $table_phases WHERE id = :id_phase LIMIT 1");
        self::setQuery('get_all_phases', "SELECT id, name, label FROM $table_phases ORDER BY id ASC");
        self::setQuery('get_phase_name', "SELECT name FROM $table_phases WHERE id = :id_phase");

        // area_is_adjacent
        self::setQuery('get_area_is_adjacent', "SELECT id_area2 AS id_adjacent_area FROM $table_area_is_adjacent WHERE id_area1 = :id_area");

        // area info
        self::setQuery('get_all_area_ids', "SELECT id FROM $table_areas");
        self::setQuery('get_areas_for_type', "SELECT id FROM $table_areas WHERE id_type LIKE :id_type");
        self::setQuery('get_all_area_info', "SELECT id,name,number,coords_small,x,y,x2,y2,xres,yres,height,width,tanksize,id_type,zone,economy FROM $table_areas WHERE id = :id_area");
        self::setQuery('get_area_name', "SELECT id,name FROM $table_areas WHERE id = :id_area LIMIT 1");
        self::setQuery('get_area_infos', "SELECT id AS id, number, tanksize, id_type FROM $table_areas WHERE id = :id_area LIMIT 1");
        self::setQuery('get_area_type', "SELECT id_type FROM $table_areas WHERE id = :id_area LIMIT 1");
        self::setQuery('get_adjacent_areas', "SELECT id_area2 AS id_area FROM $table_area_is_adjacent WHERE id_area1 = :id_area");

        // color info
        self::setQuery('get_color', "SELECT id,name,key FROM $table_colors WHERE id = :id_color");
        self::setQuery('get_colors', "SELECT id,name,key FROM $table_colors");
        self::setQuery('get_all_colors', "SELECT id FROM $table_colors");
        self::setQuery('check_free_color', "SELECT id FROM $table_user_is_in_game WHERE id_game = :id_game AND id_color = :id_color");
        self::setQuery('get_free_colors_for_game', "SELECT id AS id, key AS color FROM $table_colors WHERE id NOT IN (SELECT id_color FROM $table_user_is_in_game WHERE id_game= :id_game)");

        // ressource info
        self::setQuery('get_resources', "SELECT * FROM $table_resources");
        self::setQuery('get_resource', "SELECT * FROM $table_resources WHERE id = :id_resource");
        self::setQuery('get_resource_allocation', "SELECT id, id_resource, res_power, economy, count FROM $table_areas_get_resources WHERE economy = :economy");

        // select-start
        self::setQuery('get_startregions_for_set', "SELECT id, id_area, id_optiontype, id_set, option_group FROM $table_start_set_has_areas WHERE id_set = :id_set ORDER BY option_group ASC");
        self::setQuery('get_option_types', "SELECT id, units, countries FROM $table_option_types");

        // start ships
        self::setQuery('get_start_ships_for_players', "SELECT * FROM $table_start_ships WHERE players = :players");

        // unit info
        self::setQuery('get_land_unit', "SELECT * FROM $table_units WHERE id = :id_unit");
        self::setQuery('get_all_land_units', "SELECT * FROM $table_units WHERE id_type = " . TYPE_LAND . " OR id_type = " . TYPE_AIR);
        self::setQuery('get_ship', "SELECT * FROM $table_units WHERE id = :id_unit");
        self::setQuery('get_all_ships', "SELECT * FROM $table_units WHERE id_type = " . TYPE_SEA);


        /*******
         * map *
         *******/

        self::setQuery('get_map_for_running_game', "
            SELECT 
                game_areas.id AS id_game_area, game_areas.productivity AS prod, 
                areas.id, areas.number AS number, areas.name AS name, areas.coords_small AS coords, areas.x AS posleft, areas.y AS postop, 
                areas.height AS height, areas.width AS width, areas.id_type AS area_type, areas.xres AS xres, areas.yres AS yres, 
                resources.name AS resource, resources.label AS res_label, 
                user.id AS id_user, user.user AS user, user.color AS color 
            FROM $table_game_areas AS game_areas 
                LEFT JOIN $table_resources AS resources ON (game_areas.id_resource = resources.id) 
                LEFT JOIN $table_areas AS areas ON (game_areas.id_area = areas.id) 
                LEFT JOIN (
                    SELECT user.id AS id, user.login AS user, colors.color AS color 
                    FROM $table_user_is_in_game AS iig 
                        LEFT JOIN $table_user AS user ON (iig.id_user = user.id) 
                        LEFT JOIN $table_colors AS colors ON (colors.id = iig.id_color) 
                    WHERE iig.id_game = :id_game
                ) AS user ON (game_areas.id_user = user.id)
            WHERE game_areas.id_game = :id_game
        ");
        self::setQuery('get_map_for_new_game', "
            SELECT 
                game_areas.id AS id_game_area, game_areas.productivity AS prod, 
                areas.number AS number, areas.name AS name, areas.coords_small AS coords, areas.x AS posleft, areas.y AS postop, 
                areas.height AS height, areas.width AS width, areas.id_type AS area_type, areas.xres AS xres, areas.yres AS yres, 
                resources.name AS resource, resources.label AS res_label, 
                start.*
            FROM $table_game_areas AS game_areas 
                LEFT JOIN $table_resources AS resources ON (game_areas.id_resource = resources.id) 
                LEFT JOIN $table_areas AS areas ON (game_areas.id_area = areas.id) 
                LEFT JOIN (
                    SELECT user.login AS user, colors.color AS color, set_has_areas.id_area, set_has_areas.option_group AS countrySelectOption, optypes.units AS countrySelectUnitCount, optypes.countries AS countrySelectCount 
                    FROM $table_user_is_in_game AS iig 
                        LEFT JOIN $table_user AS user ON (iig.id_user = user.id) 
                        LEFT JOIN $table_colors AS colors ON (colors.id = iig.id_color) 
                        INNER JOIN $table_start_set_has_areas AS set_has_areas ON (set_has_areas.id_set = iig.id_set) 
                        LEFT JOIN $table_option_types AS optypes ON (optypes.id = set_has_areas.id_optiontype) 
                        WHERE iig.id_game = :id_game
                ) AS start ON (game_areas.id_area = start.id_area)
            WHERE game_areas.id_game = :id_game
        ");


        /*********
         * moves *
         *********/

        // start moves
        self::setQuery('get_start_move', "
            SELECT moves.id, moves.id_user, moves.id_phase, moves.round, moves.deleted, areas.id_game_area, areas.step
            FROM $table_game_moves as moves
				LEFT JOIN $table_game_move_has_areas AS areas ON (moves.id = areas.id_game_move)
            WHERE moves.id = :id_move
        ");
        self::setQuery('get_start_move_for_user', "
            SELECT moves.id
            FROM $table_game_moves
            WHERE id_game = :id_game AND id_user = :id_user AND id_phase = :id_phase AND round = :round AND deleted = 0
            LIMIT 1
        ");

        // set ship moves
        self::setQuery('get_set_ships_move', "
            SELECT moves.id, moves.id_user, moves.id_phase, moves.round, moves.deleted, port_area.id_game_area AS id_port_area, sea_area.id_game_area AS id_sea_area, ship.id_game_unit
            FROM $table_game_moves AS moves
                LEFT JOIN $table_game_move_has_areas AS port_area ON (moves.id = port_area.id_game_move AND port_area.step = 0) 
                LEFT JOIN $table_game_move_has_areas AS sea_area ON (moves.id = sea_area.id_game_move AND sea_area.step = 1)
                LEFT JOIN $table_game_move_has_units AS ship ON (moves.id = ship.id_game_move)
            WHERE moves.id = :id_move
        ");

        // in-game moves
        self::setQuery('get_land_move', "
            SELECT moves.id, moves.id_user, moves.id_phase, moves.round, moves.deleted,
                move_areas.id_game_area, move_areas.step,
                move_units.id_game_unit, move_units.numberof,
                units.id AS id_unit, units.abbreviation, units.name
            FROM $table_game_moves AS moves
            LEFT JOIN $table_game_move_has_areas AS move_areas ON (moves.id = move_areas.id_game_move)
            LEFT JOIN $table_game_move_has_units AS move_units ON (moves.id = move_units.id_game_move)
            LEFT JOIN $table_game_units AS game_units ON (move_units.id_game_unit = game_units.id)
            LEFT JOIN $table_units ON (game_units.id_unit = units.id)
            WHERE moves.id = :id_move
        ");
        self::setQuery('get_production_move', "
            SELECT moves.id, moves.id_user, moves.id_phase, moves.round, moves.deleted,
                move_areas.id_game_area, move_areas.step,
                move_units.id_game_unit, move_units.numberof,
                units.id AS id_unit, units.abbreviation, units.name
            FROM $table_game_moves AS moves
            LEFT JOIN $table_game_move_has_areas AS move_areas ON (moves.id = move_areas.id_game_move)
            LEFT JOIN $table_game_move_has_units AS move_units ON (moves.id = move_units.id_game_move)
            LEFT JOIN $table_game_units AS game_units ON (move_units.id_game_unit = game_units.id)
            LEFT JOIN $table_units ON (game_units.id_unit = units.id)
            WHERE moves.id = :id_move
        ");

        // new moves
        self::setQuery('create_move', "INSERT INTO $table_game_moves (id_game, id_user, id_phase, round) VALUES (:id_game, :id_user, :id_phase, :round)");
        self::setQuery('insert_area_for_move', "INSERT INTO $table_game_move_has_areas (id_game_move, id_game_area, step) VALUES (:id_move, :id_game_area, :step)");
        self::setQuery('insert_land_units_for_move', "INSERT INTO $table_game_move_has_units (id_game_move, id_game_unit, numberof) VALUES (:id_move, :id_game_unit, :count)");
        self::setQuery('insert_ship_for_move', "INSERT INTO $table_game_move_has_units (id_game_move, id_game_unit) VALUES (:id_move, :id_game_unit)");

        // select moves
        self::setQuery('get_all_moves_for_phase_and_round', "SELECT id FROM $table_game_moves WHERE id_game = :id_game AND id_phase = :id_phase AND round = :round AND deleted = 0");
        self::setQuery('get_specific_moves', "SELECT id FROM $table_game_moves WHERE id_game = :id_game AND id_phase = :id_phase AND round = :round AND id_user = :id_user AND deleted = 0");

        // delete moves
        self::setQuery('delete_move_areas_for_step', "DELETE FROM $table_game_move_has_areas WHERE id_game_move = :id_move AND step = :step");
        self::setQuery('delete_move_areas_for_move', "DELETE FROM $table_game_move_has_areas WHERE id_game_move = :id_move");
        self::setQuery('delete_units_for_move', "DELETE FROM $table_game_move_has_units WHERE id_game_move = :id_move");
        self::setQuery('delete_move', "DELETE FROM $table_game_moves WHERE id = :id_move");
        self::setQuery('flag_move_deleted', "UPDATE $table_game_moves SET deleted = 1 WHERE id = :id_move");


        /*******************
         * game areas info *
         *******************/

        // query
        self::setQuery('get_zareas', "SELECT id FROM $table_game_areas WHERE id_game = :id_game AND id_user = :id_user");
        self::setQuery('get_zarea_for_area', "SELECT id FROM $table_game_areas WHERE id_game = :id_game AND id_area = :id_area");
        self::setQuery('get_all_zarea_info', "SELECT * FROM $table_game_areas WHERE id = :id_game_area");
        self::setQuery('get_zarea_infos', "SELECT * FROM $table_game_areas WHERE id_game = :id_game AND id_area = :id_area");
        self::setQuery('get_zarea_user', "SELECT id_user FROM $table_game_areas WHERE id = :id_game_area");
        self::setQuery('get_zarea_ressource_productivity', "SELECT id_ressource, productivity FROM $table_game_areas WHERE id = :id_game_area");
        self::setQuery('get_all_empty_zareas_id_ress_prod', "
            SELECT game_areas.id, game_areas.id_ressource, game_areas.productivity
            FROM $table_game_areas AS game_areas 
                LEFT JOIN $table_areas AS areas ON (areas.id = game_areas.id_area) 
            WHERE game_areas.id_user IS NULL AND areas.id_type = " . TYPE_LAND
        );
        self::setQuery('get_area_production_for_user', "SELECT id_ressource, productivity FROM $table_game_areas WHERE id_game = :id_game AND id_user = :id_user");

        // update
        self::setQuery('update_area_user_by_area', "UPDATE $table_game_areas SET id_user = :id_user WHERE id = :id_game_area");
        self::setQuery('update_zarea_id_user', "UPDATE $table_game_areas SET id_user = :id_user WHERE id = :id_game_area");
        self::setQuery('update_zarea_id_resource', "UPDATE $table_game_areas SET id_resource = :id_resource WHERE id = :id_game_area");
        self::setQuery('update_zarea_productivity', "UPDATE $table_game_areas SET productivity = :productivity WHERE id = :id_game_area");

        // create
        self::setQuery('create_zarea', "INSERT INTO $table_game_areas (id_game, id_user, id_area, id_resource, productivity) VALUES (:id_game, :id_user, :id_area, :id_resource, :productivity)");


        /************************
         * game land units info *
         ************************/

        // query
        self::setQuery('get_land_units_for_zarea_user_unit', "
            SELECT id, id_unit, id_user, id_game_area, numberof AS count 
            FROM $table_game_units 
            WHERE id_game_area = :id_game_area AND id_user = :id_user AND id_unit = :id_unit
        ");

        // update
        self::setQuery('set_land_unit_count', "UPDATE $table_game_units SET numberof = :count WHERE id = :id_game_unit");

        // create
        self::setQuery('create_unit_for_zarea_user', "INSERT INTO $table_game_units (numberof, id_user, id_game_area, id_unit) VALUES (:count, :id_user, :id_game_area, :id_unit)");


        /*******************
         * game ships info *
         *******************/

        // query
        self::setQuery('get_ingame_ship_by_id', "
            SELECT * 
            FROM $table_game_units 
            WHERE id = :id_game_unit
        ");
        self::setQuery('get_ingame_ship_by_name', "
            SELECT game_units.*
            FROM $table_game_units AS game_units
                LEFT JOIN $table_game_areas AS game_areas ON (game_areas.id = game_units.id_game_area)
            WHERE game_areas.id_game = :id_game AND game_units.name = :name
            LIMIT 1
        ");
        self::setQuery('get_all_ships_in_area_not_in_port_by_user', "
            SELECT game_units.id
            FROM $table_game_units AS game_units
                LEFT JOIN $table_units AS units ON (units.id = game_units.id_unit)
            WHERE id_user = :id_user AND id_game_area = :id_game_area AND id_game_area_in_port IS NULL AND units.id_type = " . TYPE_SEA
        );
        self::setQuery('get_all_ships_in_port_by_user', "
            SELECT game_units.id
            FROM $table_game_units AS game_units
                LEFT JOIN $table_units AS units ON (units.id = game_units.id_unit)
            WHERE id_user = :id_user AND id_game_area_in_port = :id_game_area_in_port AND units.id_type = " . TYPE_SEA
        );
        self::setQuery('get_all_ships_in_area_not_in_port', "
            SELECT game_units.id
            FROM $table_game_units AS game_units
                LEFT JOIN $table_units AS units ON (units.id = game_units.id_unit)
            WHERE id_game_area = :id_game_area AND id_game_area_in_port IS NULL AND units.id_type = " . TYPE_SEA
        );
        self::setQuery('get_all_ships_in_port', "
            SELECT game_units.id
            FROM $table_game_units AS game_units
                LEFT JOIN $table_units AS units ON (units.id = game_units.id_unit)
            WHERE id_game_area_in_port = :id_game_area_in_port AND units.id_type = " . TYPE_SEA
        );

        // update
        self::setQuery('set_ship_user', "UPDATE $table_game_units SET id_user = :id_user WHERE id = :id_game_unit");
        self::setQuery('set_ship_zarea', "UPDATE $table_game_units SET id_game_area = :id_game_area WHERE id = :id_game_unit");
        self::setQuery('set_ship_tank', "UPDATE $table_game_units SET tank = :tank WHERE id = :id_game_unit");
        self::setQuery('set_ship_hitpoints', "UPDATE $table_game_units SET hitpoints = :hitpoints WHERE id = :id_game_unit");
        self::setQuery('set_ship_experience', "UPDATE $table_game_units SET experience = :experience WHERE id = :id_game_unit");
        self::setQuery('set_ship_dive_status', "UPDATE $table_game_units SET dive_status = :dive_status WHERE id = :id_game_unit");
        self::setQuery('set_ship_in_port', "UPDATE $table_game_units SET id_game_area_in_port = :id_game_area_in_port WHERE id = :id_game_unit");

        // create
        self::setQuery('create_ship', "
            INSERT INTO $table_game_units (tank, hitpoints, name, experience, dive_status, id_user, id_game_area, id_game_area_in_port, id_unit) 
            VALUES (:tank, :hitpoints, :name, :experience, :dive_status, :id_user, :id_game_area, :id_game_area_in_port, :id_unit)
        ");

        // delete
        self::setQuery('delete_ship', "DELETE FROM $table_game_units WHERE id = :id_game_unit");
    }

}
