<?php
noDirectAccess();
ini_set('display_errors', 1);
set_time_limit(25);

require_once('vendor/autoload.php');
require_once('phpQuery-onefile.php');

function getGoogleClient($cal = false){
	$client = new Google_Client();
	$client->setApplicationName('delisys by reolink');
	$client->setAuthConfig(__DIR__.'/client_secret.json');
	//$client->setAccessType("offline");
	$client->setIncludeGrantedScopes(true);
	$client->addScope(Google_Service_Calendar::CALENDAR);
	$client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);
	$client->addScope(Google_Service_People::USERINFO_PROFILE);
	//$client->setApprovalPrompt('force');
	$client->setRedirectUri('https://delisys.xperd.net/auth?mode=success');
	$client->setDeveloperKey('AIzaSyAY3LxdQdnM1s2P7ztXiCkK_95YDs-Tl-w');
	return $client;
}

function noDirectAccess(){
	$gif = get_included_files();
	if (array_shift($gif) === __FILE__){
		http_response_code(400);
		exit();
	}
}

function getRequest(){
	$index = strrpos($_SERVER['REQUEST_URI'], '/') + 1;
	$pos = strpos($_SERVER['REQUEST_URI'], '#', $index);
	if ($pos === false)
		return substr($_SERVER['REQUEST_URI'], $index);
	else
		return substr($_SERVER['REQUEST_URI'], $index, $pos - $index + 1);
}

function locateWelcome($query = ''){
  header('location: /welcome'.$query);
  exit();
}

function locateLogin($client){
	header('location: '.$client->createAuthUrl());
  exit();
}

function sessionCheck($page = false){
  $client = getGoogleClient();
  session_start();
  if (isset($_SESSION['accessToken'])){
		//ログイン済み
    $client->setAccessToken($_SESSION['accessToken']);
    if (!$client->isAccessTokenExpired()){
			if (!isset($_SESSION['id'])){
	      $people = new Google_Service_PeopleService($client);
  	    $userinfo = $people->people->get('people/me', ['personFields'=>'names,photos']);
				$_SESSION['id'] = explode("/", $userinfo['resourceName'])[1];
				$_SESSION['google_photo_url'] = $userinfo['photos'][0]['url'];
				$_SESSION['google_user_name'] = $userinfo['names'][0]['displayName'];
			}
			if (!isset($_SESSION['userdata'])){
				$link = mysqli_connect();
				$res = mysqli_query($link, "SELECT * FROM delisys.user WHERE id = '".$_SESSION['id']."';");
				if (mysqli_num_rows($res) === 1){
					$_SESSION['userdata'] = mysqli_fetch_assoc($res);
					unset($_SESSION['google_user_name']);
				}
				mysqli_close($link);
      }
      session_write_close();
			return $client;
		}
		else {
			//期限切れ...
		}
  }
  session_write_close();
	//accessTokenがなかったり、Expiredなとき
	/*if (!$page){
		echo(json_encode([ 'status'=>'expired', 'url'=>$client->createAuthUrl() ]));
		exit();
	}
	else
		return false;*/
	return false;
}

/* 初期のセッションやID取得 */
function createSession($curl){
	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://cup.chiba-u.jp/campusweb/campussquare.do?locale=ja_JP&_flowId=SYW3901100-flow',
		CURLOPT_REFERER => '',
		CURLOPT_COOKIE => '',
		CURLOPT_POST => false,
		CURLOPT_POSTFIELDS => '',
		CURLOPT_HEADER => true,
		CURLOPT_FOLLOWLOCATION => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_TIMEOUT => 5,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]);
	$res = curl_exec($curl);
	$info = curl_getinfo($curl);
	$header = substr($res, 0, $info['header_size']);
	$cookie = [];
	$cookieCount = substr_count($header, 'Set-Cookie:');
	$start = 0;
	for ($i = 0; $i < $cookieCount; $i++){
		$start = strpos($header, 'Set-Cookie:', $start + 1) + 12;
		$end = strpos($header, ';', $start);
		$cookie[] = substr($header, $start, $end - $start);
	}
	
	//入力画面へ遷移
	curl_setopt_array($curl, [
		CURLOPT_URL => $info['redirect_url'],
		CURLOPT_REFERER => '',
		CURLOPT_COOKIE => implode('; ', $cookie),
		CURLOPT_POST => false,
		CURLOPT_POSTFIELDS => '',
		CURLOPT_HEADER => true,
		CURLOPT_FOLLOWLOCATION => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_TIMEOUT => 5,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]);
	curl_exec($curl);

	//mysqlに一時保存
	$link = mysqli_connect();
	mysqli_query($link, "UPDATE delisys.temp SET data='".$info['redirect_url']."' WHERE name='url';");
	mysqli_query($link, "UPDATE delisys.temp SET data='".json_encode($cookie)."' WHERE name='cookie';");
  mysqli_close($link);

  session_start();
  $_SESSION['url'] = $info['redirect_url'];
  $_SESSION['cookie'] = $cookie;
  session_write_close();
}

/* 一時保存されたセッション取得 */
function getSession($curl){
	$link = mysqli_connect();
	$resultUrl = mysqli_query($link, "SELECT data FROM delisys.temp WHERE name = 'url';");
	$resultCookie = mysqli_query($link, "SELECT data FROM delisys.temp WHERE name = 'cookie';");
	if ($resultUrl && $resultCookie){
    //session_start();
		$_SESSION['url'] = mysqli_fetch_assoc($resultUrl)['data'];
    $_SESSION['cookie'] = json_decode(mysqli_fetch_assoc($resultCookie)['data']);
    //session_write_close();
	  mysqli_close($link);
	}
  else {
    mysqli_close($link);
    createSession($curl);
  }
}

