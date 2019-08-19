<?php
//OAuthスクリプトの読み込み
require_once('twitteroauth/twitteroauth.php');

//APIキー
$api_key = 'UwLL6Q2Mwy0oLnnTN8wPkBxrz';
//APIシークレット
$api_secret = 'xxAKZU9KzqZXgE03UPK4MRm98t0gEA1WtMTACvWVq1E6CPHuP8';
//アクセストークン
$access_token = '1148944368834768896-h3TTkoGPKJ4OEgg3fRTdrbNquKgdWC';
//アクセストークンシークレット
$access_token_secret = 'sM6yMJm0n01Nlc0V6pBbp70IokH8do04sm7D4dX1yOTAG';

$connection = new TwitterOAuth($api_key, $api_secret, $access_token, $access_token_secret);
$req = $connection->OAuthRequest('https://api.twitter.com/1.1/statuses/update.json', 'POST', array('status' => $message));
?>