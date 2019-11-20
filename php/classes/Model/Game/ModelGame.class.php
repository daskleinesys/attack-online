<?php
namespace Attack\Model\Game;

use Attack\Exceptions\ModelException;
use Attack\Model\Areas\ModelArea;
use Attack\Model\User\ModelColor;
use Attack\Model\Areas\ModelEconomy;
use Attack\Model\Game\Start\ModelStartSet;
use Attack\Database\SQLConnector;
use Attack\Tools\Iterator\ModelIterator;
use Attack\Model\User\ModelIsInGameInfo;
use Attack\Model\User\ModelInGamePhaseInfo;
use Attack\Model\User\ModelUser;
use Attack\Exceptions\NullPointerException;
use Attack\Exceptions\GameCreationException;
use Attack\Exceptions\DatabaseException;
use Attack\Exceptions\GameAdministrationException;
use Logger;

class ModelGame implements \JsonSerializable {

    private static $logger;

    // currently (seleced) game model
    private static $current_game = null;

    // list of all game models
    private static $games = array();

    // pre filled member_vars
    private $id; // int
    private $name; // string
    private $playerslots; // int
    private $id_creator; // int
    private $pw_protected; // bool
    private $status; // string
    private $id_phase; // int
    private $round; // int
    private $processing; // bool

    // resolved
    private $creator; // ModelUser
    private $phase; // ModelPhase
    private $players; // [ModelIsInGameInfo, ...]

    /**
     * creates new game object, fills in relevant info if id given, otherwise use create function to create new game
     *
     * @param int $id_game
     * @throws NullPointerException
     */
    private function __construct($id_game) {
        $this->id = intval($id_game);

        // fill game data
        if (!$this->fill_member_vars()) {
            throw new NullPointerException('Game not found.');
        }
        if (!isset(self::$logger)) {
            self::$logger = Logger::getLogger('ModelGame');
        }
    }

    /**
     * returns game model for given id
     *
     * @param int $id_game
     * @return ModelGame
     * @throws NullPointerException (if game not found)
     */
    public static function getGame($id_game) {
        if (isset(self::$games[$id_game])) {
            return self::$games[$id_game];
        }

        return self::$games[$id_game] = new ModelGame($id_game);
    }

    /**
     * returns an iterator for games
     *
     * @param string $status - define for game status
     * @param int $id_user
     * @return ModelIterator
     * @throws DatabaseException
     * @throws NullPointerException
     */
    public static function iterator($status = GAME_STATUS_ALL, $id_user = null)
    {
        $query = 'get_games';
        $dict = [];
        $games = [];
        if ($status !== GAME_STATUS_ALL) {
            $query .= '_by_status';
            $dict[':status'] = $status;
        }
        if ($id_user != null) {
            $query .= '_by_user';
            $dict[':id_user'] = intval($id_user);
        }
        $result = SQLConnector::Singleton()->epp($query, $dict);
        foreach ($result as $game) {
            $id_game = $game['id'];
            if (!isset(self::$games[$id_game])) {
                self::$games[$id_game] = new ModelGame($id_game);
            }
            $games[] = self::$games[$id_game];
        }

        return new ModelIterator($games);
    }

    /**
     * tries to create a new game - returns true on success
     *
     * @param string $name
     * @param int $players
     * @param int $id_creator
     * @param string $password
     * @return ModelGame
     * @throws GameCreationException
     */
    public static function createGame($name, $players, $id_creator, $password) {
        $result = SQLConnector::Singleton()->epp('check_game_name', array(':name' => $name));
        if (!empty($result)) {
            throw new GameCreationException('Spielname bereits vergeben!');
        }
        $dict = array();
        $dict[':game_name'] = $name;
        $dict[':players'] = $players;
        $dict[':id_creator'] = $id_creator;
        if (empty($password)) {
            $query = 'insert_game';
        } else {
            $query = 'insert_game_with_password';
            $dict[':password'] = $password;
        }
        SQLConnector::Singleton()->epp($query, $dict);
        $result = SQLConnector::Singleton()->epp('check_game_name', array(':name' => $name));
        $id_game = intval($result[0]['id']);
        return self::getGame($id_game);
    }

