<?php
$request = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $request === 'xmlhttprequest') {
  require_once($_SERVER['DOCUMENT_ROOT'].'/core.php');
  $client = sessionCheck();
  $link = mysqli_connect();

  if ($_POST['method'] === 'search') {
    $curl = curl_init();
    response($client, search($link, $curl, $_POST));
    curl_close($curl);
  }

  else if ($_POST['method'] === 'mincam') {
    response($client, mincam($link, $_POST));
  }
  
  else if ($_POST['method'] === 'syllabus') {
    $curl = curl_init();
    if ($_POST['command'] === 'get')
      response($client, syllabus($link, $curl, $_POST));
    else if ($_POST['command'] === 'temp')
      syllabus($link, $curl, $_POST, true);
    curl_close($curl);
  }
  
  else if ($_POST['method'] === 'memo') {
    if ($_POST['command'] === 'get')
      response($client, memoGet($link, $_POST));
    else if ($_POST['command'] === 'save')
      memoSave($link, $_POST);
  }
  
  else if ($_POST['method'] === 'calendar') {
    if ($_POST['command'] === 'get')
      response($client, getAllCalendarSubjects($client));
    else if ($_POST['command'] === 'week')
      response($client, getWeekCalendarSubjects($client));
    else if ($_POST['command'] === 'add')
      response($client, addCalendar($client, $link, $_POST));
    else if ($_POST['command'] === 'delete')
      response($client, deleteCalendarSubjects($client, $_POST['id']));
    else if ($_POST['command'] === 'notification')
      response($client, toggleCalendarNotification($client, $link, $_POST));
  }

  else if ($_POST['method'] === 'favorite') {
    if ($_POST['command'] === 'get')
      response($client, getFavoriteById($link));
    else if ($_POST['command'] === 'change')
      response($client, changeFavorite($link, $_POST));
  }
  
  else if ($_POST['method'] === 'comment') {
    if ($_POST['command'] === 'get')
      response($client, commentGet($link, $_POST));
    else if ($_POST['command'] === 'post')
      response($client, commentPost($link, $_POST));
  }

  else if ($_POST['method'] === 'register') {
    session_start();
    maria_query($link, "INSERT INTO delisys.user (id, register, university, studentName, studentID, studentSex, notification) VALUES ('$_SESSION[id]', NOW(), 'chiba', '$_POST[studentName]', '$_POST[studentID]', '$_POST[studentSex]', 1);");
    session_write_close();
  }

  else
    http_response_code(400);

  mysqli_close($link);
}
else
  http_response_code(400);

/**
 * $clientを利用してログインしていないときはstatus:expiredにします
 *
 * @param $client Google Clientのインスタンス
 * @param array $data 連想配列のデータ
 * @return void
 */
function response($client, array $data){
  if (!$client) $data['status'] = 'expired';
  echo(json_encode($data));
}
?>