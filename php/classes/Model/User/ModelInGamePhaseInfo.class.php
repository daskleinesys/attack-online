<?php
namespace AttOn\Model\User;
use AttOn\Model\Atton\ModelPhase;
use AttOn\Model\DataBase\DataSource;
use AttOn\Model\Iterator\ModelIterator;
use AttOn\Exceptions\NullPointerException;

class ModelInGamePhaseInfo {

    // list of all models
    // $models = dict(int id_user = array(int id_game = ModelInGamePhaseInfo)))
    private static $models = array();

    // member vars
    private $id_user; // int
    private $id_game; // int
    private $notification_rules = array(); // dict(int id_phase = boolean)
    private $is_ready = array(); // dict(int id_phase = boolean)

    /**
     * returns the specific model, if no id_game given, default rules are loaded (id_game == 0)
     *
     * @param $id_user int
     * @param $id_game int
     * @throws NullPointerException
     * @return ModelInGamePhaseInfo
     */
    private function __construct($id_user, $id_game = null) {
        if ($id_game === null) {
            $id_game = 0;
        }
        $this->id_user = intval($id_user);
        $this->id_game = intval($id_game);

        // check if user exists
        ModelUser::getUser($id_user);

        $this->fill_member_vars();
    }

    /**
     * returns the specific model, if no id_game given, default rules are loaded (id_game == 0)
     *
     * @param $id_user int
     * @param $id_game int
     * @throws NullPointerException
     * @return ModelInGamePhaseInfo
     */
    public static function getInGamePhaseInfo($id_user, $id_game = null) {
        if ($id_game == null) {
            $id_game = 0;
        }
        if (isset(self::$models[$id_user][$id_game])) {
            return self::$models[$id_user][$id_game];
        }

        return self::$models[$id_user][$id_game] = new ModelInGamePhaseInfo($id_user,$id_game);
    }

    /**
     * returns an iterator for all games the user is in or all user for a given game
     *
     * @param int $id_user
     * @throws DataSourceException
     * @return ModelIterator
     */
    public static function iterator($id_user = null, $id_game = null) {
        $models = array();
        $query = 'get_iig_ids';
        $dict = array();
        $dict[':id_user'] = ($id_user == null) ? '%' : intval($id_user);
        $dict[':id_game'] = ($id_game == null) ? '%' : intval($id_game);

        // query user
        $result = DataSource::Singleton()->epp($query, $dict);

        foreach ($result as $iig) {
            $id_game = $iig['id_game'];
            $id_user = $iig['id_user'];
            $models[] = self::getInGamePhaseInfo($id_user, $id_game);
        }

        return new ModelIterator($models);
    }

    /**
     * deletes all models and corresponding database infos
     *
     * @param $id_game int
     * @param $id_user int (if null all models for this game are deleted)
     * @return void
     */
    public static function deleteInGamePhaseInfos($id_game, $id_user = null) {
        $query = ($id_user == null) ? 'delete_game_phase_info_for_game' : 'delete_game_phase_info_for_user';
        $dict = array(':id_game' => intval($id_game));
        if ($id_user != null) {
            $dict[':id_user'] = intval($id_user);
        }
        DataSource::getInstance()->epp($query, $dict);

        if ($id_user === null) {
            foreach (array_keys(self::$models) as $key) {
                unset(self::$models[$key][$id_game]);
            }
        } else {
            unset(self::$models[$id_user][$id_game]);
        }
    }

    /**
     * sets the notification rule for specific phase
     *
     * @param id_phase int
     * @param rule boolean
     * @throws NullPointerException
     * @return void
     */
    public function setNotificationRule($id_phase, $rule) {
        $id_phase = intval($id_phase);
        if (!isset($this->notification_rules[$id_phase])) {
            throw new NullPointerException('No such rule found.');
        }
        $this->notification_rules[$id_phase] = ($rule) ? true : false;
        $query = 'update_ingame_notification_rule';
        $dict = array();
        $dict[':id_user'] = $this->id_user;
        $dict[':id_game'] = $this->id_game;
        $dict[':id_phase'] = $id_phase;
        $dict[':rule'] = $this->notification_rules[$id_phase];
        DataSource::getInstance()->epp($query,$dict);
    }

