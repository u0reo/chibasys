<?php
	$maria;
	$curl;

	const jikanwariShozoku = [
		''=>'指定なし', 'G1'=>'普遍教育', 'L1'=>'文学部', 'E1'=>'教育学部', 'A1'=>'法経学部', 'B1'=>'法政経学部',
		'S1'=>'理学部', 'S11'=>'数学・情報数理学科', 'S12'=>'物理学科', 'S13'=>'化学科', 'S14'=>'生物学科',
		'S15'=>'地球科学科', 'S18'=>'先進科学プログラム', 'M1'=>'医学部', 'M11'=>'医学科', 'P1'=>'薬学部', 'P13'=>'薬学科',
		'P14'=>'薬科学科', 'N1'=>'看護学部', 'N11'=>'看護学科', 'T1'=>'工学部', 'T1V'=>'総合工学科','T1V1'=>'建築学コース',
		'T1V2'=>'都市環境システムコース', 'T1V3'=>'デザインコース', 'T1V4'=>'機械工学コース', 'T1V5'=>'医工学コース',
		'T1V6'=>'電気電子工学コース', 'T1V7'=>'物質科学コース', 'T1V8'=>'共生応用化学コース', 'T1V9'=>'情報工学コース',
		'T1E'=>'都市環境システム学科', 'T1K'=>'先進科学プログラム', 'T1K2'=>'工学部先進科学プログラム(フロンティア)',
		'T1L'=>'メディカルシステム工学科', 'T1M'=>'共生応用化学科Aコース', 'T1N'=>'建築学科', 'T1P'=>'デザイン学科',
		'T1Q'=>'機械工学科', 'T1R'=>'電気電子工学科', 'T1S'=>'ナノサイエンス学科', 'T1T'=>'画像科学科', 'T1U'=>'情報画像学科',
		'T1F'=>'デザイン工学科Aコース', 'T1F4'=>'建築コース', 'H1'=>'園芸学部', 'Z1'=>'国際教養学部', 'Z11'=>'国際教養学科',
		'E2'=>'教育学研究科', 'E21'=>'学校教育専攻', 'E215'=>'学校心理学コース', 'E216'=>'発達教育科学コース',
		'E22'=>'国語教育専攻', 'E23'=>'社会科教育専攻', 'E24'=>'数学教育専攻', 'E25'=>'理科教育専攻', 'E26'=>'音楽教育専攻',
		'E27'=>'美術教育専攻', 'E28'=>'保健体育専攻', 'E2A'=>'家政教育専攻', 'E2B'=>'英語教育専攻', 'E2C'=>'養護教育専攻',
		'E2D'=>'学校教育臨床専攻', 'E2E'=>'カリキュラム開発専攻', 'E2F'=>'特別支援専攻', 'E2G'=>'スクールマネジメント専攻',
		'E2H'=>'学校教育科学専攻', 'E2H1'=>'教育発達支援系', 'E2H2'=>'教育開発臨床系', 'E2I'=>'教科教育科学専攻',
		'E2I1'=>'言語・社会系', 'E2I2'=>'理数・技術系', 'E2I3'=>'芸術・体育系', 'S2'=>'理学研究科', 'S21'=>'基盤理学専攻',
		'S211'=>'数学・情報数理学コース', 'S212'=>'物理学コース', 'S213'=>'化学コース', 'S22'=>'地球生命圏科学専攻',
		'S221'=>'生物学コース', 'S222'=>'地球科学コース', 'S23'=>'基盤理学専攻', 'S231'=>'数学・情報数理学コース',
		'S232'=>'物理学コース', 'S233'=>'化学コース', 'S24'=>'地球生命圏科学専攻', 'S241'=>'生物学コース',
		'S242'=>'地球科学コース', 'N2'=>'看護学研究科', 'N21'=>'看護学専攻', 'N265'=>'国際プログラム(訪問)',
		'N266'=>'国際プログラム(看護管理)', 'N267'=>'国際プログラム(看護病態)', 'T2'=>'工学研究科', 'T21'=>'建築・都市科学専攻',
		'T211'=>'建築学コース', 'T212'=>'都市環境システムコース', 'T22'=>'デザイン科学専攻', 'T221'=>'デザイン科学コース',
		'T23'=>'人工システム科学専攻', 'T231'=>'機械系コース', 'T232'=>'電気電子系コース', 'T233'=>'メディカルシステムコース',
		'T24'=>'共生応用化学専攻', 'T241'=>'共生応用化学コース', 'T25'=>'建築・都市科学専攻', 'T251'=>'建築学コース',
		'T252'=>'都市環境システムコース', 'T26'=>'デザイン科学専攻', 'T261'=>'デザイン科学コース', 'T27'=>'人工システム科学専攻',
		'T271'=>'機械系コース', 'T272'=>'電気電子系コース', 'T273'=>'メディカルシステムコース', 'T28'=>'共生応用化学専攻',
		'T281'=>'共生応用化学コース', 'H2'=>'園芸学研究科', 'I2'=>'人文社会科学研究科', 'I21'=>'地域文化形成専攻',
		'I213'=>'言語行動', 'I22'=>'公共研究専攻', 'I221'=>'公共思想制度研究', 'I222'=>'共生社会基盤研究',
		'I23'=>'社会科学研究専攻', 'I232'=>'経済理論・政策学(経)', 'I233'=>'経済理論・政策学(金)', 'I24'=>'総合文化研究専攻',
		'I241'=>'言語構造', 'I243'=>'人間行動', 'I25'=>'先端経営科学専攻', 'I26'=>'公共研究専攻', 'I261'=>'公共哲学',
		'I27'=>'社会科学研究専攻', 'I28'=>'文化科学研究専攻', 'I281'=>'比較言語文化', 'Y2'=>'融合科学研究科',
		'Y21'=>'ナノサイエンス専攻', 'Y211'=>'ナノ物性コース', 'Y212'=>'ナノバイオロジーコース', 'Y22'=>'情報科学専攻',
		'Y221'=>'画像マテリアルコース', 'Y222'=>'知能情報コース(前期)', 'Y23'=>'ナノサイエンス専攻',
		'Y231'=>'ナノ物性コース(後期)', 'Y232'=>'ナノバイオロジーコース', 'Y24'=>'情報科学専攻', 'Y241'=>'画像マテリアル(後期)',
		'Y242'=>'知能情報コース', 'J2'=>'医学薬学府', 'J21'=>'総合薬品科学専攻', 'J22'=>'医療薬学専攻', 'J23'=>'環境健康科学専攻',
		'J231'=>'医学領域', 'J232'=>'薬学領域', 'J24'=>'先進医療科学専攻', 'J241'=>'医学領域', 'J242'=>'薬学領域',
		'J25'=>'先端生命科学専攻', 'J251'=>'医学領域', 'J252'=>'薬学領域', 'J26'=>'創薬生命科学専攻', 'J27'=>'医科学専攻',
		'J28'=>'先端医学薬学専攻', 'J281'=>'先端生命(医学)', 'J282'=>'先端生命(薬学)', 'J283'=>'免疫統御(医学)',
		'J284'=>'免疫統御(薬学)', 'J285'=>'先端臨床(医学)', 'J286'=>'先端臨床(薬学)', 'J287'=>'がん先端(医学)',
		'J288'=>'がん先端(薬学)', 'J29'=>'先端創薬科学専攻', 'J2A'=>'先進予防医学共同専攻', 'K2'=>'専門法務研究科',
		'W2'=>'融合理工学府', 'W20'=>'数学情報科学専攻', 'W201'=>'数学・情報数理学コース', 'W202'=>'情報科学コース',
		'W21'=>'地球環境科学専攻', 'W211'=>'地球科学コース', 'W212'=>'リモートセンシングコース',
		'W213'=>'都市環境システムコース', 'W22'=>'先進理化学専攻', 'W221'=>'物理学コース', 'W222'=>'物質科学コース',
		'W223'=>'化学コース', 'W224'=>'共生応用化学コース', 'W225'=>'生物学コース', 'W23'=>'創成工学専攻',
		'W231'=>'建築学コース', 'W232'=>'イメージング科学コース', 'W233'=>'デザインコース', 'W24'=>'基幹工学専攻',
		'W241'=>'機械工学コース', 'W242'=>'医工学コース', 'W243'=>'電気電子工学コース', 'W25'=>'数学情報科学専攻',
		'W251'=>'数学・情報数理学コース', 'W252'=>'情報科学コース', 'W26'=>'地球環境科学専攻', 'W261'=>'地球科学コース',
		'W262'=>'リモートセンシングコース', 'W263'=>'都市環境システムコース', 'W27'=>'先進理化学専攻', 'W271'=>'物理学コース',
		'W272'=>'物質科学コース', 'W273'=>'化学コース', 'W274'=>'共生応用化学コース', 'W275'=>'生物学コース',
		'W28'=>'創成工学専攻', 'W281'=>'建築学コース', 'W282'=>'イメージング科学コース', 'W283'=>'デザインコース',
		'W29'=>'基幹工学専攻', 'W291'=>'機械工学コース', 'W292'=>'医工学コース', 'W293'=>'電気電子工学コース',
		'D2'=>'人文公共学府', 'D21'=>'人文科学専攻', 'D22'=>'公共社会科学専攻', 'D23'=>'人文公共学専攻',
		'H3'=>'園芸学部園芸別科', 'C1'=>'留学生', 'G2'=>'大学院共通教育'
	];

	const class_type = [
		''=>'指定なし', 'A'=>'個別領域科目 特論科目', 'B'=>'共通基礎科目', 'B1'=>'共通基礎科目(必修)', 'B2'=>'共通基礎科目(選択必修)',
		'C'=>'共通科目', 'D'=>'実習科目', 'E'=>'実践研究指導科目', 'F'=>'実践科目',	/*ない*/'G'=>'専門教育科目', 'G1'=>'共通基礎科目',
		'G2'=>'専門基礎科目', 'G3'=>'専門科目', 'G4'=>'自由科目', 'H'=>'専門科目', 'H1'=>'専門科目', 'H2'=>'特別研究',
		'H3'=>'自コース', /*結合しない*/'I'=>'必修科目', 'I1'=>'必修科目A', 'I2'=>'必修科目B', 'I3'=>'必修科目C',
		'J'=>'教育実践に関する科目', 'K'=>'普遍教育科目', 'K1'=>'スポーツ・健康科目', 'K2'=>'外国語科目', 'K3'=>'情報リテラシー科目',
		'K4'=>'教養コア科目', 'K5'=>'教養展開科目', 'L'=>'系統講義科目', 'M'=>'自由選択', 'M1'=>'自由選択科目', 'N'=>'論文指導科目',
		/*ない*/'O'=>'講義科目', 'O1'=>'総合薬品科学科目', 'P'=>'選択科目', 'P1'=>'所属コース外の科目', 'P2'=>'所属コース科目', 'Q'=>'その他'
	];

	const time_day = [ ''=>'全て', '1'=>'月', '2'=>'火', '3'=>'水', '4'=>'木', '5'=>'金', '6'=>'土' ];
	
	use Hashids\Hashids;

	/**
	 * 各種設定と初期化
	 *
	 * @param bool $accessable
	 * @return void
	 */
	function init(bool $accessable = false): void {
		global $maria, $curl;
		//ダイレクトアクセス禁止
		if (!$accessable) no_direct_access();
		//エラー表示オン
		ini_set('display_errors', 1);
		//タイムアウト60秒
		set_time_limit(60);
		//Google Client 関連のライブラリの読み込み
		require_once('vendor/autoload.php');
		//PHP Query ライブラリの読み込み
		//require_once('phpQuery-onefile.php');
		//Maria DBへ接続
		$maria = mysqli_connect('localhost', 'chibasys', 'P8IpIqW2Zb8CZNCC', 'chibasys');
		//cURLの初期化
		$curl = curl_init();
	}

	/**
	 * 終了処理
	 *
	 * @return void
	 */
	function finalize(): void {
		global $maria, $curl;
		//Maria DBから切断
		mysqli_close($maria);
		//cURLの終了
		curl_close($curl);
	}

	/**
	 * 直接アクセスを禁止、直接アクセスしたときは400 Bad Requestに
	 *
	 * @return void
	 */
	function no_direct_access(): void {
		$files = get_included_files();
		if (array_shift($files) === __FILE__) {
			http_response_code(400);
			exit();
		}
	}

	/**
	 * HTMLの改行コードを普通の改行に変換
	 * http://hi.seseragiseven.com/archives/559
	 *
	 * @param string $string <br>などを含む文字列
	 * @return string \nに置換された文字列
	 */
	function br2n($string): string {
		return preg_replace('/<br[[:space:]]*\/?[[:space:]]*>/i', "\n", $string);
	}

	/**
	 * 文字列を整形(スペースや改行削除、全角英字を半角英字へ、半角カナを全角カナへ、最初最後のトリム)
	 *
	 * @param string $string 汚い文字列
	 * @return string 整形された文字列
	 */
	function shape_line($string): string {
		return trim(shape_punc(shape_space(mb_convert_kana(preg_replace('/(?:\n|\r|\r\n)/', '', br2n($string)), 'asKV'))));
	}

	/**
	 * 文字列を整形(スペースや改行削除、全角英字を半角英字へ、半角カナを全角カナへ、最初最後のトリム)
	 *
	 * @param string $string 汚い文字列
	 * @return string 整形された文字列
	 */
	function shape_content($string): string {
		return trim(shape_space(mb_convert_kana(br2n($string), 'asKV')));
	}

	/**
	 * 日本語の文末のみ,.ゅを、。にする
	 *
	 * @param string $string 元の文字列
	 * @return string 整形済みの文字列
	 */
	function shape_punc(string $string): string {
		/*$byte = strlen($string);
		$len = mb_strlen($string);
		$jap = ($byte - $len) / 2;
		$eng = $len - $jap;
		if ($jap >= $eng) return str_replace('.', '。', str_replace(',', '、', str_replace('. ', '。', str_replace(', ', '、', $string))));
		else return $string;*/
		return preg_replace('/([!-~])。/', '$1. ', preg_replace('/([!-~])、/', '$1, ', str_replace([' . ',' .', '. ', '.'], '。', str_replace([' , ', ' ,', ', ', ','], '、', $string))));
	}

	/**
	 * 続くスペースや全角スペースを半角スペース一つにする
	 *
	 * @param string $string 元の文字列
	 * @return string 整形済みの文字列
	 */
	function shape_space(string $string): string {
		return preg_replace('/\s+/s', ' ', str_replace('　', ' ', $string));
	}

	/**
	 * URLの/の後から#の前までを返却
	 *
	 * @return string 現在のURLのクエリ ex) syllabus?2000-AA-BBBBBB-ja_JP
	 */
	function get_request(): string {
		$index = strrpos($_SERVER['REQUEST_URI'], '/') + 1;
		$pos = strpos($_SERVER['REQUEST_URI'], '#', $index);
		if ($pos === false)
			return substr($_SERVER['REQUEST_URI'], $index);
		else
			return substr($_SERVER['REQUEST_URI'], $index, $pos - $index + 1);
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
	 * ようこそ画面へ遷移、最後にexit()実行
	 *
	 * @param string $query 追加したいクエリ ex)'?error=...'
	 * @return void
	 */
	function locate_welcome(string $query = ''): void {
		header('location: /welcome'.$query);
	}

	/**
	 * ログイン画面へ遷移、最後にexit()実行
	 *
	 * @return void
	 */
	function locate_login($client = null): void {
		if (!$client) $client = google_client_create();
		header('location: '.$client->createAuthUrl());
	}

	/**
	 * Google_Clientのインスタンスを取得
	 *
	 * @return Google_Client Clientのインスタンス
	 */
	function google_client_create(): Google_Client {
		$client = new Google_Client();
		$client->setApplicationName('chibasys by reolink');
		$client->setAuthConfig(__DIR__.'/client_secret.json');
		//$client->setAccessType("offline");
		$client->setIncludeGrantedScopes(true);
		$client->addScope(Google_Service_Calendar::CALENDAR);
		$client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);
		$client->addScope(Google_Service_People::USERINFO_PROFILE);
		//$client->setApprovalPrompt('force');
		$client->setRedirectUri('https://'.$_SERVER['HTTP_HOST'].'/auth?mode=success');
		$client->setDeveloperKey('AIzaSyAY3LxdQdnM1s2P7ztXiCkK_95YDs-Tl-w');
		session_start();
		if (isset($_SESSION['accessToken'])) $client->setAccessToken($_SESSION['accessToken']);
		session_write_close();
		return $client;
	}

	const ERROR_CURL_FAILED = -3;
	const ERROR_DATA_NOT_FOUND = -2;
	const ERROR_SQL_FAILED = -1;
	const ERROR_NO_LOGIN = 1;
	const ERROR_GOOGLE_EXPIRED = 2;
	const ERROR_USER_NOT_FOUND = 3;
	const ERROR_SYLLABUS_NOT_FOUND = 4;
	const ERROR_MINCAM_DOWN = 5;
	const ERROR_PORTAL_NO_LOGIN = 10;
	const ERROR_PORTAL_WRONG_ID_PASS = 11;
	const ERROR_PORTAL_DOWN = 12;
	const ERROR_PORTAL_LOGIN_ADDITIONAL = 15;
	const ERROR_PORTAL_REGISTER_SUBJECT_ADDITIONAL = 16;
	const ERROR_MESSAGE = [ null, 'ログインされていません', 'Googleで再ログインしてください', 'ユーザーが見つかりません', 'シラバスが見つかりません', 'みんなのキャンパスに接続できません', '6', '7', '8', '9',
		'学生ポータルに登録されていません', '学生ポータルのIDかパスワードが違います', '学生ポータルに接続できません', '13', '14', '', '' ];

	function error_data(int $error_code, string $msg = '', array $data = []) {
		$data['error_code'] = $error_code;
		$data['error_message'] = ($error_code > 0 ? ERROR_MESSAGE[$error_code].$msg : '内部エラー')." (コード:$error_code)";
		return $data;
	}

	/**
	 * Undocumented function
	 *
	 * @param string $query SQLクエリ
	 * @param bool $all 常にログ出力する
	 * @return mysqli_result|bool クエリ実行結果
	 */
	function maria_query(string $query, $all = false) {
		global $maria;
		$r = mysqli_query($maria, $query);
		if (!$r || $all)
			error_log("\n[".date('Y-m-d H-i-s')."]ERROR: ".mysqli_error($maria)."\nQUERY: $query", "3", "/var/www/mysql-error.log");
		return $r;
	}

	/**
	 * 一時データをMySQLから取得
	 *
	 * @param string $name 一時データの名前
	 * @param ?string $user_id (Googleの)ユーザーID
	 * @return mixed 一時データ
	 */
	function temp_load(string $name, ?string $user_id) {
		$result = maria_query("SELECT data FROM chibasys.temp WHERE name='$name' AND user='$user_id';");
		if (!$result) return error_data(ERROR_SQL_FAILED);
		if (mysqli_num_rows($result) !== 1) return '';//error_data(ERROR_DATA_NOT_FOUND);
		return mysqli_fetch_assoc($result)['data'];
	}

	/**
	 * 一時データをMySQLとセッションに保存
	 *
	 * @param string $name 一時データの名前
	 * @param string $data 一時データの内容
	 * @param ?string $user_id GoogleのIDまたは'general'や'syllabus'
	 * @return bool 保存が成功したかどうか
	 */
	function temp_save(string $name, string $data, ?string $user_id): bool {
		//userIDが空なら終了  
		if (!$user_id) return false;

		//既にデータがあるかどうかで場合分け
		$result = maria_query("SELECT data FROM chibasys.temp WHERE name='$name' AND user='$user_id';");
		if ($result) {
			if (mysqli_num_rows($result) === 1)
				return (bool)maria_query("UPDATE chibasys.temp SET data='$data' WHERE name='$name' AND user='$user_id';");
			else
				return (bool)maria_query("INSERT INTO chibasys.temp (name, user, data) VALUES ('$name', '$user_id', '$data');");
		}
		else
			return false;

		//セッションに保管
		/*session_start();
		$_SESSION[$user_id === 'general' ? $name : "user_$name"] = $data;
		session_write_close();*/
	}

	/**
	 * ユーザーデータを保存
	 *
	 * @param ?string $user_id (Googleの)ユーザーID
	 * @param array $query POSTデータ
	 * @return array|bool 通常はtrue
	 */
	function userdata_save(?string $user_id, array $query) {
		global $maria;

		foreach ($query as $key => $value) $query[$key] = mysqli_real_escape_string($maria, $value);
		if (isset($query['studentPass']) && $query['studentPass'] === '') unset($query['studentPass']);
		//$queryにgoogle_idを追加
		session_start();
		if (isset($_SESSION['google_id'])) $query['google_id'] = $_SESSION['google_id'];
		session_write_close();

		$result = maria_query("SELECT * FROM chibasys.user WHERE user_id='$user_id';");
		if (!$result) return error_data(ERROR_SQL_FAILED);
		if ($user_id === 'new') {
			$user_id = (new Hashids('e4KrxdB2', 8))->encode(time());
			$result2 = maria_query("INSERT INTO chibasys.user (user_id, register, ".implode(',', array_keys($query)).", notification) VALUES ('$user_id', NOW(), '".implode("','", array_values($query))."', 1);");
			session_start();
			$_SESSION['user_id'] = $user_id;
			session_write_close();
		}
		else if (mysqli_num_rows($result) === 1)
			$result2 = maria_query("UPDATE chibasys.user SET studentName='$query[studentName]', studentSex='$query[studentSex]', studentID='$query[studentID]'".(isset($query['studentPass']) ? ", studentPass='$query[studentPass]'" : '').(isset($query['google_id']) ? ", google_id='$query[google_id]'" : '')." WHERE user_id='$user_id';");
		else
			return error_data(ERROR_USER_NOT_FOUND);
		
		if (!$result2) return error_data(ERROR_SQL_FAILED);
		else return [ 'result'=>true ];
	}

	/**
	 * 項目名の日本語→英語の配列 falseは含めない trueは/で分ける
	 */
	const syllabus_je = ["学科(専攻)・科目の種別等"=>'class_type', "授業科目"=>'name', "授業コード"=>false,
		"科目コード"=>'subject_code', "ナンバリングコード"=>'numbering_code', "授業の方法"=>'method',
		"使用言語"=>'language', "単位数"=>'credit', "時間数"=>'hour', "期別"=>'period', "履修年次/ターム"=>true,
		"曜日・時限"=>'time', "副専攻"=>'sub_major', "副題"=>'sub_title', "受入人数"=>'student_count',
		"担当教員"=>'teacher', "受講対象"=>'target_student', "教室"=>'room',
		"更新日"=>'update_date', "概要"=>'summary', "目的・目標"=>'purpose', "授業計画・授業内容"=>'content',
		"授業外学習"=>'homework', "キーワード"=>'keyword', "教科書・参考書"=>'textbook', "評価方法・基準"=>'evaluation_method',
		"関連科目"=>'related_subject', "履修要件"=>'requirement', "備考"=>'remark', "関連URL"=>'related_url'];

	const syllabus_ej = ["jikanwaricd"=>"授業コード", /*"department"=>"所属学部", "subject"=>"所属学科",
		"course"=>"所属コース等", "class_type"=>"学科(専攻)・科目の種別等", "name"=>"授業科目", "subject_code"=>"科目コード",
		"numbering_code"=>"ナンバリングコード", "method"=>"授業の方法", "language"=>"使用言語", */"credit"=>"単位数",
		"hour"=>"時間数", "period"=>"期別", /*"grade"=>"履修年次", */"term"=>"ターム", "time"=>"曜日・時限",
		/*"sub_major"=>"副専攻", "sub_title"=>"副題", "student_count"=>"受入人数", */"teacher"=>"担当教員",
		"target_student"=>"受講対象", "room"=>"教室", /*"update_date"=>"更新日", */"summary"=>"概要", "purpose"=>"目的・目標",
		"content"=>"授業計画・授業内容", "homework"=>"授業外学習", /*"keyword"=>"キーワード", */"textbook"=>"教科書・参考書",
		"evaluation_method"=>"評価方法・基準", /*"related_subject"=>"関連科目", */"requirement"=>"履修要件", "remark"=>"備考",
		"related_url"=>"関連URL", "detail"=>"授業計画詳細情報"];

	/**
	 * 千葉大学シラバス検索のセッションを取得し、MySQLに保存
	 *
	 * @return array クッキーとURLの配列
	 */
	function portal_real_search_cookie_url_create(): array {
		//cURLで千葉大学シラバスの入口にアクセスし、Cookieを取得
		$site = web('', '', '', 'https://cup.chiba-u.jp/campusweb/campussquare.do?locale=ja_JP&_flowId=SYW3901100-flow', ERROR_PORTAL_DOWN, false);
		if (isset($site['error_code'])) return $site;
		//クッキーを解析
		$c = [];
		$cookieCount = substr_count($site['header'], 'Set-Cookie: ');
		$start = 0;
		for ($i = 0; $i < $cookieCount; $i++) {
			$start = strpos($site['header'], 'Set-Cookie: ', $start + 1) + 12;
			$end = strpos($site['header'], ';', $start);
			$c[] = substr($site['header'], $start, $end - $start);
		}
		$cookie = implode('; ', $c);
		//入力画面へ遷移しておく
		web($cookie, '', '', $site['redirect_url']);
		//一時データとして保存
		temp_save('portal_cookie', $cookie, 'syllabus');
		temp_save('syllabus_url', $site['redirect_url'], 'syllabus');

		return [ 'cookie'=>$cookie, 'referer'=>$site['redirect_url'] ];
	}

	const term_num = [
		1=>['T1','T1-2','T1-3','T1集','T12集','T13集','前','前集'],
		2=>['T2','T1-2','T1-3','T2集','T12集',        '前','前集'],
		3=>['T3',       'T1-3','T3集',        'T13集','前','前集'],
		4=>['T4','T4-5','T4-6','T4集','T45集','T46集','後','後集'],
		5=>['T5','T4-5','T4-6','T5集','T45集',        '後','後集'],
		6=>['T6',       'T4-6','T6集',        'T46集','後','後集']
	];

	/**
	 * MySQLから千葉大学シラバスを検索
	 *
	 * @param array $query POSTデータ
	 * @return array 検索結果の連想配列
	 */
	function portal_search(?string $user_id, array $query): array {
		parse_str($query['query'], $query);
		$where = [];
		if (isset($query['jikanwariShozokuCode']))
			$where[] = "LEFT(jikanwariShozokuCode, ".strlen($query['jikanwariShozokuCode']).") = '$query[jikanwariShozokuCode]'";
		if (isset($query['class_type'])) {
			$w = [ "class_type = '".((strlen($query['class_type']) === 2) ? class_type[substr($query['class_type'], 0, 1)].' ' : '').class_type[$query['class_type']]."'" ];
			if (strlen($query['class_type']) === 1)
				for ($i = 1; ; $i++)
					if (isset(class_type[$query['class_type'].$i])) {
						if ($query['class_type'] === 'I') $w[] = "class_type '".class_type[$query['class_type'].$i]."'";
						else $w[] = "class_type = '".class_type[$query['class_type']].' '.class_type[$query['class_type'].$i]."'";
					}
					else break;
			$where[] = '('.implode(' OR ', $w).')';
		}
		if (isset($query['term'])) {
			$terms = str_split($query['term']);
			if (count($terms) === 1) $term = term_num[intval($terms[0])];
			else if (count($terms) === 2) $term = array_intersect(term_num[intval($terms[0])], term_num[intval($terms[1])]);
			else if (count($terms) === 3) $term = array_intersect(term_num[intval($terms[0])], term_num[intval($terms[1])], term_num[intval($terms[2])]);
			else if (count($terms) === 4) $term = array_intersect(term_num[intval($terms[0])], term_num[intval($terms[1])], term_num[intval($terms[2])], term_num[intval($terms[3])]);
			else if (count($terms) === 5) $term = array_intersect(term_num[intval($terms[0])], term_num[intval($terms[1])], term_num[intval($terms[2])], term_num[intval($terms[3])], term_num[intval($terms[4])]);
			else $terms = null;

			if ($term) $where[] = "(term = '".implode("' OR term = '", $term)."')";
		}
		if (isset($query['grade'])) {
			$grade = str_split($query['grade']);
			$where[] = "(FIND_IN_SET('".implode("', grade) OR FIND_IN_SET('", $grade)."', grade))";
		}
		if (isset($query['day']) || isset($query['hour'])) {
			$time = [];
			if (!isset($query['day'])) {
				foreach (time_day as $k => $v) if ($k !== '') $time[] = $v.$query['hour'];
			}
			else if (!isset($query['hour']))
				foreach ([1,2,3,4,5,6,7] as $v) $time[] = time_day[$query['day']].$v;
			else
				$time[] = time_day[$query['day']].$query['hour'];

			$where[] = "(FIND_IN_SET('".implode("', time) OR FIND_IN_SET('", $time)."', time))";
		}
		global $maria;
		if (isset($query['name'])) {
			foreach (explode(' ', shape_line($query['name'])) as $n)
				$where[] = "name LIKE '%".mysqli_real_escape_string($maria, $n)."%'";
		}
		if (isset($query['teacher']))
			$where[] = "teacher LIKE '%".mysqli_real_escape_string($maria, shape_line($query['teacher']))."%'";
		$msc = microtime(true);
		$result = maria_query("SELECT jikanwaricd, term, time, name, teacher, summary FROM chibasys.syllabus_$query[nendo] WHERE ".implode(' AND ', $where).";");
		$msc = microtime(true) - $msc;
		if (!$result)	return error_data(ERROR_SQL_FAILED);
		$classes = [];
		while ($row = mysqli_fetch_assoc($result)) {
			$row['code'] = "$query[nendo]-$row[jikanwaricd]";
			unset($row['jikanwaricd']);
			$classes[$row['code']] = $row;
		}
		return [ 'sql'=>"SELECT jikanwaricd, term, time, name, teacher, summary FROM chibasys.syllabus_$query[nendo] WHERE ".implode(' AND ', $where).";",
			'classes'=>$classes, 'num'=>mysqli_num_rows($result), 'time'=>$msc ];
	}

	/**
	 * シラバスコードでMySQLから千葉大学シラバスの内容を取得
	 *
	 * @param array $query POSTデータ
	 * @return array シラバス内容と元のURLの連想配列
	 */
	function portal_syllabus_get(array $query): array {
		//シラバスコードを分解
		$code = explode('-', $query['code']);
		if (count($code) === 4) $code[1] = $code[2];
		if (count($code) < 2) return error_data(ERROR_SYLLABUS_NOT_FOUND);

		$result = maria_query("SELECT * FROM chibasys.syllabus_$code[0] WHERE jikanwaricd='$code[1]';");
		if (!$result)	return error_data(ERROR_SQL_FAILED);
		if (mysqli_num_rows($result) !== 1) return error_data(ERROR_SYLLABUS_NOT_FOUND);
		$data = mysqli_fetch_assoc($result);
		if ($data['detail']) $data['detail'] = json_decode($data['detail']);
		return [ 'data'=>$data, 
			'url_ja'=>(strpos('ja_JP', $data['slocale']) !== false ? "https://cup.chiba-u.jp/campusweb/campussquare.do?locale=ja_JP&_flowId=SYW3901100-flow&nendo=$code[0]&jikanwariShozokuCode=$data[jikanwariShozokuCode]&jikanwaricd=$code[1]&slocale=ja_JP" : null), 
			'url_en'=>(strpos('en_US', $data['slocale']) !== false ? "https://cup.chiba-u.jp/campusweb/campussquare.do?locale=ja_JP&_flowId=SYW3901100-flow&nendo=$code[0]&jikanwariShozokuCode=$data[jikanwariShozokuCode]&jikanwaricd=$code[1]&slocale=en_US" : null) ];
	}

	/**
	 * 千葉大学シラバスを検索し、教科一覧を返す→廃止予定
	 *
	 * @param array $query POSTデータ
	 * @return array 教科一覧の連想配列
	 */
	function portal_real_search(?string $user_id, array $query, $cookie = null, $referer = null): array {
		//$data = ['s_no' => '0', '_eventId' => 'search', 'nendo' => 2019, 'kaikoKamokunmLike' => '英語'];
		//'s_no=0&'.substr($baseUrl, strpos($baseUrl, "?") + 1).'&_eventId=search&nendo=2019&jikanwariShozokuCode=&gakkiKubunCode=&kaikoKubunCode=&kyokannmLike=&jikanwaricdLike=&kaikoKamokunmLike=%E8%8B%B1%E8%AA%9E&nenji=&yobi=&jigen=&freeWord=&nbrGakubucd=&nbrGakkacd=&nbrSuijuncd=&fukusenkocd=&syReferOrder=&_displayCount=100';
		//URLやCookieをMySQLから取得
		if (!$cookie) $cookie = temp_load('portal_cookie', 'syllabus');
		if (isset($cookie['error_code'])) return $cookie;
		if (!$referer) $referer = temp_load('portal_syllabus_url', 'syllabus');
		if (isset($referer['error_code'])) return $referer;

		//cURLで千葉大学シラバスの検索結果を取得
		$site = web($cookie, $referer, 's_no=0&'.url_extract($referer).'&_eventId=search&_displayCount=1928&'.$query['query']);
		if (isset($site['error_code'])) return $site;
		//セッション切れをチェック
		if (!portal_session_check($site)) {
			$data = portal_real_search_cookie_url_create();
			return portal_real_search($user_id, $query, $data['cookie'], $data['referer']);
		}
		//PHPQueryのインスタンス生成
		$tbody = phpQuery::newDocument($site['res'])->find('table > tbody');
		$classes = [];
		//trの数=教科数
		$subjectCount = count($tbody->find('tr'));
		for($i=0; $i<$subjectCount; $i++) {
			$tr = $tbody->find('tr:eq('.$i.')');
			$class = [];
			$dataCount = count($tr->find('td')); //0-10
			//trタグ内の一つ一つの改行やスペースを除去
			for($j=1; $j<$dataCount; $j++)
				$class[] = shape_line($tr->find("td:eq($j)")->text());
			//最後のtdにある初めのボタンを取得
			$button = $tr->find('td:eq('.($dataCount - 1).')')->find('input:eq(0)');
			//disabledがあるかどうかで日本語か英語かを判別。日本語優先
			if ($button->attr('disabled') == null) $class[] = 'ja_JP';
			else $class[] = 'en_US';
			//onclickを解析して「jikanwariShozokuCode」を入手
			$refer = $button->attr('onclick');
			$start = strpos($refer, ",") + 2;
			$end = strpos($refer, "'", $start);
			$class[] = substr($refer, $start, $end - $start);
			//教科一覧に追加
			$classes[] = $class;
		}
		//ログイン済みの時、検索履歴に追加
		if (isset($user_id))
		maria_query("INSERT INTO chibasys.history_search VALUES ('$user_id', NOW(), '$query[query]', '$subjectCount');");
		
		return [ 'query'=>$query['query'], 'classes'=>$classes ];
	}

	/**
	 * 千葉大学シラバスを所属学部で検索し、教科一覧を保存する
	 *
	 * @param array $query POSTデータ
	 * @return array POSTデータと追加情報の連想配列
	 */
	function portal_real_search_save(array $query, $cookie = null, $referer = null): array {
		//$data = ['s_no' => '0', '_eventId' => 'search', 'nendo' => 2019, 'kaikoKamokunmLike' => '英語'];
		//'s_no=0&'.substr($baseUrl, strpos($baseUrl, "?") + 1).'&_eventId=search&nendo=2019&jikanwariShozokuCode=&gakkiKubunCode=&kaikoKubunCode=&kyokannmLike=&jikanwaricdLike=&kaikoKamokunmLike=%E8%8B%B1%E8%AA%9E&nenji=&yobi=&jigen=&freeWord=&nbrGakubucd=&nbrGakkacd=&nbrSuijuncd=&fukusenkocd=&syReferOrder=&_displayCount=100';
		//URLやCookieをMySQLから取得
		if (!$cookie) $cookie = temp_load('portal_cookie', 'syllabus');
		if (isset($cookie['error_code'])) return $cookie;
		if (!$referer) $referer = temp_load('portal_syllabus_url', 'syllabus');
		if (isset($referer['error_code'])) return $referer;

		//cURLで千葉大学シラバスの検索結果を取得
		$site = web($cookie, $referer, 's_no=0&'.url_extract($referer).'&_eventId=search&_displayCount=1928&nendo='.$query['nendo'].'&jikanwariShozokuCode='.$query['jikanwariShozokuCode']);
		if (isset($site['error_code'])) return $site;
		//セッション切れをチェック
		if (!portal_session_check($site)) {
			$data = portal_real_search_cookie_url_create();
			return portal_real_search_save($query, $data['cookie'], $data['referer']);
		}
		
		//PHPQueryのインスタンス生成
		$tbody = phpQuery::newDocument($site['res'])->find('table > tbody');
		//trの数=教科数
		$subjectCount = count($tbody->find('tr'));
		for($i=0; $i<$subjectCount; $i++) {
			$tr = $tbody->find('tr:eq('.$i.')');
			$class = [];
			$dataCount = count($tr->find('td')); //0-10
			//$class['nendo'] = shape_line($tr->find("td:eq(0)")->text());

			//最後のtdにある言語選択のボタンを取得
			$button = $tr->find('td:eq('.($dataCount - 1).')')->find('input:eq(0)');
			$button2 = $tr->find('td:eq('.($dataCount - 1).')')->find('input:eq(1)');
			//onclickを解析して「jikanwariShozokuCode」を入手
			$refer = $button->attr('onclick');
			$start = strpos($refer, ",") + 2;
			$end = strpos($refer, "'", $start);
			$class['jikanwariShozokuCode'] = substr($refer, $start, $end - $start);

			$class['jikanwaricd'] = shape_line($tr->find("td:eq(5)")->text());

			//disabledがあるかどうかで日本語か英語かを判別。日本語優先
			$lang = [];
			if ($button->attr('disabled') == null) $lang[] = 'ja_JP';
			if ($button2->attr('disabled') == null) $lang[] = 'en_US';
			$class['slocale'] = implode(',', $lang);

			if (isset($query['department']) && $query['department'] !== '') $class['department'] = $query['department'];
			if (isset($query['subject']) && $query['subject'] !== '') $class['subject'] = $query['subject'];
			if (isset($query['course']) && $query['course'] !== '') $class['course'] = $query['course'];
			
			$result = maria_query("SELECT * FROM chibasys.syllabus_$query[nendo] WHERE jikanwaricd='$class[jikanwaricd]'");
			if (!$result || !mysqli_fetch_assoc($result))
			maria_query("INSERT INTO chibasys.syllabus_$query[nendo] (".implode(',', array_keys($class)).') VALUES (\''.implode('\',\'', array_values($class)).'\');');
		}
		$query['count'] = $subjectCount;

		return $query;
	}

	/**
	 * シラバスの最新データを取得し保存する
	 *
	 * @param int $nendo シラバスの年度
	 * @param int $pos MySQLの上から数えた番号
	 * @return array 取得したデータの連想配列
	 */
	function portal_real_syllabus_save(int $nendo, int $pos): array {
		$result = maria_query("SELECT * FROM chibasys.syllabus_$nendo LIMIT 1 OFFSET $pos");
		if (!$result) return error_data(ERROR_SQL_FAILED);
		if (mysqli_num_rows($result) !== 1) return error_data(ERROR_DATA_NOT_FOUND);
		$query = mysqli_fetch_assoc($result);
		$syllabus = portal_real_syllabus(['nendo'=>$nendo,'jikanwariShozokuCode'=>$query['jikanwariShozokuCode'],
			'jikanwaricd'=>$query['jikanwaricd'],'slocale'=>explode(',', $query['slocale'])[0]]);

		$data = [];
		foreach ($query as $key => $value) $data[$key] = ($value && $value !== '' ? $value : null);
		foreach ($syllabus['detail-1'] as $key => $value) {
			$k = syllabus_je[$key];
			if (!$k) continue;
			if ($k === true){
				$v = explode('/', $value);
				$data['grade'] = str_replace('･', ',', preg_replace('/年|\s/', '', $v[0]));
				$data['term'] = $v[1];
			}
			else
				$data[$k] = $value;
		}
		foreach ($syllabus['detail-2'] as $key => $value) {
			$k = syllabus_je[$key];
			$data[$k] = $value;
		}
		if ($syllabus['detail-3']) $data['detail'] = ($syllabus['detail-3'] ? json_encode($syllabus['detail-3']) : null);

		global $maria;
		$d = [];
		foreach ($data as $k => $v) if (in_array($k, ['time','teacher','keyword'], true)) $data[$k] = str_replace('、', ',', str_replace('。', '.', $v));
		foreach ($data as $k => $v) $d[] = ($k === 'hour' || $k === 'credit' ? ($v ? str_replace(' ', '', "$k=$v") : "$k=null") : ($v ? "$k='".mysqli_real_escape_string($maria, $v)."'" : "$k=null"));
		$result = maria_query("UPDATE chibasys.syllabus_$nendo SET ".implode(',', $d).
			" WHERE jikanwariShozokuCode='$query[jikanwariShozokuCode]' AND jikanwaricd='$query[jikanwaricd]';");
		if ($result)
			return [ 'data'=>$data ];
		else
			return error_data(ERROR_SQL_FAILED);
	}

	/**
	 * シラバスコードで千葉大学シラバスの内容を取得
	 *
	 * @param array $query POSTデータ
	 * @return array シラバス内容の連想配列
	 */
	function portal_real_syllabus(array $query): array {
		//千葉大学シラバスのURLを生成 (ポータル内:https://cup.chiba-u.jp/campusweb/campussquare.do?_flowId=SYW0001000-flow)
		$url = "https://cup.chiba-u.jp/campusweb/campussquare.do?locale=ja_JP&_flowId=SYW3901100-flow&nendo=$query[nendo]&jikanwariShozokuCode=$query[jikanwariShozokuCode]&jikanwaricd=$query[jikanwaricd]&slocale=$query[slocale]";
		//cURLで千葉大学シラバスの情報を取得
		$site = web('', '', '', $url, ERROR_PORTAL_DOWN, false);
		if (isset($site['error_code'])) return $site;

		//Cookieのみ抽出
		$c = [];
		$cookieCount = substr_count($site['header'], 'Set-Cookie: ');
		$start = 0;
		for ($i = 0; $i < $cookieCount; $i++) {
			$start = strpos($site['header'], 'Set-Cookie: ', $start + 1) + 12;
			$end = strpos($site['header'], ';', $start);
			$c[] = substr($site['header'], $start, $end - $start);
		}
		//Cookieの文字列に変換
		$cookie = implode('; ', $c);
		$site = web($cookie, $url, '', $site['redirect_url']);
		if (isset($site['error_code'])) return $site;
		
		//PHPQueryのインスタンス生成
		$doc = phpQuery::newDocument($site['res']);
		//タブごとにインスタンスを生成
		$details1 = table_analysis($doc->find('#tabs-1'), "");
		$details2 = table_analysis($doc->find('#tabs-2'), "\n");
		$details3 = table_analysis_third($doc->find('#tabs-3'));

		return [ 'detail-1'=>$details1, 'detail-2'=>$details2, 'detail-3'=>$details3 ];//, 'redirect'=>$info['url'], 'original'=>$url ];
	}

	/**
	 * 通常のCampusSquareのテーブルを解析
	 *
	 * @param phpQueryObject $tbody テーブル内のHTML
	 * @param string $insert 行間に挟む文字列
	 * @return array データを整形して連想配列にしたもの
	 */
	function table_analysis(phpQueryObject $tbody, string $insert) : array {
		$details = [];
		//trの数=データの種類(変動)
		$detailsCount = count($tbody->find('tr'));
		for ($i=0; $i<$detailsCount; $i++) {
			//名前の列は半角に整形し、スペースをなくす
			$name = shape_line($tbody->find("tr:eq($i)")->find('th')->text());
			//詳細の列はHTMLを半角に整形し、<br>を\nにして、trimをかける
			$detail = shape_content($tbody->find("tr:eq($i)")->find('td')->html());
			//どちらかの列が無名だったり、15の力は無視
			if ($name === '' || $detail === '' || mb_strpos($name, '15の力') !== false) continue;
			//$detailに改行がある場合、何もない行を消去する
			//if (mb_strpos($detail, "\n") !== false) {
				//改行で配列に分ける
				$detailList = explode("\n", $detail);
				//各要素にshape_lineをかける
				$detailList = array_map('shape_line', $detailList);
				//文字列が0の要素を取り除く
				$detailList = array_filter($detailList, 'strlen');
				//$insertを間に入れて文字列に戻す
				$detail = implode($insert, $detailList);
			//}
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
				$tempReplaced = mb_substr($detail, $posLinkStart, $posLinkEnd - $posLinkStart);
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
	 * @return array|bool データを整形して連想配列にしたもの
	 */
	function table_analysis_third(phpQueryObject $tbody) {
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
				$td[] = shape_line($tbody->find("tr:eq($i)")->find("td:eq($j)")->text());
			//「1. 第1回 名前\n内容: 内容内容\n備考: 備考備考」の形に整形
			$detail = trim(("$td[0]. $td[1] $td[2]".($td[3] === '' ? '' : "\n内容: $td[3]").($td[4] === '' ? '' : "\n備考: $td[4]")));
			//連想配列に
			$details[] = $detail;//str_replace("\n", '<br>', $detail);
		}
		return $details;
	}

	/**
	 * cURLの簡易化メソッド
	 * 既定値はリダイレクトオン、クッキー有効、タイムアウト30秒、IPv4のみ使用、ユーザーエージェントはWindows
	 *
	 * @param $cookie クッキー
	 * @param $referer 参照元URL
	 * @param string $post ポストする場合のデータ
	 * @param string $url アクセス先URL
	 * @param int $down_code ステータスコードがおかしいときのエラー番号
	 * @param bool $follow リダイレクトを追跡するかどうか
	 * @return array レスポンスや各種データの配列
	 */
	function web($cookie = '', $referer = '', string $post = '',
		string $url = 'https://cup.chiba-u.jp/campusweb/campussquare.do', int $down_code = ERROR_PORTAL_DOWN, bool $follow = true) : array {
		global $curl;
		if (isset($cookie['error_code'])) return $cookie;

		curl_setopt_array($curl, [
			CURLOPT_URL => $url,
			CURLOPT_REFERER => $referer,
			CURLOPT_COOKIE => $cookie,
			CURLOPT_POST => ($post !== ''),
			CURLOPT_POSTFIELDS => $post,
			CURLOPT_HEADER => true,
			CURLOPT_FOLLOWLOCATION => $follow,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_COOKIESESSION => true,
			CURLOPT_CONNECTTIMEOUT => 0,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
			CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.120 Safari/537.36']);
		$res = curl_exec($curl);
		$info = curl_getinfo($curl);
		$info['header'] = substr($res, 0, $info['header_size']);
		$info['res'] = substr($res, $info['header_size']);
		
		if (!$res)
			return error_data(ERROR_CURL_FAILED);
		else if ($info['http_code'] !== 200 && $info['http_code'] !== 302)
			return error_data($down_code, " (HTTPコード: $info[http_code])");
		else
			return $info;
	}

	/**
	 * 学生ポータルへログインしてセッション取得、MySQLに保存
	 *
	 * @param ?string $user_id
	 * @param array $query POSTデータ
	 * @return mixed 取得したクッキー
	 */
	function portal_cookie_create(?string $user_id, array $query = []) {
		$userdata = null;
		$result = maria_query("SELECT studentID, studentPass FROM chibasys.user WHERE user_id='$user_id';");
		if ($result) {
			if (isset($query['portal_id']) && isset($query['portal_pass']))
				$userdata = [ 'studentID'=>$query['portal_id'], 'studentPass'=>$query['portal_pass'] ];
			else if (mysqli_num_rows($result) === 1)
				$userdata = mysqli_fetch_assoc($result);
			else
				return error_data(ERROR_USER_NOT_FOUND);
			if (!$userdata['studentID'] || !$userdata['studentPass'])
				return error_data(ERROR_PORTAL_NO_LOGIN);
		}
		else
			return error_data(ERROR_SQL_FAILED);
		
		//cURLで学生ポータルへログイン
		$site = web('', '', "wfId=nwf_PTW0000002_login&userName=$userdata[studentID]&password=$userdata[studentPass]&locale=ja_JP&undefined=&action=rwf&tabId=home&page=",
			'https://cup.chiba-u.jp/campusweb/campusportal.do');//&rwfHash=86c8c93c52abb4ae783c237d364dd203
		if (isset($site['error_code'])) return $site;
		//成功したとき <!DOCTYPE HTML><div><script type="text/javascript">reloadPortal('', 'main');</script>now loading...<br></div>
		//失敗したとき <!DOCTYPE HTML><div><script type="text/javascript">$(document).ready(function(){setTimeout(function(){ $("input[name='loginerrok']").focus(); }, 500);});
		//          <span class="error">ユーザ名またはパスワードの入力に誤りがあります</span><br><br><br><br><center><input type="button" id="loginerrok" name="loginerrok" value="&nbsp; O &nbsp;&nbsp; K &nbsp;" onClick="closeLoginDialog(this)"></center></div>
		$error_text = phpQuery::newDocument($site['res'])->find('.error')->text();
		if ($error_text || strpos($site['res'], 'now loading') === false || strpos($site['res'], 'reloadPortal') === false) {
			error_log("\n[".date('Y-m-d H:m:s')."]ERROR: PORTAL_LOGIN_ERROR\nSITE: ".str_replace("\n", '', json_encode($site)), "3", "/var/www/chibasys-error.log");
			return error_data(ERROR_PORTAL_LOGIN_ADDITIONAL, shape_line($error_text));
		}

		//Cookieのみ抽出
		$c = [];
		$cookieCount = substr_count($site['header'], 'Set-Cookie: ');
		$start = 0;
		for ($i = 0; $i < $cookieCount; $i++) {
			$start = strpos($site['header'], 'Set-Cookie: ', $start + 1) + 12;
			$end = strpos($site['header'], ';', $start);
			$c[] = substr($site['header'], $start, $end - $start);
		}
		//Cookieの文字列に変換
		$cookie = implode('; ', $c);
		//一時データとして保存
		temp_save('portal_cookie', $cookie, $user_id);
		return $cookie;
	}
	
	/**
	 * ポータルのセッション切れ等のエラーをチェック
	 * 有効な結果の時、trueを返却
	 *
	 * @param array $site cURLで取得した情報
	 * @return bool セッションが有効かどうか
	 */
	function portal_session_check(array $site): bool {
		return $site['url'] !== 'https://cup.chiba-u.jp/campusweb/campusportal.do' &&
			$site['url'] !== 'https://cup.chiba-u.jp/campusweb/campussquare.do' &&
			mb_strpos($site['res'], 'SYSTEM ERROR') === false &&
			mb_strpos($site['res'], 'システムエラー') === false &&
			mb_strpos($site['res'], '認証エラー') === false &&
			mb_strpos($site['res'], '有効期限ぎれ') === false;
	}

	const PORTAL_INIT_URL = [
		'portal_subject_change_url'=>   'https://cup.chiba-u.jp/campusweb/campussquare.do?_flowId=KHW0001100-flow',
		'portal_reg_url'=>     'https://cup.chiba-u.jp/campusweb/campussquare.do?_flowId=RSW0001000-flow',
		'portal_reg_list_url'=>'https://cup.chiba-u.jp/campusweb/campussquare.do?_flowId=JPW0001000-flow',
		'portal_grade_url'=>            'https://cup.chiba-u.jp/campusweb/campussquare.do?_flowId=SIW0001200-flow' ];
																	//'https://cup.chiba-u.jp/campusweb/campussquare.do?_flowId=SIW0001300-flow'
	
	/**
	 * URLにアクセスして一時的なURLを取得
	 *
	 * @param string $name URLの種類名
	 * @param $cookie ポータルにログイン済みのクッキー
	 * @param ?string $user_id (Googleの)ユーザーID
	 * @return mixed 取得したURL
	 */
	function portal_url_get(string $name, $cookie, ?string $user_id) {
		//cURLでアクセス
		$site = web($cookie, '', '', PORTAL_INIT_URL[$name]);
		if (isset($site['error_code'])) return $site;
		//一時データとして保存
		if ($user_id !== null) temp_save($name, $site['url'], $user_id);
	
		if ($name === 'portal_reg_url') {
			//各学期の住所確認がある場合、自動で済ます
			$event = phpQuery::newDocument($site['res'])->find('input[name="_eventId"]');
			if ($event->attr('value') === 'rsRefer') {
				//学生住所変更画面へ
				$site2 = web($cookie, $site['url'], '_eventId=rsRefer&'.url_extract($site['url']));
				//変更なしをクリック
				web($cookie, $site2['url'], '_eventId=nochange&'.url_extract($site2['url']));
			}
		}
	
		return $site['url'];
	}

	function login_with_portal(array $query): array {
		global $maria;
		
		//$queryのportal_idとportal_passをチェック
		if (!isset($query['portal_id']) || !isset($query['portal_pass']))
			return error_data(ERROR_NO_LOGIN);

		$result = maria_query("SELECT * FROM user WHERE studentID='$query[portal_id]'");
		$student_info = portal_student_info_get(null, $query);

		if (mysqli_num_rows($result) >= 1) {
			//おそらく登録済み
			$user = mysqli_fetch_assoc($result);
			if (isset($student_info['error_code'])) {
				if ($student_info['error_code'] !== ERROR_PORTAL_DOWN)
					return $student_info;
				else if ($query['portal_pass'] !== $user['studentPass'])
					//乗っ取りを防ぐために
					return error_data(ERROR_PORTAL_DOWN, "\nパスワード変えた場合はポータルが復活するまで待つか、以前登録したパスワードでログインしてください。");
			}
			else {
				if ($query['portal_pass'] !== $user['studentPass']) {
					$result2 = maria_query("UPDATE user SET studentPass='".mysqli_real_escape_string($maria, $query['portal_pass'])."' WHERE user_id='$user[user_id]';");
					if (isset($result2['error_code'])) return $result2;
				}
				session_start();
				session_regenerate_id(true);
				$_SESSION['user_id'] = $user['user_id'];
				session_write_close();
			}

			return [ 'result'=>true, 'name'=>$user['studentName'], 'userdata'=>$user, 'student_info'=>$student_info ];
		}
		else {
			session_start();
			session_regenerate_id(true);
			$_SESSION['user_id'] = 'new';
			session_write_close();

			return [ 'result'=>true, 'name'=>$student_info['学生氏名'], 'student_info'=>$student_info, 'new'=> true ];
		}
	}

	/**
	 * 学生情報を取得 ($queryのportal_idとportal_passを優先)
	 *
	 * @param ?string $user_id (Googleの)ユーザーID
	 * @param array $query POSTデータ
	 * @param $cookie クッキー
	 * @return array 学生情報の連想配列
	 */
	function portal_student_info_get(?string $user_id, array $query, $cookie = null): array {
		//$queryのportal_idとportal_passをチェックかつ未ログインならば終了
		if ((!isset($query['portal_id']) || !isset($query['portal_pass'])) && !$user_id)
			return error_data(ERROR_NO_LOGIN);

		//CookieやURLを取得
		if (!$cookie) $cookie = temp_load('portal_cookie', $user_id);
		if (isset($cookie['error_code'])) return $cookie;
		
		//cURLで学生情報にアクセス
		$site = web($cookie, '', '', 'https://cup.chiba-u.jp/campusweb/campussquare.do?_flowId=CHW0001000-flow');
		if (isset($site['error_code'])) return $site;
		//期限切れセッションチェック
		if (!portal_session_check($site))
			return portal_student_info_get($user_id, $query, portal_cookie_create($user_id, $query));
		
		$data = [];
		//PHPQueryのインスタンス生成
		$doc = phpQuery::newDocument($site['res']);
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

		return $data;
	}

	/**
	 * 履修登録を行う (yobi=1-6, jigen=1-7)(集中講義は9,0)
	 *
	 * @param ?string $user_id (Googleの)ユーザーID
	 * @param array $query POSTデータ
	 * @param $cookie クッキー
	 * @param $referer 参照元URL
	 * @return array 教科コードと教科名の連想配列
	 */
	function portal_reg(?string $user_id, array $query, $cookie = null, $referer = null) {
		//未ログインならば終了
		if (!$user_id) return error_data(ERROR_NO_LOGIN);
		//CookieやURLを取得
		if (!$cookie) $cookie = temp_load('portal_cookie', $user_id);
		if (isset($cookie['error_code'])) return $cookie;
		if (!$referer) $referer = temp_load('portal_reg_url', $user_id);
		if (isset($referer['error_code'])) return $referer;
		
		$code = explode('-', $query['code']);
		if ($query['bool'] == 'true') {
			//cURLで履修登録画面を取得
			$site = web($cookie, $referer, '_eventId=input&'.url_extract($referer).'&nendo=&jikanwariShozokuCode=&jikanwariCode=&yobi=1&jigen=1');
			if (isset($site['error_code'])) return $site;
			//セッション切れをチェック
			if (!portal_session_check($site)) {
				$cookie = portal_cookie_create($user_id);
				return portal_reg($user_id, $query, $cookie,
					portal_url_get('portal_reg_url', $cookie, $user_id));
				}
			//時間割所属コードを取得→なくてもOK 間違うとアウト
			//$jikanwariShozokuCode = maria_query($link, "SELECT jikanwariShozokuCode FROM syllabus_$code[0] WHERE jikanwaricd='$code[1]';");
			//cURLで履修登録ボタンをクリック
			$site = web($cookie, $site['url'], '_eventId=insert&'.url_extract($site['url']).
				"&nendo=$code[0]&jikanwariShozokuCode=&jikanwariCode=$code[1]&dummy=");
			if (isset($site['error_code'])) return $site;
			//セッション切れをチェック
			if (!portal_session_check($site)) {
				$cookie = portal_cookie_create($user_id);
				return portal_reg($user_id, $query, $cookie,
					portal_url_get('portal_reg_url', $cookie, $user_id));
			}
			//<span class="error">にエラー内容が入っていることが多いので、それを参照
			$error = phpQuery::newDocument($site['res'])->find('span.error')->text();
		}
		else {
			//cURLで履修登録の削除確認画面を取得(jikanwariShozokuCode?yobi?jigen?)
			$site = web($cookie, $referer, '_eventId=delete&'.url_extract($referer).'&nendo=$code[0]&jikanwariShozokuCode=&jikanwariCode=$code[1]&yobi=&jigen=');
			if (isset($site['error_code'])) return $site;
			//セッション切れをチェック
			if (!portal_session_check($site)) {
				$cookie = portal_cookie_create($user_id);
				return portal_reg($user_id, $query, $cookie,
					portal_url_get('portal_reg_url', $cookie, $user_id));
				}
			//cURLで履修登録の削除ボタンをクリック
			$site = web($cookie, $site['url'], '_eventId=delete&'.url_extract($site['url']));
			if (isset($site['error_code'])) return $site;
			//セッション切れをチェック
			if (!portal_session_check($site)) {
				$cookie = portal_cookie_create($user_id);
				return portal_reg($user_id, $query, $cookie,
					portal_url_get('portal_reg_url', $cookie, $user_id));
			}
			//<span class="error">にエラー内容が入っていることが多いので、それを参照
			$error = phpQuery::newDocument($site['res'])->find('span.error')->text();
		}
		if ($error && $error !== '')
			return error_data(ERROR_PORTAL_REGISTER_SUBJECT_ADDITIONAL, shape_line($error));
		else {
			$data = [ 'code'=>$query['code'], 'bool'=>$query['bool'] ];
			//通知用に教科名を取得
			$result = maria_query("SELECT name FROM syllabus_$code[0] WHERE jikanwaricd='$code[1]';");
			//無駄にエラーは出さない
			if ($result && mysqli_num_rows($result) === 1) $data['name'] = mysqli_fetch_assoc($result)['name'];
			return $data;
		}
	}

	/**
	 * 履修登録一覧を取得
	 *
	 * @param ?string $user_id (Googleの)ユーザーID
	 * @param array $query POSTデータ
	 * @param $cookie クッキー
	 * @param $referer 参照元URL
	 * @return array 教科一覧の連想配列
	 */
	function portal_reg_list_get(?string $user_id, array $query): array {
		if (isset($query['publicID']) && $query['publicID']) {
			$result = maria_query("SELECT user_id FROM chibasys.user WHERE publicID='$query[publicID]';");
			if (!$result) return error_data(ERROR_SQL_FAILED);
			if (mysqli_num_rows($result) !== 1) return error_data(ERROR_USER_NOT_FOUND);
			$user_id = mysqli_fetch_assoc($result)['user_id'];
		}
		else if (!isset($user_id)) return error_data(ERROR_NO_LOGIN);

		//更新フラグがある場合は取得しに行く、エラー番号だけ保存してMySQLから取得
		$refresh_error = null;
		if (isset($query['refresh']) && $query['refresh']) {
			$data = portal_reg_list_refresh($user_id, $query);
			if (isset($data['error_code'])) {
				$refresh_error = $data['error_code'];
				if ($refresh_error === ERROR_PORTAL_DOWN) return error_data($refresh_error);
			}
		}

		$result = maria_query("SELECT nendo, jikanwariCd FROM chibasys.registration WHERE user_id='$user_id';");
		if ($result) {
			$reg_code = [];
			$reg_data = [];
			while ($row = mysqli_fetch_assoc($result)) {
				$result2 = maria_query("SELECT term, time, credit, name, teacher, room FROM chibasys.syllabus_$row[nendo] WHERE jikanwariCd='$row[jikanwariCd]';");
				if ($result2 && mysqli_num_rows($result2) === 1) {
					$code = "$row[nendo]-$row[jikanwariCd]";
					$reg_code[] = $code;
					$reg_data[$code] = mysqli_fetch_assoc($result2);
					$reg_data[$code]['code'] = $code;
				}
			}
		}
		else
			return error_data(ERROR_SQL_FAILED);
		
		$data = [ 'reg_code'=>$reg_code, 'reg_data'=>$reg_data ];
		return $refresh_error ? error_data($refresh_error, '', $data) : $data;
	}

	/**
	 * 履修登録一覧を再取得
	 *
	 * @param ?string $user_id (Googleの)ユーザーID
	 * @param array $query POSTデータ
	 * @param $cookie クッキー
	 * @param $referer 参照元URL
	 * @return array 教科コード一覧と教科一覧の連想配列
	 */
	function portal_reg_list_refresh(?string $user_id, array $query, $cookie = null, $referer = null) {
		//CookieやURLをMySQLから取得
		if (!$cookie) $cookie = temp_load('portal_cookie', $user_id);
		if (isset($cookie['error_code'])) return $cookie;
		if (!$referer) $referer = temp_load('portal_reg_list_url', $user_id);
		if (isset($referer['error_code'])) return $referer;
		
		$reg_code = [];
		$subjectsZero = [ 1=>false, 2=>false ];
		foreach ([1,2] as $kubun)	{
			//cURLで履修一覧画面を取得
			$site = web($cookie, $referer, '_eventId=changeNendoGakkiGakusei&'.
				url_extract($referer)."&nendo=$query[nendo]&gakkiKbnCd=$kubun");
			if (isset($site['error_code']))	return $site;
			//セッション切れをチェック
			if (!portal_session_check($site)) {
				$cookie = portal_cookie_create($user_id);
				return portal_reg_list_refresh($user_id, $query, $cookie,
					portal_url_get('portal_reg_list_url', $cookie, $user_id));
			}

			//PHPQueryのインスタンス生成
			$tbody = phpQuery::newDocument($site['res'])->find('table.list_tbl tbody');
			//trの数=教科数
			$trCount = count($tbody->find('tr'));
			for ($i = 0; $i < $trCount; $i++) {
				$tr = $tbody->find("tr:eq($i)");
				//「該当するデータはありません」の時はスルー
				if (count($tr->find('td')) <= 1) {
					$subjectsZero[$kubun] = true;
					continue;
				}
				$q = [];
				$url = $tr->find('td:eq(1)')->find('a')->attr('href');
				parse_str(url_extract($url), $q);
				$reg_code[] = "$q[nendo]-$q[jikanwariCd]";
			}
		}

		//履修登録が1つでもある場合やゼロでもデータがないことを確認が取れた時のみデータ更新
		if (count($reg_code) > 0 || (count($reg_code) === 0 && $subjectsZero[1] && $subjectsZero[2])) {
			if (maria_query("DELETE FROM chibasys.registration WHERE user_id='$user_id' AND nendo='$query[nendo]';"))
				foreach ($reg_code as $c) {
					$code = explode('-', $c);
					maria_query("INSERT INTO chibasys.registration (user_id, nendo, jikanwariCd) VALUES ('$user_id', '$code[0]', '$code[1]');");
				}
		}

		return true;
	}

	
	/**
	 * 成績を取得
	 *
	 * @param ?string $user_id (Googleの)ユーザーID
	 * @param array $query POSTデータ
	 * @param $cookie クッキー
	 * @param $referer 参照元URL
	 * @return array 教科一覧の連想配列
	 */
	function portal_grade_list_get(?string $user_id, array $query): array {
		if (!isset($user_id)) return error_data(ERROR_NO_LOGIN);

		//更新フラグがある場合は取得しに行く、エラー番号だけ保存してMySQLから取得
		$refresh_error = null;
		if (isset($query['refresh']) && $query['refresh']) {
			$data = portal_grade_list_refresh($user_id);
			if (isset($data['error_code'])) {
				$refresh_error = $data['error_code'];
				if ($refresh_error === ERROR_PORTAL_DOWN) return error_data($refresh_error);
			}
		}

		$result = maria_query("SELECT nendo, jikanwariCd, point, pass FROM chibasys.grade WHERE user_id='$user_id';");
		if ($result) {
			$grade_data = [];
			while ($row = mysqli_fetch_assoc($result)) {
				$result2 = maria_query("SELECT term, time, credit, name, teacher, room FROM chibasys.syllabus_$row[nendo] WHERE jikanwariCd='$row[jikanwariCd]';");
				if ($result2 && mysqli_num_rows($result2) === 1) {
					$code = "$row[nendo]-$row[jikanwariCd]";
					$grade_data[$code] = array_merge($row, mysqli_fetch_assoc($result2));
					//$grade_data[$code]['code'] = $code;
				}
			}
		}
		else
			return error_data(ERROR_SQL_FAILED);
		
		$data = [ 'grade_data'=>$grade_data ];
		return $refresh_error ? error_data($refresh_error, '', $data) : $data;
	}

	const GRADE_NUM = [ '秀'=>4, '優'=>3, '良'=>2, '可'=>1, '不可'=>0, '合格'=>null, '不合格'=>null ];
	/**
	 * 成績を再取得
	 *
	 * @param ?string $user_id (Googleの)ユーザーID
	 * @param $cookie クッキー
	 * @param $referer 参照元URL
	 * @return array 教科一覧の連想配列
	 */
	function portal_grade_list_refresh(?string $user_id, $cookie = null, $referer = null) {
		//未ログインならば終了
		if (!$user_id) return error_data(ERROR_NO_LOGIN);
		//CookieやURLをMySQLから取得
		if (!$cookie) $cookie = temp_load('portal_cookie', $user_id);
		if (isset($cookie['error_code'])) return $cookie;
		if (!$referer) $referer = temp_load('portal_grade_url', $user_id);
		if (isset($referer['error_code'])) return $referer;
		
		//cURLで成績画面を取得
		$site = web($cookie, $referer, '_eventId=display&'.url_extract($referer).'&spanType=0&dummy=');//spanType=1&nendo=2019&gakkiKbnCd=2
		if (isset($site['error_code'])) return $site;
		//セッション切れをチェック
		if (!portal_session_check($site)) {
			$cookie = portal_cookie_create($user_id);
			return portal_grade_list_refresh($user_id, $cookie,
				portal_url_get('portal_grade_url', $cookie, $user_id));
		}
		
		$result = true;
		//PHPQueryのインスタンス生成
		$tbody = phpQuery::newDocument($site['res'])->find('table.normal tbody');
		//trの数=教科数
		$trCount = count($tbody->find('tr'));
		for ($i = 0; $i < $trCount; $i++) {
			$class = [];
			$tr = $tbody->find("tr:eq($i)");
			//「履修成績データはありません」の時はスルー
			if (count($tr->find('td')) <= 1) continue;
			foreach ([1,3,4,5] as $j)
				$class[] = shape_line($tr->find("td:eq($j)")->text());
			$class[] = GRADE_NUM[shape_line($tr->find('td:eq(6)')->text())]; //秀/優/良/可/不可/合格/不合格
			$class[] = (shape_line($tr->find('td:eq(7)')->text()) === '合' ? 1 : 0); //合/否
			
			$row = maria_query("SELECT * FROM chibasys.grade WHERE user_id='$user_id' AND nendo='$class[0]' AND jikanwaricd='$class[1]';");
			$syllabus = portal_syllabus_get([ 'code'=>"$class[0]-$class[1]" ]);
			if ($row && mysqli_fetch_assoc($row))
				$r = maria_query("UPDATE chibasys.grade SET name='$class[2]', teacher='$class[3]', point=".($class[4] === null ? 'null' : $class[4]).", pass=$class[5], credit=".$syllabus['data']['credit']." WHERE user_id='$user_id' AND nendo='$class[0]' AND jikanwaricd='$class[1]';");
			else
				$r = maria_query("INSERT INTO chibasys.grade (user_id, nendo, jikanwariCd, name, teacher, point, pass, credit) VALUES ('$user_id','$class[0]','$class[1]','$class[2]','$class[3]',".($class[4] === null ? 'null' : $class[4]).",$class[5],".$syllabus['data']['credit'].");");
			if (!$r) $result = error_data(ERROR_SQL_FAILED);
		}
		return $result;
	}

	/**
	 * 特定の授業日の休講/補講/教室変更を取得
	 *
	 * @param array $query POSTデータ
	 * @param $cookie クッキー
	 * @param $referer 参照元URL
	 * @return array 日付と教科一覧の連想配列
	 */
	function portal_subject_change_refresh(array $query, $cookie = null, $referer = null): array {
		$user_id = '113700091446153817952';
		//CookieやURLをMySQLから取得
		if (!$cookie) $cookie = temp_load('portal_cookie', $user_id);
		if (isset($cookie['error_code'])) return $cookie;
		if (!$referer) $referer = temp_load('portal_subject_change_url', $user_id);
		if (isset($referer['error_code'])) return $referer;

		//日付を用意
		$datetime = new DateTime($query['date']);
		$date = urlencode($query['date']);
		$year = date_format($datetime, 'Y');
		$month = date_format($datetime, 'n');
		$day = date_format($datetime, 'j');
		//cURLで休講/補講/教室変更を取得
		$site = web($cookie, $referer, url_extract($referer).
			"&dispType=list&dispData=chg&_eventId_search=+%E8%A1%A8+%E7%A4%BA+%E3%81%99+%E3%82%8B&startDay=$date&startDay_year=$year&startDay_month=$month&startDay_day=$day&endDay=$date&endDay_year=$year&endDay_month=$month&endDay_day=$day&_rishuchuFlg=1&kyokanCode=&shozokuCode=");
		if (isset($site['error_code'])) return $site;
		//セッション切れをチェック
		if (!portal_session_check($site)) {
			portal_cookie_create($user_id);
			return portal_subject_change_refresh($query, $cookie, portal_url_get('portal_subject_change_url', $cookie, $user_id));
		}

		//PHPQueryのインスタンス生成
		$tbody = phpQuery::newDocument($site['res'])->find('.normal');
		$classes = [];
		//trの数=教科数+1
		$subjectCount = count($tbody->find('tr'));
		for ($i = 0; $i < $subjectCount; $i++) {
			$tr = $tbody->find("tr:eq($i)");
			$class = [];
			$dataCount = count($tr->find('td'));
			//初めの行は省く(全てthなので)
			if ($dataCount === 0) continue;
			//trタグ内の一つ一つの改行やスペースを除去
			foreach ([0,1] as $j)
				$class[] = shape_line($tr->find("td:eq($j)")->text());
			//データなしの場合、配列は空で終了
			if ($class[0] === '該当するデータはありません') break;
			//onclickを解析して詳細URLを入手
			//return jumpInfo('&_eventId_refer=_eventId_refer&taishoymd=20190711&jigen=2&nendo=2019&jikanwariShozokucd=E1&jikanwaricd=E1P543001');
			$refer = $tr->find('a')->attr('onclick');
			$start = strpos($refer, "('") + 2;
			$end = strpos($refer, "')");
			$detailUrl = $site['url'].substr($refer, $start, $end - $start);
			portal_subject_change_detail($cookie, $detailUrl, $class);
			//教科一覧に追加
			$classes[] = $class;
		}

		return [ 'date'=>$query['date'], 'classes'=>$classes, 'url'=>$site['url'] ];
	}

	/**
	 * 授業変更の詳細を取得、配列に追加
	 *
	 * @param $cookie クッキー
	 * @param string $url 詳細URL
	 * @param array $class 教科情報の連想配列
	 * @return void
	 */
	function portal_subject_change_detail($cookie, string $url, array &$class) {
		//cURLで千葉大学シラバスの検索結果を取得
		$site = web($cookie, '', '', $url);
		if (isset($site['error_code'])) return $site;
		//PHPQueryのインスタンス生成
		$tbody = phpQuery::newDocument($site['res'])->find('.normal');
		//trの数=データ数
		$subjectCount = count($tbody->find('tr'));
		for ($i = 0; $i < $subjectCount; $i++) {
			$tr = $tbody->find("tr:eq($i)");
			$class[] = shape_line($tr->find('td:eq(1)')->text());
		}
	}

	/**
	 * みんなのキャンパスへログインしてセッション取得、MySQLに保存
	 *
	 * @return void
	 */
	function mincam_cookie_create() {
		//cURLでログイン
		$site = web('', '', '__event=ID01_001_001&service_id=p06&return_url=index.phtml&return_url_nikki='.
			'https%3A%2F%2Fcampus.nikki.ne.jp%2F&pp_version=20170213&u=xperd00&p=q0d9gd3y&submit='.
			'%A5%ED%A5%B0%A5%A4%A5%F3', 'https://grp03.id.rakuten.co.jp/rms/nid/vc', ERROR_MINCAM_DOWN);
		if (isset($site['error_code'])) return $site;
		//Cookieのみ抽出、"pitto"のみでOK
		$c = [];
		$cookieCount = substr_count($site['header'], 'Set-Cookie: pitto');//substr_count($header, 'Set-Cookie:');
		$start = 0;
		for ($i = 0; $i < $cookieCount; $i++) {
			$start = strpos($site['header'], 'Set-Cookie: pitto', $start + 1) + 12;
			$end = strpos($site['header'], ';', $start);
			$c[] = substr($site['header'], $start, $end - $start);
		}
		$cookie = implode('; ', $c);
		//MySQLに一時的に保存
		$result =	maria_query("UPDATE chibasys.temp SET data='$cookie' WHERE name='mincam_cookie';");
		if ($result) return $cookie;
		else return error_data(ERROR_SQL_FAILED);
	}

	/**
	 * みんなのキャンパスからcURLでデータを取得
	 *
	 * @param int $page ページ番号
	 * @param $cookie クッキー
	 * @return array 投稿別になった「みんなのキャンパス」データの配列
	 */
	function mincam_data_get(int $page, $cookie = null): array {
		//クッキーがセッションにあるかチェック
		if (!$cookie) $cookie = temp_load('mincam_cookie', 'general');
		if (isset($cookie['error_code'])) return $cookie;

		//cURLでpageの投稿を取得;
		$site = web($cookie, '', '', "https://campus.nikki.ne.jp/?module=lesson&action=index&univ=".
			"%C0%E9%CD%D5%C2%E7%B3%D8&lname=&fname=&lesson_name=&faculty1=&id=&order=1&page=$page",
			ERROR_MINCAM_DOWN, false);
		if (isset($site['error_code'])) return $site;
		//文字コードをEUC-JPからUTF-8に
		$res = str_replace('euc-jp', 'utf-8', mb_convert_encoding($site['res'], 'UTF-8', 'eucjp-win'));
		//PHPQueryのインスタンス生成
		$doc = phpQuery::newDocument($res);

		//ログイン済みかどうかをチェック
		if (count($doc->find('.login')) > 0)
			return mincam_data_get($page, mincam_cookie_create());

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
			$post_id = intval(str_replace(['[', ']'], '', $section->find('.college span:eq(3) font')->text()));
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
				'lastName'=>$lastName, 'firstName'=>$firstName, 'post_id'=>$post_id, 'richPoint'=>$richPoint, 'easyPoint'=>$easyPoint,
				'creditUniversity'=>$creditUniversity, 'creditName'=>$creditName, 'postDate'=>$postDate, 'attend'=>$attend,
				'textbook'=>$textbook, 'middleExam'=>$middleExam, 'finalExam'=>$finalExam, 'bringIn'=>$bringIn, 'message'=>$message ];
		}
		return $data;
	}

	/**
	 * 投稿ごとに配列になったデータを読み、MySQLに挿入
	 *
	 * @param array $data 投稿別になった「みんなのキャンパス」データの配列
	 * @return bool SQLが成功したかどうか
	 */
	function mincam_data_save(array $data) {
		global $maria;
		$query = '';
		foreach ($data as $d)
			$query += "INSERT IGNORE INTO chibasys.mincam VALUES ('$d[title]', '$d[university]', '$d[faculty]', '$d[department]', ".
				"'$d[lastName]', '$d[firstName]', $d[post_id], $d[richPoint], $d[easyPoint], '$d[creditUniversity]', '$d[creditName]', ".
				"'$d[postDate]', '$d[attend]', '$d[textbook]', '$d[middleExam]', '$d[finalExam]', '$d[bringIn]', '".mysqli_real_escape_string($maria, $d['message'])."');";
		//成功か失敗かの真偽値を返す
		return maria_query($query);
	}

	/**
	 * みんなのキャンパスのMySQL(mincam)を検索
	 *
	 * @param array $query POSTデータ
	 * @return array みんなのキャンパスのデータ、配列化したクエリの連想配列
	 */
	function mincam_search(array $query): array {
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
		$msc = microtime(true);
		$result = maria_query('SELECT * FROM chibasys.mincam WHERE '.implode(' AND ', $where).' ORDER BY post_id DESC;');
		$msc = microtime(true) - $msc;
		$classes = [];
		//データベースをそのまま配列に格納
		if ($result) {
			while ($row = mysqli_fetch_assoc($result)) $classes[] = $row;
			return [ 'classes'=>$classes, 'num'=>mysqli_num_rows($result), 'time'=>$msc ];
		}
		else
			return error_data(ERROR_SQL_FAILED);
	}

	/**
	 * シラバスに付随するメモデータをMySQLから取得
	 *
	 * @param ?string $user_id (Googleの)ユーザーID
	 * @param array $query POSTデータ
	 * @return array テキスト、最終更新日の連想配列
	 */
	function memo_get(?string $user_id, array $query): array {
		//未ログインならば終了
		if (!$user_id) return error_data(ERROR_NO_LOGIN);
		//シラバスコードを分解
		$code = explode('-', $query['code']);
		if (count($code) === 4) $code[1] = $code[2];
		//メモを取得
		$result = maria_query("SELECT nendo, jikanwaricd, text, lastUpdated FROM chibasys.memo WHERE user_id='$user_id' and nendo='$code[0]' and jikanwaricd='$code[1]';");
		if (!$result) return error_data(ERROR_SQL_FAILED);
		if (mysqli_num_rows($result) === 1) $data = mysqli_fetch_assoc($result);
		else $data = [ 'nendo'=>$code[0], 'jikanwaricd'=>$code[1], 'text'=>'', 'lastUpdated'=>null ];

		return $data;
	}

	/**
	 * シラバスに付随するメモデータをMySQLに保存
	 *
	 * @param ?string $user_id (Googleの)ユーザーID
	 * @param array $query POSTデータ
	 * @return array memo_get()の結果
	 */
	function memo_save(?string $user_id, array $query): array {
		//未ログインならば終了
		if (!$user_id) return error_data(ERROR_NO_LOGIN);
		//シラバスコードを分解
		$code = explode('-', $query['code']);
		if (count($code) === 4) $code[1] = $code[2];

		//テキストがない場合は、レコードを消去
		if ($query['text'] === '') {
			$result = maria_query("DELETE FROM chibasys.memo WHERE user_id='$user_id' AND nendo='$code[0]' AND jikanwaricd='$code[1]';");
			if (!$result) return error_data(ERROR_SQL_FAILED);
		}
		//テキストがある場合はまずSELECTしてレコードの存在をチェックし、INSERTかUPDATEかを使い分ける
		else {
			global $maria;
			$result = maria_query("SELECT text, lastUpdated FROM chibasys.memo WHERE user_id='$user_id' AND nendo='$code[0]' AND jikanwaricd='$code[1]';");
			if (!$result) return error_data(ERROR_SQL_FAILED);
			if (mysqli_num_rows($result) === 1)
				$result2 = maria_query("UPDATE chibasys.memo SET text='".mysqli_real_escape_string($maria, $query['text'])."', lastUpdated=NOW() WHERE user_id='$user_id' AND nendo='$code[0]' AND jikanwaricd='$code[1]';");
			else
				$result2 = maria_query("INSERT INTO chibasys.memo (user_id, nendo, jikanwaricd, text, lastUpdated) VALUES ('$user_id', '$code[0]', '$code[1]', '".mysqli_real_escape_string($maria, $query['text'])."', NOW());");
			if (!$result2) return error_data(ERROR_SQL_FAILED);
		}
		return [ 'result'=>true ];
	}

	/**
	 * シラバスに付随するコメントをMySQLから取得
	 *
	 * @param array $query POSTデータ
	 * @return array コメントデータ、次のインデックスの連想配列
	 */
	function comment_get(array $query): array {
		//シラバスコードを分解
		$code = explode('-', $query['code']);
		if (count($code) === 4) $code[1] = $code[2];
		//標準の取得数は30
		$amount = 30;
		//うまく動かないので常に上から
		$query['index'] = 0;
		/*//reloadがtrueのときは再取得
		if ($query['reload'] === 'true') {
			//数は今まで読み込んだ数全て
			$amount = $query['index'] + 1;
			//始まりのインデックスは初めの0に
			$query['index'] = 0;
		}*/

		//全年度を取得するかどうかで挙動を変える(未実装)
		//if ($query['all_nendo'] === 'true')
			//$result = maria_query("SELECT num, name, text, datetime, nendo FROM chibasys.comment WHERE jikanwaricd='$code[1]' ORDER BY datetime DESC LIMIT $amount OFFSET $query[index];");
		//else
			$result = maria_query("SELECT num, name, text, datetime FROM chibasys.comment WHERE nendo=$code[0] AND jikanwaricd='$code[1]' ORDER BY datetime DESC LIMIT $amount OFFSET $query[index];");
		if (!$result) return error_data(ERROR_SQL_FAILED);
		$comments = [];
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
		return [ 'comment'=>$comments, 'index'=>($query['index'] + count($comments)) ];
	}

	/**
	 * シラバスに付随するコメントを投稿、MySQLに保存
	 *
	 * @param ?string $user_id (Googleの)ユーザーID
	 * @param array $query POSTデータ
	 * @return array comment_get()の結果
	 */
	function comment_post(?string $user_id, array $query): array {
		//未ログインならば終了
		if (!$user_id) return error_data(ERROR_NO_LOGIN);
		//シラバスコードを分解
		$code = explode('-', $query['code']);
		if (count($code) === 4) $code[1] = $code[2];
		//コメントの最大番号を取得し、今回の番号を決める
		$result = maria_query("SELECT MAX(num) FROM chibasys.comment WHERE nendo=$code[0] AND jikanwaricd='$code[1]';");
		if (!$result) error_data(ERROR_SQL_FAILED);
		if (mysqli_num_rows($result) === 1) $num = intval(mysqli_fetch_assoc($result)['MAX(num)']) + 1;
		else $num = 1;

		global $maria;
		$result2 = maria_query("INSERT INTO chibasys.comment (user_id, num, name, text, datetime, ip, nendo, jikanwaricd) ".
			"VALUES ('$user_id', $num, '".(trim($query['name']) === '' ? '名無しの千葉大生' : trim($query['name']))."', '".
			mysqli_real_escape_string($maria, $query['text'])."', NOW(), '$_SERVER[REMOTE_ADDR]', $code[0], '$code[1]');");
		if (!$result2) return error_data(ERROR_SQL_FAILED);
		
		return [ 'result'=>true ];
	}

	/**
	 * お気に入りのステータスを変更、MySQLに変更適用
	 *
	 * @param ?string $user_id (Googleの)ユーザーID
	 * @param array $query POSTデータ
	 * @return array { result: true }
	 */
	function fav_change(?string $user_id, array $query): array {
		//未ログインならば終了
		if (!$user_id) return error_data(ERROR_NO_LOGIN);
		//シラバスコードを分解
		$code = explode('-', $query['code']);
		if (count($code) === 4) $code[1] = $code[2];

		//trueは追加、falseは削除
		if ($query['bool'])
			$result = maria_query("INSERT INTO chibasys.favorite (user_id, nendo, jikanwaricd) VALUES ('$user_id', $code[0], '$code[1]');");
		else
			$result = maria_query("DELETE FROM chibasys.favorite WHERE user_id='$user_id' AND nendo=$code[0] AND jikanwaricd='$code[1]';");
		if (!$result) return error_data(ERROR_SQL_FAILED);

		return [ 'result'=>true ];
	}

	/**
	 * お気に入り一覧をMySQLから取得
	 *
	 * @param ?string $user_id (Googleの)ユーザーID
	 * @return array お気に入り一覧の連想配列
	 */
	function fav_list_get(?string $user_id): array {
		//未ログインならば終了
		if (!$user_id) return error_data(ERROR_NO_LOGIN);
		$data = [];
		
		//MySQLからお気に入り一覧を取得
		$result = maria_query("SELECT nendo, jikanwaricd FROM chibasys.favorite WHERE user_id='$user_id';");
		if (!$result) return error_data(ERROR_SQL_FAILED);
		while ($row = mysqli_fetch_assoc($result)) {
			$result2 = maria_query("SELECT term, time, credit, name, teacher, summary FROM chibasys.syllabus_$row[nendo] WHERE jikanwaricd='$row[jikanwaricd]';");
			if (!$result2) return error_data(ERROR_SQL_FAILED);
			if (mysqli_num_rows($result2) !== 1) continue;
			
			$code = "$row[nendo]-$row[jikanwaricd]";
			$data[$code] = array_merge([ 'code'=>$code ], $row, mysqli_fetch_assoc($result2));
		}
		return [ 'data'=>$data ];
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
	 * @param Google_Client $client Google_Clientのインスタンス
	 * @return array イベント一覧の連想配列
	 */
	function cal_list_get(?string $user_id): array {
		//未ログインならば終了
		if (!$user_id) return error_data(ERROR_NO_LOGIN);
		$client = google_client_create();
		//Googleアカウントの再ログインが必要ならば終了
		if ($client->isAccessTokenExpired()) return error_data(ERROR_GOOGLE_EXPIRED);
		//カレンダーサービスのインスタンスを生成
		$service = new Google_Service_Calendar($client);
		//「Created By chibasys」が入ったイベント一覧を取得
		$result = $service->events->listEvents('primary', [
			'q' => 'Created By', 'maxResults' => 2500
		]);
		$cal_code = [];
		$cal_data = [];
		foreach ($result->getItems() as $event) {
			//_が入った個々のイベントは無視する
			if (strpos($event->getId(), '_') !== false) continue;
			//idをbase32hexからデコードして&区切りで配列に戻す
			//UNIX時間&シラバスコード&ターム&曜時&単位数&★ターム&★曜日
			$event_id = explode('&', Encoding::base32HexDecode($event->getId()));
			//$idの要素数が6以上の時は連想配列に追加
			if (count($event_id) >= 6) {
				$code = explode('-', $event_id[1]);
				if (count($code) === 4)
					$code = "$code[0]-$code[2]";
				else
					$code = $event_id[1];
				$cal_code[] = $code;
				$cal_data[$code] = [ 'event_id'=>$event->getId(), 'add'=>date(DateTime::ATOM, $event_id[0]), 'nendo'=>explode('-', $event_id[1])[0], 'code'=>$code, 'term'=>$event_id[2], 'time'=>$event_id[3], 'credit'=>intval($event_id[4]), 'index'=>[ 'term'=>$event_id[5], 'time'=>$event_id[6] ],
					'name'=>$event['summary'], 'room'=>str_replace('千葉大学 ', '', $event['location']), 'start'=>$event['start']['dateTime'], 'end'=>$event['end']['dateTime'], 'notification'=>(count($event['reminders']['overrides']) > 0) ];
			}
			//そうでない場合はなかったものとして処理 !!!!!!!!!!
			//$cal_data[] = [ 'event_id'=>$event->getId(), 'name'=>$event['summary'], 'room'=>$event['location'], 'start'=>$event['start']['dateTime'], 'end'=>$event['end']['dateTime'], 'notification'=>(count($event['reminders']['overrides']) > 0) ];
		}
		return [ 'cal_code'=>$cal_code, 'cal_data'=>$cal_data ];
	}

	/**
	 * 今週のGoogleカレンダーのイベントを取得
	 *
	 * @param ?string $user_id (Googleの)ユーザーID
	 * @return array イベント一覧の連想配列
	 */
	function cal_week_get(?string $user_id): array {
		//未ログインならば終了
		if (!$user_id) return error_data(ERROR_NO_LOGIN);
		$client = google_client_create();
		//Googleアカウントの再ログインが必要ならば終了
		if ($client->isAccessTokenExpired()) return error_data(ERROR_GOOGLE_EXPIRED);
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
		$cal_week_data = [];
		foreach ($result->getItems() as $event) {
			//_があるときのみそれより前の文字列をidとしてbase32hexからデコードして&区切りで配列に戻す
			$event_id = explode('&', Encoding::base32HexDecode((strpos($event['id'], '_') !== false ? explode('_', $event['event_id'])[0] : $event['event_id'])));
			//$idの要素数が6以上の時は連想配列に追加
			if (count($event_id) >= 6) {
				$code = explode('-', $event_id[1]);
				if (count($code) === 4)
					$code = "$code[0]-$code[2]";
				else
					$code = $event_id[1];
				$cal_week_data[] = [ 'add'=>date(DateTime::ATOM, $event_id[0]), 'code'=>$code, 'term'=>$event_id[2], 'time'=>$event_id[3], 'credit'=>intval($event_id[4]), 'index'=>[ 'term'=>$event_id[5], 'time'=>$event_id[6] ],
					'name'=>$event['summary'], 'room'=>str_replace('千葉大学 ', '', $event['location']), 'start'=>$event['start']['dateTime'], 'end'=>$event['end']['dateTime']  ];
			}
		}
		return [ 'cal_week_data'=>$cal_week_data ];
	}

	/**
	 * Googleカレンダーにイベントを追加
	 *
	 * @param ?string $user_id (Googleの)ユーザーID
	 * @param array $data シラバスの詳細データ
	 * @return array 各イベントの返り値の連想配列
	 */
	function calender_add(?string $user_id, array $data): array {
		//data: term (startDate endDate) time (startTime endTime) code name room description
		//未ログインならば終了
		if (!$user_id) return error_data(ERROR_NO_LOGIN);
		$client = google_client_create();
		//Googleアカウントの再ログインが必要ならば終了
		if ($client->isAccessTokenExpired()) return error_data(ERROR_GOOGLE_EXPIRED);
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
				$event_id = "$_SERVER[REQUEST_TIME]&$data[code]&$data[term]&$data[time]&$data[credit]&$i&$j";
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
				//$_SESSION['userdata']['notification'] = ($data['notification'] === 'true' ? '1' : '0');
				//セッションデータ利用終了
				session_write_close();
				
				$result[] = $service->events->insert('primary', new Google_Service_Calendar_Event([
					'id' => str_replace('=', '', Encoding::base32HexEncode($event_id)), //base32hexエンコードをしたID
					'colorId' => '2', //アカウントによって違う
					'summary' => $data['name'], //予定のタイトル
					'location' => ($data['room'] === '' ? '' : '千葉大学 '.$data['room']), //予定の位置
					'description' => $data['description']."シラバスの詳細ページはこちら\nhttps://".$_SERVER['HTTP_HOST']."/syllabus?".$data['code']."\nCreated by chibasys",
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
						'url' => 'https://'.$_SERVER['HTTP_HOST'].'/syllabus?'.$data['code'], 'title' => $data['name'].'の詳細 -チバシス-'
					], //通知設定、デフォルトはいずれも使わない
					'reminders' => [ 'useDefault' => false, 'overrides' => $overrides ]
				]));
			}
		}
		//以前の通知設定を変更
		calender_notification_settings_change($user_id, $data['notification'] === 'true');
		return [ 'result'=>$result ];
	}

	/**
	 * Googleカレンダーのイベントを追加や削除する
	 *
	 * @param ?string $user_id (Googleの)ユーザーID
	 * @param array $query POSTデータ
	 * @return array イベントの返り値の連想配列
	 */
	function cal_change(?string $user_id, array $query): array {
		//未ログインならば終了
		if (!$user_id) return error_data(ERROR_NO_LOGIN);
		$client = google_client_create();
		//Googleアカウントの再ログインが必要ならば終了
		if ($client->isAccessTokenExpired()) return error_data(ERROR_GOOGLE_EXPIRED);
		//カレンダーサービスのインスタンスを生成
		$service = new Google_Service_Calendar($client);
		if ($query['bool']) {
			//シラバスコードを分解
			$code = explode('-', $query['code']);
			$syllabus = portal_syllabus_get([ 'code'=>$query['code'] ])['data'];
	
			//タームの演算
			//$syllabus.data.term ex)T1 T1-2 T1･3 T1集
			//$terms = [ [ 'start'=>'2019-04-08', 'end'=>'2019-06-10' ] ];
			$terms = [];
			//集中→日程指定してもらった日付を流用(未実装)
			if (mb_strpos($syllabus['term'], '集') !== false)
				$terms[] = [ 'start'=>$query['startDate'], 'end'=>$query['endDate'] ];
			//通年→1~6ターム？として処理
			else if (mb_strpos($syllabus['term'], '通') !== false)
				$terms[] = [ 'start'=>StartTerm[$code[0]][1], 'end'=>EndTerm[$code[0]][6] ];
			//独立した複数タームにまたがる→イベントを分ける
			else if (strpos($syllabus['term'], '･') !== false) {
				//Tを除いて「･」区切りでタームを抽出し数字を配列に
				$termList = explode('･', substr($syllabus['term'], 1)); //[1,3]
				foreach ($termList as $term)
					$terms[] = [ 'start'=>StartTerm[$code[0]][intval($term)], 'end'=>EndTerm[$code[0]][(intval($term))] ];
			}
			//連続した複数タームにまたがる→イベントをつなげる
			else if (strpos($syllabus['term'], '-') !== false) {
				//Tを除いて「-」区切りでタームを抽出し数字を配列に
				$termList = explode('-', substr($syllabus['term'], 1)); //[1,3]
				$terms[] = [ 'start'=>StartTerm[$code[0]][intval($termList[0])], 'end'=>EndTerm[$code[0]][(intval($termList[1]))] ];
			}
			//通常の単独ターム
			else {
				//Tを除いて数字に
				$term = intval(substr($syllabus['term'], 1));
				$terms[] = [ 'start'=>StartTerm[$code[0]][$term], 'end'=>EndTerm[$code[0]][$term] ];
			}
	
			//開始時刻の演算
			//$syllabus['time'] ex)月1 月1,月2
			//$times = [ [  ] ];
			$times = []; //[day:月,start:8:50,end:10:20]
			//終日イベントに、毎日のみ(禁止or未実装)
			if ($query['allDay'] === 'true' || $syllabus['time'] === '通')
				$times[] = [ 'day'=>'all', 'hour'=>null ];
			//時間指定を利用、毎日のみ(禁止or未実装)
			else if ($syllabus['time'] === '他')
				$times[] = [ 'day'=>'time', 'hour'=>null, 'start'=>$query['startTime'], 'end'=>$query['endTime'] ];
			//カンマ区切りで複数時間の時
			else if (mb_strpos($syllabus['time'], ',') !== false) {
				//「,」区切りで時間を配列に分ける
				$timeList = explode(',', $syllabus['time']);
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
				$hour = intval(mb_substr($syllabus['time'], 1, 1));
				$times[] = [ 'day'=>mb_substr($syllabus['time'], 0, 1), 'hour'=>$hour, 'start'=>StartTime[$hour], 'end'=>EndTime[$hour] ];
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
					$event_id = "$_SERVER[REQUEST_TIME]&$query[code]&$syllabus[term]&$syllabus[time]&$syllabus[credit]&$i&$j";
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
					if (isset($query['ignoreExdate']) && $query['ignoreExdate'] === 'false')
						foreach ($holidayList as $h)
							if ($everyDay || intval($h->format('w')) === DOW[$times[$j]['day']])
								$exdate[] = 'EXDATE:'.$h->format('Ymd').'T'.str_replace(':', '', $times[$j]['start'].':00');
					$overrides = [];
					//通知が有効なとき
					if ($query['notification'] === 'true') $overrides[] = [ 'method' => 'popup', 'minutes' => ($times[$j]['hour'] === 3 ? 50 : 10) ];
					$description = '';
					foreach ($syllabus as $key => $value)
						if (in_array($key, array_keys(syllabus_ej), true) && $value !== '' && $value !== null)
							$description .= ($key !== 'detail' ? syllabus_ej[$key].': '.$value."\n" : "★授業計画詳細情報\n".implode("\n", $value)."\n");
					$description .= "\nシラバスの詳細ページはこちら\nhttps://".$_SERVER['HTTP_HOST']."/syllabus?".$query['code']."\nCreated by chibasys";
					
					$result[] = $service->events->insert('primary', new Google_Service_Calendar_Event([
						'id' => str_replace('=', '', Encoding::base32HexEncode($event_id)), //base32hexエンコードをしたID
						'colorId' => '2', //アカウントによって違う
						'summary' => $syllabus['name'], //予定のタイトル
						'location' => ($syllabus['room'] === '' ? '' : '千葉大学 '.$syllabus['room']), //予定の位置
						'description' => $description,
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
							'url' => 'https://'.$_SERVER['HTTP_HOST'].'/syllabus?'.$query['code'], 'title' => $syllabus['name'].'の詳細 -チバシス-'
						], //通知設定、デフォルトはいずれも使わない
						'reminders' => [ 'useDefault' => false, 'overrides' => $overrides ]
					]));
				}
			}
			//以前の通知設定を変更
			calender_notification_settings_change($user_id, $query['notification'] === 'true');
			return [ 'result'=>$result ];
		}
		else {
			//idを指定してイベントを消去
			$result = $service->events->delete('primary', $query['event_id']);
			return [ 'result'=>$result ];
		}
	}

	/**
	 * Googleカレンダーのイベントの通知設定変更
	 *
	 * @param ?string $user_id (Googleの)ユーザーID
	 * @param array $query POSTデータ
	 * @return array イベントの返り値の連想配列
	 */
	function calender_notification_toggle(?string $user_id, array $query): array {
		//未ログインならば終了
		if (!$user_id) return error_data(ERROR_NO_LOGIN);
		$client = google_client_create();
		//Googleアカウントの再ログインが必要ならば終了
		if ($client->isAccessTokenExpired()) return error_data(ERROR_GOOGLE_EXPIRED);
		//カレンダーサービスのインスタンスを生成
		$service = new Google_Service_Calendar($client);
		$result = [];
		foreach ($query['event_id'] as $event_id) {
			//idごとにイベントを取得
			$event = $service->events->get('primary', $event_id);
			//通知を有効に変更するとき
			if ($query['notification'] === 'true') {
				$event['reminders'] =
					[ 'useDefault' => false, 'overrides' => [ [ 'method' => 'popup', 'minutes' =>
						(substr($event['start']['dateTime'], 11, 5) === '12:50' ? 50 : 10) ] ] ];
			}
			//通知を無効に変更するとき
			else if ($query['notification'] === 'false') {
				$event['reminders'] = [ 'useDefault' => false, 'overrides' => [] ];
			}
			//一部を書き換えたやつをそのままアップデートとして突っ込む
			$result[] = $service->events->update('primary', $event_id, $event);  
		}
		//以前の通知設定を変更
		calender_notification_settings_change($user_id, $query['notification'] === 'true');
		return [ 'result'=>$result ];
	}

	/**
	 * 以前の変更通知をMySQLに保存
	 *
	 * @param ?string $user_id (Googleの)ユーザーID
	 * @param bool $bool 有効かどうか
	 * @return array 成功したかどうかの連想配列
	 */
	function calender_notification_settings_change(?string $user_id, bool $bool): array {
		//以前の通知設定をMySQLに保存
		$result = maria_query("UPDATE chibasys.user SET notification=".($bool ? 1 : 0)." WHERE user_id='$user_id';");
		if (!$result) return error_data(ERROR_SQL_FAILED);
		return [ 'notification'=>$bool ];
	}
?>