/* みんなのキャンパスへログイン&セッション取得 */
function createMincamSession($curl){
	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://grp03.id.rakuten.co.jp/rms/nid/vc',
		CURLOPT_REFERER => '',//https://www.nikki.ne.jp/login/?return_url_nikki=https://campus.nikki.ne.jp/
		CURLOPT_COOKIE => '',
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => '__event=ID01_001_001&service_id=p06&return_url=index.phtml&return_url_nikki=https%3A%2F%2Fcampus.nikki.ne.jp%2F&pp_version=20170213&u=xperd00&p=q0d9gd3y&submit=%A5%ED%A5%B0%A5%A4%A5%F3',
		CURLOPT_HEADER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_TIMEOUT => 5,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]);
	$res = curl_exec($curl);
	$info = curl_getinfo($curl);
  $header = substr($res, 0, $info['header_size']);
  //pittoのみで動作
	$cookie = [];
	$cookieCount = substr_count($header, 'Set-Cookie: pitto');//substr_count($header, 'Set-Cookie:');
	$start = 0;
	for ($i = 0; $i < $cookieCount; $i++){
		$start = strpos($header, 'Set-Cookie: pitto', $start + 1) + 12;
		$end = strpos($header, ';', $start);
		$cookie[] = substr($header, $start, $end - $start);
	}

	//mysqlに一時保存
	$link = mysqli_connect();
	mysqli_query($link, "UPDATE delisys.temp SET data='".json_encode($cookie)."' WHERE name='mincam_cookie';");
  mysqli_close($link);

  session_start();
  $_SESSION['mincam_cookie'] = $cookie;
  session_write_close();
}

/* 一時保存されたセッション取得 */
function getMincamSession($curl){
	$link = mysqli_connect();
	$resultCookie = mysqli_query($link, "SELECT data FROM delisys.temp WHERE name='mincam_cookie';");
	if ($resultCookie){
    session_start();
    $_SESSION['mincam_cookie'] = json_decode(mysqli_fetch_assoc($resultCookie)['data']);
    session_write_close();
	  mysqli_close($link);
	}
  else {
    mysqli_close($link);
    createMincamSession($curl);
  }
}

function getMincamData($curl, $page){
  session_start();
  if (!isset($_SESSION['mincam_cookie'])) getMincamSession($curl);

	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://campus.nikki.ne.jp/?module=lesson&action=index&univ=%C0%E9%CD%D5%C2%E7%B3%D8&lname=&fname=&lesson_name=&faculty1=&id=&order=1&page='.$page,
		CURLOPT_REFERER => '',
		CURLOPT_COOKIE => implode('; ', $_SESSION['mincam_cookie']),
		CURLOPT_POST => false,
		CURLOPT_POSTFIELDS => '',
		CURLOPT_HEADER => false,
		CURLOPT_FOLLOWLOCATION => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_TIMEOUT => 5,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]);
  session_write_close();
  $rawRes = curl_exec($curl);
  $res = str_replace('euc-jp', 'utf-8', mb_convert_encoding($rawRes, 'UTF-8', 'eucjp-win'));
  $doc = phpQuery::newDocument($res);

  //セッション切れをチェック
	if (count($doc->find('.login')) > 0){
    createMincamSession($curl);
    return getMincamData($curl, $page);
  }

  $data = [];
  $count = count($doc->find('#apartValue .list'));
  for($i = 0; $i < $count; $i++){
    $section = $doc->find('#apartValue .list:eq('.$i.')');
    $title = $section->find('.lecture')->text();
    $university = $section->find('.college span:eq(0)')->text();
    $faculty = $section->find('.college span:eq(1)')->text();
    $department = $section->find('.college span:eq(2)')->text();
    /**/$teacherUrl = mb_convert_encoding(urldecode($section->find('.college span:eq(3) a')->attr('href')), 'UTF-8', 'EUC-JP');
    /**/$teacherQuery = mb_substr($teacherUrl, mb_strpos($teacherUrl, '?') + 1);
    /**/$teacherQueryData = explode('&', $teacherQuery);
    foreach ($teacherQueryData as $d){
      if (mb_strpos($d, 'lname') !== false) $lastName = mb_substr($d, mb_strpos($d, '=') + 1);
      else if (mb_strpos($d, 'fname') !== false) $firstName = mb_substr($d, mb_strpos($d, '=') + 1);
    }
    $id = intval(str_replace(['[', ']'], '', $section->find('.college span:eq(3) font')->text()));
    $richPoint = intval(mb_substr($section->find('.value img:eq(0)')->attr('alt'), 0, 1));
    $easyPoint = intval(mb_substr($section->find('.value img:eq(1)')->attr('alt'), 0, 1));
    /**/$credit = explode(' ', $section->find('.subject span')->text());
    /**/$creditPos = mb_strpos($credit[2], '(');
    $creditUniversity = $credit[1];
    $creditName = mb_substr($credit[2], 0, $creditPos);
    $postDate = '20'.mb_substr($credit[2], $creditPos + 1, mb_strpos($credit[2], ')') - $creditPos - 1);
    $attend = $section->find('.apartContents .attend dd')->text();
    $textbook = $section->find('.apartContents .book dd')->text();
    $middleExam = mb_substr($section->find('.apartContents .apartBox .test dd p:eq(0)')->text(), 6);
    $finalExam = mb_substr($section->find('.apartContents .apartBox .test dd p:eq(1)')->text(), 6);
    $bringIn = mb_substr($section->find('.apartContents .apartBox .test dd p:eq(2)')->text(), 5);
    $message = $section->find('.apartContents .apartBox .message span')->html();
    $data[] = [ 'title'=>$title, 'university'=>$university, 'faculty'=>$faculty, 'department'=>$department,
      'lastName'=>$lastName, 'firstName'=>$firstName, 'id'=>$id, 'richPoint'=>$richPoint, 'easyPoint'=>$easyPoint,
      'creditUniversity'=>$creditUniversity, 'creditName'=>$creditName, 'postDate'=>$postDate, 'attend'=>$attend,
      'textbook'=>$textbook, 'middleExam'=>$middleExam, 'finalExam'=>$finalExam, 'bringIn'=>$bringIn, 'message'=>$message ];
  }
  return $data;
}