    /**
     * deletes all tables,rows from database for this game
     *
     * @param int $id_game
     * @return bool - true if successfull
     * @throws GameAdministrationException - if game isn't loaded or the game isn't new
     */
    public static function deleteGame($id_game) {
        try {
            $game = self::getGame($id_game);
        } catch (NullPointerException $ex) {
            throw new GameAdministrationException('Game not found.');
        }
        if ($game->getStatus() !== GAME_STATUS_NEW) {
            throw new GameAdministrationException('Only new games can be deleted.');
        }
        ModelInGamePhaseInfo::deleteInGamePhaseInfos(null, $id_game);
        ModelIsInGameInfo::deleteIsInGameInfos(null, $id_game);
        $dict = array(':id_game' => $id_game);
        SQLConnector::Singleton()->epp('delete_game', $dict);

        unset(self::$games[$id_game]);

        return true;
    }

    /**
     * returns all ids of all games where everybody is finished
     *
     * @return array(int id_game)
     */
    public static function getGamesForProcessing() {
        $query = 'get_games_for_processing';
        $output = array();
        $result = SQLConnector::getInstance()->epp($query);
        foreach ($result as $line) {
            $output[] = $line['id'];
        }
        return $output;
    }

    /**
     * sets the current game model to game-model using given id
     *
     * @param int $id_game
     * @return ModelGame
     * @throws NullPointerException if game not found
     */
    public static function setCurrentGame($id_game) {
        $id = intval($id_game);

        // set game model
        self::$current_game = self::getGame($id);

        return self::getCurrentGame();
    }

    /**
     * returns the currently selected game, or null if no game selected
     *
     * @return ModelGame/null
     */
    public static function getCurrentGame() {
        return self::$current_game;
    }

    /**
     * @brief returns all view-relevant game-data as associative array
     *
     * @return array
     */
    public function getViewData() {
        $data = array(
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'id_phase' => $this->id_phase,
            'round' => $this->round,
            'processing' => $this->processing
        );

        return $data;
    }

    /**
     * sets game status of a new game to GAME_STATUS_STARTED
     *
     * @return bool
     * @throws GameAdministrationException
     * @throws ModelException
     */
    public function startGame() {
        if ($this->status !== GAME_STATUS_NEW) {
            throw new GameAdministrationException('Only new games can be started.');
        }

        // allocate starting sets to users
        $iter_player = ModelIsInGameInfo::iterator(null, $this->id);
        $players = $iter_player->size();
        $iter_sets = ModelStartSet::iterator($players, true);
        while ($iter_player->hasNext()) {
            if (!$iter_sets->hasNext()) {
                throw new GameAdministrationException('Not enough starting sets found!');
            }
            $iter_player->next()->setStartingSet($iter_sets->next()->getId());
        }

        // allocate resources
        $iter_poor = ModelEconomy::iterator(ECONOMY_POOR);
        $iter_weak = ModelEconomy::iterator(ECONOMY_WEAK);
        $iter_normal = ModelEconomy::iterator(ECONOMY_NORMAL);
        $iter_strong = ModelEconomy::iterator(ECONOMY_STRONG);
        $iter_areas = ModelArea::iterator(TYPE_LAND);
        while ($iter_areas->hasNext()) {
            $_Area = $iter_areas->next();
            switch ($_Area->getEconomy()) {
                case ECONOMY_POOR:
                    $_Eco = $iter_poor->next();
                    break;
                case ECONOMY_WEAK:
                    $_Eco = $iter_weak->next();
                    break;
                case ECONOMY_NORMAL:
                    $_Eco = $iter_normal->next();
                    break;
                case ECONOMY_STRONG:
                    $_Eco = $iter_strong->next();
                    break;
                default:
                    throw new ModelException('Area with invalid eco type found: ' . $_Area->getId());
                    break;
            }
            ModelGameArea::setGameArea($this->id, NEUTRAL_COUNTRY, $_Area->getId(), $_Eco->getIdResource(), $_Eco->getResPower());
        }

        // create sea areas
        $iter_sea = ModelArea::iterator(TYPE_SEA);
        while ($iter_sea->hasNext()) {
            $_Area = $iter_sea->next();
            ModelGameArea::setGameArea($this->id, NEUTRAL_COUNTRY, $_Area->getId(), NO_RESOURCE, 0);
        }

        // set game to started
        $this->setStatus(GAME_STATUS_STARTED);

        return true;
    }

