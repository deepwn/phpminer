<?php
require_once("mine.php"); // it's the core ,hahaha
$server="http://13.230.197.214:8541/"; //you can use phpminerBridge.js to build a bridge to MiningPool
function get($data){
	global $server;
	return explode("-",file_get_contents($server."*".$data."*"));
}
function getJob(){
	return get("getjob-");
}
function pushHash($a,$b){
	get("hash-".$a."-".$b);
}
function dec2hex($dec){
	$str=dechex($dec);
	if(strlen($str)==1)
		$str="0".$str;
	return $str;
}
function genNonce(){
	return dec2hex(rand(0,255)).dec2hex(rand(0,255)).dec2hex(rand(0,255)).dec2hex(rand(0,255));
}

$time=microtime(TRUE);

$arr=getJob();

if(count($arr)!=3)die("在向通信桥获取工作的时候出现错误\n Error occurred when getting job from the bridge.");

$blob=$arr[2];

$nonce=genNonce();
$blob=substr_replace($blob,$nonce,39*2,8);
echo "当前工作: Target:".$arr[1]." Blob: $blob\n<br/>";

$blobarr=array_map('ord', str_split(hex2bin($blob)));

echo "计算Hash... Nonce:$nonce \n<br/>";
$hash=bin2hex(byteArraytoStr(cryptonight($blobarr)));
//$hash="";
$tm=microtime(TRUE)-$time;
echo "计算完毕,耗时:$tm s,Hash已提交给矿池:".$hash."<br/>";
pushHash($hash,$nonce);



?>