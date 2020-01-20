<?php
$curl = curl_init('https://cup.chiba-u.jp/campusweb/campusportal.do');
curl_exec($curl);
var_dump(curl_getinfo($curl));
var_dump(curl_error($curl));
curl_close($curl);
?>