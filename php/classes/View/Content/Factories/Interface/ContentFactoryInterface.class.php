<?php
interface ContentFactoryInterface {
	public function getName();
	public function getOperation($id_user, $id_game);
}
?>