<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/core.php');
init();
$client = google_client_create();
if (!isset($_GET['mode']))
  http_response_code(400);
else if ($_GET['mode'] === 'login')
  locate_login();
else if ($_GET['mode'] === 'success'){
  //ログインに成功したはず
  if (isset($_GET['code'])){
    session_start();
    session_regenerate_id(true);
    $client->authenticate($_GET['code']);
    $_SESSION['accessToken'] = $client->getAccessToken();
    if (!$client->getAccessToken()) locate_login($client);
		if ($client->isAccessTokenExpired()) locate_login($client);
    //GoogleのPeople APIでユーザーの情報を取得
    $people = new Google_Service_PeopleService($client);
    $userinfo = $people->people->get('people/me', ['personFields'=>'names,photos']);
    $_SESSION['google_id'] = explode("/", $userinfo['resourceName'])[1];
    $_SESSION['google_photo_url'] = $userinfo['photos'][0]['url'];
    $_SESSION['google_user_name'] = $userinfo['names'][0]['displayName'];

    $result = maria_query("SELECT user_id FROM user WHERE google_id='$_SESSION[google_id]'");
    $_SESSION['user_id'] = mysqli_num_rows($result) >= 1 ? mysqli_fetch_assoc($result)['user_id'] : 'new';
    session_write_close();
    header('location: /');
  }
  else if (isset($_GET['error']))
    locate_welcome('?error='.$_GET['error']);
  else
    locate_welcome();
}
else if ($_GET['mode'] === 'logout'){
  //ログアウト
  session_start();
  $_SESSION = [];
  session_write_close();
  session_destroy();
  locate_welcome('?msg=logout_completed');
}
else if ($_GET['mode'] === 'revoke'){
  //退会 データベースの内容削除も
  $client->revokeToken();
  locate_welcome();
}
else
  locate_welcome();

finalize();
?>