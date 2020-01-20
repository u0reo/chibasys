<?php
$request = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $request === 'xmlhttprequest') {
  require_once($_SERVER['DOCUMENT_ROOT'].'/core.php');
  $client = sessionCheck();
  $link = mysqli_connect('localhost', 'chibasys', 'P8IpIqW2Zb8CZNCC', 'chibasys');

  if ($_POST['method'] === 'search') {
    $curl = curl_init();
    response($client, search($link, $curl, $_POST));
    curl_close($curl);
  }

  else if ($_POST['method'] === 'mincam') {
    response($client, mincam($link, $_POST));
  }
  
  else if ($_POST['method'] === 'syllabus') {
    response($client, syllabus($link, $_POST));
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

  else if ($_POST['method'] === 'portal') {
    $curl = curl_init();
    if ($_POST['command'] === 'registration')
      response($client, portal_registration($link, $curl, $_POST));
    else if ($_POST['command'] === 'registration_list')
      response($client, portal_registration_list($link, $curl));
    else if ($_POST['command'] === 'grade')
      response($client, portal_grade($link, $curl));
    else if ($_POST['command'] === 'change')
      response($client, portal_student_info($link, $_POST));
    curl_close($curl);
  }
  
  else if ($_POST['method'] === 'comment') {
    if ($_POST['command'] === 'get')
      response($client, commentGet($link, $_POST));
    else if ($_POST['command'] === 'post')
      response($client, commentPost($link, $_POST));
  }

  else if ($_POST['method'] === 'register') {
    session_start();
    unset($_POST['method']);
    if (isset($_POST['studentPass']) && $_POST['studentPass'] === '') unset($_POST['studentPass']);
		$row = maria_query($link, "SELECT * FROM chibasys.user WHERE id='$_SESSION[id]';");
		if ($row && mysqli_fetch_assoc($row))
			$r = maria_query($link, "UPDATE chibasys.user SET studentName='$_POST[studentName]', studentSex='$_POST[studentSex]', studentID='$_POST[studentID]'".(isset($_POST['studentPass']) ? ", studentPass='$_POST[studentPass]'" : '')." WHERE id='$_SESSION[id]';");
		else
			$r = maria_query($link, "INSERT INTO chibasys.user (id, register, ".implode(',', array_keys($_POST)).", notification) VALUES ('$_SESSION[id]', NOW(), '".implode("','", array_values($_POST))."', 1);");
    
    $res = maria_query($link, "SELECT * FROM chibasys.user WHERE id = '$_SESSION[id]';");
    if (mysqli_num_rows($res) === 1) $_SESSION['userdata'] = mysqli_fetch_assoc($res);
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