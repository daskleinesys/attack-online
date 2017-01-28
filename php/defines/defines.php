<?php
namespace Attack;

/*******************
 * general helpers *
 *******************/

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
