<?php
namespace Attack;

/******************************************
 * game settings also defined in database *
 ******************************************/

// types
define('TYPE_LAND', 1);
define('TYPE_SEA', 2);
define('TYPE_AIR', 3);
define('TYPE_SUBMARINE', 4);

// units
define('ID_INFANTRY', 1);
define('ID_ARTILLERY', 2);
define('ID_TANK', 3);
define('ID_AIRCRAFT', 4);
define('ID_SUBMARINE', 5);
define('ID_DESTROYER', 6);
define('ID_BATTLESHIP', 7);
define('ID_CARRIER', 8);
define('DIVE_STATUS_UP', 'up');
define('DIVE_STATUS_DIVING', 'diving');
define('DIVE_STATUS_SILENT', 'silent');

// phases
define('PHASE_LANDMOVE', 1);
define('PHASE_SEAMOVE', 2);
define('PHASE_TRADEROUTES', 3);
define('PHASE_TROOPSMOVE', 4);
define('PHASE_PRODUCTION', 5);
define('PHASE_GAME_START', 21);
define('PHASE_SELECTSTART', 22);
define('PHASE_SETSHIPS', 23);

// game
define('GAME_STATUS_NEW', 'new');
define('GAME_STATUS_STARTED', 'started');
define('GAME_STATUS_RUNNING', 'running');
define('GAME_STATUS_DONE', 'done');
define('GAME_STATUS_ALL', 'all');

// areas
define('ECONOMY_POOR', 'poor');
define('ECONOMY_WEAK', 'weak');
define('ECONOMY_NORMAL', 'normal');
define('ECONOMY_STRONG', 'strong');

// resources
define('RESOURCE_OIL', 1);
define('RESOURCE_TRANSPORT', 2);
define('RESOURCE_INDUSTRY', 3);
define('RESOURCE_MINERALS', 4);
define('RESOURCE_POPULATION', 5);

// user
define('NEUTRAL_COUNTRY', -1);
define('STATUS_USER_INACTIVE', 'inactive');
define('STATUS_USER_ACTIVE', 'active');
define('STATUS_USER_MODERATOR', 'moderator');
define('STATUS_USER_ADMIN', 'admin');
define('STATUS_USER_DELETED', 'deleted');
define('STATUS_USER_ALL', 'all');

// colors
define('ID_COLOR_RED', 1);
define('ID_COLOR_BLUE', 2);
define('ID_COLOR_GREEN', 3);
define('ID_COLOR_ORANGE', 4);
define('ID_COLOR_PURPLE', 5);
define('ID_COLOR_YELLOW', 6);

/****************************************************
 * game settings that are NOT reflected in database *
 ****************************************************/

define('NO_RESOURCE', -1);
define('NO_AREA', -1);

define('MAX_LAND_ATTACKS', 1);

define('MAX_MONEY_SPENDABLE', 10);

define('TRADEROUTE_MAX_VALUE_MULTIPLIER', 2); // shortest sea route between to areas * TRADEROUTE_MAX_VALUE_MULTIPLIER
define('TRADEROUTE_PP_MULTIPLIER', 2); // current_traderoute_value * TRADEROUTE_PP_MULTIPLIER == pp-output
define('TRADEROUTE_MIN_START_VALUE', 3);