    /**
     * sets the game password, set to no password if null given
     *
     * @param string $password
     * @return void
     */
    public function setPassword($password = null) {
        $dict = array();
        $dict[':id_game'] = $this->id;
        if ($password === null) {
            $this->pw_protected = false;
            $query = 'set_game_password_null';
        } else {
            $this->pw_protected = true;
            $query = 'set_game_password';
            $dict[':password'] = $password;
        }
        SQLConnector::Singleton()->epp($query, $dict);
    }

    /**
     * sets the game status (and if necessary also changes the phase)
     *
     * @param string $status - ENUM(new, started, running, done)
     * @return void
     */
    public function setStatus($status) {
        if ($this->status === $status) {
            return;
        }
        $query = 'set_game_status';
        $dict = array();
        $dict[':id_game'] = $this->id;
        $dict[':status'] = $status;
        try {
            SQLConnector::Singleton()->epp($query, $dict);
            $this->status = $status;
        } catch (DatabaseException $ex) {
            self::$logger->error($ex);
            return;
        }

        if ($this->status === GAME_STATUS_NEW) {
            $this->setPhase(PHASE_GAME_START);
        } else if ($this->status === GAME_STATUS_STARTED && $this->id_phase < PHASE_SELECTSTART) {
            $this->setPhase(PHASE_SELECTSTART);
        } else if ($this->status === GAME_STATUS_RUNNING && $this->id_phase >= GAME_STATUS_STARTED) {
            $this->setPhase(PHASE_LANDMOVE);
        }
    }

    /**
     * sets the game phase (and if necessary also changes the status)
     *
     * @param int $id_phase
     * @return void
     * @throws NullPointerException
     */
    public function setPhase($id_phase) {
        $id_phase = intval($id_phase);
        if ($this->id_phase === $id_phase) {
            return;
        }
        ModelPhase::getPhase($id_phase);

        $query = 'set_game_phase';
        $dict = array();
        $dict[':id_game'] = $this->id;
        $dict[':id_phase'] = $id_phase;
        SQLConnector::Singleton()->epp($query, $dict);
        $this->id_phase = $id_phase;

        if ($this->status === GAME_STATUS_DONE) {
            return;
        }
        if ($this->id_phase < PHASE_GAME_START) {
            $this->setStatus(GAME_STATUS_RUNNING);
        } else if ($this->id_phase === PHASE_GAME_START) {
            $this->setStatus(GAME_STATUS_NEW);
        } else if ($this->id_phase > PHASE_GAME_START) {
            $this->setStatus(GAME_STATUS_STARTED);
        }
    }

    /**
     * moves the game in the next phase, updates phase and game_round and status if necessary
     *
     * @return void
     */
    public function moveToNextPhase() {
        $phases = [
            PHASE_LANDMOVE, PHASE_SEAMOVE, PHASE_TRADEROUTES, PHASE_TROOPSMOVE, PHASE_PRODUCTION,
            PHASE_GAME_START, PHASE_SELECTSTART, PHASE_SETSHIPS
        ];

        // check which phase is next
        $add_round = false;
        $pos = array_search($this->id_phase, $phases);

        if (!isset($phases[$pos + 1])) {
            $next_phase = $phases[0];
            $add_round = true;
        } elseif ($this->status === GAME_STATUS_RUNNING && $phases[$pos + 1] >= PHASE_GAME_START) {
            $next_phase = $phases[0];
            $add_round = true;
        } else {
            $next_phase = $phases[$pos + 1];
        }

        // add round and set phase
        if ($add_round) {
            $this->nextRound();
        }
        $this->setPhase($next_phase);
    }

    /**
     * set to processing to block further action while calculating logic
     *
     * @param $processing bool
     * @return void
     */
    public function setProcessing($processing) {
        if ($processing === true) {
            // set game to processing
            $query = 'set_game_processing';
            $dict = array(':id_game' => $this->id);
            SQLConnector::getInstance()->epp($query, $dict);
            $this->processing = true;
        } else if ($processing === false) {
            // set game to processing done
            $query = 'set_game_processing_done';
            $dict = array(':id_game' => $this->id);
            SQLConnector::getInstance()->epp($query, $dict);
            $this->processing = false;
        }
    }

