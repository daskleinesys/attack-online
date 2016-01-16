<?php
class Footer {
	public function run() {
		$xtpl = new XTemplate('./xtpl/general/footer.xtpl');

		$xtpl->parse('main');
		$xtpl->out('main');
	}
}
?>