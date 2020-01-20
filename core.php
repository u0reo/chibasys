<?php
//use ___PHPSTORM_HELPERS\object;

noDirectAccess();
ini_set('display_errors', 1);
set_time_limit(60);

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
function br2n($string) {
	return preg_replace('/<br[[:space:]]*\/?[[:space:]]*>/i', "\n", $string);
}

/**
 * 文字列を整形(スペースや改行削除、全角英字を半角英字へ、半角カナを全角カナへ、最初最後のトリム)
 *
 * @param string $string 汚い文字列
 * @return string 整形された文字列
 */
function shape_line($string) {
	return trim(shape_punc(shape_space(mb_convert_kana(preg_replace('/(?:\n|\r|\r\n|\s)/', '', br2n($string)), 'asKV'))));
}

/**
 * 文字列を整形(スペースや改行削除、全角英字を半角英字へ、半角カナを全角カナへ、最初最後のトリム)
 *
 * @param string $string 汚い文字列
 * @return string 整形された文字列
 */
function shape_content($string) {
	return trim(shape_punc(shape_space(mb_convert_kana(br2n($string), 'asKV'))));
}

function shape_punc($string) {
	return preg_replace('/([!-~])。/', '$1.', preg_replace('/([!-~])。/', '$1.', str_replace('.', '。', str_replace(',', '、', $string))));
}

function shape_space($string) {
	return preg_replace('/ +/s', ' ', str_replace('　', ' ', $string));
}

/**
 * Google Clientのインスタンスを取得
 *
 * @return Google Clientのインスタンス
 */
function getGoogleClient() {
	$client = new Google_Client();
	$client->setApplicationName('chibasys by reolink');
	$client->setAuthConfig(__DIR__.'/client_secret.json');
	//$client->setAccessType("offline");
	$client->setIncludeGrantedScopes(true);
	$client->addScope(Google_Service_Calendar::CALENDAR);
	$client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);
	$client->addScope(Google_Service_People::USERINFO_PROFILE);
	//$client->setApprovalPrompt('force');
	$client->setRedirectUri('https://chibasys.xperd.net/auth?mode=success');
	$client->setDeveloperKey('AIzaSyAY3LxdQdnM1s2P7ztXiCkK_95YDs-Tl-w');
	return $client;
}

/**
 * 直接アクセスを禁止、直接アクセスしたときは400 Bad Requestに
 *
 * @return void
 */
