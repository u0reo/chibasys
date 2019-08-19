<?php
//use ___PHPSTORM_HELPERS\object;

noDirectAccess();
ini_set('display_errors', 1);
set_time_limit(25);

//Google Client 関連のライブラリの読み込み
require_once('vendor/autoload.php');
//PHP Query ライブラリの読み込み
require_once('phpQuery-onefile.php');

/**
 * HTMLの改行コードを普通の改行に変換
 * http://hi.seseragiseven.com/archives/559
 *
 * @param string $string <br>などを含む文字列
 * @return string \nに置換された文字列
 */
function br2n($string)
{
	return preg_replace('/<br[[:space:]]*\/?[[:space:]]*>/i', "\n", $string);
}

/**
 * Google Clientのインスタンスを取得
 *
 * @return Google Clientのインスタンス
 */
function getGoogleClient(){
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

/**
 * 直接アクセスを禁止、直接アクセスしたときは400 Bad Requestに
 *
 * @return void
 */
function noDirectAccess(){
	$gif = get_included_files();
	if (array_shift($gif) === __FILE__){
		http_response_code(400);
		exit();
	}
}

/**
 * URLの/の後から#の前までを返却
 *
 * @return string 現在のURLのクエリ ex) syllabus?2000-AA-BBBBBB-ja_JP
 */
function getRequest(): string {
	$index = strrpos($_SERVER['REQUEST_URI'], '/') + 1;
	$pos = strpos($_SERVER['REQUEST_URI'], '#', $index);
	if ($pos === false)
		return substr($_SERVER['REQUEST_URI'], $index);
	else
		return substr($_SERVER['REQUEST_URI'], $index, $pos - $index + 1);
}

function maria_query(mysqli $link, string $query){
	$r = mysqli_query($link, $query);
	if (!$r)
		error_log(print_r($r, true)."\nQUERY:$query", "3", "/var/log/mysql/php_error.log");
	return $r;
}

/**
 * ようこそ画面へ遷移、最後にexit()実行
 *
 * @param string $query 追加したいクエリ ex)'?error=...'
 * @return void
 */
function locateWelcome(string $query = ''){
  header('location: /welcome'.$query);
  exit();
}

/**
 * ログイン画面へ遷移、最後にexit()実行
 *
 * @param $client Google Clientのインスタンス
 * @return void
 */
function locateLogin($client){
	header('location: '.$client->createAuthUrl());
  exit();
}

/**
 * Googleログインをしているか否かを判定
 *
 * @return Google Clientのインスタンス
 */
function sessionCheck(){
  $client = getGoogleClient();
  session_start();
  if (isset($_SESSION['accessToken'])){
		//一度はログイン済みの場合
    $client->setAccessToken($_SESSION['accessToken']);
    if (!$client->isAccessTokenExpired()){
			if (!isset($_SESSION['id'])){
				//GoogleのPeople APIでユーザーの情報を取得
	      $people = new Google_Service_PeopleService($client);
  	    $userinfo = $people->people->get('people/me', ['personFields'=>'names,photos']);
				$_SESSION['id'] = explode("/", $userinfo['resourceName'])[1];
				$_SESSION['google_photo_url'] = $userinfo['photos'][0]['url'];
				$_SESSION['google_user_name'] = $userinfo['names'][0]['displayName'];
			}
			if (!isset($_SESSION['userdata'])){
				//MySQLからユーザー情報を取得して、登録済みの時はセッションに保管
				$link = mysqli_connect();
				$res = maria_query($link, "SELECT * FROM delisys.user WHERE id = '$_SESSION[id]';");
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
	return null;
}

/**
 * 千葉大学シラバス検索のセッションを取得し、MySQLに保存
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURLのインスタンス
 * @return void
 */
function createSyllabusSession(mysqli $link, $curl){
	//cURLで千葉大学シラバスの入口にアクセスし、Cookieを取得
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
	$c = [];
	$cookieCount = substr_count($header, 'Set-Cookie:');
	$start = 0;
	for ($i = 0; $i < $cookieCount; $i++){
		$start = strpos($header, 'Set-Cookie:', $start + 1) + 12;
		$end = strpos($header, ';', $start);
		$c[] = substr($header, $start, $end - $start);
	}
	$cookie = implode('; ', $c);
	//一応、入力画面へ遷移しておく
	curl_setopt_array($curl, [
		CURLOPT_URL => $info['redirect_url'],
		CURLOPT_REFERER => '',
		CURLOPT_COOKIE => $cookie,
		CURLOPT_POST => false,
		CURLOPT_POSTFIELDS => '',
		CURLOPT_HEADER => true,
		CURLOPT_FOLLOWLOCATION => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_TIMEOUT => 5,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]);
	curl_exec($curl);
	//MySQLに入力画面のURLとCookieを一時保存
	maria_query($link, "UPDATE delisys.temp SET data='$info[redirect_url]' WHERE name='url';");
	maria_query($link, "UPDATE delisys.temp SET data='$cookie' WHERE name='syllabus_cookie';");
	//セッションにデータを保管
  session_start();
  $_SESSION['syllabus_url'] = $info['redirect_url'];
  $_SESSION['syllabus_cookie'] = $cookie;
  session_write_close();
}

/**
 * 一時保存されたセッション取得
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURLのインスタンス
 * @return void
 */
function getSyllabusSession(mysqli $link, $curl){
	//SQLを実行
	$resultUrl = maria_query($link, "SELECT data FROM delisys.temp WHERE name = 'url';");
	$resultCookie = maria_query($link, "SELECT data FROM delisys.temp WHERE name = 'syllabus_cookie';");
	if ($resultUrl && $resultCookie){
    session_start();
		$_SESSION['syllabus_url'] = mysqli_fetch_assoc($resultUrl)['data'];
    $_SESSION['syllabus_cookie'] = mysqli_fetch_assoc($resultCookie)['data'];
    session_write_close();
	}
	else
		//MySQLになければ、セッション取得
    createSyllabusSession($link, $curl);
}

/**
 * 千葉大学シラバスを検索し、教科一覧を返す→廃止予定
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURLのインスタンス
 * @param array $query POSTデータ
 * @return array 成功したかどうか、教科一覧の連想配列
 */
function search(mysqli $link, $curl, array $query): array {
	//$data = ['s_no' => '0', '_eventId' => 'search', 'nendo' => 2019, 'kaikoKamokunmLike' => '英語'];
	//'s_no=0&'.substr($baseUrl, strpos($baseUrl, "?") + 1).'&_eventId=search&nendo=2019&jikanwariShozokuCode=&gakkiKubunCode=&kaikoKubunCode=&kyokannmLike=&jikanwaricdLike=&kaikoKamokunmLike=%E8%8B%B1%E8%AA%9E&nenji=&yobi=&jigen=&freeWord=&nbrGakubucd=&nbrGakkacd=&nbrSuijuncd=&fukusenkocd=&syReferOrder=&_displayCount=100';
	//セッションデータ利用を開始してURLやCookieが保管されているか確認、なければMySQLから取得
  session_start();
	if (!isset($_SESSION['syllabus_url']) || !isset($_SESSION['syllabus_cookie'])) {
		session_write_close();
		getSyllabusSession($link, $curl);
		session_start();
	}
  //cURLで千葉大学シラバスの検索結果を取得
	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://cup.chiba-u.jp/campusweb/campussquare.do',
		CURLOPT_REFERER => $_SESSION['syllabus_url'],
		CURLOPT_COOKIE => $_SESSION['syllabus_cookie'],
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => 's_no=0&'.substr($_SESSION['syllabus_url'], strpos($_SESSION['syllabus_url'], "?") + 1).'&_eventId=search&_displayCount=1928&'.$query['query'], //http_build_query
		CURLOPT_HEADER => false,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_CONNECTTIMEOUT => 0,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]);
	//セッションデータ利用終了
	session_write_close();
	//cURLを実行
	$res = curl_exec($curl);
	//各種情報を取得
	$info = curl_getinfo($curl);
	//セッション切れをチェック
	if ($info['url'] === 'https://cup.chiba-u.jp/campusweb/campusportal.do' ||
		$info['url'] === 'https://cup.chiba-u.jp/campusweb/campussquare.do' ||
		mb_strpos($res, 'エラー') !== false) {
		//おそらく、セッション切れなので、セッションを取得してやり直し
		createSyllabusSession($link, $curl);
    return search($link, $curl, $query);
  }
	//PHPQueryのインスタンス生成
	$tbody = phpQuery::newDocument($res)->find('table > tbody');
	$subjects = [];
	//trの数=教科数
	$subjectCount = count($tbody->find('tr'));
	for($i=0; $i<$subjectCount; $i++){
		$tr = $tbody->find('tr:eq('.$i.')');
		$sub = [];
		$dataCount = count($tr->find('td')); //0-10
		//trタグ内の一つ一つの改行やスペースを除去
		for($j=1; $j<$dataCount; $j++)
			$sub[] = mb_convert_kana(preg_replace('/(?:\n|\r|\r\n|\s)/', '', $tr->find("td:eq($j)")->text()), 'asKV');
		//最後のtdにある初めのボタンを取得
		$button = $tr->find('td:eq('.($dataCount - 1).')')->find('input:eq(0)');
		//disabledがあるかどうかで日本語か英語かを判別。日本語優先
		if ($button->attr('disabled') == null) $sub[] = 'ja_JP';
		else $sub[] = 'en_US';
		//onclickを解析して「jikanwariShozokuCode」を入手
		$refer = $button->attr('onclick');
		$start = strpos($refer, ",") + 2;
		$end = strpos($refer, "'", $start);
		$sub[] = substr($refer, $start, $end - $start);
		//教科一覧に追加
		$subjects[] = $sub;
	}
  //セッションデータ利用を開始し、ログイン済みの時、検索履歴に追加
  session_start();
	if (isset($_SESSION['id']))
		maria_query($link, 'INSERT INTO delisys.history_search VALUES ("'.$_SESSION['id'].'", NOW(), "'.$query['query'].'", '.$subjectCount.');');
	//セッションデータ利用終了
	session_write_close();
	
	return [ 'status'=>'success', 'subjects'=>$subjects, 'url'=>$info['url'] ];
}

/**
 * シラバスコードで千葉大学シラバスの内容を取得
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURL
 * @param array $query POSTデータ
 * @param boolean $temp 一時的にキャッシュのみを行うかどうか
 * @return array 成功したかどうか、シラバス内容、元のURL、リダイレクト先のURLの連想配列
 */
function syllabus(mysqli $link, $curl, array $query, bool $temp = false): array {
	//シラバスコードを分解
	$data = explode('-', $query['code']);
  //セッションデータ利用を開始してURLやCookieが保管されているか確認、なければMySQLから取得
  session_start();
  if (!isset($_SESSION['syllabus_url']) || !isset($_SESSION['syllabus_cookie'])) {
		session_write_close();
		getSyllabusSession($link, $curl);
		session_start();
	}
	//キャッシュチェック、tempだけの時はMySQLにデータが既にあれば終了へ
	$result = maria_query($link, "SELECT * FROM delisys.syllabus WHERE nendo=$data[0] AND jikanwariShozokuCode='$data[1]' AND jikanwaricd='$data[2]' AND slocale='$data[3]';");
	if (mysqli_num_rows($result) === 1 && $temp) return;
	//千葉大学シラバスのURLを生成
	$url = "https://cup.chiba-u.jp/campusweb/campussquare.do?locale=ja_JP&_flowId=SYW3901100-flow&nendo=$data[0]&jikanwariShozokuCode=$data[1]&jikanwaricd=$data[2]&slocale=$data[3]";
	//cURLで千葉大学シラバスの情報を取得
	curl_setopt_array($curl, [
		CURLOPT_URL => $url,
		CURLOPT_REFERER => '',
		CURLOPT_COOKIE => $_SESSION['syllabus_cookie'],
		CURLOPT_POST => false,
		CURLOPT_POSTFIELDS => '',
		CURLOPT_HEADER => false,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_TIMEOUT => 5,
		CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]);
	//セッションデータ利用終了
	session_write_close();
	//cURLを実行
	$res = curl_exec($curl);
	//各種情報を取得
	$info = curl_getinfo($curl);
	//PHPQueryのインスタンス生成
	$doc = phpQuery::newDocument($res);
	//期限切れセッションチェック
	if ($info['url'] === 'https://cup.chiba-u.jp/campusweb/campusportal.do' ||
		$info['url'] === 'https://cup.chiba-u.jp/campusweb/campussquare.do' ||
		mb_strpos($res, 'エラー') !== false) {
    createSyllabusSession($link, $curl);
    return syllabus($link, $curl, $query);
  }
	//タブごとにインスタンスを生成
	$details1 = tableAnalysis($doc->find('#tabs-1'), "");
	$details2 = tableAnalysis($doc->find('#tabs-2'), "<br>");
	$details3 = tableAnalysisThird($doc->find('#tabs-3'));
	//正常にデータが取れている場合
	if (count($details1) > 0 && count($details2) > 0) {
		//mysqlに一時キャッシュ
		$summary = (isset($details2['概要']) ? $details2['概要'] : '');
		$summary = mb_strimwidth($summary, 0, 240, '…', 'UTF-8');
		$term = preg_replace("/(.*?)ターム/", 'T$1', substr($details1['履修年次/ターム'], strpos($details1['履修年次/ターム'], "/") + 1));
		$result = maria_query($link, "INSERT INTO delisys.syllabus VALUES ('$term', '$details1[曜日・時限]', '$details1[単位数]', '$details1[授業科目]', '$details1[担当教員]', '$summary', $data[0], '$data[1]', '$data[2]', '$data[3]');");
		//ログイン済みの場合、シラバス閲覧履歴に追加
    session_start();
		if (isset($_SESSION['id']))
      maria_query($link, "INSERT INTO delisys.history_syllabus VALUES ('$_SESSION[id]', NOW(), $data[0], '$data[1]', '$data[2]', '$data[3]');");
    session_write_close();
	}

	return [ 'status'=>((count($details1) > 0 && count($details2) > 0) ? 'success' : 'failed'), 'detail-1'=>$details1, 'detail-2'=>$details2, 'detail-3'=>$details3, 'redirect'=>$info['url'], 'original'=>$url ];
}

/**
 * 通常のCampusSquareのテーブルを解析
 *
 * @param phpQueryObject $tbody テーブル内のHTML
 * @param string $insert 行間に挟む文字列
 * @return array データを整形して連想配列にしたもの
 */
function tableAnalysis(phpQueryObject $tbody, string $insert) : array {
	$details = [];
	//trの数=データの種類(変動)
	$detailsCount = count($tbody->find('tr'));
	for($i=0; $i<$detailsCount; $i++){
		//名前の列は半角に整形し、スペースをなくす
		$name = trim(str_replace(' ', '', mb_convert_kana($tbody->find("tr:eq($i)")->find('th')->text(), 'asKV')));
		//詳細の列はHTMLを半角に整形し、<br>を\nにして、trimをかける
		$detail = trim(br2n(mb_convert_kana($tbody->find("tr:eq($i)")->find('td')->html(), 'asKV')));
		//どちらかの列が無名だったり、15の力は無視
		if ($name === '' || $detail === '' || mb_strpos($name, '15の力') !== false) continue;
		//$detailに改行がある場合、何もない行を消去する
		if (mb_strpos($detail, "\n") !== false){
			//改行で配列に分ける
			$detailList = explode("\n", $detail);
			//各要素にtrimをかける
			$detailList = array_map('trim', $detailList);
			//文字列が0の要素を取り除く
			$detailList = array_filter($detailList, 'strlen');
			//$insertを間に入れて文字列に戻す
			$detail = implode($insert, $detailList);
		}
		//英語表記があるときの区切り文字「/」の位置
		$posName = mb_strpos($name, "/");
		$posDetail = mb_strpos($detail, "/");
		if (mb_strpos($name, '履修年次') !== false){
			//履修年次/ターム/Students'Year… となるので、区切りの「/」の位置をずらす
			$posName = mb_strpos($name, "/", 6);
			$detail = str_replace('・', '･', $detail);
		}
		else if (mb_strpos($name, '曜日') !== false){
			//月 /Mon 　1… となるので、英語表記を消し、その間のスペースも除去
			$detail = str_replace(['/Sun', '/Mon', '/Tue', '/Wed', '/Thu', '/Fri', '/Sat', '/Oth'], '', $detail);
			$detail = str_replace(' ', '', $detail);
		}
		else if ($posDetail !== false && $insert === '')
			//詳細に英語表示があり、detail-1の時に、日本語のみにする
			$detail = trim(mb_substr($detail, 0, $posDetail));
		//名前は英語表示があるので、日本語のみに
		$name = mb_substr($name, 0, $posName);
		//\nをbrにして連想配列に
		$details[$name] = str_replace("\n", '<br>', $detail);
	}
	return $details;
}

/**
 * 授業計画詳細情報のCampusSquareのテーブルを解析
 *
 * @param phpQueryObject $tbody テーブル内のHTML
 * @return データを整形して連想配列にしたもの
 */
function tableAnalysisThird(phpQueryObject $tbody) {
	$details = [];
	//trの数=行頭を含む行の数
	$detailsCount = count($tbody->find('tr'));
	for($i=0; $i<$detailsCount; $i++){
		$tr = $tbody->find("tr:eq($i)");
		$td = [];
		//tdの数=列の数
		$tdCount = count($tr->find('td'));
		//「該当するデータはありません」のときはfalseを返す
		if ($detailsCount === 1 && $tdCount === 0) return false;
		//1行目はただのインデックスなので無視
		else if ($tdCount === 0) continue;
		//1つずつ連想配列に
		for ($j=0; $j<$tdCount; $j++)
			$td[] = trim(mb_convert_kana($tbody->find("tr:eq($i)")->find("td:eq($j)")->text(), 'asKV'));
		//「1. 第1回 名前\n内容: 内容内容\n備考: 備考備考」の形に整形
		$detail = trim(("$td[0]. $td[1] $td[2]".($td[3] === '' ? '' : "\n内容: $td[3]").($td[4] === '' ? '' : "\n備考: $td[4]")));
		//\nを<br>にして連想配列に
		$details[] = str_replace("\n", '<br>', $detail);
	}
	return $details;
}

/**
 * 学生ポータルへログインしてセッション取得、MySQLに保存
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURLのインスタンス
 * @return void
 */
function createPortalSession(mysqli $link, $curl){
	//cURLでログイン
	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://cup.chiba-u.jp/campusweb/campusportal.do',
		CURLOPT_REFERER => '',
		CURLOPT_COOKIE => '',
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => 'wfId=nwf_PTW0000002_login&userName=19T1688A&password=moyrv%3Fd_!6t5&locale=ja_JP&undefined=&action=rwf&tabId=home&page=',//&rwfHash=86c8c93c52abb4ae783c237d364dd203',
		CURLOPT_HEADER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_TIMEOUT => 5,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
		CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36']);
	$res = curl_exec($curl);
	$info = curl_getinfo($curl);
	//Headerを取得
  $header = substr($res, 0, $info['header_size']);
  //Cookieのみ抽出
	$c = [];
	$cookieCount = substr_count($header, 'Set-Cookie: ');//substr_count($header, 'Set-Cookie:');
	$start = 0;
	for ($i = 0; $i < $cookieCount; $i++){
		$start = strpos($header, 'Set-Cookie: ', $start + 1) + 12;
		$end = strpos($header, ';', $start);
		$c[] = substr($header, $start, $end - $start);
	}
	$cookie = implode('; ', $c);
	//MySQLに一時的に保存
	maria_query($link, "UPDATE delisys.temp SET data='$cookie' WHERE name='portal_cookie';");
	//セッションに保管
	session_start();
  $_SESSION['portal_cookie'] = $cookie;
  session_write_close();
}

/**
 * MySQLから学生ポータルのCookieを取得して、セッションに保存
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURLのインスタンス
 * @return void
 */
function getPortalSession(mysqli $link, $curl){
	//SQLを実行
	$resultCookie = maria_query($link, "SELECT data FROM delisys.temp WHERE name='portal_cookie';");
	if ($resultCookie){
		session_start();
		$_SESSION['portal_cookie'] = mysqli_fetch_assoc($resultCookie)['data'];
		session_write_close();
	}
	else
		//MySQLになければ、セッション取得
		createPortalSession($link, $curl);
}


/**
 * 休講補講参照にアクセスしてURL取得、MySQLに保存
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURLのインスタンス
 * @return void
 */
function createSubjectChangeSession(mysqli $link, $curl){
  //セッションデータ利用を開始してURLやCookieが保管されているか確認、なければMySQLから取得
  session_start();
  if (!isset($_SESSION['portal_cookie'])) {
		session_write_close();
		getPortalSession($link, $curl);
		session_start();
	}
	//cURLで休講補講参照にアクセス、リダイレクト禁止
	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://cup.chiba-u.jp/campusweb/campussquare.do?_flowId=KHW0001100-flow',
		CURLOPT_REFERER => '',
		CURLOPT_COOKIE => $_SESSION['portal_cookie'],
		CURLOPT_POST => false,
		CURLOPT_POSTFIELDS => '',
		CURLOPT_HEADER => true,
		CURLOPT_FOLLOWLOCATION => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_TIMEOUT => 5,
		CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
	]);
	//セッションデータ利用終了
	session_write_close();
	//cURLを実行
	$res = curl_exec($curl);
	//各種情報を取得
	$info = curl_getinfo($curl);
	//期限切れセッションチェック
	if ($info['url'] === 'https://cup.chiba-u.jp/campusweb/campusportal.do' ||
		$info['url'] === 'https://cup.chiba-u.jp/campusweb/campussquare.do' ||
		mb_strpos($res, 'エラー') !== false) {
    createPortalSession($link, $curl);
    return createSubjectChangeSession($link, $curl);
  }
	//MySQLに一時的に保存
	maria_query($link, "UPDATE delisys.temp SET data='$info[redirect_url]' WHERE name='subject_change_url';");
	//セッションに保管
	session_start();
  $_SESSION['subject_change_url'] = $info['redirect_url'];
  session_write_close();
}

