<?php
//Владения на ГК
include('settings.kak');
$connect = mysql_connect($host, $account, $password);
$db = mysql_select_db($dbname, $connect) or die("Ошибка подключения к БД");
$setnames = mysql_query( 'SET NAMES utf8' );
$idc = $_GET['idc'];
$sql="SELECT count(*) as cnt from `btl` where idc='$idc'";
$res = mysql_query($sql,$connect);
$row = mysql_fetch_array($res,MYSQL_ASSOC); 
$count = $row['cnt'];
$responce = new stdclass;
$responce->page = 1;
$responce->total = 1;
$responce->records = $count;
$i=0;

$SQL = "select id_b,idb, type,time, id_prov, prov, id_prov1, prov1, started, arena, arena1 from btl where idc='$idc' order by time";
$result2 = mysql_query( $SQL,$connect );
while($row = mysql_fetch_array($result2,MYSQL_ASSOC)) { 
	$provm=$row['type'];
	$timem="";
	$clane="";
	//$pr=$row['prov'];
	$idpr=$row['id_prov'];
	$idpr1=$row['id_prov1'];
	$pr = $row["prov"];
	$pr1 = $row["prov1"];
	$arena=$row["arena"];
	$arena1=$row["arena1"];
	$pr = "<a href='http://worldoftanks.ru/uc/clanwars/maps/?province=$idpr' target='_blank'>$pr</a>";
	$pr1 = "<a href='http://worldoftanks.ru/uc/clanwars/maps/?province=$idpr1' target='_blank'>$pr1</a>";
	if ($row['type']=="landing"){$provm="Высадка";}
		$SQL2 = "select * from possession where idpr='$idpr'and idc='$idc'";
		$result22 = mysql_query( $SQL2,$connect );
		if (mysql_fetch_array($result22,MYSQL_ASSOC)){
			$provm=$provm." (удерживаем)";
		}
	if ($row['type']=="for_province"){$provm="За провинцию";
		$SQL2 = "select * from possession where idpr='$idpr'and idc='$idc'";
		$result22 = mysql_query( $SQL2,$connect );
		if (!mysql_fetch_array($result22,MYSQL_ASSOC)){
			$provm=$provm." (атакуем)";
		}else{
			$provm=$provm." (защищаемся)";
		}
		$SQL2 = "select idc from possession where idpr='$idpr'and idc<>'$idc'";
		$result22 = mysql_query( $SQL2,$connect );
		$row3=mysql_fetch_array($result22,MYSQL_ASSOC);
		if ($row3<>NULL){
			$clane=$row3['idc'];
		}
	}
	if ($row['started']==1){
		$timem=date("H:i",$row['time']);
	}else{
		$timem=date("H:i",$row['time'])." +";
	}
	if ($row['time']==0){ $timem="--:--";}
	$SQL2 = "select type, periphery from province where id='$idpr'";
	$result22 = mysql_query( $SQL2,$connect );
	$row3=mysql_fetch_array($result22,MYSQL_ASSOC);
	$periphery="Неизв.";
	if ($row3<>NULL){
		$periphery=$row3['periphery'];
		switch ($row3["type"]) {
		case "normal":
			$pr = '<img src="images/province_type_normal.png" style="width: 20px; height:20px;" align="absmiddle"/>'." ".$pr;// alt='Обычная провинция' >";
			break;
		case "gold":
			$pr = '<img src="images/province_type_gold.png" style="width: 20px; height:20px;" align="absmiddle"/>'." ".$pr; //alt='Ключевая провинция' >";
			break;
		case "start":
			$pr = '<img src="images/province_type_start.png" style="width: 20px; height:20px;" align="absmiddle"/>'." ".$pr;// alt='Стартовая провинция' >";
			break;
		}
	}
	// $SQL2 = "select type from province where id='$idpr1'";
	// $result22 = mysql_query( $SQL2,$connect );
	// $row3=mysql_fetch_array($result22,MYSQL_ASSOC);
	// if ($row3<>NULL){
		// switch ($row3["type"]) {
		// case "normal":
			// $pr1 = '<img src="images/province_type_normal.png" style="width: 20px; height:20px;" align="absmiddle"/>'." ".$pr1;// alt='Обычная провинция' >";
			// break;
		// case "gold":
			// $pr1 = '<img src="images/province_type_gold.png" style="width: 20px; height:20px;" align="absmiddle"/>'." ".$pr1; //alt='Ключевая провинция' >";
			// break;
		// case "start":
			// $pr1 = '<img src="images/province_type_start.png" style="width: 20px; height:20px;" align="absmiddle"/>'." ".$pr1;// alt='Стартовая провинция' >";
			// break;
		// }
	// }
	if ($row['type']=="meeting_engagement"){
		$SQL2 = "select type from province where id='$idpr1'";
		$result22 = mysql_query( $SQL2,$connect );
		$row3=mysql_fetch_array($result22,MYSQL_ASSOC);
		if ($row3<>NULL){
			switch ($row3["type"]) {
			case "normal":
				$pr1 = '<img src="images/province_type_normal.png" style="width: 20px; height:20px;" align="absmiddle"/>'." ".$pr1;// alt='Обычная провинция' >";
				break;
			case "gold":
				$pr1 = '<img src="images/province_type_gold.png" style="width: 20px; height:20px;" align="absmiddle"/>'." ".$pr1; //alt='Ключевая провинция' >";
				break;
			case "start":
				$pr1 = '<img src="images/province_type_start.png" style="width: 20px; height:20px;" align="absmiddle"/>'." ".$pr1;// alt='Стартовая провинция' >";
				break;
			}
		}
		$provm="Встречный бой";
		$pr=$pr.'<img src="images/icons/vs.png" align="absmiddle">'.$pr1;
		$arena=$arena." или ".$arena1;
	}
	
	$responce->rows[$i]['cell']=array("<b>".$provm."</b>","<b>".$pr."</b>", "<b>".$arena."</b>",$timem,$clane,$periphery); //$clandays,$las_onl); 
	$i++; 
} 
//header("Content-type: text/script;charset=utf-8");
echo json_encode($responce);
?>