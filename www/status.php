<?php

$ch = curl_init(); 
// set url 
curl_setopt($ch, CURLOPT_URL, "www.loadshedding.eskom.co.za"); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
$output = curl_exec($ch); 
curl_close($ch);


$firstSplit = explode('lsstatus',$output);
$secondSplit = explode('span',$firstSplit[1]);
//echo "secondSplit=".$secondSplit[0]; //secondSplit=" style="font-size:18px; font-weight:bold"> not Load Shedding</

//echo "::::::status1=".$secondSplit[0];
$status1 = explode(">",$secondSplit[0]);
//var_dump($status1);
$status2 = explode("<",$status1[1]);
echo "s=".$status2[0];
$status = trim($status2[0]);