/**
 * MySQLから休講補講参照のURLを取得して、セッションに保存
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURLのインスタンス
 * @return void
 */
function getSubjectChangeSession(mysqli $link, $curl){
	//SQLを実行
	$resultURL = maria_query($link, "SELECT data FROM delisys.temp WHERE name='subject_change_url';");
	if ($resultURL){
		session_start();
		$_SESSION['subject_change_url'] = mysqli_fetch_assoc($resultURL)['data'];
		session_write_close();
	}
	else
		//MySQLになければ、セッション取得
		createSubjectChangeSession($link, $curl);
}

/**
 * 千葉大学シラバスを検索し、教科一覧を返す→廃止予定
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURLのインスタンス
 * @param array $query POSTデータ
 * @return array 成功したかどうか、教科一覧の連想配列
 */
function getSubjectChange(mysqli $link, $curl, array $query): array {
	//セッションデータ利用を開始してURLやCookieが保管されているか確認、なければMySQLから取得
	session_start();
	if (!isset($_SESSION['portal_cookie']) || !isset($_SESSION['subject_change_url'])) {
		session_write_close();
		getPortalSession($link, $curl);
		getSubjectChangeSession($link, $curl);
		session_start();
	}
	//cURLで千葉大学シラバスの検索結果を取得
	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://cup.chiba-u.jp/campusweb/campussquare.do',
		CURLOPT_REFERER => $_SESSION['subject_change_url'],
		CURLOPT_COOKIE => $_SESSION['portal_cookie'],
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => substr($_SESSION['subject_change_url'], strpos($_SESSION['subject_change_url'], "?") + 1).//2019%2F01%2F01
			"&dispType=list&dispData=chg&_eventId_search=+%E8%A1%A8+%E7%A4%BA+%E3%81%99+%E3%82%8B&startDay=$query[date]&startDay_year=$query[year]&startDay_month=$query[month]&startDay_day=$query[day]&endDay=$query[date]&endDay_year=$query[year]&endDay_month=$query[month]&endDay_day=$query[day]&_rishuchuFlg=1&kyokanCode=&shozokuCode=",
		CURLOPT_HEADER => false,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_CONNECTTIMEOUT => 0,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
	]);
	//セッションデータ利用終了
	session_write_close();
	//cURLを実行
	$res = curl_exec($curl);
	//各種情報を取得
	$info = curl_getinfo($curl);
	//セッション切れをチェック
	if ($info['url'] === 'https://cup.chiba-u.jp/campusweb/campusportal.do' ||
		$info['url'] === 'https://cup.chiba-u.jp/campusweb/campussquare.do' ||
		mb_strpos($res, 'エラー') !== false) {
		//おそらく、セッション切れなので、セッションを取得してやり直し
		createPortalSession($link, $curl);
		createSubjectChangeSession($link, $curl);
		return getSubjectChange($link, $curl, $query);
	}
	//PHPQueryのインスタンス生成
	$tbody = phpQuery::newDocument($res)->find('.normal');
	$subjects = [];
	//trの数=教科数+1
	$subjectCount = count($tbody->find('tr'));
	for ($i = 0; $i < $subjectCount; $i++) {
		$tr = $tbody->find("tr:eq($i)");
		$sub = [];
		$dataCount = count($tr->find('td'));
		//初めの行は省く(全てthなので)
		if ($dataCount === 0) continue;
		//trタグ内の一つ一つの改行やスペースを除去
		foreach ([0,1] as $j)
			$sub[] = mb_convert_kana(preg_replace('/(?:\n|\r|\r\n|\s)/', '', $tr->find("td:eq($j)")->text()), 'asKV');
		//onclickを解析して詳細URLを入手
		//return jumpInfo('&_eventId_refer=_eventId_refer&taishoymd=20190711&jigen=2&nendo=2019&jikanwariShozokucd=E1&jikanwaricd=E1P543001');
		$refer = $tr->find('a')->attr('onclick');
		$start = strpos($refer, "('") + 2;
		$end = strpos($refer, "')");
		$detailUrl = $info['url'].substr($refer, $start, $end - $start);
		getSubjectChangeDetail($curl, $detailUrl, $sub);
		//教科一覧に追加
		$subjects[] = $sub;
	}

	return [ 'status'=>'success', 'subjects'=>$subjects, 'url'=>$info['url'] ];
}