function addMincamData($link, $data){
  $return = true;
	foreach ($data as $d){
    $query = "INSERT IGNORE INTO delisys.mincam VALUES ('$d[title]', '$d[university]', '$d[faculty]', '$d[department]', ".
      "'$d[lastName]', '$d[firstName]', $d[id], $d[richPoint], $d[easyPoint], '$d[creditUniversity]', '$d[creditName]', ".
	    "'$d[postDate]', '$d[attend]', '$d[textbook]', '$d[middleExam]', '$d[finalExam]', '$d[bringIn]', '".mysqli_real_escape_string($link, $d['message'])."');";
    if (!mysqli_query($link, $query)) $return = false;
  }
  return $return;
}

/* シラバス検索 */
function mincam($link, $query){
  $where = [];
  if (isset($query['title']) && $query['title'] !== '') $where[] = "title LIKE '%$query[title]%'";
  if (isset($query['teacher']) && $query['teacher'] !== ''){
    if (mb_strpos($query['teacher'], ' ') !== false){
      $n = explode(' ', $query['teacher']);
      $where[] = "lastName = '$n[0]'";
      $where[] = "firstName = '$n[1]'";
    }
    else if (mb_strpos($query['teacher'], '　') !== false){
      $n = explode('　', $query['teacher']);
      $where[] = "lastName = '$n[0]'";
      $where[] = "firstName = '$n[1]'";
    }
    else
      $where[] = "(lastName = '$query[teacher]' OR firstName = '$query[teacher]')";
  }
  if (isset($query['message']) && $query['message'] !== '') $where[] = "message LIKE '%$query[message]%'";
  $result = mysqli_query($link, 'SELECT * FROM delisys.mincam WHERE '.implode(' AND ', $where).' ORDER BY id DESC;');
  $subjects = [];
	if ($result)
		while ($row = mysqli_fetch_assoc($result))
			$subjects[] = $row;
	
	return [ 'status'=>'success', 'subjects'=>$subjects, 'query'=>$query ];
}

/* シラバス検索 */
function search($curl, $query){
	//$data = ['s_no' => '0', '_eventId' => 'search', 'nendo' => 2019, 'kaikoKamokunmLike' => '英語'];
	//'s_no=0&'.substr($baseUrl, strpos($baseUrl, "?") + 1).'&_eventId=search&nendo=2019&jikanwariShozokuCode=&gakkiKubunCode=&kaikoKubunCode=&kyokannmLike=&jikanwaricdLike=&kaikoKamokunmLike=%E8%8B%B1%E8%AA%9E&nenji=&yobi=&jigen=&freeWord=&nbrGakubucd=&nbrGakkacd=&nbrSuijuncd=&fukusenkocd=&syReferOrder=&_displayCount=100';

  session_start();
	if (!isset($_SESSION['url']) || !isset($_SESSION['cookie'])) getSession($curl);
  //get result from server
  set_time_limit(60);
	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://cup.chiba-u.jp/campusweb/campussquare.do',
		CURLOPT_REFERER => $_SESSION['url'],
		CURLOPT_COOKIE => implode('; ', $_SESSION['cookie']),
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => 's_no=0&'.substr($_SESSION['url'], strpos($_SESSION['url'], "?") + 1).'&_eventId=search&_displayCount=1928&'.$query['query'], //http_build_query
		CURLOPT_HEADER => false,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_CONNECTTIMEOUT => 0,
		CURLOPT_TIMEOUT => 60,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]);
  session_write_close();
	$res = curl_exec($curl);
	$info = curl_getinfo($curl);

	//セッション切れをチェック
	if ($info['url'] === 'https://cup.chiba-u.jp/campusweb/campusportal.do' || $info['url'] === 'https://cup.chiba-u.jp/campusweb/campussquare.do'){
    createSession($curl);
    return search($curl, $query);
  }
	
	//HTMLをパース
	$tbody = phpQuery::newDocument($res)->find('table > tbody');
	$subjects = [];
	$subjectCount = count($tbody->find('tr'));
	for($i=0; $i<$subjectCount; $i++){
		$tr = $tbody->find('tr:eq('.$i.')');

		/*if (isset($query['proc_kaikoKamokunmLike']) && isset($query['real_kaikoKamokunmLike'])){
			$title = getTextFromTr($tr, 6);
			$words = explode(' ', $query['real_kaikoKamokunmLike']);
			$incorrect = false;
			foreach ($words as $w){
				if (mb_strpos($title, $w) !== false) continue;
				else { $incorrect = true; break; }
			}
			if ($incorrect) continue;
		}*/

		$sub = [];
		$dataCount = count($tr->find('td')); //0-10
		for($j=1; $j<$dataCount; $j++)
			$sub[] = getTextFromTr($tr, $j);
		$button = $tr->find('td:eq('.($dataCount - 1).')')->find('input:eq(0)');
		$refer = $button->attr('onclick');
		if ($button->attr('disabled') == null) $sub[] = 'ja_JP';
		else $sub[] = 'en_US';
		$start = strpos($refer, ",") + 2;
		$end = strpos($refer, "'", $start);
		$sub[] = substr($refer, $start, $end - $start);
		$subjects[] = $sub;
	}
	
  //履歴に追加
  session_start();
	if (isset($_SESSION['id'])){
		$link = mysqli_connect();
		mysqli_query($link, 'INSERT INTO delisys.history_search VALUES ("'.$_SESSION['id'].'", NOW(), "'.$query['query'].'", '.$subjectCount.');');
  }
  session_write_close();
	
	return [ 'status'=>'success', 'subjects'=>$subjects, 'url'=>$info['url'] ];
}

