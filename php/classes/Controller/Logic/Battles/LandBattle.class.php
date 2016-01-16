<?php

class LandBattle {
	private $units = array(); // $units[$id_user][$id_unit] = int count
	
	private $battle_lines = array(); // $units[$id_user][$id_unit] = int count
	
	private $fight_done = false;
	
	private $round = 0;
	private $winner;
	private $losers = array();
	
	/**
	 * create land battle
	 * needs at least 2 participants
	 * @param array $units[$id_user][$id_unit] = int count
	 */
	public function __construct($units) {
		foreach ($units as $id_user => $units_user) {
			if (!isset($this->units[$id_user])) $this->units[$id_user] = array();
			$iter = ModelLandUnit::iterator();
			while ($iter->hasNext()) {
				$_Unit = $iter->next();
				$id_unit = $_Unit->getId();
				$this->units[$id_user][$id_unit] = (isset($units[$id_user][$id_unit])) ? intval($units[$id_user][$id_unit]) : 0;
			}
		}
	}
	
	/**
	 * executes the battle
	 * @throws LogicException
	 * @return void
	 */
	public function fight() {
		if ($this->fight_done) throw new LogicException('Can\'t execute fight twice!');
		
		while (!$this->fight_done) {
			$this->round++;
			$this->buildBattleLines();
			
			// TODO: remove debug output
			echo '<pre>';
			echo "<br>round: $this->round<br>";
			echo '<br>units:<br>';
			print_r($this->units);
			echo '<br><br>battle_lines:<br>';
			print_r($this->battle_lines);
			
			$this->calculateHitsAndTakeLosses();
			$this->checkIfFinished();
		}
	}
	
	/**
	 * returns the winner, but only if battle already over
	 * @throws LogicException
	 * @return int $id_user
	 */
	public function getWinner() {
		if (!$this->fight_done) throw new LogicException('No Winner - Battle not fought yet!');
		return $this->winner;
	}
	
	/**
	 * returns the losers, but only if battle already over
	 * @throws LogicException
	 * @return array(int $id_user)
	 */
	public function getLosers() {
		if (!$this->fight_done) throw new LogicException('No Losers - Battle not fought yet!');
		return $this->losers;
	}
	
	private function buildBattleLines() {
		foreach ($this->units as $id_user => $units_user) {
			$this->battle_lines[$id_user] = array();
			$line_size = 0;
			while ($line_size < BATTLE_LINE_SIZE) {
				$old_size = $line_size;
				$iter = ModelLandUnit::iterator();
				while ($iter->hasNext()) {
					$_Unit = $iter->next();
					$id_unit = $_Unit->getId();
					if (!isset($this->battle_lines[$id_user][$id_unit])) $this->battle_lines[$id_user][$id_unit] = 0;
					if ($this->battle_lines[$id_user][$id_unit] < $this->units[$id_user][$id_unit]) {
						$this->battle_lines[$id_user][$id_unit]++;
						$line_size++;
					}
					if ($line_size >= BATTLE_LINE_SIZE) break;
				}
				if ($old_size == $line_size) break;
			}
		}
	}
	
	private function calculateHitsAndTakeLosses() {
		foreach ($this->units as $id_user => $units_user) {
			
			
		}
		
		
		// TODO: coden
		foreach ($this->units as $id_user => $units_user) {
			foreach ($units_user as $id_unit => $count) {
				if ($this->units[$id_user][$id_unit] > 0) $this->units[$id_user][$id_unit]--;
			}
		}
	}
	
	private function checkIfFinished() {
		$users_left = array();
		$users_dead = array();
		foreach ($this->units as $id_user => $units_user) {
			$count_all = 0;
			foreach ($units_user as $id_unit => $count) {
				$count_all += $count;
			}
			if ($count_all > 0) {
				$users_left[] = $id_user;
			} else {
				$users_dead[] = $id_user;
			}
		}
		if (count($users_left) > 1) {
			return;
		} else if (count($users_left == 1)) {
			$this->winner = reset($users_left);
		} else {
			$this->winner = NEUTRAL_COUNTRY;
		}
		$this->losers = $users_dead;
		$this->fight_done = true;
	}
}

?>