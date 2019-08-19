<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/core.php');
$link = mysqli_connect();
$curl = curl_init();
var_dump(getSubjectChange($link, $curl, ['date'=>'2019%2F07%2F10','year'=>2019,'month'=>7,'day'=>10]));
mysqli_close($link);
curl_close($curl);
?>