function getTextFromTr($tr, $j){
	return mb_convert_kana(preg_replace('/(?:\n|\r|\r\n|\s)/', '', $tr->find('td:eq('.$j.')')->text()), 'asKV');
}

/* シラバス詳細取得 元のURL不要版 */
function syllabus($curl, $link, $query, $temp = false){
	$data = explode('-', $query['code']);
  //セッションチェック
  session_start();
  if (!isset($_SESSION['url']) || !isset($_SESSION['cookie'])) getSession($curl);
	//キャッシュチェック、tempだけの時は終了へ
	$result = mysqli_query($link, 'SELECT credit FROM delisys.syllabus WHERE nendo='.$data[0].' and jikanwariShozokuCode="'.$data[1].'" and jikanwaricd="'.$data[2].'" and slocale="'.$data[3].'";');
	if (mysqli_num_rows($result) === 1 && /*mysqli_fetch_assoc($result)['credit'] !== '' &&*/ $temp) return;
	$url = 'https://cup.chiba-u.jp/campusweb/campussquare.do?locale=ja_JP&_flowId=SYW3901100-flow&nendo='.$data[0].'&jikanwariShozokuCode='.$data[1].'&jikanwaricd='.$data[2].'&slocale='.$data[3];

	//情報をサーバーから取得
	curl_setopt_array($curl, [
		CURLOPT_URL => $url,
		CURLOPT_REFERER => '',
		CURLOPT_COOKIE => implode('; ', $_SESSION['cookie']),
		CURLOPT_POST => false,
		CURLOPT_POSTFIELDS => '',
		CURLOPT_HEADER => false,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_TIMEOUT => 5,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]);
  session_write_close();
	$res = curl_exec($curl);
	$info = curl_getinfo($curl);
	
	//HTMLをパース
	$doc = phpQuery::newDocument($res);
	
	//期限切れセッションチェック
	if ($info['url'] === 'https://cup.chiba-u.jp/campusweb/campusportal.do' || $info['url'] === 'https://cup.chiba-u.jp/campusweb/campussquare.do' || $doc->find('title')->text() === '認証エラー'){
    createSession($curl);
    return syllabus($curl, $link, $query);
  }

	$details1 = tableAnalysis($doc->find('#tabs-1'), "");
	$details2 = tableAnalysis($doc->find('#tabs-2'), "<br>");
	$details3 = tableAnalysisThird($doc->find('#tabs-3'));

	//mysqlに一時キャッシュ
	if (count($details1) > 0 && count($details2) > 0){
		$summary = (isset($details2['概要']) ? $details2['概要'] : '');
		$summary = (mb_strlen($summary) > 120 ? mb_substr($summary, 0, 120) : $summary);
		/*if (mysqli_num_rows($result) === 1 && mysqli_fetch_assoc($result)['credit'] === '')
			$result = mysqli_query($link, 'UPDATE delisys.syllabus SET credit="'.$details1['単位数'].'", summary="'.$summary.'" WHERE nendo='.$query['nendo'].' and jikanwariShozokuCode="'.$query['jikanwariShozokuCode'].'" and jikanwaricd="'.$query['jikanwaricd'].'" and slocale="'.$query['slocale'].'";');
		else */if (mysqli_num_rows($result) === 0)
			$result = mysqli_query($link, 'INSERT INTO delisys.syllabus VALUES ("'.preg_replace("/(.*?)ターム/", 'T$1', substr($details1['履修年次/ターム'], strpos($details1['履修年次/ターム'], "/") + 1)).'", "'.$details1['曜日・時限'].'", "'.$details1['単位数'].'", "'.$details1['授業科目'].'", "'.$details1['担当教員'].'", "'.$summary.'", '.$data[0].', "'.$data[1].'", "'.$data[2].'", "'.$data[3].'");');
		
    //履歴に追加
    session_start();
		if (isset($_SESSION['id']))
      mysqli_query($link, 'INSERT INTO delisys.history_syllabus VALUES ("'.$_SESSION['id'].'", NOW(), '.$data[0].', "'.$data[1].'", "'.$data[2].'", "'.$data[3].'");');
    session_write_close();
	}

	return [ 'status'=>((count($details1) > 0 && count($details2) > 0) ? 'success' : 'failed'), 'detail-1'=>$details1, 'detail-2'=>$details2, 'detail-3'=>$details3, 'redirect'=>$info['url'], 'original'=>$url ];
}

function tableAnalysis($tbody, $insert){
	$details = [];
	$detailsCount = count($tbody->find('tr'));
	for($i=0; $i<$detailsCount; $i++){
		$name = mb_convert_kana(str_replace([" ", "　"], '', $tbody->find('tr:eq('.$i.')')->find('th')->text()), 'aKV');
		$detail = mb_convert_kana($tbody->find('tr:eq('.$i.')')->find('td')->html(), 'asKV');
		$detail = str_replace('<br>', "\n", $detail);
		if ($name === '' || mb_strpos($name, '15の力') !== false) continue;
		if (mb_strpos($detail, "\n") !== false){
			$detailList = explode("\n", $detail);
			$detailList = array_map('trim', $detailList); // 各行にtrim()をかける
			$detailList = array_filter($detailList, 'strlen'); // 文字数が0の行を取り除く
			$detail = implode($insert, $detailList);
		}
		$detail = trim($detail);
		if ($detail === '') continue;
		$posName = mb_strpos($name, "/");
		$posDetail = mb_strpos($detail, "/");
		if (mb_strpos($name, '履修年次') !== false){
			$posName = mb_strpos($name, "/", 6);
			$detail = str_replace('・', '･', $detail);
		}
		else if (mb_strpos($name, '曜日') !== false){
			$detail = str_replace(['/Sun', '/Mon', '/Tue', '/Wed', '/Thu', '/Fri', '/Sat', '/Oth'], '', $detail);
			$detail = str_replace(' ,', ',', $detail);
		}
		else if ($posDetail !== false && $insert === '')
			$detail = trim(mb_substr($detail, 0, $posDetail));
		$name = mb_substr($name, 0, $posName);
		$details[$name] = str_replace(["\r", "\n", "\r\n"], '<br>', $detail);
	}
	return $details;
}