function getSubjectChangeDetail($curl, string $url, array &$sub) {
	//cURLで千葉大学シラバスの検索結果を取得
	curl_setopt_array($curl, [
		CURLOPT_URL => $url,
		CURLOPT_REFERER => '',
		CURLOPT_COOKIE => $_SESSION['portal_cookie'],
		CURLOPT_POST => false,
		CURLOPT_POSTFIELDS => '',
		CURLOPT_HEADER => false,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_CONNECTTIMEOUT => 0,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
	]);
	//cURLを実行
	$res = curl_exec($curl);
	//PHPQueryのインスタンス生成
	$tbody = phpQuery::newDocument($res)->find('.normal');
	//trの数=データ数
	$subjectCount = count($tbody->find('tr'));
	for ($i = 0; $i < $subjectCount; $i++) {
		$tr = $tbody->find("tr:eq($i)");
		$sub[] = mb_convert_kana(preg_replace('/(?:\n|\r|\r\n|\s)/', '', $tr->find('td:eq(1)')->text()), 'asKV');
	}
}


/**
 * みんなのキャンパスへログインしてセッション取得、MySQLに保存
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURLのインスタンス
 * @return void
 */
function createMincamSession(mysqli $link, $curl){
	//cURLでログイン
	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://grp03.id.rakuten.co.jp/rms/nid/vc',
		CURLOPT_REFERER => '',
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
	//Headerを取得
  $header = substr($res, 0, $info['header_size']);
  //Cookieのみ抽出、"pitto"のみでOK
	$c = [];
	$cookieCount = substr_count($header, 'Set-Cookie: pitto');//substr_count($header, 'Set-Cookie:');
	$start = 0;
	for ($i = 0; $i < $cookieCount; $i++){
		$start = strpos($header, 'Set-Cookie: pitto', $start + 1) + 12;
		$end = strpos($header, ';', $start);
		$c[] = substr($header, $start, $end - $start);
	}
	$cookie = implode('; ', $c);
	//MySQLに一時的に保存
	maria_query($link, "UPDATE delisys.temp SET data='$cookie' WHERE name='mincam_cookie';");
	//セッションに保管
	session_start();
  $_SESSION['mincam_cookie'] = $cookie;
  session_write_close();
}

