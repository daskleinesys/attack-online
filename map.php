<?php
	session_start();
	include_once('./libs/xtpl/XTemplate.class.php');
	require_once('./defines/appvars.php');
	require_once('./defines/connectvars.php');
	$xtpl = new XTemplate('./xtpl/map/map.xtpl');
	
	$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	mysqli_set_charset($dbc,'utf8');
	
	if (!isset($_SESSION['game_id']) || !isset($_SESSION['user_id'])) {
		die('Login and select game first!');
	}
	
	$game_id = $_SESSION['game_id'];
	$user_id = $_SESSION['user_id'];
	
	$error_msg = NULL;
	
	// game-daten querien
	$query = "SELECT g.status, p.label FROM games g JOIN phases p ON (g.id_phase = p.id) WHERE g.id = $game_id";
	
	//require_once('./round.php');
	$gameinfo = mysqli_fetch_array(mysqli_query($dbc,$query));
	$xtpl->assign('game',$gameinfo);
	
	// spiel laeuft - GAME == first atton game ever
	if ((($gameinfo['status']=='running') || (($gameinfo['status']=='started') && ($gameinfo['label']=='setships'))) && intval($game_id) == 39) {
		$query = "SELECT areas.id AS ctrnr, areas.name AS country, areas.coords_small AS coords, areas.x AS posleft, areas.y AS postop, areas.height AS height, areas.width AS width, user.login AS user, user.id AS id_area_user, " . 
		"resources.name AS resource, zareas.productivity AS prod, zareas.tank AS tank, units.name AS unit, zunits.numberof AS unr, zunits.name AS shipname, zunits.id_zsea AS coastnr, " . 
		"coasts.name AS coast, colors.name AS color, zunits.id_type AS unit_type, iig.id_game AS id_game, units.id AS id_unit, areas.id_type AS areas_type, " . 
		"areas.xres AS xres, areas.yres AS yres, TR.id AS id_TR, TR.id_area1 AS TR_area1, TR_area1.name AS TR_area1_name, TR.id_area2 AS TR_area2, TR_area2.name AS TR_area2_name, " . 
		"resources.label AS res_label, zships.id_user AS id_ship_user, " . 
		"zships.name AS shipname_atsea, zships.id_zsea AS seanr, zships.id AS id_ship, zships.id_unit AS id_shiptype_atsea, ships.name AS shipclass_atsea, zships.id_zarea AS coastnr_atsea, " . 
		"a2a.id_area2 AS id_adjacent, ships_user.login AS ships_user " . 
		"FROM z" . $game_id . "_areas AS zareas " . 
		"LEFT JOIN resources AS resources ON (zareas.id_resource=resources.id) " . 
		"LEFT JOIN z" . $game_id . "_units AS zunits ON (zunits.id_zarea=zareas.id) " . 
		"LEFT JOIN areas AS areas ON (zareas.id_area=areas.id) " . 
		"LEFT JOIN areas AS coasts ON (zunits.id_zsea=coasts.id) " . 
		"LEFT JOIN units AS units ON (zunits.id_unit=units.id) " . 
		"LEFT JOIN user AS user ON (zareas.id_user=user.id) " . 
		"LEFT JOIN is_in_game AS iig ON (zareas.id_user=iig.id_user) " . 
		"LEFT JOIN colors AS colors ON (colors.id=iig.id_color) " . 
		"LEFT JOIN z" . $game_id . "_traderoutes AS zTR ON (zareas.id_user=zTR.id_user) " . 
		"LEFT JOIN traderoutes AS TR ON (TR.id=zTR.id_traderoute1) " . 
		"LEFT JOIN areas AS TR_area1 ON (TR.id_area1=TR_area1.id) " . 
		"LEFT JOIN areas AS TR_area2 ON (TR.id_area2=TR_area2.id) " . 
		"LEFT JOIN z" . $game_id . "_units AS zships ON (zships.id_zsea=zareas.id) " . 
		"LEFT JOIN units AS ships ON (zships.id_unit=ships.id) " . 
		"LEFT JOIN a2a AS a2a ON (areas.id=a2a.id_area1) " . 
		"LEFT JOIN user AS ships_user ON (zships.id_user=ships_user.id) " . 
		"WHERE iig.id_game = $game_id OR iig.id_game IS NULL " . 
		"ORDER BY areas.id, coasts.name, zunits.id_unit ASC, zships.id_unit ASC, zships.id_user ASC";
	}
	
	// spiel laeuft
	else if (($gameinfo['status']=='running') || (($gameinfo['status']=='started') && ($gameinfo['label']=='setships'))) {
		$query = "SELECT areas.id AS ctrnr, areas.name AS country, areas.coords_small AS coords, areas.x AS posleft, areas.y AS postop, areas.height AS height, areas.width AS width, user.login AS user, user.id AS id_area_user, " . 
		"resources.name AS resource, zareas.productivity AS prod, zareas.tank AS tank, units.name AS unit, zunits_land.count AS unr, zunits_sea.name AS shipname, zunits_harbor.id_zarea AS coastnr, " . 
		"coasts.name AS coast, colors.name AS color, units.id_type AS unit_type, iig.id_game AS id_game, units.id AS id_unit, areas.id_type AS areas_type, " . 
		"areas.xres AS xres, areas.yres AS yres, TR.id AS id_TR, TR.id_area1 AS TR_area1, TR_area1.name AS TR_area1_name, TR.id_area2 AS TR_area2, TR_area2.name AS TR_area2_name, " . 
		"resources.label AS res_label, " . 
		"a2a.id_area2 AS id_adjacent " . 
		"FROM z" . $game_id . "_areas AS zareas " . 
		"LEFT JOIN resources AS resources ON (zareas.id_resource=resources.id) " . 
		"LEFT JOIN z" . $game_id . "_units AS zunits ON (zunits.id_zarea=zareas.id) " . 
		"LEFT JOIN z" . $game_id . "_units_land AS zunits_land ON (zunits.id=zunits_land.id_zunit) " . 
		"LEFT JOIN z" . $game_id . "_units_sea AS zunits_sea ON (zunits.id=zunits_sea.id_zunit) " . 
		"LEFT JOIN z" . $game_id . "_units_in_harbor AS zunits_harbor ON (zunits.id=zunits_harbor.id_zunit) " . 
		"LEFT JOIN areas AS areas ON (zareas.id_area=areas.id) " . 
		"LEFT JOIN areas AS coasts ON (zunits_harbor.id_zarea=coasts.id) " . 
		"LEFT JOIN units AS units ON (zunits.id_unit=units.id) " . 
		"LEFT JOIN user AS user ON (zareas.id_user=user.id) " . 
		"LEFT JOIN is_in_game AS iig ON (zareas.id_user=iig.id_user) " . 
		"LEFT JOIN colors AS colors ON (colors.id=iig.id_color) " . 
		"LEFT JOIN z" . $game_id . "_traderoutes AS zTR ON (zareas.id_user=zTR.id_user) " . 
		"LEFT JOIN traderoutes AS TR ON (TR.id=zTR.id_traderoute1) " . 
		"LEFT JOIN areas AS TR_area1 ON (TR.id_area1=TR_area1.id) " . 
		"LEFT JOIN areas AS TR_area2 ON (TR.id_area2=TR_area2.id) " . 
		"LEFT JOIN a2a AS a2a ON (areas.id=a2a.id_area1) " . 
		"WHERE iig.id_game = $game_id OR iig.id_game IS NULL " . 
		"ORDER BY areas.id, coasts.name, zunits.id_unit ASC, zunits_sea.id_zunit ASC, zunits.id_user ASC";
	}
	
	// laender muessen noch gewaehlt werden
	else if (($gameinfo['status']=='started') && ($gameinfo['label']=='selectstart')) {
		$query = "SELECT areas.id AS ctrnr, areas.name AS country, areas.coords_small AS coords, areas.x AS posleft, areas.y AS postop, areas.height AS height, areas.width AS width, user.login AS user, " . 
		"resources.name AS resource, zareas.productivity AS prod, colors.name AS color, iig.id_game, areas.id_type AS areas_type, resources.label AS res_label, " . 
		"startreg.options AS options, optypes.countries AS countries, optypes.units AS units, areas.xres AS xres, areas.yres AS yres " . 
		"FROM z" . $game_id . "_areas AS zareas " . 
		"LEFT JOIN resources AS resources ON (zareas.id_resource=resources.id) " . 
		"LEFT JOIN areas AS areas ON (zareas.id_area=areas.id) " . 
		"LEFT JOIN startregions AS startreg ON (areas.id=startreg.id_area) " . 
		"LEFT JOIN is_in_game AS iig ON (startreg.id_set=iig.id_set) " . 
		"LEFT JOIN user AS user ON (iig.id_user=user.id) " . 
		"LEFT JOIN colors AS colors ON (colors.id=iig.id_color) " . 
		"LEFT JOIN optiontypes AS optypes ON (optypes.id=startreg.id_optiontype) " . 
		"ORDER BY areas.id ASC";
	}
	
	// game not in the right status
	else die('Select a running game.');
	
	$countrydata = mysqli_query($dbc,$query);
	$actualCountryId = 0;
	$actualUnitType = 0;
	$actualShipID_atSea = NULL;
	$actualOption = 0;
	$actualId_game = 0;
	$shipTypeCount = 0;
	$shipTypeCount_atSea = 0;
	$shipTypeCount_atSea_otherUser = 0;
	$shipCount = 0;
	$unitCount = 0;
	$actualTank = 0;
	$traderouteIDs = array();
	$parsedShips = array();
	$parsedAdjacentCountries = array();
	$xtpl->assign('color','empty');
	$xtpl->assign('area_type','empty');
	
	while ($country = mysqli_fetch_array($countrydata)) {
	
		if (($actualOption > 0) && ($actualId_game == $game_id)) {
			$xtpl->parse('main.country.options');
			$xtpl->parse('main.infobox_float.options');
		}
		if (($actualCountryID != $country['ctrnr']) && ($actualCountryID != 0)) {
			
			if (($actualUser) || ($actualresource > 0)) {
				$xtpl->parse('main.country.user');
				$xtpl->parse('main.infobox_float.user');
			}
			if ($actualresource > 0) {
				$xtpl->parse('main.country.resource');
				$xtpl->parse('main.infobox_float.resource');
				if (($_GET[resources] == 'yes') || (!isset($_GET[resources]))) {
					$xtpl->parse('main.country.resourceimg');
				}
			}
			if ($actualTank > 0) {
				$xtpl->parse('main.country.tankimg');
			}
			if ($countryHAStraderoute == true) {
				$xtpl->parse('main.country.traderoute');
				$xtpl->parse('main.infobox_float.traderoute');
			}
			if (($countryHASunits == true)) {
				if ($unitCount > 50) {
					$unitCount = 50;
				}
				if ($shipCount > 21) {
					$shipCount = 21;
				}
				$showUnitImg = false;
				$landunitsShown = false;
				if (($_GET[landunits] == 'yes') || (!isset($_GET[landunits]))) {
					$xtpl->assign('unitCount',$unitCount);
					$xtpl->parse('main.country.unitimg.landunits');
					$showUnitImg = true;
					$landunitsShown = true;
				}
				if (($_GET[seaunits] == 'yes') || (!isset($_GET[seaunits]))) {
					$xtpl->assign('shipCount',$shipCount);
					$xtpl->parse('main.country.unitimg.seaunits');
					$showUnitImg = true;
					if ($landunitsShown) {
						$xtpl->assign('seaunits','seaunits');
					}
					else {
						$xtpl->assign('seaunits','');
					}
				}
				if ($showUnitImg) {
				$xtpl->parse('main.country.unitimg');
				}
			}
			// parse ships in harbor
			if ($shipTypeCount != 0) {
				$xtpl->assign('howmanyships',$shipTypeCount);
				$xtpl->parse('main.country.ship');
				$shipTypeCount = 0;
			}
			// parse ships at sea
			if ($shipTypeCount_atSea != 0) {
				if ($shipTypeCount_atSea_otherUser != 0) {
					$xtpl->assign('howmanyships_atsea_otheruser',$shipTypeCount_atSea_otherUser);
					$xtpl->parse('main.country.shipatsea.otheruser');
				}
				$xtpl->assign('howmanyships_atsea',$shipTypeCount_atSea);
				$xtpl->parse('main.country.shipatsea');
				$shipTypeCount_atSea = 0;
				$shipTypeCount_atSea_otherUser = 0;
			}
			$xtpl->assign('adjacentCountries',$adjacentCountries);
			$xtpl->parse('main.country');
			$xtpl->parse('main.infobox_float');
			$xtpl->parse('main.country_imagemap');
			$xtpl->assign('user','');
			$xtpl->assign('color','empty');
			$xtpl->assign('area_type','empty');
			$xtpl->assign('options','');
			$xtpl->assign('countries','');
			$xtpl->assign('units','');
			$actualUnitID = NULL;
			$actualShipID_atSea = NULL;
			$countryHAStraderoute = false;
			$countryHASunits = false;
			$unitCount = 0;
			$shipCount = 0;
			$shipTypeCount = 0;
			$shipTypeCount_atSea = 0;
			$shipTypeCount_atSea_otherUser = 0;
			$actualTank = 0;
			$traderouteIDs = array();
			$parsedAdjacentCountries = array();
			$adjacentCountries = NULL;
		}
		/*if ($country['color'] == NULL) {
			$country['color'] = 'yellow';
		}*/
		if ($country['id_game'] == $game_id) {
			$xtpl->assign('user',$country['user']);
			if ($country['color']) {
				$xtpl->assign('color',$country['color']);
			}
			else {
				$xtpl->assign('color','empty');
			}
			$xtpl->assign('options',$country['options']);
			$xtpl->assign('countries',$country['countries']);
			$xtpl->assign('units',$country['units']);
		}
		if (($country['id_game'] == $game_id) && ($country['user'])) {
			if ($country['areas_type'] == 1) {
				$xtpl->assign('area_type','country');
			}
			if ($country['areas_type'] == 2) {
				$xtpl->assign('area_type','sea');
			}
		}
		// parse ships in harbor
		if (($actualUnitID != $country[id_unit]) && ($country['unit_type'] == 2) && ($shipTypeCount != 0)) {
			$xtpl->assign('howmanyships',$shipTypeCount);
			$xtpl->parse('main.country.ship');
			$shipTypeCount = 0;
		}
		// parse ships at sea
		if (($actualShipID_atSea != $country[id_shiptype_atsea]) && ($shipTypeCount_atSea != 0)) {
			if ($shipTypeCount_atSea_otherUser != 0) {
				$xtpl->assign('howmanyships_atsea_otheruser',$shipTypeCount_atSea_otherUser);
				$xtpl->parse('main.country.shipatsea.otheruser');
			}
			$xtpl->assign('howmanyships_atsea',$shipTypeCount_atSea);
			$xtpl->parse('main.country.shipatsea');
			$shipTypeCount_atSea = 0;
			$shipTypeCount_atSea_otherUser = 0;
		}
		$xtpl->assign('country',$country);
		
		// traderoutes anzeigen
		if ($actualTRID != $country['id_TR']) {
			if ($country[TR_area1] == $country[ctrnr]) {
				if (($_GET[traderoutes] == 'yes') || (!isset($_GET[traderoutes]))) {
					if (!in_array($country[id_TR], $traderouteIDs)) {
						$xtpl->parse('main.country.traderoute.area2');
						$xtpl->parse('main.infobox_float.traderoute.area2');
						$xtpl->parse('main.country.traderouteimg');
						$countryHAStraderoute = true;
						array_push($traderouteIDs,$country[id_TR]);
					}
				}
			}
			if ($country[TR_area2] == $country[ctrnr]) {
				if (($_GET[traderoutes] == 'yes') || (!isset($_GET[traderoutes]))) {
					if (!in_array($country[id_TR], $traderouteIDs)) {
						$xtpl->parse('main.country.traderoute.area1');
						$xtpl->parse('main.infobox_float.traderoute.area1');
						$xtpl->parse('main.country.traderouteimg');
						$countryHAStraderoute = true;
						array_push($traderouteIDs,$country[id_TR]);
					}
				}
			}
		}
		
		
		if ($country[posleft] < 500) {
			$infobox_posleft = $country[posleft] + $country[width] + 100;
			$horizontal_align = "left: 20px;";
		}
		else {
			$infobox_posleft = $country[posleft] - 250;
			$horizontal_align = "left: 20px;";
		}
		$xtpl->assign('infobox_posleft',$infobox_posleft);
		$xtpl->assign('horizontal_align',$horizontal_align);
		
		// parse units
			// land units
			if ($country['unit_type'] == 1 || $country['unit_type'] == 3) {
				if ($actualUnitID != $country['id_unit']) {
					$xtpl->parse('main.country.unit');
					$xtpl->parse('main.infobox_float.unit');
					$unitCount = $unitCount + $country[unr];
					$countryHASunits = true;
				}
			}
			// ships in harbor
			if ($country['unit_type'] == 2 || $country['unit_type'] == 4) {
					if (($actualUnitID != $country['id_unit']) || ($actualUnitShipName != $country[shipname])) {
						if (!in_array($country[shipname],$parsedShips)) {
							if ($country['coastnr']) {
								$xtpl->parse('main.infobox_float.ship.coast');
							}
							$xtpl->parse('main.infobox_float.ship');
							$shipTypeCount++;
							$shipCount++;
							$countryHASunits = true;
							array_push($parsedShips,$country[shipname]);
						}
					}
			}
			// ships at sea
			if (($country[shipname_atsea]) && (!($country[coastnr_atsea]))) {
				if (!in_array($country[id_ship],$parsedShips)) {
					$shipTypeCount_atSea++;
					$shipCount++;
					if ($country[id_ship_user] != $country[id_area_user]) {
						$shipTypeCount_atSea_otherUser++;
						$xtpl->parse('main.infobox_float.shipatsea.otheruser');
					}
					$xtpl->parse('main.infobox_float.shipatsea');
					$countryHASunits = true;
					array_push($parsedShips,$country[id_ship]);
				}
			}
			
			if (!in_array($country[id_adjacent],$parsedAdjacentCountries)) {
				if (empty($adjacentCountries)) {
					$adjacentCountries = $country[id_adjacent];
				}
				else {
					$adjacentCountries = $adjacentCountries . ", " . $country[id_adjacent];
				}
				array_push($parsedAdjacentCountries,$country[id_adjacent]);
			}

		$actualCountryID = $country['ctrnr'];
		$actualUnitType = $country['unit_type'];
		$actualOption = $country['options'];
		$actualId_game = $country['id_game'];
		$actualUnitID = $country['id_unit'];
		$actualShipID_atSea = $country[id_shiptype_atsea];
		$actualShipName = $country[shipname];
		$actualresource = $country['prod'];
		$actualUser = $country['user'];
		$actualTRID = $country[id_TR];
		$actualTank = $country[tank];
	}
	// ende while schleife
	
	if (($actualUser) || ($actualresource > 0)) {
		$xtpl->parse('main.country.user');
		$xtpl->parse('main.infobox_float.user');
	}
	if ($actualresource > 0) {
		$xtpl->parse('main.country.resource');
		$xtpl->parse('main.infobox_float.resource');
		if (($_GET[resources] == 'yes') || (!isset($_GET[resources]))) {
			$xtpl->parse('main.country.resourceimg');
		}
	}
	if ($actualTank > 0) {
		$xtpl->parse('main.country.tankimg');
	}
	if ($country['id_game'] == $game_id) {
		$xtpl->parse('main.country.options');
		$xtpl->parse('main.infobox_float.options');
	}
	if ($countryHAStraderoute == true) {
		$xtpl->parse('main.country.traderoute');
		$xtpl->parse('main.infobox_float.traderoute');
	}
	if ($countryHASunits == true) {
		if ($unitCount > 50) {
			$unitCount = 50;
		}
		if ($shipCount > 21) {
			$shipCount = 21;
		}
		$showUnitImg = false;
		$landunitsShown = false;
		if (($_GET[landunits] == 'yes') || (!isset($_GET[landunits]))) {
			$xtpl->assign('unitCount',$unitCount);
			$xtpl->parse('main.country.unitimg.landunits');
			$showUnitImg = true;
			$landunitsShown = true;
		}
		if (($_GET[seaunits] == 'yes') || (!isset($_GET[seaunits]))) {
			$xtpl->assign('shipCount',$shipCount);
			$xtpl->parse('main.country.unitimg.seaunits');
			$showUnitImg = true;
			if ($landunitsShown) {
				$xtpl->assign('seaunits','seaunits');
			}
			else {
				$xtpl->assign('seaunits','');
			}
		}
		if ($showUnitImg) {
		$xtpl->parse('main.country.unitimg');
		}
	}
	// parse ships in harbor
	if ($shipTypeCount != 0) {
		$xtpl->assign('howmanyships',$shipTypeCount);
		$xtpl->parse('main.country.ship');
		$shipTypeCount = 0;
	}
	// parse ships at sea
	if ($shipTypeCount_atSea != 0) {
		if ($shipTypeCount_atSea_otherUser != 0) {
			$xtpl->assign('howmanyships_atsea_otheruser',$shipTypeCount_atSea_otherUser);
			$xtpl->parse('main.country.shipatsea.otheruser');
		}
		$xtpl->assign('howmanyships_atsea',$shipTypeCount_atSea);
		$xtpl->parse('main.country.shipatsea');
		$shipTypeCount_atSea = 0;
		$shipTypeCount_atSea_otherUser = 0;
	}
	$xtpl->assign('adjacentCountries',$adjacentCountries);
	$xtpl->parse('main.country');
	$xtpl->parse('main.infobox_float');
	$xtpl->parse('main.country_imagemap');


	// get links erstellen
	if (($_GET[traderoutes] == 'yes') || (!isset($_GET[traderoutes]))) {
		$xtpl->assign('traderoutes','&traderoutes=yes');
	}
	else {
		$xtpl->assign('traderoutes','&traderoutes=no');
	}
	if (($_GET[resources] == 'yes') || (!isset($_GET[resources]))) {
		$xtpl->assign('resources','&resources=yes');
	}
	else {
		$xtpl->assign('resources','&resources=no');
	}
	if (($_GET[landunits] == 'yes') || (!isset($_GET[landunits]))) {
		$xtpl->assign('landunits','&landunits=yes');
	}
	else {
		$xtpl->assign('landunits','&landunits=no');
	}
	if (($_GET[seaunits] == 'yes') || (!isset($_GET[seaunits]))) {
		$xtpl->assign('seaunits','&seaunits=yes');
	}
	else {
		$xtpl->assign('seaunits','&seaunits=no');
	}
	
	// get links parsen
	$unitcaption = false;
	if (($_GET[traderoutes] == 'yes') || (!isset($_GET[traderoutes]))) {
		$xtpl->parse('main.getlinks.traderouteson');
	}
	else {
		$xtpl->parse('main.getlinks.traderoutesoff');
	}
	if (($_GET[resources] == 'yes') || (!isset($_GET[resources]))) {
		$xtpl->parse('main.getlinks.reson');
		$xtpl->parse('main.getlinks.rescaption');
	}
	else {
		$xtpl->parse('main.getlinks.resoff');
	}
	if (($_GET[landunits] == 'yes') || (!isset($_GET[landunits]))) {
		$xtpl->parse('main.getlinks.landunitson');
		$unitcaption = true;
	}
	else {
		$xtpl->parse('main.getlinks.landunitsoff');
	}
	if (($_GET[seaunits] == 'yes') || (!isset($_GET[seaunits]))) {
		$xtpl->parse('main.getlinks.seaunitson');
		$unitcaption = true;
	}
	else {
		$xtpl->parse('main.getlinks.seaunitsoff');
	}
	if ($unitcaption) {
		$xtpl->parse('main.getlinks.unitcaption');
	}
		
	$xtpl->parse('main.getlinks');
	$xtpl->parse('main');
	$xtpl->out('main');

?>