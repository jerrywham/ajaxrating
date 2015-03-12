<?php
/*
Page:           rpc.php
Created:        Aug 2006
Last Mod:       Apr 29 2013
This page handles the 'AJAX' type response if the user
has Javascript enabled.
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
	$artId = preg_replace("/[^0-9a-zA-Z]/","",$_REQUEST['a']);
} else {
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
$numbers['used_ips'] = ((!is_array($numbers['used_ips'])) ? array($numbers['used_ips']) : $numbers['used_ips']);

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
// these are new queries to get the new values!
$numbers = $plxPlugin->parseBdd($filename);
$count = $numbers['total_votes'];//how many votes total
$current_rating = $numbers['total_value'];//total number of rating added together and stored
$tense=($count>1) ? $plxPlugin->getLang('L_VOTES') : $plxPlugin->getLang('L_VOTE'); //plural form votes/vote
$cast=($count>1) ?  $plxPlugin->getLang('L_CAST_PLURAL') : $plxPlugin->getLang('L_CAST'); //plural form cast/cast

// $new_back is what gets 'drawn' on your page after a successful 'AJAX/Javascript' vote

$new_back = array();

$new_back[] .= '<ul class="unit-rating" style="width:'.$units*$plxPlugin->rating_unitwidth.'px;">';
$new_back[] .= '<li class="current-rating" style="width:'.(($count>0) ? number_format($current_rating/$count,2)*$plxPlugin->rating_unitwidth : number_format($current_rating,2)*$plxPlugin->rating_unitwidth).'px;">'.$plxPlugin->getLang('L_CURRENT_RATING').'</li>';
$new_back[] .= '<li class="r1-unit">1</li>';
$new_back[] .= '<li class="r2-unit">2</li>';
$new_back[] .= '<li class="r3-unit">3</li>';
$new_back[] .= '<li class="r4-unit">4</li>';
$new_back[] .= '<li class="r5-unit">5</li>';
$new_back[] .= '<li class="r6-unit">6</li>';
$new_back[] .= '<li class="r7-unit">7</li>';
$new_back[] .= '<li class="r8-unit">8</li>';
$new_back[] .= '<li class="r9-unit">9</li>';
$new_back[] .= '<li class="r10-unit">10</li>';
$new_back[] .= '</ul>';
$new_back[] .= '<p class="voted"><span id="root" style="display:none;">'.$plxMotor->racine.'</span>&nbsp;'.$plxPlugin->getLang('L_RATING').'&nbsp;: <strong>'.(($added>0) ? number_format($sum/$added,1) : number_format($sum,1)).'</strong>/'.$units.' ('.$count.' '.$tense.' '.$cast.') ';
$new_back[] .= '<span class="thanks">'.$plxPlugin->getLang('L_THANK_YOU').'</span></p>';

$allnewback = implode("\n", $new_back);

// ========================

//name of the div id to be updated | the html that needs to be changed
$output = "unit_long$id_sent|$allnewback";
echo $output;
exit();
?>