    /**
     * sets the is_ready info for specific phase
     *
     * @param $id_phase int
     * @param $is_ready boolean
     * @throws NullPointerException
     * @return void
     */
    public function setIsReady($id_phase,$is_ready) {
        $id_phase = intval($id_phase);
        if (!isset($this->is_ready[$id_phase])) {
            throw new NullPointerException('No such info found.');
        }
        $this->is_ready[$id_phase] = ($is_ready) ? true : false;
        $query = 'update_ingame_is_ready';
        $dict = array();
        $dict[':id_user'] = $this->id_user;
        $dict[':id_game'] = $this->id_game;
        $dict[':id_phase'] = $id_phase;
        $dict[':is_ready'] = $this->is_ready[$id_phase];
        DataSource::getInstance()->epp($query,$dict);
    }

    /**
     * @return int
     */
    public function getIdUser() {
        return $this->id_user;
    }

    /**
     * @return int (id_game == 0 => default rules)
     */
    public function getIdGame() {
        return $this->id_game;
    }

    /**
     * returns the rule for specific game and phase
     *
     * @throws NullPointerException
     * @return boolean
     */
    public function getNotificationRuleForPhase($id_phase) {
        if (!isset($this->notification_rules[$id_phase])) {
            throw new NullPointerException('No such rule found.');
        }
        return $this->notification_rules[$id_phase];
    }

    /**
     * returns all rules for specific user and game
     *
     * @return array(int id_phase => boolean)
     */
    public function getNotificationRules() {
        return $this->notification_rules;
    }

    /**
     * returns the info if user is ready for specific phase
     *
     * @throws NullPointerException
     * @return boolean
     */
    public function getIsReadyForPhase($id_phase) {
        if (!isset($this->is_ready[$id_phase])) {
            throw new NullPointerException('No such info found.');
        }
        return $this->is_ready[$id_phase];
    }

    /**
     * returns info if user is ready for all phases
     *
     * @return array(int id_phase => boolean)
     */
    public function getIsReady() {
        return $this->is_ready;
    }

    private function fill_member_vars() {
        $query = 'get_game_phase_info';
        $dict = array();
        $dict[':id_user'] = $this->id_user;
        $dict[':id_game'] = $this->id_game;
        $iter = ModelPhase::iterator();

        while ($iter->hasNext()) {
            $id_phase = $iter->next()->getId();
            $dict[':id_phase'] = $id_phase;
            $result = DataSource::getInstance()->epp($query,$dict);
            if (empty($result)) {
                $this->create_info($id_phase);
            } else {
                $this->notification_rules[$id_phase] = ($result[0]['notif_rule']) ? true : false;
                $this->is_ready[$id_phase] = ($result[0]['is_ready']) ? true : false;
            }
        }
    }

    private function create_info($id_phase) {
        $query = 'set_new_game_phase_info';
        $dict = array();
        $dict[':id_user'] = $this->id_user;
        $dict[':id_game'] = $this->id_game;
        $dict[':id_phase'] = $id_phase;

        if ($this->id_game === 0) {
            $dict[':rule'] = true;
            $dict[':is_ready'] = false;
        } else {
            $_Rule = ModelInGamePhaseInfo::getInGamePhaseInfo($this->id_user);
            $dict[':rule'] = $_Rule->getNotificationRuleForPhase($id_phase);
            $dict[':is_ready'] = $_Rule->getIsReadyForPhase($id_phase);
        }

        DataSource::getInstance()->epp($query,$dict);

        $this->notification_rules[$id_phase] = $dict[':rule'];
        $this->is_ready[$id_phase] = $dict[':is_ready'];
    }

}