/**
 * MySQLからみんなのキャンパスのCookieを取得して、セッションに保存
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURLのインスタンス
 * @return void
 */
function getMincamSession(mysqli $link, $curl){
	//SQLを実行
	$resultCookie = maria_query($link, "SELECT data FROM delisys.temp WHERE name='mincam_cookie';");
	if ($resultCookie){
		session_start();
		$_SESSION['mincam_cookie'] = mysqli_fetch_assoc($resultCookie)['data'];
		session_write_close();
	}
	else
		//MySQLになければ、セッション取得
		createMincamSession($link, $curl);
}

/**
 * みんなのキャンパスからcURLでデータを取得
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURLのインスタンス
 * @param int $page ページ番号
 * @return array 投稿別になった「みんなのキャンパス」データの配列
 */
function getMincamData(mysqli $link, $curl, int $page): array {
	//セッションデータ利用開始
	session_start();
	//クッキーがセッションにあるかチェック
  if (!isset($_SESSION['mincam_cookie'])) {
		session_write_close();
		getMincamSession($link, $curl);
		session_start();
	}
	//cURLでpageの投稿を取得
	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://campus.nikki.ne.jp/?module=lesson&action=index&univ=%C0%E9%CD%D5%C2%E7%B3%D8&lname=&fname=&lesson_name=&faculty1=&id=&order=1&page='.$page,
		CURLOPT_REFERER => '',
		CURLOPT_COOKIE => $_SESSION['mincam_cookie'],
		CURLOPT_POST => false,
		CURLOPT_POSTFIELDS => '',
		CURLOPT_HEADER => false,
		CURLOPT_FOLLOWLOCATION => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_TIMEOUT => 5,
		CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]);
	//セッションデータ利用終了
	session_write_close();
	//cURL実行
	$rawRes = curl_exec($curl);
	//文字コードをEUC-JPからUTF-8に
  $res = str_replace('euc-jp', 'utf-8', mb_convert_encoding($rawRes, 'UTF-8', 'eucjp-win'));
	//PHPQueryのインスタンス生成
	$doc = phpQuery::newDocument($res);

  //ログイン済みかどうかをチェック
	if (count($doc->find('.login')) > 0){
    createMincamSession($link, $curl);
    return getMincamData($link, $curl, $page);
  }

	$data = [];
	//.listの数=投稿数
	$count = count($doc->find('#apartValue .list'));
	//投稿ごとにデータを整形、dataに追加
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

/**
 * 投稿ごとに配列になったデータを読み、MySQLに挿入
 *
 * @param mysqli $link MySQLのインスタンス
 * @param array $data 投稿別になった「みんなのキャンパス」データの配列
 * @return boolean SQLが成功したかどうか
 */
