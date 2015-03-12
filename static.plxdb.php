<?php
/*
Page:           db.php
Created:        Aug 2006
Last Mod:       Apr 29 2013
This page handles the database update if the user
does NOT have Javascript enabled.	
--------------------------------------------------------- 
ryan masuga, masugadesign.com
ryan@masugadesign.com 

Cyril MAGUIRE, ecyseo.net
http://www.ecyseo.net/contact

Licensed under a Creative Commons Attribution 3.0 License.
http://creativecommons.org/licenses/by/3.0/
See readme.txt for full credit details.
--------------------------------------------------------- */
if(!defined('PLX_ROOT')) exit;
header("Cache-Control: no-cache");
header("Pragma: nocache");

$plxMotor = plxMotor::getInstance();

//getting the values
$vote_sent = (isset($_REQUEST['j'])) ? preg_replace("/[^0-9]/","",$_REQUEST['j']) : '';
$id_sent = (isset($_REQUEST['q'])) ? preg_replace("/[^0-9a-zA-Z]/","",$_REQUEST['q']) : '';
$ip_num = (isset($_REQUEST['t'])) ? $_REQUEST['t'] : '';
$units = (isset($_REQUEST['c'])) ? preg_replace("/[^0-9]/","",$_REQUEST['c']) : '';
$ip = md5($_SERVER['REMOTE_ADDR']);

if (isset($_REQUEST['a'])) {
	$f = str_replace('.xml','',$plxMotor->plxGlob_arts->aFiles[$_REQUEST['a']]);
	$url = substr($f,strrpos($f, '.')+1 );
	$referer  = $plxMotor->urlRewrite('?article'.intval($_REQUEST['a']).'/'.$url);
	$artId = preg_replace("/[^0-9a-zA-Z]/","",$_REQUEST['a']);
} else {
	$referer = 'index.php';
	$artId = '';
}

# Chargement du fichier de données
if ($plxMotor->version == '5.1.6') {
	$filename = PLX_ROOT.'data/configuration/plugins/ajaxrating/'.$id_sent.'.'.$f.'.xml';
} else {
	$filename = PLX_ROOT.PLX_CONFIG_PATH.'plugins/ajaxrating/'.$id_sent.'.'.$f.'.xml';
}

$plxPlugin = $plxMotor->plxPlugins->aPlugins['ajaxrating'];

if ($vote_sent > $units) exit("Sorry, vote appears to be invalid."); // kill the script because normal users will never see this.

//connecting to the database to get some information
$numbers=$plxPlugin->parseBdd($filename);

$checkIP = unserialize($numbers['used_ips']);

$count = $numbers['total_votes']; //how many votes total
$current_rating = $numbers['total_value']; //total number of rating added together and stored
$sum = $vote_sent+$current_rating; // add together the current vote value and the total vote value

// checking to see if the first vote has been tallied
// or increment the current number of votes
($sum==0 ? $added=0 : $added=$count+1);

// if it is an array i.e. already has entries the push in another value
((is_array($checkIP)) ? array_push($checkIP,$ip_num) : $checkIP=array($ip_num));
$insertip=serialize($checkIP);

$numbers['used_ips'] = unserialize($numbers['used_ips']);

//IP check when voting
$voted=in_array($ip, $numbers['used_ips']);

if(!$voted) {     //if the user hasn't yet voted, then vote normally...
	if (($vote_sent >= 1 && $vote_sent <= $units) && ($ip == $ip_num)) { // keep votes within range
		$content = array(
			'total_votes'=>$added,
			'total_value'=>$sum,
		 	'used_ips'=>$insertip
		);
		$plxPlugin->editRecordInBdd($content, $filename);
	} 
} //end for the "if(!$voted)"
header("Location: $referer"); // go back to the page we came from 
exit();
?>