function noDirectAccess() {
	$gif = get_included_files();
	if (array_shift($gif) === __FILE__) {
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

function maria_query(mysqli $link, string $query, $all = false) {
	$r = mysqli_query($link, $query);
	if (!$r || $all)
		error_log("\nERROR: ".print_r($r, true)."\nQUERY: $query", "3", "/var/www/mysql-error.log");
	return $r;
}

/**
 * ようこそ画面へ遷移、最後にexit()実行
 *
 * @param string $query 追加したいクエリ ex)'?error=...'
 * @return void
 */
function locateWelcome(string $query = '') {
  header('location: /welcome'.$query);
  exit();
}

/**
 * ログイン画面へ遷移、最後にexit()実行
 *
 * @param $client Google Clientのインスタンス
 * @return void
 */
function locateLogin($client) {
	header('location: '.$client->createAuthUrl());
  exit();
}

/**
 * Googleログインをしているか否かを判定
 *
 * @return Google Clientのインスタンス
 */
function sessionCheck() {
  $client = getGoogleClient();
  session_start();
  if (isset($_SESSION['accessToken'])) {
		//一度はログイン済みの場合
    $client->setAccessToken($_SESSION['accessToken']);
    if (!$client->isAccessTokenExpired()) {
			if (!isset($_SESSION['id'])) {
				//GoogleのPeople APIでユーザーの情報を取得
	      $people = new Google_Service_PeopleService($client);
  	    $userinfo = $people->people->get('people/me', ['personFields'=>'names,photos']);
				$_SESSION['id'] = explode("/", $userinfo['resourceName'])[1];
				$_SESSION['google_photo_url'] = $userinfo['photos'][0]['url'];
				$_SESSION['google_user_name'] = $userinfo['names'][0]['displayName'];
			}
			if (!isset($_SESSION['userdata'])) {
				//MySQLからユーザー情報を取得して、登録済みの時はセッションに保管
				$link = mysqli_connect('localhost', 'chibasys', 'P8IpIqW2Zb8CZNCC', 'chibasys');
				$res = maria_query($link, "SELECT * FROM chibasys.user WHERE id = '$_SESSION[id]';");
				if (mysqli_num_rows($res) === 1) {
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
 * 一時データをMySQLから取得、セッションに保存
 *
 * @param mysqli $link
 * @param string $name
 * @param string $userID
 * @return bool データが既に存在したかどうか
 */
function setTempToSession(mysqli $link, string $name, string $userID = 'general') {
	$result = maria_query($link, "SELECT data FROM chibasys.temp WHERE name='$name' AND user='$userID';");
	if ($result) {
		if ($result = mysqli_fetch_assoc($result)) {
			session_start();
			$_SESSION[$userID === 'general' ? $name : "user_$name"] = $result['data'];
			session_write_close();
		}
	}
	return (bool)$result;
}

/**
 * 一時データをMySQLとセッションに保存
 *
 * @param mysqli $link MySQLのインスタンス
 * @param string $name 一時データの名前
 * @param string $data 一時データの内容
 * @param bool $user ユーザーデータとして保存するかどうか
 * @return void
 */
function temp_save(mysqli $link, string $name, string $data, bool $user = false) {
	//userIDにGoogleのIDまたは'general'を格納
	session_start();
	$userID = $user && isset($_SESSION['id']) ? $_SESSION['id'] : 'general';
	session_write_close();

	//既にデータがあるかどうかで場合分け
	if (setTempToSession($link, $name, $userID))
		maria_query($link, "UPDATE chibasys.temp SET data='$data' WHERE name='$name' AND user='$userID';");
	else
		maria_query($link, "INSERT INTO chibasys.temp (name, user, data) VALUES ('$name', '$userID', '$data');");
	
	//セッションに保管
	session_start();
  $_SESSION[$userID === 'general' ? $name : "user_$name"] = $data;
	session_write_close();
}

/**
 * URLの?以降のflowExecutionKeyを抽出
 *
 * @param string $url 元のURL
 * @return string flowExecutionKey
 */
function url_extract(string $url): string {
	return substr($url, strpos($url, '?') + 1);
}

/**
 * 千葉大学シラバス検索のセッションを取得し、MySQLに保存
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURLのインスタンス
 * @return void
 */
function syllabus_cookie_url_get(mysqli $link, $curl) {
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
	for ($i = 0; $i < $cookieCount; $i++) {
		$start = strpos($header, 'Set-Cookie:', $start + 1) + 12;
		$end = strpos($header, ';', $start);
		$c[] = substr($header, $start, $end - $start);
	}
	$cookie = implode('; ', $c);
	//入力画面へ遷移しておく
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
	//一時データとして保存
	temp_save($link, 'syllabus_url', $info['redirect_url']);
	temp_save($link, 'syllabus_cookie', $cookie);
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
  setTempToSession($link, 'syllabus_url');
	setTempToSession($link, 'syllabus_cookie');
	//cURLで千葉大学シラバスの検索結果を取得
	session_start();
	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://cup.chiba-u.jp/campusweb/campussquare.do',
		CURLOPT_REFERER => $_SESSION['syllabus_url'],
		CURLOPT_COOKIE => $_SESSION['syllabus_cookie'],
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => 's_no=0&'.url_extract($_SESSION['syllabus_url']).'&_eventId=search&_displayCount=1928&'.$query['query'], //http_build_query
		CURLOPT_HEADER => false,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_CONNECTTIMEOUT => 0,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]);
	session_write_close();
	//cURLを実行
	$res = curl_exec($curl);
	//各種情報を取得
	$info = curl_getinfo($curl);
	//セッション切れをチェック
	if (!portal_session_check($res, $info)) {
		syllabus_cookie_url_get($link, $curl);
    return search($link, $curl, $query);
  }
	//PHPQueryのインスタンス生成
	$tbody = phpQuery::newDocument($res)->find('table > tbody');
	$subjects = [];
	//trの数=教科数
	$subjectCount = count($tbody->find('tr'));
	for($i=0; $i<$subjectCount; $i++) {
		$tr = $tbody->find('tr:eq('.$i.')');
		$sub = [];
		$dataCount = count($tr->find('td')); //0-10
		//trタグ内の一つ一つの改行やスペースを除去
		for($j=1; $j<$dataCount; $j++)
			$sub[] = shape_line($tr->find("td:eq($j)")->text());
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
		maria_query($link, 'INSERT INTO chibasys.history_search VALUES ("'.$_SESSION['id'].'", NOW(), "'.$query['query'].'", '.$subjectCount.');');
	//セッションデータ利用終了
	session_write_close();
	
	return [ 'status'=>'success', 'subjects'=>$subjects, 'url'=>$info['url'] ];
}

const eng = ["学科(専攻)・科目の種別等"=>'class_type', "授業科目"=>'name', "授業コード"=>false,
"科目コード"=>'subject_code', "ナンバリングコード"=>'numbering_code', "授業の方法"=>'method',
"使用言語"=>'language', "単位数"=>'credit', "時間数"=>'hour', "期別"=>'period', "履修年次/ターム"=>true,
"曜日・時限"=>'time', "副専攻"=>'sub_major', "副題"=>'sub_title', "受入人数"=>'student_count',
"担当教員"=>'teacher', "受講対象"=>'target_student', "教室"=>'room',
"更新日"=>'update_date', "概要"=>'summary', "目的・目標"=>'purpose', "授業計画・授業内容"=>'content',
"授業外学習"=>'homework', "キーワード"=>'keyword', "教科書・参考書"=>'textbook', "評価方法・基準"=>'evaluation_method',
"関連科目"=>'related_subject', "履修要件"=>'requirement', "備考"=>'remark', "関連URL"=>'related_url'];
/**
 * 千葉大学シラバスを検索し、教科一覧を返す→廃止予定
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURLのインスタンス
 * @param array $query POSTデータ
 * @return array 成功したかどうか、教科一覧の連想配列
 */
function search_and_save(mysqli $link, $curl, array $query) {
	//$data = ['s_no' => '0', '_eventId' => 'search', 'nendo' => 2019, 'kaikoKamokunmLike' => '英語'];
	//'s_no=0&'.substr($baseUrl, strpos($baseUrl, "?") + 1).'&_eventId=search&nendo=2019&jikanwariShozokuCode=&gakkiKubunCode=&kaikoKubunCode=&kyokannmLike=&jikanwaricdLike=&kaikoKamokunmLike=%E8%8B%B1%E8%AA%9E&nenji=&yobi=&jigen=&freeWord=&nbrGakubucd=&nbrGakkacd=&nbrSuijuncd=&fukusenkocd=&syReferOrder=&_displayCount=100';
	//セッションデータ利用を開始してURLやCookieが保管されているか確認、なければMySQLから取得
  setTempToSession($link, 'syllabus_url');
	setTempToSession($link, 'syllabus_cookie');
	//cURLで千葉大学シラバスの検索結果を取得
	session_start();
	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://cup.chiba-u.jp/campusweb/campussquare.do',
		CURLOPT_REFERER => $_SESSION['syllabus_url'],
		CURLOPT_COOKIE => $_SESSION['syllabus_cookie'],
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => 's_no=0&'.url_extract($_SESSION['syllabus_url']).'&_eventId=search&_displayCount=1928&nendo='.$query['nendo'].'&jikanwariShozokuCode='.$query['jikanwariShozokuCode'], //http_build_query
		CURLOPT_HEADER => false,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_CONNECTTIMEOUT => 0,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]);
	session_write_close();
	//cURLを実行
	$res = curl_exec($curl);
	//各種情報を取得
	$info = curl_getinfo($curl);
	//セッション切れをチェック
	/*if (!portal_session_check($res, $info)) {
		syllabus_cookie_url_get($link, $curl);
    return search_and_save($link, $curl, $query);
  }*/
	//PHPQueryのインスタンス生成
	$tbody = phpQuery::newDocument($res)->find('table > tbody');
	//trの数=教科数
	$subjectCount = count($tbody->find('tr'));
	for($i=0; $i<$subjectCount; $i++) {
		$tr = $tbody->find('tr:eq('.$i.')');
		$sub = [];
		$dataCount = count($tr->find('td')); //0-10
		//$sub['nendo'] = shape_line($tr->find("td:eq(0)")->text());

		//最後のtdにある初めのボタンを取得
		$button = $tr->find('td:eq('.($dataCount - 1).')')->find('input:eq(0)');
		//onclickを解析して「jikanwariShozokuCode」を入手
		$refer = $button->attr('onclick');
		$start = strpos($refer, ",") + 2;
		$end = strpos($refer, "'", $start);
		$sub['jikanwariShozokuCode'] = substr($refer, $start, $end - $start);

		$sub['jikanwaricd'] = shape_line($tr->find("td:eq(5)")->text());

		//disabledがあるかどうかで日本語か英語かを判別。日本語優先
		$lang = [];
		if ($button->attr('disabled') == null) $lang[] = 'ja_JP';
		$button2 = $tr->find('td:eq('.($dataCount - 1).')')->find('input:eq(1)');
		if ($button2->attr('disabled') == null) $lang[] = 'en_US';
		$sub['slocale'] = implode(',', $lang);

		if (isset($query['department']) && $query['department'] !== '') $sub['department'] = $query['department'];
		if (isset($query['subject']) && $query['subject'] !== '') $sub['subject'] = $query['subject'];
		if (isset($query['course']) && $query['course'] !== '') $sub['course'] = $query['course'];
		
		$result = maria_query($link, "SELECT * FROM chibasys.syllabus_2019 WHERE jikanwariShozokuCode='$sub[jikanwariShozokuCode]' AND jikanwaricd='$sub[jikanwaricd]'");
		if (!$result || !mysqli_fetch_assoc($result))
			maria_query($link, 'INSERT INTO chibasys.syllabus_2019 ('.implode(',', array_keys($sub)).') VALUES (\''.implode('\',\'', array_values($sub)).'\');');
	}
	$query['count'] = $subjectCount;

	return $query;
}

function syllabus_save(mysqli $link, $curl, $pos){
	$result = mysqli_fetch_assoc(maria_query($link, 'SELECT * FROM chibasys.syllabus_2019 LIMIT 1 OFFSET '.$pos));
	
	$syllabus = real_syllabus($link, $curl, [ 'code'=>'2019-'.$result['jikanwariShozokuCode'].'-'.$result['jikanwaricd'].'-'.explode(',', $result['slocale'])[0] ]);
	if ($syllabus['status'] !== 'success') return;

	/*$data = [ "department"=>null,"subject"=>null,"course"=>null,"class_type"=>null,
		"name"=>null,"false"=>null,"subject_code"=>null,"numbering_code"=>null,"method"=>null,"language"=>null,
		"credit"=>null,"hour"=>null,"period"=>null,"grade"=>null,"term"=>null,"time"=>null,"sub_major"=>null,
		"sub_title"=>null,"student_count"=>null,"teacher"=>null,"target_student"=>null,"room"=>null,
		"update_date"=>null,"summary"=>null,"purpose"=>null,"content"=>null,"homework"=>null,"keyword"=>null,
		"textbook"=>null,"evaluation_method"=>null,"related_subject"=>null,"requirement"=>null,"remark"=>null,
		"related_url"=>null,"detail"=>null ];*/
	$data = [];
	foreach ($result as $key => $value) $data[$key] = ($value && $value !== '' ? $value : null);

	foreach ($syllabus['detail-1'] as $key => $value) {
		$k = eng[$key];
		if (!$k) continue;
		if ($k === true){
			$v = explode('/', $value);
			$data['grade'] = $v[0];
			$data['term'] = $v[1];
		}
		else
			$data[$k] = $value;
	}
	foreach ($syllabus['detail-2'] as $key => $value) {
		$k = eng[$key];
		$data[$k] = $value;
	}
	if ($syllabus['detail-3']) $data['detail'] = json_encode($syllabus['detail-3']);

	$d = [];
	foreach ($data as $k => $v) $d[] = $k.'=\''.mysqli_real_escape_string($link, $v).'\'';

	return [ 'status'=>maria_query($link, 'UPDATE chibasys.syllabus_2019 SET '.implode(',', $d).
		" WHERE jikanwariShozokuCode='$result[jikanwariShozokuCode]' AND jikanwaricd='$result[jikanwaricd]';"), 'data'=>$d ];
}


/**
 * シラバスコードでMySQLから千葉大学シラバスの内容を取得
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURL
 * @param array $query POSTデータ
 * @return array 成功したかどうか、シラバス内容、元のURL、リダイレクト先のURLの連想配列
 */
function syllabus(mysqli $link, array $query): array {
	//シラバスコードを分解
	$code = explode('-', $query['code']);
	if (count($code) === 4) $code[1] = $code[2];
	$result = maria_query($link, "SELECT * FROM chibasys.syllabus_$code[0] WHERE jikanwaricd = '$code[1]';");
	$data = mysqli_fetch_assoc($result);
	if ($data['detail']) $data['detail'] = json_decode($data['detail']);
	return [ 'status'=>($result ? 'success' : 'failed'), 'data'=>$data, 
	'url_ja'=>(strpos('ja_JP', $data['slocale']) !== false ? "https://cup.chiba-u.jp/campusweb/campussquare.do?locale=ja_JP&_flowId=SYW3901100-flow&nendo=$code[0]&jikanwariShozokuCode=$data[jikanwariShozokuCode]&jikanwaricd=$code[1]&slocale=ja_JP" : null), 
	'url_en'=>(strpos('en_US', $data['slocale']) !== false ? "https://cup.chiba-u.jp/campusweb/campussquare.do?locale=ja_JP&_flowId=SYW3901100-flow&nendo=$code[0]&jikanwariShozokuCode=$data[jikanwariShozokuCode]&jikanwaricd=$code[1]&slocale=en_US" : null) ];
}

/**
 * シラバスコードで千葉大学シラバスの内容を取得
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURL
 * @param array $query POSTデータ
 * @return array 成功したかどうか、シラバス内容、元のURL、リダイレクト先のURLの連想配列
 */
function real_syllabus(mysqli $link, $curl, array $query): array {
	//シラバスコードを分解
	$data = explode('-', $query['code']);
	//セッションデータ利用を開始してURLやCookieが保管されているか確認、なければMySQLから取得
	setTempToSession($link, 'syllabus_url');
	setTempToSession($link, 'syllabus_cookie');
	session_start();
	
	//千葉大学シラバスのURLを生成 (ポータル内:https://cup.chiba-u.jp/campusweb/campussquare.do?_flowId=SYW0001000-flow)
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
		CURLOPT_TIMEOUT => 15,
		CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]);
	session_write_close();
	//cURLを実行
	$res = curl_exec($curl);
	//各種情報を取得
	$info = curl_getinfo($curl);
	//セッション切れをチェック
	if (!portal_session_check($res, $info)) {
    syllabus_cookie_url_get($link, $curl);
    return real_syllabus($link, $curl, $query);
	}
	
	//PHPQueryのインスタンス生成
	$doc = phpQuery::newDocument($res);
	//タブごとにインスタンスを生成
	$details1 = tableAnalysis($doc->find('#tabs-1'), "");
	$details2 = tableAnalysis($doc->find('#tabs-2'), "\n");
	$details3 = tableAnalysisThird($doc->find('#tabs-3'));

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
	for($i=0; $i<$detailsCount; $i++) {
		//名前の列は半角に整形し、スペースをなくす
		$name = shape_line($tbody->find("tr:eq($i)")->find('th')->text());
		//詳細の列はHTMLを半角に整形し、<br>を\nにして、trimをかける
		$detail = shape_content($tbody->find("tr:eq($i)")->find('td')->html());
		//どちらかの列が無名だったり、15の力は無視
		if ($name === '' || $detail === '' || mb_strpos($name, '15の力') !== false) continue;
		//$detailに改行がある場合、何もない行を消去する
		if (mb_strpos($detail, "\n") !== false) {
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
		if (mb_strpos($name, '履修年次') !== false) {
			//履修年次/ターム/Students'Year… となるので、区切りの「/」の位置をずらす
			$posName = mb_strpos($name, "/", 6);
			$detail = str_replace('・', '･', $detail);
		}
		else if (mb_strpos($name, '曜日') !== false) {
			//月 /Mon 　1… となるので、英語表記を消し、その間のスペースも除去
			$detail = str_replace(['/Sun', '/Mon', '/Tue', '/Wed', '/Thu', '/Fri', '/Sat', '/Oth'], '', $detail);
			$detail = str_replace(' ', '', $detail);
		}
		else if ($posDetail !== false && $insert === '')
			//詳細に英語表示があり、detail-1の時に、日本語のみにする
			$detail = trim(mb_substr($detail, 0, $posDetail));
		//名前は英語表示があるので、日本語のみに
		$name = mb_substr($name, 0, $posName);

		$posLinkIndex = 0;
		while ($posLinkStart = mb_strpos($detail, '<a ', $posLinkIndex) !== false) {
			$posLinkEnd = mb_strpos($detail, '</a>', $posLinkIndex);
			$tempBefore = mb_substr($detail, 0, $posLinkStart);
			$tempAfter = mb_substr($detail, $posLinkEnd);
			$tempReplaced = str_replace('。', '.', mb_substr($detail, $posLinkStart, $posLinkEnd - $posLinkStart));
			$detail = $tempBefore.$tempReplaced.$tempAfter;
			$posLinkIndex = $posLinkEnd + 4;
		}

		//連想配列に
		$details[$name] = $detail;//str_replace("\n", '<br>', $detail);
	}
	return $details;
}

/**
 * 授業計画詳細情報のCampusSquareのテーブルを解析
 *
 * @param phpQueryObject $tbody テーブル内のHTML
 * @return array データを整形して連想配列にしたもの
 */
function tableAnalysisThird(phpQueryObject $tbody) {
	$details = [];
	//trの数=行頭を含む行の数
	$detailsCount = count($tbody->find('tr'));
	for($i=0; $i<$detailsCount; $i++) {
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
			$td[] = shape_content($tbody->find("tr:eq($i)")->find("td:eq($j)")->text());
		//「1. 第1回 名前\n内容: 内容内容\n備考: 備考備考」の形に整形
		$detail = trim(("$td[0]. $td[1] $td[2]".($td[3] === '' ? '' : "\n内容: $td[3]").($td[4] === '' ? '' : "\n備考: $td[4]")));
		//連想配列に
		$details[] = $detail;//str_replace("\n", '<br>', $detail);
	}
	return $details;
}

/**
 * 学生ポータルへログインしてセッション取得、MySQLに保存
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURLのインスタンス
 * @return bool 成功したかどうか
 */
function portal_cookie_create(mysqli $link, $curl, bool $user = false, bool $query = null) {
	//ID、パスワードを設定
	if (!$user) {
		$id = '19T1688A';
		$pass = 'moyrv%3Fd_!6t5';
	}
	else {
		if ($query === null) {
			session_start();
			$result = maria_query($link, "SELECT studentID, studentPass FROM chibasys.user WHERE id='$_SESSION[id]';");
			session_write_close();
			while ($row = mysqli_fetch_assoc($result)) {
				$id = urlencode($row['studentID']);
				$pass = urlencode($row['studentPass']);
			}
		}
		else {
			$id = urlencode($query['studentID']);
			$pass = urlencode($query['studentPass']);
		}
	}
	//IDやパスワードがないとき、終了
	if (!isset($id) || !isset($pass)) return false;
	//cURLでログイン
	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://cup.chiba-u.jp/campusweb/campusportal.do',
		CURLOPT_REFERER => '',
		CURLOPT_COOKIE => '',
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => "wfId=nwf_PTW0000002_login&userName={$id}&password={$pass}&locale=ja_JP&undefined=&action=rwf&tabId=home&page=",//&rwfHash=86c8c93c52abb4ae783c237d364dd203',
		CURLOPT_HEADER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_TIMEOUT => 5,
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
		CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36']);
	$res = curl_exec($curl);
	$info = curl_getinfo($curl);

	//成功したとき <!DOCTYPE HTML><div><script type="text/javascript">reloadPortal('', 'main');</script>now loading...<br></div>
	//失敗したとき <!DOCTYPE HTML><div><script type="text/javascript">$(document).ready(function(){setTimeout(function(){ $("input[name='loginerrok']").focus(); }, 500);});
  //          <span class="error">ユーザ名またはパスワードの入力に誤りがあります</span><br><br><br><br><center><input type="button" id="loginerrok" name="loginerrok" value="&nbsp; O &nbsp;&nbsp; K &nbsp;" onClick="closeLoginDialog(this)"></center></div>
	if (strpos($res, 'now loading') === false || strpos($res, 'reloadPortal') === false) {
		return false;
	}

	//Headerを取得
  $header = substr($res, 0, $info['header_size']);
  //Cookieのみ抽出
	$c = [];
	$cookieCount = substr_count($header, 'Set-Cookie: ');//substr_count($header, 'Set-Cookie:');
	$start = 0;
	for ($i = 0; $i < $cookieCount; $i++) {
		$start = strpos($header, 'Set-Cookie: ', $start + 1) + 12;
		$end = strpos($header, ';', $start);
		$c[] = substr($header, $start, $end - $start);
	}
	//Cookieの文字列に変換
	$cookie = implode('; ', $c);
	//一時データとして保存
	temp_save($link, 'portal_cookie', $cookie, $user);
	return true;
}

/**
 * 初期設定時に学生情報を取得
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURLのインスタンス
 * @return array 成功したかどうか、学生情報の連想配列
 */
function portal_student_info(mysqli $link, $curl) {
	//UserIDを変数に
	session_start();
	if (isset($_SESSION['id']))
		$userID = $_SESSION['id'];
	session_write_close();
	//未ログインならば終了
	if (!isset($_SESSION['id']))
		return [ 'status'=>'expired' ];

  //セッションにCookieが保管されているか確認、なければMySQLから取得
  if (!setTempToSession($link, 'portal_cookie', $userID))
		portal_cookie_create($link, $curl, $userID);
	
	//cURLで学生情報にアクセス
	session_start();
	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://cup.chiba-u.jp/campusweb/campussquare.do?_flowId=CHW0001000-flow',
		CURLOPT_REFERER => '',
		CURLOPT_COOKIE => $_SESSION['user_portal_cookie'],
		CURLOPT_POST => false,
		CURLOPT_POSTFIELDS => '',
		CURLOPT_HEADER => false,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_TIMEOUT => 5,
		CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
	]);
	session_write_close();
	//cURLを実行
	$res = curl_exec($curl);
	//各種情報を取得
	$info = curl_getinfo($curl);
	//期限切れセッションチェック
	if ($info['url'] === 'https://cup.chiba-u.jp/campusweb/campusportal.do' ||
		$info['url'] === 'https://cup.chiba-u.jp/campusweb/campussquare.do' ||
		mb_strpos($res, 'SYSTEM ERROR') !== false) {
    portal_cookie_create($link, $curl, $userID);
    return portal_student_info($link, $curl);
	}
	$data = [];
	//PHPQueryのインスタンス生成
	$doc = phpQuery::newDocument($res);
	//いくつかのテーブルにデータが分かれている
	$tableCount = count($doc->find('.normal'));
	for ($i = 0; $i < $tableCount; $i++) {
		$table = $doc->find(".normal:eq($i)");
		//trの数=データ数+1 のため、初めの行は省く
		$rowCount = count($table->find('tr'));
		for ($j = 1; $j < $rowCount; $j++) {
			$tr = $table->find("tr:eq($j)");
			// 名前|データ となるため、二つセットで処理
			$colCount = count($tr->find('td'));
			for ($k = 0; $k < $colCount; $k += 2) {
				$content = shape_line($tr->find("td:eq(".($k+1).")")->text());
				//内容があるもののみデータとして格納
				if (isset($content) && $content !== '')
					$data[shape_line($tr->find("td:eq($k)")->text())] = $content;
			}
		}
	}

	return [ 'status'=>(count($data) > 0 ? 'success' : 'failed'), 'data'=>$data ];
}

const PORTAL_INIT_URL = [
	'subject_change_url'=>'https://cup.chiba-u.jp/campusweb/campussquare.do?_flowId=KHW0001100-flow',
	'portal_registration_url'=>'https://cup.chiba-u.jp/campusweb/campussquare.do?_flowId=RSW0001000-flow',
	'portal_registration_list_url'=>'https://cup.chiba-u.jp/campusweb/campussquare.do?_flowId=JPW0001000-flow',
	'portal_grade_url'=>'https://cup.chiba-u.jp/campusweb/campussquare.do?_flowId=SIW0001200-flow' ];
										//'https://cup.chiba-u.jp/campusweb/campussquare.do?_flowId=SIW0001300-flow'

function portal_url_get(mysqli $link, $curl, string $name, bool $user = false) {
  //セッションデータ利用を開始してURLやCookieが保管されているか確認、なければMySQLから取得
	setTempToSession($link, $user ? 'user_portal_cookie' : 'portal_cookie');
  session_start();
	//cURLでアクセス
	curl_setopt_array($curl, [
		CURLOPT_URL => PORTAL_INIT_URL[$name],
		CURLOPT_REFERER => '',
		CURLOPT_COOKIE => $_SESSION[$user ? 'user_portal_cookie' : 'portal_cookie'],
		CURLOPT_POST => false,
		CURLOPT_POSTFIELDS => '',
		CURLOPT_HEADER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_TIMEOUT => 5,
		CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
	]);
	session_write_close();
	//cURLを実行
	$res = curl_exec($curl);
	//各種情報を取得
	$info = curl_getinfo($curl);
	//一時データとして保存
	temp_save($link, $name, curl_getinfo($curl)['url'], $user);

	if ($name = 'portal_registration_url') {
		$event = phpQuery::newDocument($res)->find('input[name="_eventId"]');
		if ($event->attr('value') === 'rsRefer') {
			curl_setopt_array($curl, [
				CURLOPT_URL => 'https://cup.chiba-u.jp/campusweb/campussquare.do',
				CURLOPT_REFERER => $info['url'],
				CURLOPT_COOKIE => $_SESSION['user_portal_cookie'],
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => '_eventId=rsRefer&'.url_extract($info['url']),
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
			//各種情報を取得
			$info = curl_getinfo($curl);
			
			curl_setopt_array($curl, [
				CURLOPT_URL => 'https://cup.chiba-u.jp/campusweb/campussquare.do',
				CURLOPT_REFERER => $info['url'],
				CURLOPT_COOKIE => $_SESSION['user_portal_cookie'],
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => '_eventId=nochange&'.url_extract($info['url']),
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
		}
	}
}

/**
 * ポータルのセッション切れ等のエラーをチェック
 * 有効な結果の時、trueを返却
 *
 * @param string $res cURLで取得したHTML
 * @param array $info cURLで取得した情報
 * @return bool セッションが有効かどうか
 */
function portal_session_check(string $res, array $info) : bool {
	return $info['url'] !== 'https://cup.chiba-u.jp/campusweb/campusportal.do' &&
		$info['url'] !== 'https://cup.chiba-u.jp/campusweb/campussquare.do' &&
		mb_strpos($res, 'SYSTEM ERROR') === false &&
		mb_strpos($res, 'システムエラー') === false &&
		mb_strpos($res, '認証エラー') === false &&
		mb_strpos($res, '有効期限ぎれ') === false;
}

/**
 * 履修登録を行う (yobi=1-6, jigen=1-7)(集中講義は9,0)
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURLのインスタンス
 * @param array $query POSTデータ
 * @return array 成功したかどうか、教科一覧の連想配列
 */
function portal_registration(mysqli $link, $curl, array $query) {
	//UserIDを変数に
	session_start();
	if (isset($_SESSION['id']))
		$userID = $_SESSION['id'];
	session_write_close();
	//未ログインならば終了
	if (!isset($_SESSION['id']))
		return [ 'status'=>'expired' ];
	//ポータルパスワードが登録されていないときは終了
	if (!$_SESSION['userdata'] || !$_SESSION['userdata']['studentID'] || !$_SESSION['userdata']['studentPass'])
		return [ 'status'=>'portal_non_register' ];
	
	//セッションデータ利用を開始してURLやCookieが保管されているか確認、なければMySQLから取得
	if (!setTempToSession($link, 'portal_cookie', $userID))
		if (!portal_cookie_create($link, $curl, $userID))
			return [ 'status'=>'portal_cannot_login' ];
	if (!setTempToSession($link, 'portal_registration_url', $userID))
		portal_url_get($link, $curl, 'portal_registration_url', true);
	
	//cURLで履修登録画面を取得
	session_start();
	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://cup.chiba-u.jp/campusweb/campussquare.do',
		CURLOPT_REFERER => $_SESSION['user_portal_registration_url'],
		CURLOPT_COOKIE => $_SESSION['user_portal_cookie'],
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => '_eventId=input&'.url_extract($_SESSION['user_portal_registration_url']).
			"&nendo=&jikanwariShozokuCode=&jikanwariCode=&yobi=1&jigen=1",
		CURLOPT_HEADER => false,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_CONNECTTIMEOUT => 0,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
	]);
	session_write_close();
	//cURLを実行
	$res = curl_exec($curl);
	//各種情報を取得
	$info = curl_getinfo($curl);
	//セッション切れをチェック
	if (!portal_session_check($res, $info)) {
		if (!portal_cookie_create($link, $curl, $userID))
			return [ 'status'=>'portal_cannot_login' ];
		portal_url_get($link, $curl, 'portal_registration_url', true);
		return portal_registration($link, $curl, $query);
	}
	
	$code = explode('-', $query['code']);
	$jikanwariShozokuCode = maria_query($link, "SELECT jikanwariShozokuCode FROM syllabus_$code[0] WHERE jikanwaricd = '$code[1]';");
	//cURLで履修登録
	session_start();
	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://cup.chiba-u.jp/campusweb/campussquare.do',
		CURLOPT_REFERER => $info['url'],
		CURLOPT_COOKIE => $_SESSION['user_portal_cookie'],
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => '_eventId=insert&'.url_extract($info['url']).
			"&nendo=$code[0]&jikanwariShozokuCode=$jikanwariShozokuCode&jikanwariCode=$code[1]&dummy=",
		CURLOPT_HEADER => false,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_CONNECTTIMEOUT => 0,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
	]);
	session_write_close();
	//cURLを実行
	$res = curl_exec($curl);
	//各種情報を取得
	$info = curl_getinfo($curl);
	//セッション切れをチェック
	if (!portal_session_check($res, $info)) {
		portal_cookie_create($link, $curl, $userID);
		portal_url_get($link, $curl, 'portal_registration_url', true);
		return portal_registration_add($link, $curl, $query);
	}
	$error = phpQuery::newDocument($res)->find('span.error')->text();
	if ($error && $error !== '')
		return [ 'status'=>'error', 'message'=>shape_line($error) ];
	else {
		$r = portal_registration_list($link, $curl);
		$r['html'] = $res;
		return $r;
	}
}

/**
 * 履修登録一覧を取得、短縮版授業コード一覧を返却
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURLのインスタンス
 * @return array 成功したかどうか、教科一覧の連想配列
 */
function portal_registration_list(mysqli $link, $curl) {
	//UserIDを変数に
	session_start();
	if (isset($_SESSION['id']))
		$userID = $_SESSION['id'];
	session_write_close();
	//未ログインならば終了
	if (!isset($_SESSION['id']))
		return [ 'status'=>'expired' ];
	//ポータルパスワードが登録されていないときは終了
	if (!isset($_SESSION['userdata']) || !isset($_SESSION['userdata']['studentID']) || !isset($_SESSION['userdata']['studentPass']))
		return [ 'status'=>'portal_non_register' ];
	
	//セッションデータ利用を開始してURLやCookieが保管されているか確認、なければMySQLから取得
	if (!setTempToSession($link, 'portal_cookie', $userID))
		if (!portal_cookie_create($link, $curl, $userID))
			return [ 'status'=>'portal_cannot_login' ];
	if (!setTempToSession($link, 'portal_registration_list_url', $userID))
		portal_url_get($link, $curl, 'portal_registration_list_url', true);
	
	//初めに履修登録済み一覧を消す
	$subjects = [];
	$subjectsData = [];
	maria_query($link, "DELETE FROM chibasys.registration WHERE id='$userID' AND nendo='2019';");

	foreach ([1,2] as $kubun)	{
		//cURLで履修登録画面を取得
		session_start();
		curl_setopt_array($curl, [
			CURLOPT_URL => 'https://cup.chiba-u.jp/campusweb/campussquare.do',
			CURLOPT_REFERER => $_SESSION['user_portal_registration_list_url'],
			CURLOPT_COOKIE => $_SESSION['user_portal_cookie'],
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => '_eventId=changeNendoGakkiGakusei&'.url_extract($_SESSION['user_portal_registration_list_url']).
				"&nendo=2019&gakkiKbnCd=".$kubun,
			CURLOPT_HEADER => false,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_COOKIESESSION => true,
			CURLOPT_CONNECTTIMEOUT => 0,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
		]);
		session_write_close();
		//cURLを実行
		$res = curl_exec($curl);
		//各種情報を取得
		$info = curl_getinfo($curl);
		//セッション切れをチェック
		if (!portal_session_check($res, $info)) {
			if (!portal_cookie_create($link, $curl, $userID))
				return [ 'status'=>'portal_cannot_login' ];
			portal_url_get($link, $curl, 'portal_registration_list_url', true);
			return portal_registration_list($link, $curl);
		}

		//PHPQueryのインスタンス生成
		$tbody = phpQuery::newDocument($res)->find('table.list_tbl tbody');
		//trの数=教科数
		$trCount = count($tbody->find('tr'));
		for ($i = 0; $i < $trCount; $i++) {
			$tr = $tbody->find("tr:eq($i)");
			//「該当するデータはありません」の時はスルー
			if (count($tr->find('td')) <= 1) continue;
			$q = [];
			$url = $tr->find('td:eq(1)')->find('a')->attr('href');
			parse_str(url_extract($url), $q);
			$subjects[] = "$q[nendo]-$q[jikanwariCd]";
			maria_query($link, "INSERT INTO chibasys.registration (id, nendo, jikanwariCd) VALUES ('$userID', '$q[nendo]', '$q[jikanwariCd]');");

			$result = maria_query($link, "SELECT term, time, credit, name, teacher, room FROM chibasys.syllabus_2019 WHERE jikanwariCd='$q[jikanwariCd]';");
			if ($result && mysqli_num_rows($result) === 1) $subjectsData[] = mysqli_fetch_assoc($result);
		}
	}
	return [ 'status'=>'success', 'subjects'=>$subjects, 'subjectsData'=>$subjectsData ];
}

const GRADE_NUM = [ '秀'=>4, '優'=>3, '良'=>2, '可'=>1, '不可'=>0, '合格'=>null, '不合格'=>null ];
/**
 * 成績を取得
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURLのインスタンス
 * @return array 成功したかどうか、教科一覧の連想配列
 */
function portal_grade(mysqli $link, $curl) {
	//UserIDを変数に
	session_start();
	if (isset($_SESSION['id']))
		$userID = $_SESSION['id'];
	session_write_close();
	//未ログインならば終了
	if (!isset($_SESSION['id']))
		return [ 'status'=>'expired' ];
	//ポータルパスワードが登録されていないときは終了
	if (!isset($_SESSION['userdata']) || !isset($_SESSION['userdata']['studentID']) || !isset($_SESSION['userdata']['studentPass']))
		return [ 'status'=>'portal_non_register' ];
	
	//セッションデータ利用を開始してURLやCookieが保管されているか確認、なければMySQLから取得
	if (!setTempToSession($link, 'portal_cookie', $userID))
		if (!portal_cookie_create($link, $curl, $userID))
			return [ 'status'=>'portal_cannot_login' ];
	if (!setTempToSession($link, 'portal_grade_url', $userID))
		portal_url_get($link, $curl, 'portal_grade_url', true);
	
	//cURLで成績画面を取得
	session_start();
	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://cup.chiba-u.jp/campusweb/campussquare.do',
		CURLOPT_REFERER => $_SESSION['user_portal_grade_url'],
		CURLOPT_COOKIE => $_SESSION['user_portal_cookie'],
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => '_eventId=display&'.url_extract($_SESSION['user_portal_grade_url']).'&spanType=0&dummy=',
		CURLOPT_HEADER => false,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_CONNECTTIMEOUT => 0,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
	]);
	session_write_close();
	//cURLを実行
	$res = curl_exec($curl);
	//各種情報を取得
	$info = curl_getinfo($curl);
	//セッション切れをチェック
	if (!portal_session_check($res, $info)) {
		if (!portal_cookie_create($link, $curl, $userID))
			return [ 'status'=>'portal_cannot_login' ];
		portal_url_get($link, $curl, 'portal_grade_url', true);
		return portal_grade($link, $curl);
	}
	$result = true;
	//PHPQueryのインスタンス生成
	$tbody = phpQuery::newDocument($res)->find('table.normal tbody');
	$subjects = [];
	//trの数=教科数
	$trCount = count($tbody->find('tr'));
	for ($i = 0; $i < $trCount; $i++) {
		$sub = [ $userID ];
		$tr = $tbody->find("tr:eq($i)");
		//「履修成績データはありません」の時はスルー
		if (count($tr->find('td')) <= 1) continue;
		foreach ([1,3,4,5] as $j)
			$sub[] = shape_line($tr->find("td:eq($j)")->text());
		$sub[] = GRADE_NUM[shape_line($tr->find('td:eq(6)')->text())]; //秀/優/良/可/不可/合格/不合格
		$sub[] = (shape_line($tr->find('td:eq(7)')->text()) === '合' ? 1 : 0); //合/否
		$subjects[] = [ 'code'=>"$sub[1]-$sub[2]", 'grade'=>$sub[5], 'pass'=>($sub[6] === 1) ];
		
		$row = maria_query($link, "SELECT * FROM chibasys.grade WHERE id='$sub[0]' AND nendo='$sub[1]' AND jikanwaricd='$sub[2]';");
		if ($row && mysqli_fetch_assoc($row))
			$r = maria_query($link, "UPDATE chibasys.grade SET name='$sub[3]', teacher='$sub[4]', point=".($sub[5] === null ? 'null' : $sub[5]).", pass=$sub[6] WHERE id='$sub[0]' AND nendo='$sub[1]' AND jikanwaricd='$sub[2]';");
		else
			$r = maria_query($link, "INSERT INTO chibasys.grade (id, nendo, jikanwariCd, name, teacher, point, pass) VALUES ('$sub[0]','$sub[1]','$sub[2]','$sub[3]','$sub[4]',".($sub[5] === null ? 'null' : $sub[5]).",$sub[6]);");
		if (!$r) $result = false;
	}
	return [ 'status'=>$result ? 'success' : 'failed', 'subjects'=>$subjects ];
}

