<?php
namespace AttOn;

// define error-codes
define('DATABASE_ERROR', -1);
define('GAMEVARS_NOT_INTEGER', -2);
define('MULTIPLE_USER_LEFT', -3);
define('OBJECT_NOT_SET', -7);

// writeBattleReport Errors
define('NO_BATTLE_REPORT_OPEN', -4);
define('USER_NOT_FOUND', -5);
define('BR_NOT_ENOUGH_USER', -6);

define('SUCCESS', 1);
define('BR_START_UNITS', 2);
define('BR_LOST_UNITS', 3);
define('BR_HEALED_UNITS', 4);
define('BR_BATTLE_LINE_UNITS', 5);
define('BR_HITTING_UNITS', 6);

define('BR_USER_ATTACKER', 7);
define('BR_USER_DEFENER', 8);
define('BR_STATUS_NORMAL', 9);
define('BR_STATUS_NML', 10);

// illegal move reasons
define('MOVE_ERROR_NO_REASON', 1);
define('MOVE_ERROR_TOO_MUCH_OUT', 2);
define('MOVE_ERROR_NO_IN_EMPTY', 3);
define('MOVE_ERROR_TOO_MUCH_ATTACKS', 4);
define('MOVE_ERROR_WRONG_AREA_TYPE', 5);
define('MOVE_ERROR_NOT_ENOUGH_SPEED', 6);
define('MOVE_ERROR_NO_PATH_FOUND', 7);
define('MOVE_ERROR_USER_NOT_OWNER', 8);
define('MOVE_ERROR_SHIP_NAME_ALREADY_TAKEN', 9);
define('MOVE_ERROR_NOT_ENOUGH_PP', 10);

// session status
define('CHECK_SESSION_GAME', 1); // user has a game_session active
define('CHECK_SESSION_MOD', 2); // user is a moderator
define('CHECK_SESSION_ADMIN', 3); // user is an admin
define('CHECK_SESSION_USER', 4); // check if user has an active acount
define('CHECK_SESSION_GAME_START', 5); // user has a game_session active (this game is in the starting phase)
define('CHECK_SESSION_NONE', 6); // no session required
define('CHECK_SESSION_GAME_RUNNING', 7); // user has a game_session and this game is running

// user login
define('USER_TOKEN_MAX_LENGTH', 50);

// user status
define('STATUS_USER_INACTIVE', 'inactive');
define('STATUS_USER_ACTIVE', 'active');
define('STATUS_USER_MODERATOR', 'moderator');
define('STATUS_USER_ADMIN', 'admin');
define('STATUS_USER_DELETED', 'deleted');
define('STATUS_USER_ALL', 'all');

// sort options user: id,name,lastname,login,email,status
define('SORT_BY_ID', 'id');
define('SORT_BY_NAME', 'name');
define('SORT_BY_LASTNAME', 'lastname');
define('SORT_BY_LOGIN', 'login');
define('SORT_BY_EMAIL', 'email');
define('SORT_BY_STATUS', 'status');

// information rules:
define('RULE_START', 1);
define('RULE_SELECT_AREAS', 2);
define('RULE_SET_SHIPS', 3);

// general
define('PAGE_TITLE', 'Attack Online');
define('ADMIN_MAIL', 'thomas.schagerl@gmx.net');