    /**
     * @return bool - true if game is processing
     */
    public function checkProcessing() {
        return $this->processing;
    }

    /**
     * @return bool - true if this game has a password set
     */
    public function checkPasswordProtection() {
        return $this->pw_protected;
    }

    /**
     * @param string $password
     * @return bool - true if password is correct
     */
    public function checkPassword($password) {
        $result = SQLConnector::Singleton()->epp('check_game_password', array(':id_game' => $this->id, ':password' => $password));
        if (empty($result)) {
            return false;
        }
        return true;
    }

    /**
     * @param int $id_color
     * @return boolean
     * @throws NullPointerException - if this color doesn't exist
     */
    public function checkIfColorIsFree($id_color) {
        $id_color = intval($id_color);
        ModelColor::getModelColor($id_color);
        $iter = ModelIsInGameInfo::iterator(null, $this->id);
        while ($iter->hasNext()) {
            if ($iter->next()->getIdColor() == $id_color) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return array(int id => dict(id = int, color = string))
     */
    public function getFreeColors() {
        $colors_taken = array();
        $output = array();
        // array with taken colors
        $iter = ModelIsInGameInfo::iterator(null, $this->id);
        while ($iter->hasNext()) {
            $colors_taken[] = $iter->next()->getIdColor();
        }

        $iter = ModelColor::iterator();
        while ($iter->hasNext()) {
            $_Color = $iter->next();
            if (in_array($_Color->getId(), $colors_taken)) {
                continue;
            }
            $output[$_Color->getId()] = array('id' => $_Color->getId(), 'color' => $_Color->getName());
        }
        return $output;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string/enum (new, started, running, done)
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getPlayerSlots() {
        return $this->playerslots;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getFreeSlots() {
        return ($this->playerslots - $this->getNumberOfPlayers());
    }

    /**
     * @return int
     */
    public function getIdCreator() {
        return $this->id_creator;
    }

    /**
     * @return ModelUser
     */
    public function getCreator() {
        return ModelUser::getUser($this->id_creator);
    }

    /**
     * @return int
     */
    public function getNumberOfPlayers() {
        $iter = ModelIsInGameInfo::iterator(null, $this->id);
        return $iter->size();
    }

    /**
     * @return int
     */
    public function getIdPhase() {
        return $this->id_phase;
    }

    /**
     * @return int
     */
    public function getRound() {
        return $this->round;
    }

    /**
     * resolves creator, phase and player models
     *
     * @return void
     * @throws NullPointerException
     * @throws DatabaseException
     */
    public function resolve() {
        $this->creator = ModelUser::getUser($this->id_creator);
        $this->phase = ModelPhase::getPhase($this->id_phase);
        $this->players = [];
        $iterator = ModelIsInGameInfo::iterator(null, $this->id);
        while ($iterator->hasNext()) {
            $is_in_game = $iterator->next();
            $is_in_game->resolve();
            $this->players[] = $is_in_game;
        }
    }

    private function fill_member_vars() {
        // check if there is a game
        $result = SQLConnector::Singleton()->epp('get_game_by_id', array(':id_game' => $this->id));
        if (empty($result)) {
            return false;
        }

        // fill in info
        $data = $result[0];
        $this->name = $data['name'];
        $this->playerslots = intval($data['players']);
        $this->id_creator = intval($data['id_creator']);
        if ($data['password'] === null) {
            $this->pw_protected = false;
        } else {
            $this->pw_protected = true;
        }
        $this->status = $data['status'];
        $this->id_phase = intval($data['id_phase']);
        $this->round = intval($data['round']);
        if ((int)$data['processing'] === 1) {
            $this->processing = true;
        } else {
            $this->processing = false;
        }

        return true;
    }

    private function nextRound() {
        ++$this->round;

        $query = 'set_game_round';
        $dict = array();
        $dict[':id_game'] = $this->id;
        $dict[':round'] = $this->round;
        SQLConnector::getInstance()->epp($query, $dict);
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'playerslots' => $this->playerslots,
            'id_creator' => $this->id_creator,
            'pw_protected' => $this->pw_protected,
            'status' => $this->status,
            'id_phase' => $this->id_phase,
            'round' => $this->round,
            'processing' => $this->processing,
            'creator' => $this->creator,
            'phase' => $this->phase,
            'players' => $this->players,
        ];
    }
}
