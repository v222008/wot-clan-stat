<?php
// выборка списка клана. анализ, внесение изменений, запись в лог-таблицу

include('settings.kak');
$connect = mysql_connect($host, $account, $password);
$db = mysql_select_db($dbname, $connect) or die("Ошибка подключения к БД");
$setnames = mysql_query( 'SET NAMES utf8' );
header('Content-Type: text/html; charset=UTF-8'); 
//$clan_array[] = array("clan_id" => "12638", "clan_tag" => "[SMPLC]",  "clan_name" => "Sample clan");

foreach ($clan_array as $clan_i) {
	$idc = $clan_i["clan_id"];
	$clantag = $clan_i["clan_tag"];
	$pageidc = "community/clans/".$idc."/api/1.1/?source_token=WG-WoT_Assistant-test";		
	$pageidc = $wot_host.'/'.$pageidc;
	$date = date("Y-m-d",strtotime($hosttime));
	$time = date("H:i:s",strtotime($hosttime));
	//$date = date("Y-m-d");
	//$time = date("H:i:s");

	$data = get_page($pageidc);
	$data = json_decode($data, true);
	if ($data['status'] == 'ok') {
		// тут добавить сбор инфы о клане //
		for($i=0;$i<count($data['data']['members']);$i++){
			//проверка на "нового игрока в клане"
			$t=date("Y-m-d",($data['data']['members'][$i]['created_at']));
			$idp=$data['data']['members'][$i]['account_id'];
			$sql = "select id_c from clan where idp='$idp' and idc='$idc'";
			$q = mysql_query($sql, $connect);
			if (mysql_errno() <> 0) echo "MySQL Error ".mysql_errno().": ".mysql_error()."\n";
			$qqt = mysql_fetch_array($q);
			if($qqt['id_c']==NULL){ // игрока нет в данном клане	
				//проверка, что игрок был в другом клане альянса
				$sql = "select id_c from clan where idp='$idp'";
				$q = mysql_query($sql, $connect);
				if (mysql_errno() <> 0) echo "MySQL Error ".mysql_errno().": ".mysql_error()."\n";
				$qqt = mysql_fetch_array($q);
				if($qqt['id_c'] != NULL) {
					$message=$data['data']['members'][$i]['account_name']." перешел в ".$clantag;
					$sql = "INSERT INTO event_clan (type,idp, idc, message, reason, date, time)";
					$sql.= " VALUES (2,'$idp', '$idc', '$message', NULL, '$date', '$time')";
					$q = mysql_query($sql, $connect);
					if (mysql_errno() <> 0) echo "MySQL Error ".mysql_errno().": ".mysql_error()."\n";

					$sql = "UPDATE `clan` SET `idc`='$idc' WHERE `idp`='$idp'";
					$q = mysql_query($sql, $connect);
					if (mysql_errno() <> 0) echo "MySQL Error ".mysql_errno().": ".mysql_error()."\n";
			
				} else {
					$message="Приветствуем ".$data['data']['members'][$i]['account_name'].' в '.$clantag;
					$sql = "INSERT INTO event_clan (type,idp, idc, message, reason, date, time)";
					$sql.= " VALUES (2,'$idp', '$idc', '$message', NULL, '$date', '$time')";
					$q = mysql_query($sql, $connect);
					if (mysql_errno() <> 0) echo "MyQL Error ".mysql_errno().": ".mysql_error()."\n";
					#=================== Insert into clan tables ==============#
					$created_at=date("Y-m-d",$data['data']['members'][$i]['created_at']); //дата вступления в клан
					//$role=$data['data']['members'][$i]['role'];
					$role_lo=$data['data']['members'][$i]['role_localised'];
					$sql  = "insert into clan (idp, idc, date,role_localised)";
					$sql .=" values('$idp', '$idc', '$created_at','recruit')";
					//echo $sql.'<br>';
					mysql_query($sql, $connect);
				}
			}
		}
	}

}
function get_page($url) {
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_HEADER, 0);
                //curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Accept-Language: ru_ru,ru'));
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_HTTPGET, true);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
}
mysql_close($connect);
echo "Done"
?>