function tableAnalysisThird($tbody){
	$details = [];
	$detailsCount = count($tbody->find('tr'));
	for($i=0; $i<$detailsCount; $i++){
		$tr = $tbody->find('tr:eq('.$i.')');
		$td = [];
		$tdCount = count($tr->find('td'));
		if ($detailsCount === 1 && $tdCount === 0) return false; //該当するデータはありません
		else if ($tdCount === 0) continue; //1行目
		for ($j=0; $j<$tdCount; $j++)
			$td[] = trim(mb_convert_kana($tbody->find('tr:eq('.$i.')')->find('td:eq('.$j.')')->text(), 'asKV'));
		$detail = trim($td[0].'. '.$td[1].'  '.$td[2].($td[3] === '' ? '' : ("\n内容: ".$td[3])).($td[4] === '' ? '' : ("\n備考: ".$td[4])));
		$details[] = str_replace("\n", '<br>', $detail);
	}
	return $details;
}

function memoGet($link, $query){
  $data = explode('-', $query['code']);
  $result = [ 'text'=>'', 'lastUpdated'=>null ];
  session_start();
	if (isset($_SESSION['id'])){
		$memo = mysqli_fetch_assoc(mysqli_query($link, 'SELECT `text`, `lastUpdated` FROM delisys.memo WHERE id="'.$_SESSION['id'].'" and nendo='.$data[0].' and jikanwariShozokuCode="'.$data[1].'" and jikanwaricd="'.$data[2].'";'));
    if ($memo) $result = $memo;
  }
  else
    $result = false;
  session_write_close();
  $result['status'] = $result ? 'success' : 'failed';
	return $result;
}

function memoSave($link, $query){
	$data = explode('-', $query['code']);
  $result = false;
  session_start();
	if (isset($_SESSION['id'])){
    if ($query['text'] === '')
      $result = mysqli_query($link, 'DELETE FROM delisys.memo WHERE id="'.$_SESSION['id'].'" and nendo='.$data[0].' and jikanwariShozokuCode="'.$data[1].'" and jikanwaricd="'.$data[2].'";');
    else {
      $r = mysqli_fetch_assoc(mysqli_query($link, 'SELECT `text`, `lastUpdated` FROM delisys.memo WHERE id="'.$_SESSION['id'].'" and nendo='.$data[0].' and jikanwariShozokuCode="'.$data[1].'" and jikanwaricd="'.$data[2].'";'));
      if ($r) $result = mysqli_query($link, 'UPDATE delisys.memo SET text="'.mysqli_real_escape_string($link, str_replace("\n", '<br>', $query['text'])).'", lastUpdated=NOW() WHERE id="'.$_SESSION['id'].'" and nendo='.$data[0].' and jikanwariShozokuCode="'.$data[1].'" and jikanwaricd="'.$data[2].'";');
      else $result = mysqli_query($link, 'INSERT INTO delisys.memo VALUES ("'.$_SESSION['id'].'", '.$data[0].', "'.$data[1].'", "'.$data[2].'", "'.$data[3].'", "'.mysqli_real_escape_string($link, str_replace("\n", '<br>', $query['text'])).'", NOW());');
    }
  }
  session_write_close();
	return [ 'status'=>$result ? 'success' : 'failed' ];
}

/* コメント取得 */
function commentGet($link, $query){
	$data = explode('-', $query['code']);
	$amount = 30;
	if ($query['reload'] === 'true'){
		//コメント再取得
		$amount = $query['index'] + 1;
		$query['index'] = 0;
	}
	if ($query['all_nendo'] === 'true')
		$result = mysqli_query($link, 'SELECT num, name, text, datetime, nendo FROM delisys.comment WHERE jikanwariShozokuCode="'.$data[1].'" and jikanwaricd="'.$data[2].'" ORDER BY datetime DESC LIMIT '.$amount.' OFFSET '.$query['index'].';');
	else
		$result = mysqli_query($link, 'SELECT num, name, text, datetime FROM delisys.comment WHERE nendo='.$data[0].' and jikanwariShozokuCode="'.$data[1].'" and jikanwaricd="'.$data[2].'" ORDER BY datetime DESC LIMIT '.$amount.' OFFSET '.$query['index'].';');
	$data = [];
	if ($result)
		while ($row = mysqli_fetch_assoc($result)){
			$date = strtotime($row['datetime']);
			$row['datetime'] = date('Y/m/d(', $date).["日", "月", "火", "水", "木", "金", "土"][date('w', $date)].date(') H:i:s', $date);
			$row['name'] = htmlspecialchars($row['name'], ENT_QUOTES|ENT_HTML5);
			$row['text'] = htmlspecialchars($row['text'], ENT_QUOTES|ENT_HTML5);
			$data[] = $row;
		}
	return [ 'status'=>$result ? 'success' : 'failed', 'comment'=>$data, 'index'=>($query['index'] + count($data)) ];
}

