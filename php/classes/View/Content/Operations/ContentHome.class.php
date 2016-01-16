<?php
class ContentHome extends ContentOperation {

	public function run() {
		
		$this->xtpl->parse('main');
		$this->xtpl->out('main');
	}
}
?>