/**
 * 特定の授業日の休講/補講/教室変更を取得
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURLのインスタンス
 * @param array $query POSTデータ
 * @return array 成功したかどうか、教科一覧の連想配列
 */
function getSubjectChange(mysqli $link, $curl, array $query): array {
	//セッションデータ利用を開始してURLやCookieが保管されているか確認、なければMySQLから取得
	setTempToSession($link, 'portal_cookie');
	setTempToSession($link, 'subject_change_url');
	//日付を用意
	$datetime = new DateTime($query['date']);
	$date = urlencode($query['date']);
	$year = date_format($datetime, 'Y');
	$month = date_format($datetime, 'n');
	$day = date_format($datetime, 'j');
	//cURLで千葉大学シラバスの検索結果を取得
	session_start();
	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://cup.chiba-u.jp/campusweb/campussquare.do',
		CURLOPT_REFERER => $_SESSION['subject_change_url'],
		CURLOPT_COOKIE => $_SESSION['portal_cookie'],
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => url_extract($_SESSION['subject_change_url']).
			"&dispType=list&dispData=chg&_eventId_search=+%E8%A1%A8+%E7%A4%BA+%E3%81%99+%E3%82%8B&startDay=$date&startDay_year=$year&startDay_month=$month&startDay_day=$day&endDay=$date&endDay_year=$year&endDay_month=$month&endDay_day=$day&_rishuchuFlg=1&kyokanCode=&shozokuCode=",
		CURLOPT_HEADER => false,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_CONNECTTIMEOUT => 0,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
	]);
	session_write_close();
	//cURLを実行
	$res = curl_exec($curl);
	//各種情報を取得
	$info = curl_getinfo($curl);
	//セッション切れをチェック
	if (!portal_session_check($res, $info)) {
		portal_cookie_create($link, $curl);
		portal_url_get($link, $curl, 'subject_change_url');
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
			$sub[] = shape_line($tr->find("td:eq($j)")->text());
		//データなしの場合、配列は空で終了
		if ($sub[0] === '該当するデータはありません') break;
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
		$sub[] = shape_line($tr->find('td:eq(1)')->text());
	}
}