/* コメント投稿 */
function commentPost($link, $query){
	$data = explode('-', $query['code']);
	$result = mysqli_query($link, 'SELECT MAX(num) FROM delisys.comment WHERE nendo='.$data[0].' and jikanwariShozokuCode="'.$data[1].'" and jikanwaricd="'.$data[2].'";');
	if ($result) $num = intval(mysqli_fetch_assoc($result)['MAX(num)']) + 1;
  else $num = 1;
  session_start();
	if (isset($_SESSION['id']))
    mysqli_query($link, 'INSERT INTO delisys.comment VALUES ("'.$_SESSION['id'].'", '.$num.', "'.(trim($query['name']) === '' ? '名無しの千葉大生' : trim($query['name'])).'", "'.mysqli_real_escape_string($link, $query['text']).'", NOW(), "'.$_SERVER['REMOTE_ADDR'].'", '.$data[0].', "'.$data[1].'", "'.$data[2].'", "'.$data[3].'");');
  session_write_close();
	return commentGet($link, $query);
}

function changeFavorite($link, $query){
	$data = explode('-', $query['code']);
  $result = false;
  session_start();
	if (isset($_SESSION['id'])){
		if ($query['bool'] === 'true')
			$result = mysqli_query($link, 'INSERT INTO delisys.favorite VALUES ("'.$_SESSION['id'].'", '.$data[0].', "'.$data[1].'", "'.$data[2].'", "'.$data[3].'");');
		else
			$result = mysqli_query($link, 'DELETE FROM delisys.favorite WHERE id="'.$_SESSION['id'].'" and nendo='.$data[0].' and jikanwariShozokuCode="'.$data[1].'" and jikanwaricd="'.$data[2].'" and slocale="'.$data[3].'";');
  }
  session_write_close();
	return getFavoriteById($link, $result);
}

function getFavoriteById($link, $favorite = false){
	$list = [];
  $data = [];
  $favorite = false;
  session_start();
	if (isset($_SESSION['id'])){
		$favorite = mysqli_query($link, 'SELECT `nendo`, `jikanwariShozokuCode`, `jikanwaricd`, `slocale` FROM delisys.favorite WHERE id="'.$_SESSION['id'].'";');
    session_write_close();
    if ($favorite)
			while ($row = mysqli_fetch_assoc($favorite)){
				$list[] = $row['nendo'].'-'.$row['jikanwariShozokuCode'].'-'.$row['jikanwaricd'].'-'.$row['slocale'];
				$data[] = mysqli_fetch_assoc(mysqli_query($link, 'SELECT `nendo`, `term`, `time`, `credit`, `name`, `teacher`, `summary` FROM delisys.syllabus WHERE nendo='.$row['nendo'].' and jikanwariShozokuCode="'.$row['jikanwariShozokuCode'].'" and jikanwaricd="'.$row['jikanwaricd'].'" and slocale="'.$row['slocale'].'";'));
			}
  }
  else session_write_close();
	return [ 'status'=>$favorite ? 'success' : 'failed', 'list'=>$list, 'data'=>$data ];
}

function getSyllabusTemp($link, $code){
	$data = explode('-', $code);
	$result = mysqli_query($link, 'SELECT `nendo`, `term`, `time`, `credit`, `name`, `teacher`, `summary` FROM delisys.syllabus WHERE nendo='.$data[0].' and jikanwariShozokuCode="'.$data[1].'" and jikanwaricd="'.$data[2].'" and slocale="'.$data[3].'";');
	if (mysqli_num_rows($result) === 1) return [ 'status'=>'success', 'data'=>mysqli_fetch_assoc($result) ];
	else return [ 'status'=>'failed' ];
}

//1コマ 90分  XXXX-XX-XXTXX:XX:XX+09:00
define('DOW', [ '日'=>0, '月'=>1, '火'=>2, '水'=>3, '木'=>4, '金'=>5, '土'=>6 ]);
define('DOWEN', [ '日'=>'SU', '月'=>'MO', '火'=>'TU', '水'=>'WE', '木'=>'TH', '金'=>'FR', '土'=>'SA' ]);
define('StartTime', [ 1=>'08:50', 2=>'10:30', 3=>'12:50', 4=>'14:30', 5=>'16:10', 6=>'17:50', 7=>'19:30' ]);
define('EndTime', [ 1=>'10:20', 2=>'12:00', 3=>'14:20', 4=>'16:00', 5=>'17:40', 6=>'19:20', 7=>'21:00' ]);
define('StartTerm', [ '2019'=>[ 1=>'2019-04-08', 2=>'2019-06-11', 3=>'2019-08-07', 4=>'2019-10-01', 5=>'2019-12-03', 6=>'2020-02-06', 7=>'2020-04-01' ] ]);
define('EndTerm', [ '2019'=>[ 1=>'2019-06-10', 2=>'2019-08-06', 3=>'2019-09-30', 4=>'2019-12-02', 5=>'2020-02-05', 6=>'2019-03-31' ] ]);
define('Holiday', [ '2019-04-29', '2019-04-30', '2019-05-01', '2019-05-02', '2019-05-03', '2019-05-06', '2019-07-15', '2019-08-12' ]);
define('RDATE', [ '月'=>[ '20190716' ] ]);