function addMincamData(mysqli $link, array $data){
	$query = '';
	foreach ($data as $d)
    $query += "INSERT IGNORE INTO delisys.mincam VALUES ('$d[title]', '$d[university]', '$d[faculty]', '$d[department]', ".
      "'$d[lastName]', '$d[firstName]', $d[id], $d[richPoint], $d[easyPoint], '$d[creditUniversity]', '$d[creditName]', ".
	    "'$d[postDate]', '$d[attend]', '$d[textbook]', '$d[middleExam]', '$d[finalExam]', '$d[bringIn]', '".mysqli_real_escape_string($link, $d['message'])."');";
	//成功か失敗かの真偽値を返す
	return maria_query($link, $query);
}

/**
 * みんなのキャンパスのMySQL(mincam)を検索
 *
 * @param mysqli $link MySQLのインスタンス
 * @param array $query POSTデータ
 * @return array 成功したかどうか、みんなのキャンパスのデータ、配列化したクエリの連想配列
 */
function mincam(mysqli $link, array $query): array {
	//クエリをパースして連想配列に
	parse_str(urldecode($query['query']), $q);
	//WHEREの条件となる配列
	$where = [];
	//titleはスペース区切りでAND検索
  if (isset($q['title']) && $q['title'] !== ''){
    $title = explode(' ', $q['title']);
    foreach ($title as $t)
      $where[] = "title LIKE '%$t%'";
	}
	//teacherは教師名がカンマ区切り、姓名がスペース区切りを想定
  if (isset($q['teacher']) && $q['teacher'] !== ''){
		$teacher = explode(',', $q['teacher'])[0];
		//teacher_only_lastがtrueの時は姓のみ使用し、マッチ率を上げる
		if (isset($q['teacher_only_last']) && $q['teacher_only_last'] === 'true')
			$teacher = explode(' ', $teacher)[0];
		//スペースがあるとき、姓名と考えて完全一致で検索
		if (mb_strpos($teacher, ' ') !== false){
      $n = explode(' ', $teacher);
      $where[] = "lastName = '$n[0]'";
      $where[] = "firstName = '$n[1]'";
		}
		//スペースがないときは姓か名のどちらかに完全一致で検索
    else
      $where[] = "(lastName = '$teacher' OR firstName = '$teacher')";
	}
	//messageはスペース区切りでAND検索
  if (isset($q['message']) && $q['message'] !== '') {
		$message = explode(' ', $q['message']);
		foreach ($message as $m)
			$where[] = "message LIKE '%$m%'";
	}
	//SQL実行
  $result = maria_query($link, 'SELECT * FROM delisys.mincam WHERE '.implode(' AND ', $where).' ORDER BY id DESC;');
	$subjects = [];
	//データベースをそのまま配列に格納
	if ($result) while ($row = mysqli_fetch_assoc($result)) $subjects[] = $row;
	
	return [ 'status'=> $result ? 'success' : 'failed', 'subjects'=>$subjects, 'query'=>$q ];
}

/**
 * シラバスに付随するメモデータをMySQLから取得
 *
 * @param mysqli $link MySQLのインスタンス
 * @param array $query POSTデータ
 * @return array 成功したかどうか、テキスト、最終更新日の連想配列
 */
function memoGet(mysqli $link, array $query): array {
	//シラバスコードを分解
	$data = explode('-', $query['code']);
	//あらかじめ素のデータを用意
  $result = [ 'text'=>'', 'lastUpdated'=>null ];
	//セッションデータ利用開始
	session_start();
	//ログイン済みの時はメモを取得
	if (isset($_SESSION['id'])){
		$memo = mysqli_fetch_assoc(maria_query($link, 'SELECT `text`, `lastUpdated` FROM delisys.memo WHERE id="'.$_SESSION['id'].'" and nendo='.$data[0].' and jikanwariShozokuCode="'.$data[1].'" and jikanwaricd="'.$data[2].'";'));
    if ($memo) $result = $memo;
	}
	//セッションデータ利用終了
	session_write_close();
	//失敗することがないため、常に成功を返す
	$result['status'] = 'success';
	return $result;
}

/**
 * シラバスに付随するメモデータをMySQLに保存
 *
 * @param mysqli $link MySQLのインスタンス
 * @param array $query POSTデータ
 * @return array 成功したかどうかの連想配列
 */
function memoSave(mysqli $link, array $query): array {
	//シラバスコードを分解
	$data = explode('-', $query['code']);
	//あらかじめ$resultはfalseで定義しておく
	$result = false;
	//セッションデータ利用開始
	session_start();
	//ログイン済みの時
	if (isset($_SESSION['id'])){
		//テキストがない場合は、レコードを消去
    if ($query['text'] === '')
      $result = maria_query($link, 'DELETE FROM delisys.memo WHERE id="'.$_SESSION['id'].'" and nendo='.$data[0].' and jikanwariShozokuCode="'.$data[1].'" and jikanwaricd="'.$data[2].'";');
		//テキストがある場合はまずSELECTしてレコードの存在をチェックし、INSERTかUPDATEかを使い分ける
		else {
      $r = mysqli_fetch_assoc(maria_query($link, 'SELECT `text`, `lastUpdated` FROM delisys.memo WHERE id="'.$_SESSION['id'].'" and nendo='.$data[0].' and jikanwariShozokuCode="'.$data[1].'" and jikanwaricd="'.$data[2].'";'));
      if ($r) $result = maria_query($link, 'UPDATE delisys.memo SET text="'.mysqli_real_escape_string($link, str_replace("\n", '<br>', $query['text'])).'", lastUpdated=NOW() WHERE id="'.$_SESSION['id'].'" and nendo='.$data[0].' and jikanwariShozokuCode="'.$data[1].'" and jikanwaricd="'.$data[2].'";');
      else $result = maria_query($link, 'INSERT INTO delisys.memo VALUES ("'.$_SESSION['id'].'", '.$data[0].', "'.$data[1].'", "'.$data[2].'", "'.$data[3].'", "'.mysqli_real_escape_string($link, str_replace("\n", '<br>', $query['text'])).'", NOW());');
    }
	}
	//セッションデータ利用終了
	session_write_close();
	return [ 'status'=>$result ? 'success' : 'failed' ];
}

/**
 * シラバスに付随するコメントをMySQLから取得
 *
 * @param mysqli $link MySQLのインスタンス
 * @param array $query POSTデータ
 * @return array 成功したかどうか、コメントデータ、次のインデックスの連想配列
 */
function commentGet(mysqli $link, array $query): array {
	//シラバスコードを分解
	$data = explode('-', $query['code']);
	//標準の取得数は30
	$amount = 30;
	//reloadがtrueのときは再取得
	if ($query['reload'] === 'true'){
		//数は今まで読み込んだ数全て
		$amount = $query['index'] + 1;
		//始まりのインデックスは初めの0に
		$query['index'] = 0;
	}
	//前年度を取得するかどうかで挙動を変える(未実装)
	if ($query['all_nendo'] === 'true')
		$result = maria_query($link, "SELECT num, name, text, datetime, nendo FROM delisys.comment WHERE jikanwariShozokuCode='$data[1]' AND jikanwaricd='$data[2]' ORDER BY datetime DESC LIMIT $amount OFFSET $query[index];");
	else
		$result = maria_query($link, "SELECT num, name, text, datetime FROM delisys.comment WHERE nendo=$data[0] AND jikanwariShozokuCode='$data[1]' AND jikanwaricd='$data[2]' ORDER BY datetime DESC LIMIT $amount OFFSET $query[index];");
	$comments = [];
	//正しくクエリが実行されたとき
	if ($result)
		while ($row = mysqli_fetch_assoc($result)){
			$date = strtotime($row['datetime']);
			//日時は2000/05/20(土) 09:27:00の形式に
			$row['datetime'] = date('Y/m/d(', $date).['日', '月', '火', '水', '木', '金', '土'][date('w', $date)].date(') H:i:s', $date);
			//名前とテキストはインジェクションに注意してエスケープしておく
			$row['name'] = htmlspecialchars($row['name'], ENT_QUOTES|ENT_HTML5);
			$row['text'] = htmlspecialchars($row['text'], ENT_QUOTES|ENT_HTML5);
			//連想配列として追加
			$comments[] = $row;
		}
	return [ 'status'=>$result ? 'success' : 'failed', 'comment'=>$comments, 'index'=>($query['index'] + count($comments)) ];
}

