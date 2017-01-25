<?php
namespace Attack;

/*******************
 * general helpers *
 *******************/

// define error-codes
define('DATABASE_ERROR', -1);

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

// sort options user: id,name,lastname,login,email,status
define('SORT_BY_ID', 'id');
define('SORT_BY_NAME', 'name');
define('SORT_BY_LASTNAME', 'lastname');
define('SORT_BY_LOGIN', 'login');
define('SORT_BY_EMAIL', 'email');
define('SORT_BY_STATUS', 'status');
