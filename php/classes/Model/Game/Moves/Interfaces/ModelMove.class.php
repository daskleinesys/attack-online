<?php
namespace Attack\Model\Game\Moves\Interfaces;

use Attack\Database\SQLConnector;

abstract class ModelMove {

    protected $id;
    protected $id_user;
    protected $id_game;
    protected $id_phase;
    protected $round;
    protected $deleted;

    /**
     * returns the move model --> specific for phase
     *
     * @param $id_user int
     * @param $id_game int
     * @param $id_phase int
     * @param $id_move int
     * @param $round int
     * @param $deleted boolean
     */
    protected function __construct($id_user, $id_game, $id_phase, $id_move, $round, $deleted) {
        $this->id_user = intval($id_user);
        $this->id_game = intval($id_game);
        $this->id_phase = intval($id_phase);
        $this->id = intval($id_move);
        $this->round = intval($round);
        $this->deleted = ($deleted) ? true : false;
    }

    /**
     * @return int
     */
    public function getIdGame() {
        return $this->id_game;
    }

    /**
     * @return int
     */
    public function getIdMove() {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getIdUser() {
        return $this->id_user;
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
     * @return boolean
     */
    public function getDeleted() {
        return $this->deleted;
    }

    /**
     * @return boolean
     */
    public function isDeleted() {
        return $this->deleted;
    }

    /**
     * flag move as deleted
     * @return void
     */
    public function flagMoveDeleted() {
        $query = 'flag_move_deleted';
        $dict = array(':id_move' => $this->id);
        SQLConnector::getInstance()->epp($query, $dict);
        $this->deleted = true;
    }

}
