<?php

namespace Attack\Model\Units\Interfaces;

abstract class ModelGameUnit {

    protected $id;
    protected $id_user;
    protected $id_unit;
    protected $id_game_area;
    protected $id_game;

    /**
     * creates the basic unit model
     *
     * @param $id int
     * @param $id_user int
     * @param $id_game int
     * @param $id_unit int
     * @param $id_game_area int
     */
    protected function __construct($id, $id_user, $id_game, $id_unit, $id_game_area) {
        $this->id = intval($id);
        $this->id_user = intval($id_user);
        $this->id_game = intval($id_game);
        $this->id_unit = intval($id_unit);
        $this->id_game_area = intval($id_game_area);
    }

    /**
     * @return int id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return int id_user
     */
    public function getIdUser() {
        return $this->id_user;
    }

    /**
     * @return int id_unit
     */
    public function getIdUnit() {
        return $this->id_unit;
    }

    /**
     * @return int id_game_area
     */
    public function getIdGameArea() {
        return $this->id_game_area;
    }

    /**
     * @return int id_game
     */
    public function getIdGame() {
        return $this->id_game;
    }

}
