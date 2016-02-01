<?php
namespace AttOn;

// Define static values
define('TYPE_LAND', 1);
define('TYPE_SEA', 2);
define('TYPE_AIR', 3);
define('TYPE_SUBMARINE', 4);

define('ID_INFANTRY', 1);
define('ID_ARTILLERY', 2);
define('ID_TANK', 3);
define('ID_AIRCRAFT', 4);

define('VARIABLES_SPLITTER', ':');

define('DEFAULT_BATTLE_LINE', ID_INFANTRY . VARIABLES_SPLITTER . ID_ARTILLERY . VARIABLES_SPLITTER . ID_TANK . VARIABLES_SPLITTER . ID_AIRCRAFT);
define('FIRING_SEQUENCE', ID_ARTILLERY . VARIABLES_SPLITTER . ID_AIRCRAFT . VARIABLES_SPLITTER . ID_INFANTRY . VARIABLES_SPLITTER . ID_TANK);

define('HEALED_UNITS_AFTER_LAND_BATTLE_MINIMUM', 0.1);
define('HEALED_UNITS_AFTER_LAND_BATTLE_MAXIMUM', 0.2);

define('PHASE_LANDMOVE', 1);
define('PHASE_SEAMOVE', 2);
define('PHASE_TRADEROUTE', 3);
define('PHASE_TROOPSMOVE', 4);
define('PHASE_PRODUCTION', 5);
define('PHASE_GAME_START', 21);
define('PHASE_SELECTSTART', 22);
define('PHASE_SETSHIPS', 23);

define('NEUTRAL_COUNTRY', -1);

define('MAX_LAND_ATTACKS', 1);

define('BATTLE_LINE_SIZE', 4);

define('MAX_MONEY_SPENDABLE', 10);

define('GAME_STATUS_NEW','new');
define('GAME_STATUS_STARTED','started');
define('GAME_STATUS_RUNNING','running');
define('GAME_STATUS_DONE','done');
define('GAME_STATUS_ALL','all');

define('ECONOMY_POOR','poor');
define('ECONOMY_WEAK','weak');
define('ECONOMY_NORMAL','normal');
define('ECONOMY_STRONG','strong');

define('RESOURCE_NONE', 0);
define('RESOURCE_OIL', 1);
define('RESOURCE_TRANSPORT', 2);
define('RESOURCE_INDUSTRY', 3);
define('RESOURCE_MINERALS', 4);
define('RESOURCE_POPULATION', 5);
