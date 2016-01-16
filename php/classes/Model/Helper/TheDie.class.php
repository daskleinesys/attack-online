<?php
class TheDie {
	private $sides;
	private $outcomes = array(); // array(int)
	
	/**
	 * creates a x-sided dice
	 * 1 < x <= 20
	 * @param $sides
	 */
	public function __construct($sides) {
		$this->sides = intval($sides);
	}
	
	/**
	 * returns the next random int
	 * @return int
	 */
	public function rollTheDie() {
		if (count($this->outcomes) < 10) {
			$this->loadValues();
		}
		
		return array_pop($this->outcomes);
	}
	
	private function loadValues() {
		// http://www.random.org/integers/?num=1000&min=1&max=6&col=1&base=10&format=plain&rnd=new
		$url = 'http://www.random.org/integers/?num=1000&min=1&col=1&base=10&format=plain&rnd=new';
		$url = $url . '&max=' . $this->sides;
		
		$result = file_get_contents($url);
		
		foreach (explode(PHP_EOL, $result) as $line) {
			$x = intval($line);
			if ($x > 0) $this->outcomes[] = $x;
		}
	}
}
?>