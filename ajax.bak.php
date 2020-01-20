<?php
$request = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $request === 'xmlhttprequest') {
  require_once($_SERVER['DOCUMENT_ROOT'].'/core.php');
  $client = sessionCheck();

  if ($_POST['method'] === 'search') {
    $curl = curl_init();
    response($client, search($curl, $_POST));
    curl_close($curl);
  }

  else if ($_POST['method'] === 'mincam') {
    $link = mysqli_connect();
    if (isset($_POST['command']) && $_POST['command'] === 'shape') {
      $teacher = $_POST['teacher'];
      $teacher = (mb_strpos($teacher, ',') === false ? $teacher : explode(',', $teacher)[0]);
      $teacher = (mb_strpos($teacher, ' ') === false ? $teacher : explode(' ', $teacher)[0]);
      response($client, mincam($link, [ 'title'=>$_POST['title'], 'teacher'=>$teacher ]));
    }
    else {
      parse_str(urldecode($_POST['query']), $query);
      response($client, mincam($link, $query));
    }
    mysqli_close($link);
  }
  
  else if ($_POST['method'] === 'syllabus') {
    $curl = curl_init();
    $link = mysqli_connect();
    if ($_POST['command'] === 'get')
      response($client, syllabus($curl, $link, $_POST));
    else if ($_POST['command'] === 'temp')
      syllabus($curl, $link, $_POST, true);
    curl_close($curl);
    mysqli_close($link);
  }
  
  else if ($_POST['method'] === 'memo') {
    $link = mysqli_connect();
    if ($_POST['command'] === 'get')
      response($client, memoGet($link, $_POST));
    else if ($_POST['command'] === 'save')
      memoSave($link, $_POST);
    mysqli_close($link);
  }
  
  else if ($_POST['method'] === 'calendar') {
    if ($_POST['command'] === 'get')
      response($client, getAllCalendarSubjects($client));
    else if ($_POST['command'] === 'week')
      response($client, getWeekCalendarSubjects($client));
    else if ($_POST['command'] === 'add')
      response($client, addCalendar($client, $_POST));
    else if ($_POST['command'] === 'delete')
      response($client, deleteCalendarSubjects($client, $_POST['id']));
    else if ($_POST['command'] === 'notification')
      response($client, toggleCalendarNotification($client, $_POST));
  }

  else if ($_POST['method'] === 'favorite') {
	  $link = mysqli_connect();
    if ($_POST['command'] === 'get')
      response($client, getFavoriteById($link));
    else if ($_POST['command'] === 'change')
      response($client, changeFavorite($link, $_POST));
    mysqli_close($link);
  }
  
  else if ($_POST['method'] === 'comment') {
    $link = mysqli_connect();
    if ($_POST['command'] === 'get')
      response($client, commentGet($link, $_POST));
    else if ($_POST['command'] === 'post')
      response($client, commentPost($link, $_POST));
    mysqli_close($link);
  }

  else if ($_POST['method'] === 'register') {
    $link = mysqli_connect();
    session_start();
    mysqli_query($link, "INSERT INTO delisys.user (id, register, university, studentName, studentID, studentSex, notification) VALUES ('".$_SESSION['id']."', NOW(), 'chiba', '".$_POST['studentName']."', '".$_POST['studentID']."', '".$_POST['studentSex']."', 1);");
    session_write_close();
    mysqli_close($link);
  }

  else
    http_response_code(400);
}
else
  http_response_code(400);

function response($client, $data){
  if (!$client) $data['status'] = 'expired';
  echo(json_encode($data));
}
?>