use \ParagonIE\ConstantTime\Encoding;
function getAllCalendarSubjects($client){
	if (!$client) return [ 'status'=>'expired', 'result'=>null ];
	$service = new Google_Service_Calendar($client);
	$result = $service->events->listEvents('primary', [
		'q' => 'Created By delisys', 'maxResults' => 2500
	]);
	$data = [];
  foreach ($result->getItems() as $event){
    if (strpos($event->getId(), '_') !== false) continue;
		$id = explode('&', Encoding::base32HexDecode($event->getId()));
    //UNIX時間&シラバスコード&ターム&曜時&単位数&★ターム&★曜日
    if (count($id) >= 6)
  		$data[] = [ 'id'=>$event->getId(), 'add'=>date(DateTime::ATOM, $id[0]), 'nendo'=>explode('-', $id[1])[0], 'code'=>$id[1], 'term'=>$id[2], 'time'=>$id[3], 'credit'=>intval($id[4]), 'index'=>[ 'term'=>$id[5], 'time'=>$id[6] ],
        'name'=>$event['summary'], 'location'=>$event['location'], 'start'=>$event['start']['dateTime'], 'end'=>$event['end']['dateTime'], 'notification'=>(count($event['reminders']['overrides']) > 0) ];
    else
      continue;
  		//$data[] = [ 'id'=>$event->getId(),
        //'name'=>$event['summary'], 'location'=>$event['location'], 'start'=>$event['start']['dateTime'], 'end'=>$event['end']['dateTime'], 'notification'=>(count($event['reminders']['overrides']) > 0) ];
	}
	return [ 'status'=>'success', 'result'=>$data ];
}

function getWeekCalendarSubjects($client){
	if (!$client) return [ 'status'=>'expired', 'result'=>null ];
	if (date('w') === '0'){
		//日曜日
		$min = date(DateTime::ATOM, strtotime('sunday this week'));
		$max = date(DateTime::ATOM, strtotime('sunday next week'));
	}
	else{
		//月曜から土曜日
		$min = date(DateTime::ATOM, strtotime('sunday previous week'));
		$max = date(DateTime::ATOM, strtotime('sunday this week'));
	}
	$date = new DateTime();
	$service = new Google_Service_Calendar($client);
	$result = $service->events->listEvents('primary', [
		'q'=>'Created By delisys', 'maxResults'=>2500, 'singleEvents'=>true, 'timeMin'=>$min, 'timeMax'=>$max
	]);
	$data = [];
  foreach ($result->getItems() as $event){
		$id = explode('&', Encoding::base32HexDecode(explode('_', $event['id'])[0]));
		$data[] = [ 'add'=>date(DateTime::ATOM, $id[0]), 'code'=>$id[1], 'term'=>$id[2], 'time'=>$id[3], 'credit'=>intval($id[4]), 'index'=>[ 'term'=>$id[5], 'time'=>$id[6] ],
			'name'=>$event['summary'], 'location'=>$event['location'], 'start'=>$event['start']['dateTime'], 'end'=>$event['end']['dateTime']  ];
	}
	return [ 'status'=>'success', 'result'=>$data ];
}

