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
        self::setQuery('get_user_by_status', "SELECT * FROM $table_user WHERE status = :status ORDER BY id ASC");
        self::setQuery('get_user_by_status_game', "
            SELECT user.*
            FROM $table_user user
                LEFT JOIN $table_user_is_in_game iig ON (user.id = iig.id_user)
            WHERE iig.id_game = :id_game AND user.status = :status ORDER BY user.id ASC
        ");
        self::setQuery('get_user_by_game', "
            SELECT user.*
            FROM $table_user user
                LEFT JOIN $table_user_is_in_game iig ON (user.id = iig.id_user)
            WHERE iig.id_game = :id_game ORDER BY user.id ASC
        ");

        // update user
        self::setQuery('set_user_status', "UPDATE $table_user SET status = :status WHERE id = :id_user");
        self::setQuery('set_user_email', "UPDATE $table_user SET email = :email WHERE id = :id_user");
        self::setQuery('set_user_password', "UPDATE $table_user SET password = SHA(:password) WHERE id = :id_user");
        self::setQuery('set_user_token', "UPDATE $table_user SET token = :token WHERE id = :id_user");

        // new user
        self::setQuery('check_if_login_exists', "SELECT id FROM $table_user WHERE login = :login");
        self::setQuery('check_if_email_exists', "SELECT id FROM $table_user WHERE email = :email");
        self::setQuery('insert_user', "INSERT INTO $table_user (name, lastname, login, password, email, verify) VALUES (:name, :lastname, :login, SHA(:password), :email, SHA(:verify))");


        /************************
         * user queries in-game *
         ************************/

        // get in game infos
        self::setQuery('get_user_is_in_game', "SELECT * FROM $table_user_is_in_game WHERE id_game = :id_game AND id_user = :id_user");
        self::setQuery('get_all_user_is_in_game_by_user', "SELECT * FROM $table_user_is_in_game WHERE id_user = :id_user");
        self::setQuery('get_all_user_is_in_game_by_game', "SELECT * FROM $table_user_is_in_game WHERE id_game = :id_game");

        // join/leave game
        self::setQuery('insert_user_in_game', "INSERT INTO $table_user_is_in_game (id_user, id_game, id_color) VALUES (:id_user, :id_game, :id_color)");
        self::setQuery('delete_user_in_game', "DELETE FROM $table_user_is_in_game WHERE id_game = :id_game AND id_user = :id_user");
        self::setQuery('delete_user_in_game_by_game', "DELETE FROM $table_user_is_in_game WHERE id_game = :id_game");

        // update in game infos
        self::setQuery('set_user_in_game_color', "UPDATE $table_user_is_in_game SET id_color = :id_color WHERE id_user = :id_user AND id_game = :id_game");
        self::setQuery('set_user_in_game_money', "UPDATE $table_user_is_in_game SET money = :money WHERE id_user = :id_user AND id_game = :id_game");
        self::setQuery('set_user_in_game_starting_set', "UPDATE $table_user_is_in_game SET id_set = :id_set WHERE id_user = :id_user AND id_game = :id_game");

        // get/update in game phase infos
        self::setQuery('get_user_in_game_phase_info', "SELECT id_user, id_game, id_phase, is_ready FROM $table_user_in_game_phase_info WHERE id_user = :id_user AND id_game = :id_game AND id_phase = :id_phase LIMIT 1");
        self::setQuery('set_user_in_game_phase_ready', "UPDATE $table_user_in_game_phase_info SET is_ready = :is_ready WHERE id_user = :id_user AND id_phase = :id_phase AND id_game = :id_game");
        self::setQuery('insert_user_in_game_phase_info', "INSERT INTO $table_user_in_game_phase_info (id_user, id_phase, id_game, is_ready) VALUES (:id_user, :id_phase, :id_game, :is_ready)");
        self::setQuery('delete_user_in_game_phase_info', "DELETE FROM $table_user_in_game_phase_info WHERE id_game = :id_game AND id_user = :id_user");
        self::setQuery('delete_user_in_game_phase_info_by_game', "DELETE FROM $table_user_in_game_phase_info WHERE id_game = :id_game");

        // check which games are ready for processing
        self::setQuery('get_games_for_processing', "
            SELECT games.* FROM $table_games games
                JOIN (
                    SELECT id_game, count(id_user) AS count
                    FROM $table_user_is_in_game
                    GROUP BY id_game
                ) players ON (players.id_game = games.id)
                JOIN (
                    SELECT id_game, id_phase, count(id_user) AS count
                    FROM $table_user_in_game_phase_info
                    WHERE is_ready = 1
                    GROUP BY id_game, id_phase
                ) players_done ON (players_done.id_game = games.id AND players_done.id_phase = games.id_phase)
            WHERE players.count = players_done.count AND games.processing = 0
        ");


        /****************
         * game queries *
         ****************/

        // games
        self::setQuery('get_game_by_id', "SELECT id, name, players, id_creator, password, status, id_phase, round, processing FROM $table_games WHERE id = :id_game");
        self::setQuery('get_all_games', "SELECT * FROM $table_games ORDER BY id ASC");
        self::setQuery('get_games_by_status', "SELECT * FROM $table_games WHERE status = :status ORDER BY id ASC");
        self::setQuery('get_games_by_status_and_user', "SELECT * FROM $table_games WHERE status = :status AND id_user = :id_user ORDER BY id ASC");
        self::setQuery('check_game_password', "SELECT id FROM $table_games WHERE id = :id_game AND password = SHA(:password)");
        self::setQuery('check_game_name', "SELECT id FROM $table_games WHERE name = :name");

        // update game
        self::setQuery('set_game_password_null', "UPDATE $table_games SET password = NULL WHERE id = :id_game");
        self::setQuery('set_game_password', "UPDATE $table_games SET password = SHA(:password) WHERE id = :id_game");
        self::setQuery('set_game_status', "UPDATE $table_games SET status = :status WHERE id = :id_game");
        self::setQuery('set_game_phase', "UPDATE $table_games SET id_phase = :id_phase WHERE id = :id_game");
        self::setQuery('set_game_round', "UPDATE $table_games SET round = :round WHERE id = :id_game");
        self::setQuery('set_game_processing', "UPDATE $table_games SET processing = 1 WHERE id = :id_game");
        self::setQuery('set_game_processing_done', "UPDATE $table_games SET processing = 0 WHERE id = :id_game");
        self::setQuery('delete_game', "DELETE FROM $table_games WHERE id = :id_game");

        // new game
        self::setQuery('insert_game', "INSERT INTO $table_games (name, players, id_creator) VALUES (:game_name, :players, :id_creator)");
        self::setQuery('insert_game_with_password', "INSERT INTO $table_games (name, players, id_creator, password) VALUES (:game_name, :players, :id_creator, SHA(:password))");
        self::setQuery('get_all_start_sets', "SELECT * FROM $table_start_sets WHERE players = :players");
        self::setQuery('get_start_set_by_id', "SELECT * FROM $table_start_sets WHERE id = :id_set");


        /***************
         * game phases *
         ***************/

        // phase info
        self::setQuery('get_phase_by_id', "SELECT * FROM $table_phases WHERE id = :id_phase");
        self::setQuery('get_all_phases', "SELECT * FROM $table_phases ORDER BY id ASC");


        /*********
         * areas *
         *********/

        // area info
        self::setQuery('get_all_areas', "SELECT * FROM $table_areas");
        self::setQuery('get_areas_by_type', "SELECT * FROM $table_areas WHERE id_type = :id_type");
        self::setQuery('get_area_by_id', "SELECT * FROM $table_areas WHERE id = :id_area");
        self::setQuery('get_adjacent_areas_for_area', "SELECT id_area2 as id_adjacent_area FROM $table_area_is_adjacent WHERE id_area1 = :id_area");


        /**********
         * colors *
         **********/

        // color info
        self::setQuery('get_all_colors', "SELECT * FROM $table_colors");
        self::setQuery('get_color_by_id', "SELECT * FROM $table_colors WHERE id = :id_color");


        /*************
         * resources *
         *************/

        // ressource info
        self::setQuery('get_areas_get_resources_by_economy', "SELECT * FROM $table_areas_get_resources WHERE economy = :economy");


        /**************
         * start sets *
         **************/

        // select-start
        self::setQuery('get_start_set_areas_by_set', "SELECT * FROM $table_start_set_has_areas WHERE id_set = :id_set ORDER BY option_group ASC");
        self::setQuery('get_option_types', "SELECT * FROM $table_option_types");

        // start ships
        self::setQuery('get_start_ships_by_players', "SELECT * FROM $table_start_ships WHERE players = :players");


        /*********
         * units *
         *********/

        // unit info
        self::setQuery('get_unit_by_id', "SELECT * FROM $table_units WHERE id = :id_unit");
        self::setQuery('get_units_by_type', "SELECT * FROM $table_units WHERE id_type = :id_type");


        /*******
         * map *
         *******/

        self::setQuery('get_map_for_running_game', "
            SELECT 
                game_areas.id AS id_game_area, game_areas.productivity AS prod, 
                areas.id, areas.number AS number, areas.name AS name, areas.coords_small AS coords, areas.x AS posleft, areas.y AS postop, 
                areas.height AS height, areas.width AS width, areas.id_type AS area_type, areas.xres AS xres, areas.yres AS yres, 
                resources.name AS resource, resources.key AS res_label, 
                user.id AS id_user, user.user AS user, user.color AS color 
            FROM $table_game_areas AS game_areas 
                LEFT JOIN $table_resources AS resources ON (game_areas.id_resource = resources.id) 
                LEFT JOIN $table_areas AS areas ON (game_areas.id_area = areas.id) 
                LEFT JOIN (
                    SELECT user.id AS id, user.login AS user, colors.key AS color 
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
                resources.name AS resource, resources.key AS res_label, 
                start.*
            FROM $table_game_areas AS game_areas 
                LEFT JOIN $table_resources AS resources ON (game_areas.id_resource = resources.id) 
                LEFT JOIN $table_areas AS areas ON (game_areas.id_area = areas.id) 
                LEFT JOIN (
                    SELECT user.login AS user, colors.key AS color, set_has_areas.id_area, set_has_areas.option_group AS countrySelectOption, optypes.units AS countrySelectUnitCount, optypes.countries AS countrySelectCount 
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

        // select
        self::setQuery('get_game_moves_by_phase_round', "SELECT id FROM $table_game_moves WHERE id_game = :id_game AND id_phase = :id_phase AND round = :round AND deleted = 0");
        self::setQuery('get_game_moves_by_phase_round_user', "SELECT id FROM $table_game_moves WHERE id_game = :id_game AND id_phase = :id_phase AND round = :round AND id_user = :id_user AND deleted = 0");

        // start moves
        self::setQuery('get_start_move', "
            SELECT moves.id, moves.id_user, moves.id_phase, moves.round, moves.deleted, areas.id_game_area, areas.step
            FROM $table_game_moves as moves
				LEFT JOIN $table_game_move_has_areas AS areas ON (moves.id = areas.id_game_move)
            WHERE moves.id = :id_move
        ");
        self::setQuery('get_start_move_for_user', "
            SELECT id
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
        self::setQuery('insert_move', "INSERT INTO $table_game_moves (id_game, id_user, id_phase, round) VALUES (:id_game, :id_user, :id_phase, :round)");
        self::setQuery('insert_area_for_move', "INSERT INTO $table_game_move_has_areas (id_game_move, id_game_area, step) VALUES (:id_move, :id_game_area, :step)");
        self::setQuery('insert_land_units_for_move', "INSERT INTO $table_game_move_has_units (id_game_move, id_game_unit, numberof) VALUES (:id_move, :id_game_unit, :count)");
        self::setQuery('insert_ship_for_move', "INSERT INTO $table_game_move_has_units (id_game_move, id_game_unit) VALUES (:id_move, :id_game_unit)");

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
        self::setQuery('get_game_area_by_id', "SELECT * FROM $table_game_areas WHERE id = :id_game_area");
        self::setQuery('get_game_area_by_area', "SELECT * FROM $table_game_areas WHERE id_game = :id_game AND id_area = :id_area");
        self::setQuery('get_all_game_areas', "SELECT * FROM $table_game_areas WHERE id_game = :id_game");
        self::setQuery('get_game_areas_by_user', "SELECT * FROM $table_game_areas WHERE id_game = :id_game AND id_user = :id_user");

        // update
        self::setQuery('set_game_area_user', "UPDATE $table_game_areas SET id_user = :id_user WHERE id = :id_game_area");
        self::setQuery('set_game_area_resource', "UPDATE $table_game_areas SET id_resource = :id_resource WHERE id = :id_game_area");
        self::setQuery('set_game_area_productivity', "UPDATE $table_game_areas SET productivity = :productivity WHERE id = :id_game_area");

        // create
        self::setQuery('insert_game_area', "INSERT INTO $table_game_areas (id_game, id_user, id_area, id_resource, productivity) VALUES (:id_game, :id_user, :id_area, :id_resource, :productivity)");


        /************************
         * game land units info *
         ************************/

        // query
        self::setQuery('get_game_land_units_by_area_user_unit', "
            SELECT *, numberof AS count 
            FROM $table_game_units 
            WHERE id_game_area = :id_game_area AND id_user = :id_user AND id_unit = :id_unit
        ");

        // update
        self::setQuery('set_game_land_unit_count', "UPDATE $table_game_units SET numberof = :count WHERE id = :id_game_unit");

        // create
        self::setQuery('insert_game_land_unit', "INSERT INTO $table_game_units (numberof, id_user, id_game_area, id_unit) VALUES (:count, :id_user, :id_game_area, :id_unit)");


        /*******************
         * game ships info *
         *******************/

        // query
        self::setQuery('get_game_ship_by_id', "
            SELECT * 
            FROM $table_game_units 
            WHERE id = :id_game_unit
        ");
        self::setQuery('get_game_ship_by_name', "
            SELECT game_units.*
            FROM $table_game_units AS game_units
                LEFT JOIN $table_game_areas AS game_areas ON (game_areas.id = game_units.id_game_area)
            WHERE game_areas.id_game = :id_game AND game_units.name = :name
            LIMIT 1
        ");
        self::setQuery('get_all_game_ships_not_in_port_by_area_user', "
            SELECT game_units.*
            FROM $table_game_units AS game_units
                LEFT JOIN $table_units AS units ON (units.id = game_units.id_unit)
            WHERE id_user = :id_user AND id_game_area = :id_game_area AND id_game_area_in_port IS NULL AND units.id_type = " . TYPE_SEA
        );
        self::setQuery('get_all_game_ships_by_port_user', "
            SELECT game_units.*
            FROM $table_game_units AS game_units
                LEFT JOIN $table_units AS units ON (units.id = game_units.id_unit)
            WHERE id_user = :id_user AND id_game_area_in_port = :id_game_area_in_port AND units.id_type = " . TYPE_SEA
        );
        self::setQuery('get_all_game_ships_not_in_port_by_area', "
            SELECT game_units.*
            FROM $table_game_units AS game_units
                LEFT JOIN $table_units AS units ON (units.id = game_units.id_unit)
            WHERE id_game_area = :id_game_area AND id_game_area_in_port IS NULL AND units.id_type = " . TYPE_SEA
        );
        self::setQuery('get_all_ships_by_port', "
            SELECT game_units.*
            FROM $table_game_units AS game_units
                LEFT JOIN $table_units AS units ON (units.id = game_units.id_unit)
            WHERE id_game_area_in_port = :id_game_area_in_port AND units.id_type = " . TYPE_SEA
        );

        // update
        self::setQuery('set_game_ship_user', "UPDATE $table_game_units SET id_user = :id_user WHERE id = :id_game_unit");
        self::setQuery('set_game_ship_game_area', "UPDATE $table_game_units SET id_game_area = :id_game_area WHERE id = :id_game_unit");
        self::setQuery('set_game_ship_tank', "UPDATE $table_game_units SET tank = :tank WHERE id = :id_game_unit");
        self::setQuery('set_game_ship_hitpoints', "UPDATE $table_game_units SET hitpoints = :hitpoints WHERE id = :id_game_unit");
        self::setQuery('set_game_ship_experience', "UPDATE $table_game_units SET experience = :experience WHERE id = :id_game_unit");
        self::setQuery('set_game_ship_dive_status', "UPDATE $table_game_units SET dive_status = :dive_status WHERE id = :id_game_unit");
        self::setQuery('set_game_ship_port', "UPDATE $table_game_units SET id_game_area_in_port = :id_game_area_in_port WHERE id = :id_game_unit");

        // create
        self::setQuery('insert_game_ship', "
            INSERT INTO $table_game_units (tank, hitpoints, name, experience, dive_status, id_user, id_game_area, id_game_area_in_port, id_unit) 
            VALUES (:tank, :hitpoints, :name, :experience, :dive_status, :id_user, :id_game_area, :id_game_area_in_port, :id_unit)
        ");

        // delete
        self::setQuery('delete_game_ship', "DELETE FROM $table_game_units WHERE id = :id_game_unit");
    }

}
