<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/core.php');
$client = getGoogleClient();
if (!isset($_GET['mode']))
  http_response_code(400);
else if ($_GET['mode'] === 'login'){
  //ログイン画面へ遷移
  locateLogin($client);
}
else if ($_GET['mode'] === 'success'){
  //ログインに成功したはず
  if (isset($_GET['code'])){
    session_start();
    session_regenerate_id(true);
    $client->authenticate($_GET['code']);
    $_SESSION['accessToken'] = $client->getAccessToken();
    if (!$_SESSION['accessToken']) locateWelcome('?error=accesstoken_null');
    else sessionCheck(true);
    session_write_close();
    header('location: /');
    exit();
  }
  else if (isset($_GET['error']))
    locateWelcome('?error='.$_GET['error']);
  else
    locateWelcome();
}
else if ($_GET['mode'] === 'logout'){
  //ログアウト
  session_start();
  $_SESSION = [];
  session_write_close();
  session_destroy();
  locateWelcome('?msg=logout_completed');
}
else if ($_GET['mode'] === 'revoke'){
  //退会 データベースの内容削除も
  $client->revokeToken();
  locateWelcome();
}
else
  locateWelcome();
?>