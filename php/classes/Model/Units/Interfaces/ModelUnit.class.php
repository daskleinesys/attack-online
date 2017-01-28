<?php

namespace Attack\Model\Units\Interfaces;

abstract class ModelUnit {

	protected $id;
	protected $name;
	protected $abbreviation;
	protected $price;
	protected $speed;
	protected $id_type;

	/**
	 * creates the basic unit model
     *
     * @param $id int
     * @param $name string
     * @param $abbreviation string
     * @param $price int
     * @param $speed int
     * @param $id_type int
	 */
	protected function __construct($id, $name, $abbreviation, $price, $speed, $id_type) {
		$this->id = intval($id);
		$this->name = $name;
		$this->abbreviation = $abbreviation;
		$this->price = intval($price);
		$this->speed = intval($speed);
		$this->id_type = intval($id_type);
	}

	/**
	 * @return int id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getAbbreviation() {
		return $this->abbreviation;
	}

	/**
	 * @return int
	 */
	public function getPrice() {
		return $this->price;
	}

	/**
	 * @return int
	 */
	public function getSpeed() {
		return $this->speed;
	}

	/**
	 * @return int
	 */
	public function getIdType() {
		return $this->id_type;
	}

}