/**
 * シラバスに付随するコメントを投稿、MySQLに保存
 *
 * @param mysqli $link MySQLのインスタンス
 * @param array $query POSTデータ
 * @return array commentGet()でコメント一覧を再読み込みしたデータ
 */
function commentPost(mysqli $link, array $query) : array {
	//シラバスコードを分解
	$data = explode('-', $query['code']);
	//コメントの最大番号を取得し、今回の番号を決める
	$result = maria_query($link, "SELECT MAX(num) FROM delisys.comment WHERE nendo=$data[0] AND jikanwariShozokuCode='$data[1]' AND jikanwaricd='$data[2]';");
	//コメントが既にあったときはその最大値に+1
	if ($result) $num = intval(mysqli_fetch_assoc($result)['MAX(num)']) + 1;
	//コメントがまだないときは1
	else $num = 1;
	//セッションデータ利用開始
	session_start();
	//ログイン済みの時、コメントを投稿
	if (isset($_SESSION['id']))
    maria_query($link, "INSERT INTO delisys.comment VALUES ('$_SESSION[id]', $num, '".(trim($query['name']) === '' ? '名無しの千葉大生' : trim($query['name']))."', '".
			mysqli_real_escape_string($link, $query['text'])."', NOW(), '$_SERVER[REMOTE_ADDR]', $data[0], '$data[1]', '$data[2]', '$data[3]');");
	//セッションデータ利用終了
	session_write_close();
	//commentGetを実行し、コメント一覧を再読み込み
	return commentGet($link, $query);
}

/**
 * お気に入りのステータスを変更、MySQLに変更適用
 *
 * @param mysqli $link MySQLのインスタンス
 * @param array $query POSTデータ
 * @return array getFavoriteById()でお気に入り一覧を再読み込みしたデータ
 */
function changeFavorite(mysqli $link, array $query) : array {
	//シラバスコードを分解
	$data = explode('-', $query['code']);
	//あらかじめ$resultはfalseで定義しておく
	$result = false;
	//セッションデータ利用開始
	session_start();
	//ログイン済みの時
	if (isset($_SESSION['id'])){
		//追加の時はINSERT文を実行
		if ($query['bool'] === 'true')
			$result = maria_query($link, "INSERT INTO delisys.favorite VALUES ('$_SESSION[id]', $data[0], '$data[1]', '$data[2]', '$data[3]');");
		//削除の時はDELETE文を実行
		else
			$result = maria_query($link, "DELETE FROM delisys.favorite WHERE id='$_SESSION[id]' AND nendo=$data[0] AND jikanwariShozokuCode='$data[1]' AND jikanwaricd='$data[2]' AND slocale='$data[3]';");
	}
	//セッションデータ利用終了
	session_write_close();
	//getFavoriteByIdを実行し、お気に入り一覧を再読み込み
	return getFavoriteById($link, $result);
}

/**
 * IDに紐づくお気に入り一覧をMySQLから取得
 *
 * @param mysqli $link MySQLのインスタンス
 * @param boolean $changeFavorite お気に入りのステータスを変更した時の結果
 * @return array 成功したかどうか、お気に入りデータ、変更が適用できたかどうかの連想配列
 */
function getFavoriteById(mysqli $link, bool $changeFavorite = null) : array {
	//お気に入り登録したシラバスコードのみ入る配列
	$list = [];
	//お気に入り登録したシラバスの詳細も入る連想配列
	$data = [];
	//あらかじめ$resultはfalseで定義しておく
	$result = false;
	//セッションデータ利用開始
	session_start();
	//ログイン済みの時
	if (isset($_SESSION['id'])){
		//IDに紐づくお気に入り一覧を取得
		$result = maria_query($link, "SELECT * FROM delisys.favorite WHERE id='$_SESSION[id]';");
		//セッションデータ利用終了
		session_write_close();
		//正しくクエリが実行されたとき
    if ($result)
			while ($row = mysqli_fetch_assoc($result)){
				//シラバスコードを生成、配列に追加
				$list[] = $row['nendo'].'-'.$row['jikanwariShozokuCode'].'-'.$row['jikanwaricd'].'-'.$row['slocale'];
				//キャッシュされたシラバスデータを取得し、連想配列に追加
				$data[] = mysqli_fetch_assoc(maria_query($link, 'SELECT `nendo`, `term`, `time`, `credit`, `name`, `teacher`, `summary` FROM delisys.syllabus WHERE nendo='.$row['nendo'].' and jikanwariShozokuCode="'.$row['jikanwariShozokuCode'].'" and jikanwaricd="'.$row['jikanwaricd'].'" and slocale="'.$row['slocale'].'";'));
			}
	}
	//ログインしていないとき、すぐにセッションデータ利用終了
  else session_write_close();
	return [ 'status'=>$result ? 'success' : 'failed', 'list'=>$list, 'data'=>$data, 'changeFavorite'=>$changeFavorite ];
}

/**
 * 共有リンクなどでシラバスのキャッシュをMySQLから取得
 *
 * @param mysqli $link MySQLのインスタンス
 * @param string $code シラバスコード
 * @return array 成功したかどうか、シラバスのキャッシュデータの連想配列
 */
function getSyllabusTemp(mysqli $link, string $code): array {
	//シラバスコードを分解
	$data = explode('-', $code);
	//シラバスのキャッシュを取得
	$result = maria_query($link, "SELECT `nendo`, `term`, `time`, `credit`, `name`, `teacher`, `summary` FROM delisys.syllabus WHERE nendo=$data[0] AND jikanwariShozokuCode='$data[1]' AND jikanwaricd='$data[2]' AND slocale='$data[3]';");
	//成功し、結果の行数が1行だったとき
	if ($result && mysqli_num_rows($result) === 1) return [ 'status'=>'success', 'data'=>mysqli_fetch_assoc($result) ];
	//失敗したときはstatus:failedとして返却
	else return [ 'status'=>'failed' ];
}

//1コマ 90分  XXXX-XX-XXTXX:XX:XX+09:00
//曜日と数字の対応表  Day Of the Week
define('DOW', [ '日'=>0, '月'=>1, '火'=>2, '水'=>3, '木'=>4, '金'=>5, '土'=>6 ]);
//曜日と数字の対応表、英語版
define('DOWEN', [ '日'=>'SU', '月'=>'MO', '火'=>'TU', '水'=>'WE', '木'=>'TH', '金'=>'FR', '土'=>'SA' ]);
//授業の開始時間と時限の対応表
define('StartTime', [ 1=>'08:50', 2=>'10:30', 3=>'12:50', 4=>'14:30', 5=>'16:10', 6=>'17:50', 7=>'19:30' ]);
//授業の終了時間と時限の対応表
define('EndTime', [ 1=>'10:20', 2=>'12:00', 3=>'14:20', 4=>'16:00', 5=>'17:40', 6=>'19:20', 7=>'21:00' ]);
//各タームの始まる日付と年度、タームとの対応表
define('StartTerm', [ '2019'=>[ 1=>'2019-04-08', 2=>'2019-06-11', 3=>'2019-08-07', 4=>'2019-10-01', 5=>'2019-12-03', 6=>'2020-02-06', 7=>'2020-04-01' ] ]);
//各タームの終わる日付と年度、タームとの対応表
define('EndTerm', [ '2019'=>[ 1=>'2019-06-10', 2=>'2019-08-06', 3=>'2019-09-30', 4=>'2019-12-02', 5=>'2020-02-05', 6=>'2019-03-31' ] ]);
//ターム内で休日と示されている日一覧
define('Holiday', [ '2019-04-29', '2019-04-30', '2019-05-01', '2019-05-02', '2019-05-03', '2019-05-06', '2019-07-15', '2019-08-12' ]);
//月曜休日が続いたなどで振替が起きるときの曜日と日付の対応表
define('RDATE', [ '月'=>[ '20190716' ] ]);