/**
 * みんなのキャンパスへログインしてセッション取得、MySQLに保存
 *
 * @param mysqli $link MySQLのインスタンス
 * @param $curl cURLのインスタンス
 * @return void
 */
function createMincamSession(mysqli $link, $curl) {
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
	for ($i = 0; $i < $cookieCount; $i++) {
		$start = strpos($header, 'Set-Cookie: pitto', $start + 1) + 12;
		$end = strpos($header, ';', $start);
		$c[] = substr($header, $start, $end - $start);
	}
	$cookie = implode('; ', $c);
	//MySQLに一時的に保存
	maria_query($link, "UPDATE chibasys.temp SET data='$cookie' WHERE name='mincam_cookie';");
	//セッションに保管
	session_start();
  $_SESSION['mincam_cookie'] = $cookie;
  session_write_close();
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
	//クッキーがセッションにあるかチェック
  setTempToSession($link, 'mincam_cookie');
	//セッションデータ利用開始
	session_start();
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
	if (count($doc->find('.login')) > 0) {
    createMincamSession($link, $curl);
    return getMincamData($link, $curl, $page);
  }

	$data = [];
	//.listの数=投稿数
	$count = count($doc->find('#apartValue .list'));
	//投稿ごとにデータを整形、dataに追加
  for($i = 0; $i < $count; $i++) {
    $section = $doc->find('#apartValue .list:eq('.$i.')');
    $title = $section->find('.lecture')->text();
    $university = $section->find('.college span:eq(0)')->text();
    $faculty = $section->find('.college span:eq(1)')->text();
    $department = $section->find('.college span:eq(2)')->text();
    /**/$teacherUrl = mb_convert_encoding(urldecode($section->find('.college span:eq(3) a')->attr('href')), 'UTF-8', 'EUC-JP');
    /**/$teacherQuery = mb_substr($teacherUrl, mb_strpos($teacherUrl, '?') + 1);
    /**/$teacherQueryData = explode('&', $teacherQuery);
    foreach ($teacherQueryData as $d) {
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
function addMincamData(mysqli $link, array $data) {
	$query = '';
	foreach ($data as $d)
    $query += "INSERT IGNORE INTO chibasys.mincam VALUES ('$d[title]', '$d[university]', '$d[faculty]', '$d[department]', ".
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
  if (isset($q['title']) && $q['title'] !== '') {
    $title = explode(' ', $q['title']);
    foreach ($title as $t)
      $where[] = "title LIKE '%$t%'";
	}
	//teacherは教師名がカンマ区切り、姓名がスペース区切りを想定
  if (isset($q['teacher']) && $q['teacher'] !== '') {
		$teacher = explode(',', $q['teacher'])[0];
		//teacher_only_lastがtrueの時は姓のみ使用し、マッチ率を上げる
		if (isset($q['teacher_only_last']) && $q['teacher_only_last'] === 'true')
			$teacher = explode(' ', $teacher)[0];
		//スペースがあるとき、姓名と考えて完全一致で検索
		if (mb_strpos($teacher, ' ') !== false) {
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
  $result = maria_query($link, 'SELECT * FROM chibasys.mincam WHERE '.implode(' AND ', $where).' ORDER BY id DESC;');
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
	if (count($data) === 4) $data[1] = $data[2];
	//あらかじめ素のデータを用意
  $result = [ 'text'=>'', 'lastUpdated'=>null ];
	//セッションデータ利用開始
	session_start();
	//ログイン済みの時はメモを取得
	if (isset($_SESSION['id'])) {
		$memo = mysqli_fetch_assoc(maria_query($link, "SELECT text, lastUpdated FROM chibasys.memo WHERE id='$_SESSION[id]' and nendo='$data[0]' and jikanwaricd='$data[1]';"));
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
	if (count($data) === 4) $data[1] = $data[2];
	//あらかじめ$resultはfalseで定義しておく
	$result = false;
	//セッションデータ利用開始
	session_start();
	//ログイン済みの時
	if (isset($_SESSION['id'])) {
		//テキストがない場合は、レコードを消去
    if ($query['text'] === '')
      $result = maria_query($link, "DELETE FROM chibasys.memo WHERE id='$_SESSION[id]' and nendo='$data[0]' and jikanwaricd='$data[1]';");
		//テキストがある場合はまずSELECTしてレコードの存在をチェックし、INSERTかUPDATEかを使い分ける
		else {
      $r = mysqli_fetch_assoc(maria_query($link, "SELECT text, lastUpdated FROM chibasys.memo WHERE id='$_SESSION[id]' and nendo='$data[0]' and jikanwaricd='$data[1]';"));
      if ($r) $result = maria_query($link, "UPDATE chibasys.memo SET text='".mysqli_real_escape_string($link, $query['text'])."', lastUpdated=NOW() WHERE id='$_SESSION[id]' and nendo='$data[0]' and jikanwaricd='$data[1]';");
      else $result = maria_query($link, "INSERT INTO chibasys.memo (id, nendo, jikanwaricd, text, lastUpdated) VALUES ('$_SESSION[id]', '$data[0]', '$data[1]', '".mysqli_real_escape_string($link, $query['text'])."', NOW());");
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
	if (count($data) === 4) $data[1] = $data[2];
	//標準の取得数は30
	$amount = 30;
	//reloadがtrueのときは再取得
	if ($query['reload'] === 'true') {
		//数は今まで読み込んだ数全て
		$amount = $query['index'] + 1;
		//始まりのインデックスは初めの0に
		$query['index'] = 0;
	}
	//前年度を取得するかどうかで挙動を変える(未実装)
	if ($query['all_nendo'] === 'true')
		$result = maria_query($link, "SELECT num, name, text, datetime, nendo FROM chibasys.comment WHERE jikanwaricd='$data[1]' ORDER BY datetime DESC LIMIT $amount OFFSET $query[index];");
	else
		$result = maria_query($link, "SELECT num, name, text, datetime FROM chibasys.comment WHERE nendo=$data[0] AND jikanwaricd='$data[1]' ORDER BY datetime DESC LIMIT $amount OFFSET $query[index];");
	$comments = [];
	//正しくクエリが実行されたとき
	if ($result)
		while ($row = mysqli_fetch_assoc($result)) {
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
	if (count($data) === 4) $data[1] = $data[2];
	//コメントの最大番号を取得し、今回の番号を決める
	$result = maria_query($link, "SELECT MAX(num) FROM chibasys.comment WHERE nendo=$data[0] AND jikanwaricd='$data[2]';");
	//コメントが既にあったときはその最大値に+1
	if ($result) $num = intval(mysqli_fetch_assoc($result)['MAX(num)']) + 1;
	//コメントがまだないときは1
	else $num = 1;
	//セッションデータ利用開始
	session_start();
	//ログイン済みの時、コメントを投稿
	if (isset($_SESSION['id']))
    maria_query($link, "INSERT INTO chibasys.comment (id, num, name, text, datetime, ip, nendo, jikanwaricd) VALUES ('$_SESSION[id]', $num, '".(trim($query['name']) === '' ? '名無しの千葉大生' : trim($query['name']))."', '".
			mysqli_real_escape_string($link, $query['text'])."', NOW(), '$_SERVER[REMOTE_ADDR]', $data[0], '$data[1]');");
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
	if (count($data) === 4) $data[1] = $data[2];
	//あらかじめ$resultはfalseで定義しておく
	$result = false;
	//セッションデータ利用開始
	session_start();
	//ログイン済みの時
	if (isset($_SESSION['id'])) {
		//追加の時はINSERT文を実行
		if ($query['bool'] === 'true')
			$result = maria_query($link, "INSERT INTO chibasys.favorite (id, nendo, jikanwaricd) VALUES ('$_SESSION[id]', $data[0], '$data[1]');");
		//削除の時はDELETE文を実行
		else
			$result = maria_query($link, "DELETE FROM chibasys.favorite WHERE id='$_SESSION[id]' AND nendo=$data[0] AND jikanwaricd='$data[1]';");
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
	if (isset($_SESSION['id'])) {
		//IDに紐づくお気に入り一覧を取得
		$result = maria_query($link, "SELECT * FROM chibasys.favorite WHERE id='$_SESSION[id]';");
		//セッションデータ利用終了
		session_write_close();
		//正しくクエリが実行されたとき
    if ($result)
			while ($row = mysqli_fetch_assoc($result)) {
				//シラバスコードを生成、配列に追加
				$list[] = "$row[nendo]-$row[jikanwaricd]";
				//キャッシュされたシラバスデータを取得し、連想配列に追加
				$data[] = mysqli_fetch_assoc(maria_query($link, "SELECT term, time, credit, name, teacher, summary FROM chibasys.syllabus_$row[nendo] WHERE jikanwaricd='$row[jikanwaricd]';"));
			}
	}
	//ログインしていないとき、すぐにセッションデータ利用終了
  else session_write_close();
	return [ 'status'=>$result ? 'success' : 'failed', 'list'=>$list, 'data'=>$data, 'changeFavorite'=>$changeFavorite ];
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
	//「Created By chibasys」が入ったイベント一覧を取得
	$result = $service->events->listEvents('primary', [
		'q' => 'Created By', 'maxResults' => 2500
	]);
	$data = [];
  foreach ($result->getItems() as $event) {
		//_が入った個々のイベントは無視する
		if (strpos($event->getId(), '_') !== false) continue;
		//idをbase32hexからデコードして&区切りで配列に戻す
    //UNIX時間&シラバスコード&ターム&曜時&単位数&★ターム&★曜日
		$id = explode('&', Encoding::base32HexDecode($event->getId()));
		//$idの要素数が6以上の時は連想配列に追加
    if (count($id) >= 6) {
			$code = explode('-', $id[1]);
			if (count($code) === 4)
				$code = "$code[0]-$code[2]";
			else
				$code = $id[1];
  		$data[] = [ 'id'=>$event->getId(), 'add'=>date(DateTime::ATOM, $id[0]), 'nendo'=>explode('-', $id[1])[0], 'code'=>$code, 'term'=>$id[2], 'time'=>$id[3], 'credit'=>intval($id[4]), 'index'=>[ 'term'=>$id[5], 'time'=>$id[6] ],
				'name'=>$event['summary'], 'room'=>str_replace('千葉大学 ', '', $event['location']), 'start'=>$event['start']['dateTime'], 'end'=>$event['end']['dateTime'], 'notification'=>(count($event['reminders']['overrides']) > 0) ];
		}
		//そうでない場合はなかったものとして処理 !!!!!!!!!!
		else
      continue;
  		//$data[] = [ 'id'=>$event->getId(), 'name'=>$event['summary'], 'room'=>$event['location'], 'start'=>$event['start']['dateTime'], 'end'=>$event['end']['dateTime'], 'notification'=>(count($event['reminders']['overrides']) > 0) ];
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
	if (date('w') === '0') {
		//日曜日のときは1週ずらして考える
		$min = date(DateTime::ATOM, strtotime('sunday this week'));
		$max = date(DateTime::ATOM, strtotime('sunday next week'));
	}
	else{
		//月曜から土曜日は通常通り考える
		$min = date(DateTime::ATOM, strtotime('sunday previous week'));
		$max = date(DateTime::ATOM, strtotime('sunday this week'));
	}
	//「Created By chibasys」が入ったイベント一覧をSingleEvent:trueとして今週のみ取得
	$result = $service->events->listEvents('primary', [
		'q'=>'Created By', 'maxResults'=>2500, 'singleEvents'=>true, 'timeMin'=>$min, 'timeMax'=>$max
	]);
	$data = [];
  foreach ($result->getItems() as $event) {
		//_があるときのみそれより前の文字列をidとしてbase32hexからデコードして&区切りで配列に戻す
		$id = explode('&', Encoding::base32HexDecode((strpos($event['id'], '_') !== false ? explode('_', $event['id'])[0] : $event['id'])));
		//$idの要素数が6以上の時は連想配列に追加
		if (count($id) >= 6) {
			$code = explode('-', $id[1]);
			if (count($code) === 4)
				$code = "$code[0]-$code[2]";
			else
				$code = $id[1];
			$data[] = [ 'add'=>date(DateTime::ATOM, $id[0]), 'code'=>$code, 'term'=>$id[2], 'time'=>$id[3], 'credit'=>intval($id[4]), 'index'=>[ 'term'=>$id[5], 'time'=>$id[6] ],
				'name'=>$event['summary'], 'room'=>str_replace('千葉大学 ', '', $event['location']), 'start'=>$event['start']['dateTime'], 'end'=>$event['end']['dateTime']  ];
		}
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
	//data: term (startDate endDate) time (startTime endTime) code name room description

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
	else if (strpos($data['term'], '･') !== false) {
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
	else if (mb_strpos($data['time'], ',') !== false) {
		//「,」区切りで時間を配列に分ける
		$timeList = explode(',', $data['time']);
		$dayOfWeekList = [];
		//時間を曜日とその中に時限の数字を入れ、連想配列化
		foreach ($timeList as $t) {
			//曜日を抽出
			$dow = mb_substr($t, 0, 1);
			//曜日の配列がなければ作成
			if (!array_key_exists($dow, $dayOfWeekList)) $dayOfWeekList[$dow] = [];
			//時限を追加
			$dayOfWeekList[$dow][] = intval(mb_substr($t, 1, 1));
		}
		//曜日ごとに処理
		foreach ($dayOfWeekList as $dow => $list) {
			//ソートする
			asort($list);
			$startHour = -1;
			$startIndex = -1;
			for ($i = 0; $i < count($list); $i++) {
				//初めは有無を言わさず時間の始まりとして代入
				if ($startHour === -1 && $startIndex === -1) {
					$startHour = $list[$i];
					$startIndex = $i;
				}
				//配列の最後or次との時間が1時間以上空き→時間登録
				if ($i + 1 === count($list) || $list[$i + 1] - $list[$i] > 1) {
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
	for ($i = 0; $i < count($terms); $i++) {
		for ($j = 0; $j < count($times); $j++) {
			//元のIDの生成
			//UNIX時間&シラバスコード&ターム&曜時&単位数&★ターム&★曜日
			//0000000000&2000-XX-XXXXXX-ja_JP&T1&月1&2&0&0
			$id = "$_SERVER[REQUEST_TIME]&$data[code]&$data[term]&$data[time]&$data[credit]&$i&$j";
			//開始日時のDateTimeインスタンスを生成
			$startDate = new DateTime($terms[$i]['start']);
			$allDay = false;
			$everyDay = false;
			//dayがallのとき、終日かつ毎日に
			if ($times[$j]['day'] === 'all') {
				$allDay = true;
				$everyDay = true;
			}
			//dayがtimeのとき、毎日に
			else if ($times[$j]['day'] === 'time') {
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
				'location' => ($data['room'] === '' ? '' : '千葉大学 '.$data['room']), //予定の位置
				'description' => $data['description']."シラバスの詳細ページはこちら\nhttps://chibasys.xperd.net/syllabus?".$data['code']."\nCreated by chibasys",
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
					'url' => 'https://chibasys.xperd.net/syllabus?'.$data['code'], 'title' => $data['name'].'の詳細 -チバシス-'
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
    if ($query['bool'] === 'true') {
      $event['reminders'] =
        [ 'useDefault' => false, 'overrides' => [ [ 'method' => 'popup', 'minutes' =>
          (substr($event['start']['dateTime'], 11, 5) === '12:50' ? 50 : 10) ] ] ];
		}
		//通知を無効に変更するとき
    else if ($query['bool'] === 'false') {
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
  $result = maria_query($link, "UPDATE chibasys.user SET notification=".($bool ? 1 : 0)." WHERE id='$_SESSION[id]';");
	//セッションデータ利用終了
	session_write_close();
	return [ 'status'=>($result ? 'success' : 'failed') ];
}
?>