function addCalendar($client, $data){
	if (!$client) return [ 'status'=>'expired' ];
	//data: term (startDate endDate) time (startTime endTime) code name location description
	$service = new Google_Service_Calendar($client);
	$events = [];
	$nendo = explode('-', $data['code'])[0];

	//タームの演算  $data['term'] ex)T1 T1-2 T1･3 T1集
	$terms = [];
	if (mb_strpos($data['term'], '集') !== false){
		//集中→日程指定してもらった日付を流用
		$terms[] = [ 'start'=>$data['startDate'], 'end'=>$data['endDate'] ];
	}
	else if (mb_strpos($data['term'], '通') !== false){
		//通年→1~6ターム？
		$terms[] = [ 'start'=>StartTerm[$nendo][1], 'end'=>EndTerm[$nendo][6] ];
	}
	else if (strpos($data['term'], '･') !== false){
		//独立した複数タームにまたがる→イベントを分ける
		$termList = explode('･', substr($data['term'], 1)); //[1,3]
		foreach ($termList as $term)
			$terms[] = [ 'start'=>StartTerm[$nendo][intval($term)], 'end'=>EndTerm[$nendo][(intval($term))] ];
	}
	else if (strpos($data['term'], '-') !== false){
		//連続した複数タームにまたがる→イベントをつなげる
		$termList = explode('-', substr($data['term'], 1)); //[1,3]
		$terms[] = [ 'start'=>StartTerm[$nendo][intval($termList[0])], 'end'=>EndTerm[$nendo][(intval($termList[1]))] ];
	}
	else {
		//通常の単独ターム
		$term = intval(substr($data['term'], 1));
		$terms[] = [ 'start'=>StartTerm[$nendo][$term], 'end'=>EndTerm[$nendo][$term] ];
	}

	//開始時刻の演算  $data['time'] ex)月1,月2
	$times = []; //[day:月,start:8:50,end:10:20]
	if ($data['allDay'] === 'true' || $data['time'] === '通'){
		//終日イベントに、毎日のみ
		$times[] = [ 'day'=>'all', 'hour'=>null ];
	}
	else if ($data['time'] === '他'){
		//時間指定を利用、毎日のみ
		$times[] = [ 'day'=>'time', 'hour'=>null, 'start'=>$data['startTime'], 'end'=>$data['endTime'] ];
	}
	else if (mb_strpos($data['time'], ',') === false){
		$hour = intval(mb_substr($data['time'], 1, 1));
		$times[] = [ 'day'=>mb_substr($data['time'], 0, 1), 'hour'=>$hour,
								'start'=>StartTime[$hour], 'end'=>EndTime[$hour] ];
	}
	else {
		$timeList = explode(',', $data['time']);
		$dayOfWeekList = [];
		foreach ($timeList as $t){
			$dow = mb_substr($t, 0, 1);
			if (!array_key_exists($dow, $dayOfWeekList)) $dayOfWeekList[$dow] = []; //曜日の配列がなければ作成
			$dayOfWeekList[$dow][] = intval(mb_substr($t, 1, 1)); //時限
		}
		foreach ($dayOfWeekList as $dow => $list){
			asort($list);
			$startHour = -1;
			$startIndex = -1;
			for ($i = 0; $i < count($list); $i++){
				if ($startHour === -1 && $startIndex === -1){
					$startHour = $list[$i];
					$startIndex = $i;
				}
				if ($i + 1 === count($list) || $list[$i + 1] - $list[$i] > 1){
					//配列の最後or次との時間が1時間以上空き→時間登録
					$times[] = [ 'day'=>$dow, 'hour'=>$startHour,
											'start'=>StartTime[$startHour], 'end'=>EndTime[$list[$i]] ];
					$startHour = $startIndex = -1;
				}
			}
		}
	}

	$holidayList = [];
  foreach (Holiday as $h) $holidayList[] = new DateTime($h);

	//イベントの追加
	$result = [];
	for ($i = 0; $i < count($terms); $i++){
		for ($j = 0; $j < count($times); $j++){
			$id = $_SERVER['REQUEST_TIME'].'&'.$data['code'].'&'.$data['term'].'&'.$data['time'].'&'.$data['credit'].'&'.$i.'&'.$j;
			//UNIX時間&シラバスコード&ターム&曜時&単位数&★ターム&★曜日
			//0000000000&2000-XX-XXXXXX-ja_JP&T1&月1&2&0&0
			$startDate = new DateTime($terms[$i]['start']);
			$allDay = false;
			$everyDay = false;
			if ($times[$j]['day'] === 'all'){
				$allDay = true;
				$everyDay = true;
			}
			else if ($times[$j]['day'] === 'time'){
				$everyDay = true;
			}
			else {
				$diff = DOW[$times[$j]['day']] - intval($startDate->format('w'));
				if ($diff > 0) //開始日の曜日より後
					$startDate->modify('+'.$diff.' days');
				else if ($diff < 0) //開始日の曜日より前
					$startDate->modify('+'.(7 + $diff).' days');
			}
			$exdate = [ 'RRULE:FREQ='.($everyDay ? 'DAILY;' : 'WEEKLY;WKST=SU;BYDAY='.DOWEN[$times[$j]['day']].';').'UNTIL='.str_replace('-', '', $terms[$i]['end']).'T145959Z' ];
			if ($data['ignoreExdate'] === 'false')
				foreach ($holidayList as $h)
					if ($everyDay || intval($h->format('w')) === DOW[$times[$j]['day']])
						$exdate[] = 'EXDATE:'.$h->format('Ymd').'T'.str_replace(':', '', $times[$j]['start'].':00');
      $overrides = [];
      if ($data['notification'] === 'true')
        $overrides[] = [ 'method' => 'popup', 'minutes' => ($times[$j]['hour'] === 3 ? 50 : 10) ];
      session_start();
      $_SESSION['userdata']['notification'] = ($data['notification'] === 'true' ? '1' : '0');
      session_write_close();
			
			$result[] = $service->events->insert('primary', new Google_Service_Calendar_Event([
			//echo(json_encode([
				'id' => str_replace('=', '', Encoding::base32HexEncode($id)),
				'colorId' => '2', //#ff887c
				'summary' => $data['name'], //予定のタイトル
				'location' => ($data['location'] === '' ? '' : '千葉大学 '.$data['location']), //予定の位置
				'description' => $data['description']."シラバスの詳細ページはこちら\nhttps://delisys.xperd.net/syllabus?".$data['code']."\nCreated by delisys",
	  		'start' => [ //開始日時
  	  		($allDay ? 'date' : 'dateTime') => $startDate->format('Y-m-d').($allDay ? '' : 'T'.$times[$j]['start'].':00+09:00'),
    			'timeZone' => 'Asia/Tokyo',
				],
		  	'end' => [ //終了日時
  	  		($allDay ? 'date' : 'dateTime') => $startDate->format('Y-m-d').($allDay ? '' : 'T'.$times[$j]['end'].':00+09:00'),
    			'timeZone' => 'Asia/Tokyo',
				],
				'recurrence' => $exdate, //繰り返し設定
				'source' => [
					'url' => 'https://delisys.xperd.net/syllabus?'.$data['code'], 'title' => $data['name'].'の詳細 -チバシス-'
				],
				'reminders' => [ 'useDefault' => false, 'overrides' => $overrides ]
			]));
		}
  }
  
  $link = mysqli_connect();
  updateCalendarNotification($link, $data['notification'] === 'true');
  mysqli_close($link);
	return [ 'status'=>'success', 'result'=>$result ];
}

function deleteCalendarSubjects($client, $id){
	if (!$client) return [ 'status'=>'expired', 'result'=>[] ];
  $service = new Google_Service_Calendar($client);
  $result[] = $service->events->delete('primary', $id);
	return [ 'status'=>'success', 'result'=>$result ];
}

function toggleCalendarNotification($client, $query){
  if (!$client) return [ 'status'=>'expired', 'result'=>[] ];
	$service = new Google_Service_Calendar($client);
	$result = [];
  foreach ($query['id'] as $id) {
    $event = $service->events->get('primary', $id);
    if ($query['bool'] === 'true'){
      $event['reminders'] =
        [ 'useDefault' => false, 'overrides' => [ [ 'method' => 'popup', 'minutes' =>
          (substr($event['start']['dateTime'], 11, 5) === '12:50' ? 50 : 10) ] ] ];
    }
    else if ($query['bool'] === 'false'){
      $event['reminders'] = [ 'useDefault' => false, 'overrides' => [] ];
    }
  	$result[] = $service->events->update('primary', $id, $event);  
  }
  $link = mysqli_connect();
  updateCalendarNotification($link, $query['bool'] === 'true');
  mysqli_close($link);
	return [ 'status'=>'success', 'result'=>$result ];
}

function updateCalendarNotification($link, $bool){
  session_start();
  $result = mysqli_query($link, 'UPDATE delisys.user SET notification='.($bool ? 1 : 0).' WHERE id="'.$_SESSION['id'].'";');
  session_write_close();
	return [ 'status'=>($result ? 'success' : 'failed') ];
}
?>