//base32HexDecodeを使うため、use宣言
use \ParagonIE\ConstantTime\Encoding;

/**
 * 全ての登録済みのGoogleカレンダーのイベントを取得
 *
 * @param $client Google Clientのインスタンス
 * @return array 成功したかどうか、イベント一覧の連想配列
 */
function getAllCalendarSubjects($client): array {
	//ログインしていない時は、status:expiredと返す
	if (!$client) return [ 'status'=>'expired', 'result'=>null ];
	//カレンダーサービスのインスタンスを生成
	$service = new Google_Service_Calendar($client);
	//「Created By delisys」が入ったイベント一覧を取得
	$result = $service->events->listEvents('primary', [
		'q' => 'Created By delisys', 'maxResults' => 2500
	]);
	$data = [];
  foreach ($result->getItems() as $event){
		//_が入った個々のイベントは無視する
		if (strpos($event->getId(), '_') !== false) continue;
		//idをbase32hexからデコードして&区切りで配列に戻す
    //UNIX時間&シラバスコード&ターム&曜時&単位数&★ターム&★曜日
		$id = explode('&', Encoding::base32HexDecode($event->getId()));
		//$idの要素数が6以上の時は連想配列に追加
    if (count($id) >= 6)
  		$data[] = [ 'id'=>$event->getId(), 'add'=>date(DateTime::ATOM, $id[0]), 'nendo'=>explode('-', $id[1])[0], 'code'=>$id[1], 'term'=>$id[2], 'time'=>$id[3], 'credit'=>intval($id[4]), 'index'=>[ 'term'=>$id[5], 'time'=>$id[6] ],
        'name'=>$event['summary'], 'location'=>$event['location'], 'start'=>$event['start']['dateTime'], 'end'=>$event['end']['dateTime'], 'notification'=>(count($event['reminders']['overrides']) > 0) ];
		//そうでない場合はなかったものとして処理 !!!!!!!!!!
		else
      continue;
  		//$data[] = [ 'id'=>$event->getId(), 'name'=>$event['summary'], 'location'=>$event['location'], 'start'=>$event['start']['dateTime'], 'end'=>$event['end']['dateTime'], 'notification'=>(count($event['reminders']['overrides']) > 0) ];
	}
	return [ 'status'=>'success', 'result'=>$data ];
}

/**
 * 今週のGoogleカレンダーのイベントを取得
 *
 * @param $client Google Clientのインスタンス
 * @return array 成功したかどうか、イベント一覧の連想配列
 */
function getWeekCalendarSubjects($client): array {
	//ログインしていない時は、status:expiredと返す
	if (!$client) return [ 'status'=>'expired', 'result'=>null ];
	//カレンダーサービスのインスタンスを生成
	$service = new Google_Service_Calendar($client);
	//月曜日始まり、日曜日終わりへの対応
	if (date('w') === '0'){
		//日曜日のときは1週ずらして考える
		$min = date(DateTime::ATOM, strtotime('sunday this week'));
		$max = date(DateTime::ATOM, strtotime('sunday next week'));
	}
	else{
		//月曜から土曜日は通常通り考える
		$min = date(DateTime::ATOM, strtotime('sunday previous week'));
		$max = date(DateTime::ATOM, strtotime('sunday this week'));
	}
	//「Created By delisys」が入ったイベント一覧をSingleEvent:trueとして今週のみ取得
	$result = $service->events->listEvents('primary', [
		'q'=>'Created By delisys', 'maxResults'=>2500, 'singleEvents'=>true, 'timeMin'=>$min, 'timeMax'=>$max
	]);
	$data = [];
  foreach ($result->getItems() as $event){
		//_があるときのみそれより前の文字列をidとしてbase32hexからデコードして&区切りで配列に戻す
		$id = explode('&', Encoding::base32HexDecode((strpos($event['id'], '_') !== false ? explode('_', $event['id'])[0] : $event['id'])));
		//$idの要素数が6以上の時は連想配列に追加
		if (count($id) >= 6)
			$data[] = [ 'add'=>date(DateTime::ATOM, $id[0]), 'code'=>$id[1], 'term'=>$id[2], 'time'=>$id[3], 'credit'=>intval($id[4]), 'index'=>[ 'term'=>$id[5], 'time'=>$id[6] ],
				'name'=>$event['summary'], 'location'=>$event['location'], 'start'=>$event['start']['dateTime'], 'end'=>$event['end']['dateTime']  ];
	}
	return [ 'status'=>'success', 'result'=>$data ];
}

/**
 * Googleカレンダーにイベントを追加
 *
 * @param $client Google Clientのインスタンス
 * @param mysqli $link MySQLのインスタンス
 * @param array $data シラバスの詳細データ
 * @return array 成功したかどうか、各イベントの返り値の連想配列
 */
function addCalendar($client, mysqli $link, array $data): array {
	//data: term (startDate endDate) time (startTime endTime) code name location description

	//ログインしていない時は、status:expiredと返す
	if (!$client) return [ 'status'=>'expired', 'result'=>[] ];
	//カレンダーサービスのインスタンスを生成
	$service = new Google_Service_Calendar($client);
	//シラバスコードを分解
	$nendo = explode('-', $data['code'])[0];

	//タームの演算
	//$data['term'] ex)T1 T1-2 T1･3 T1集
	//$terms = [ [ 'start'=>'2019-04-08', 'end'=>'2019-06-10' ] ];
	$terms = [];
	//集中→日程指定してもらった日付を流用(未実装)
	if (mb_strpos($data['term'], '集') !== false)
		$terms[] = [ 'start'=>$data['startDate'], 'end'=>$data['endDate'] ];
	//通年→1~6ターム？として処理
	else if (mb_strpos($data['term'], '通') !== false)
		$terms[] = [ 'start'=>StartTerm[$nendo][1], 'end'=>EndTerm[$nendo][6] ];
	//独立した複数タームにまたがる→イベントを分ける
	else if (strpos($data['term'], '･') !== false){
		//Tを除いて「･」区切りでタームを抽出し数字を配列に
		$termList = explode('･', substr($data['term'], 1)); //[1,3]
		foreach ($termList as $term)
			$terms[] = [ 'start'=>StartTerm[$nendo][intval($term)], 'end'=>EndTerm[$nendo][(intval($term))] ];
	}
	//連続した複数タームにまたがる→イベントをつなげる
	else if (strpos($data['term'], '-') !== false) {
		//Tを除いて「-」区切りでタームを抽出し数字を配列に
		$termList = explode('-', substr($data['term'], 1)); //[1,3]
		$terms[] = [ 'start'=>StartTerm[$nendo][intval($termList[0])], 'end'=>EndTerm[$nendo][(intval($termList[1]))] ];
	}
	//通常の単独ターム
	else {
		//Tを除いて数字に
		$term = intval(substr($data['term'], 1));
		$terms[] = [ 'start'=>StartTerm[$nendo][$term], 'end'=>EndTerm[$nendo][$term] ];
	}

	//開始時刻の演算
	//$data['time'] ex)月1 月1,月2
	//$times = [ [  ] ];
	$times = []; //[day:月,start:8:50,end:10:20]
	//終日イベントに、毎日のみ(禁止or未実装)
	if ($data['allDay'] === 'true' || $data['time'] === '通')
		$times[] = [ 'day'=>'all', 'hour'=>null ];
	//時間指定を利用、毎日のみ(禁止or未実装)
	else if ($data['time'] === '他')
		$times[] = [ 'day'=>'time', 'hour'=>null, 'start'=>$data['startTime'], 'end'=>$data['endTime'] ];
	//カンマ区切りで複数時間の時
	else if (mb_strpos($data['time'], ',') !== false){
		//「,」区切りで時間を配列に分ける
		$timeList = explode(',', $data['time']);
		$dayOfWeekList = [];
		//時間を曜日とその中に時限の数字を入れ、連想配列化
		foreach ($timeList as $t){
			//曜日を抽出
			$dow = mb_substr($t, 0, 1);
			//曜日の配列がなければ作成
			if (!array_key_exists($dow, $dayOfWeekList)) $dayOfWeekList[$dow] = [];
			//時限を追加
			$dayOfWeekList[$dow][] = intval(mb_substr($t, 1, 1));
		}
		//曜日ごとに処理
		foreach ($dayOfWeekList as $dow => $list){
			//ソートする
			asort($list);
			$startHour = -1;
			$startIndex = -1;
			for ($i = 0; $i < count($list); $i++){
				//初めは有無を言わさず時間の始まりとして代入
				if ($startHour === -1 && $startIndex === -1){
					$startHour = $list[$i];
					$startIndex = $i;
				}
				//配列の最後or次との時間が1時間以上空き→時間登録
				if ($i + 1 === count($list) || $list[$i + 1] - $list[$i] > 1){
					$times[] = [ 'day'=>$dow, 'hour'=>$startHour, 'start'=>StartTime[$startHour], 'end'=>EndTime[$list[$i]] ];
					$startHour = $startIndex = -1;
				}
			}
		}
	}
	//時間が1つの時
	else {
		$hour = intval(mb_substr($data['time'], 1, 1));
		$times[] = [ 'day'=>mb_substr($data['time'], 0, 1), 'hour'=>$hour, 'start'=>StartTime[$hour], 'end'=>EndTime[$hour] ];
	}

	//休みリストをDateTimeでインスタンス生成
	$holidayList = [];
  foreach (Holiday as $h) $holidayList[] = new DateTime($h);

	//イベントの追加
	$result = [];
	for ($i = 0; $i < count($terms); $i++){
		for ($j = 0; $j < count($times); $j++){
			//元のIDの生成
			//UNIX時間&シラバスコード&ターム&曜時&単位数&★ターム&★曜日
			//0000000000&2000-XX-XXXXXX-ja_JP&T1&月1&2&0&0
			$id = "$_SERVER[REQUEST_TIME]&$data[code]&$data[term]&$data[time]&$data[credit]&$i&$j";
			//開始日時のDateTimeインスタンスを生成
			$startDate = new DateTime($terms[$i]['start']);
			$allDay = false;
			$everyDay = false;
			//dayがallのとき、終日かつ毎日に
			if ($times[$j]['day'] === 'all'){
				$allDay = true;
				$everyDay = true;
			}
			//dayがtimeのとき、毎日に
			else if ($times[$j]['day'] === 'time'){
				$everyDay = true;
			}
			//dayに曜日が入るとき
			else {
				$diff = DOW[$times[$j]['day']] - intval($startDate->format('w'));
				//開始日の曜日より後のとき、その分、日を進める
				if ($diff > 0) $startDate->modify('+'.$diff.' days');
				//開始日の曜日より前のとき、7日とプラスその分、日を進める
				else if ($diff < 0) $startDate->modify('+'.(7 + $diff).' days');
			}
			//iCalendarの作法に基づいて、繰り返しルールを記述
			$exdate = [ 'RRULE:FREQ='.($everyDay ? 'DAILY;' : 'WEEKLY;WKST=SU;BYDAY='.DOWEN[$times[$j]['day']].';').'UNTIL='.str_replace('-', '', $terms[$i]['end']).'T145959Z' ];
			//振替を無視しない場合 !!!!!!!!!!
			if ($data['ignoreExdate'] === 'false')
				foreach ($holidayList as $h)
					if ($everyDay || intval($h->format('w')) === DOW[$times[$j]['day']])
						$exdate[] = 'EXDATE:'.$h->format('Ymd').'T'.str_replace(':', '', $times[$j]['start'].':00');
			$overrides = [];
			//通知が有効なとき
      if ($data['notification'] === 'true') $overrides[] = [ 'method' => 'popup', 'minutes' => ($times[$j]['hour'] === 3 ? 50 : 10) ];
			//セッションデータ利用開始
			session_start();
			//通知をオンにするかどうかの履歴を変更
      $_SESSION['userdata']['notification'] = ($data['notification'] === 'true' ? '1' : '0');
			//セッションデータ利用終了
			session_write_close();
			
			$result[] = $service->events->insert('primary', new Google_Service_Calendar_Event([
				'id' => str_replace('=', '', Encoding::base32HexEncode($id)), //base32hexエンコードをしたID
				'colorId' => '2', //アカウントによって違う
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
				'source' => [ //ソース設定、楽にリンクへ飛べるようになる
					'url' => 'https://delisys.xperd.net/syllabus?'.$data['code'], 'title' => $data['name'].'の詳細 -チバシス-'
				], //通知設定、デフォルトはいずれも使わない
				'reminders' => [ 'useDefault' => false, 'overrides' => $overrides ]
			]));
		}
  }
  //以前の通知設定を変更
  updateCalendarNotification($link, $data['notification'] === 'true');
	return [ 'status'=>'success', 'result'=>$result ];
}

/**
 * Googleカレンダーのイベントを消去
 *
 * @param $client Google Clientのインスタンス
 * @param string $id イベントID
 * @return array 成功したかどうか、イベントの返り値の連想配列
 */
function deleteCalendarSubjects($client, string $id): array {
	//ログインしていない時は、status:expiredと返す
	if (!$client) return [ 'status'=>'expired', 'result'=>[] ];
	//カレンダーサービスのインスタンスを生成
	$service = new Google_Service_Calendar($client);
	//idを指定してイベントを消去
  $result[] = $service->events->delete('primary', $id);
	return [ 'status'=>'success', 'result'=>$result ];
}

/**
 * Googleカレンダーのイベントの通知設定変更
 *
 * @param $client Google Clientのインスタンス
 * @param mysqli $link MySQLのインスタンス
 * @param array $query POSTデータ
 * @return array 成功したかどうか、イベントの返り値の連想配列
 */
function toggleCalendarNotification($client, mysqli $link, array $query): array {
	//ログインしていない時は、status:expiredと返す
	if (!$client) return [ 'status'=>'expired', 'result'=>[] ];
	//カレンダーサービスのインスタンスを生成
	$service = new Google_Service_Calendar($client);
	$result = [];
  foreach ($query['id'] as $id) {
		//idごとにイベントを取得
		$event = $service->events->get('primary', $id);
		//通知を有効に変更するとき
    if ($query['bool'] === 'true'){
      $event['reminders'] =
        [ 'useDefault' => false, 'overrides' => [ [ 'method' => 'popup', 'minutes' =>
          (substr($event['start']['dateTime'], 11, 5) === '12:50' ? 50 : 10) ] ] ];
		}
		//通知を無効に変更するとき
    else if ($query['bool'] === 'false'){
      $event['reminders'] = [ 'useDefault' => false, 'overrides' => [] ];
		}
		//一部を書き換えたやつをそのままアップデートとして突っ込む
  	$result[] = $service->events->update('primary', $id, $event);  
  }
  //以前の通知設定を変更
  updateCalendarNotification($link, $query['bool'] === 'true');
	return [ 'status'=>'success', 'result'=>$result ];
}

/**
 * 以前の変更通知をMySQLに保存
 *
 * @param mysqli $link MySQLのインスタンス
 * @param boolean $bool 有効かどうか
 * @return array 成功したかどうかの連想配列
 */
function updateCalendarNotification(mysqli $link, bool $bool): array {
	//セッションデータ利用開始
	session_start();
  //以前の通知設定をMySQLに保存
  $result = maria_query($link, "UPDATE delisys.user SET notification=".($bool ? 1 : 0)." WHERE id='$_SESSION[id]';");
	//セッションデータ利用終了
	session_write_close();
	return [ 'status'=>($result ? 'success' : 'failed') ];
}
?>