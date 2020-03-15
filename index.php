<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/core.php');
init();
$nendo = intval(date('n')) >= 4 ? intval(date('Y')) : intval(date('Y')) - 1;
$r = explode("?", get_request());
$type = ($r[0] === '' ? 'website' : 'article');
$title = 'ようこそ、chibasysへ';
$summary = '';
$image_url = 'icon.png';
session_start();
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] !== 'new') {
  $result = maria_query("SELECT * FROM user WHERE user_id='$_SESSION[user_id]';");
  if (!$result) {  }
  else if (mysqli_num_rows($result) >= 1)
    //locate_welcome('?error=user_not_found');
    $userdata = mysqli_fetch_assoc($result);
}
else {
  //セッションにリクエストを保存 (syllabus?2000-AAAAAA)
  $_SESSION['request'] = get_request();

  if ($r[0] === 'syllabus') {
    $data = portal_syllabus_get([ 'code'=>$r[1] ]);
    /*if ($data['status'] === 'success') {
      $teacher = $data['data']['teacher'];
      if (mb_substr_count($teacher, ',') > 2)
        $teacher = mb_substr($teacher, 0, mb_strpos($teacher, ',', mb_strpos($teacher, ',') + 1)) . '…';
      $title = $data['data']['name'] . ($teacher === '' ?  '' : '[' . $teacher . ']');
      $summary = $data['data']['term'] . '/' . $data['data']['time'] . '/' . $data['data']['credit'] . '単位/' . $data['data']['summary'];
    }*/
  } else if ($r[0] === 'search') {
    $title = 'シラバス検索';
    $summary = '';
  }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# <?php echo($type); ?>: http://ogp.me/ns/<?php echo ($type); ?>#">
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta property="og:url" content="https://<?php echo($_SERVER['HTTP_HOST']); ?>/<?php echo(get_request()); ?>" />
  <meta property="og:type" content="<?php echo($type); ?>" />
  <meta property="og:title" content="<?php echo($title); ?>" />
  <meta property="og:description" content="<?php echo($summary); ?>" />
  <meta property="og:site_name" content="chibasys  by reolink" />
  <meta property="og:image" content="<?php echo($image_url); ?>" />
  <meta name="msapplication-square70x70logo" content="/site-tile-70x70.png">
  <meta name="msapplication-square150x150logo" content="/site-tile-150x150.png">
  <meta name="msapplication-wide310x150logo" content="/site-tile-310x150.png">
  <meta name="msapplication-square310x310logo" content="/site-tile-310x310.png">
  <meta name="msapplication-TileColor" content="#0078d7">
  <link rel="shortcut icon" type="image/vnd.microsoft.icon" href="/favicon.ico">
  <link rel="icon" type="image/vnd.microsoft.icon" href="/favicon.ico">
  <link rel="apple-touch-icon" sizes="57x57" href="/apple-touch-icon-57x57.png">
  <link rel="apple-touch-icon" sizes="60x60" href="/apple-touch-icon-60x60.png">
  <link rel="apple-touch-icon" sizes="72x72" href="/apple-touch-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="76x76" href="/apple-touch-icon-76x76.png">
  <link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon-114x114.png">
  <link rel="apple-touch-icon" sizes="120x120" href="/apple-touch-icon-120x120.png">
  <link rel="apple-touch-icon" sizes="144x144" href="/apple-touch-icon-144x144.png">
  <link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-180x180.png">
  <link rel="icon" type="image/png" sizes="36x36" href="/android-chrome-36x36.png">
  <link rel="icon" type="image/png" sizes="48x48" href="/android-chrome-48x48.png">
  <link rel="icon" type="image/png" sizes="72x72" href="/android-chrome-72x72.png">
  <link rel="icon" type="image/png" sizes="96x96" href="/android-chrome-96x96.png">
  <link rel="icon" type="image/png" sizes="128x128" href="/android-chrome-128x128.png">
  <link rel="icon" type="image/png" sizes="144x144" href="/android-chrome-144x144.png">
  <link rel="icon" type="image/png" sizes="152x152" href="/android-chrome-152x152.png">
  <link rel="icon" type="image/png" sizes="192x192" href="/android-chrome-192x192.png">
  <link rel="icon" type="image/png" sizes="256x256" href="/android-chrome-256x256.png">
  <link rel="icon" type="image/png" sizes="384x384" href="/android-chrome-384x384.png">
  <link rel="icon" type="image/png" sizes="512x512" href="/android-chrome-512x512.png">
  <link rel="icon" type="image/png" sizes="36x36" href="/icon-36x36.png">
  <link rel="icon" type="image/png" sizes="48x48" href="/icon-48x48.png">
  <link rel="icon" type="image/png" sizes="72x72" href="/icon-72x72.png">
  <link rel="icon" type="image/png" sizes="96x96" href="/icon-96x96.png">
  <link rel="icon" type="image/png" sizes="128x128" href="/icon-128x128.png">
  <link rel="icon" type="image/png" sizes="144x144" href="/icon-144x144.png">
  <link rel="icon" type="image/png" sizes="152x152" href="/icon-152x152.png">
  <link rel="icon" type="image/png" sizes="160x160" href="/icon-160x160.png">
  <link rel="icon" type="image/png" sizes="192x192" href="/icon-192x192.png">
  <link rel="icon" type="image/png" sizes="196x196" href="/icon-196x196.png">
  <link rel="icon" type="image/png" sizes="256x256" href="/icon-256x256.png">
  <link rel="icon" type="image/png" sizes="384x384" href="/icon-384x384.png">
  <link rel="icon" type="image/png" sizes="512x512" href="/icon-512x512.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/icon-16x16.png">
  <link rel="icon" type="image/png" sizes="24x24" href="/icon-24x24.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/icon-32x32.png">
  <link rel="manifest" href="/manifest.json">
  <title>chibasys by reolink</title>
  <link href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.8.2/css/mdb.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.4.0/css/bootstrap4-toggle.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.15.5/dist/bootstrap-table.min.css">
  <link href="/core.css" rel="stylesheet">
</head>

<body>
  <header class="zakuro z-depth-1">
    <div class="container d-flex">
      <a class="navbar-brand text-white pt-2 mt-1">chibasys</a>
      <button type="button" class="btn btn-default" onclick="login_proceed_button();" style="margin-left: auto;">
        <div id="header-name" style="display: inline-block;">未ログイン</div>
        <img class="rounded-circle" src="<?php if (isset($_SESSION['google_photo_url'])) echo($_SESSION['google_photo_url']); ?>" style="height: 25px;"/>
      </button>
    </div>
  </header>

  <div class="modal fade" id="login-modal" tabindex="-1" role="dialog" aria-labelledby="login-title" aria-hidden="true" style="z-index: 2000;">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="login-title">ログイン/新規登録</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group md-form">
            <label for="portal_id">学生証番号</label>
            <input type="text" id="portal_id" class="form-control" maxlength="10" pattern="^[0-9A-Z]+$" value="" required>
          </div>
          <div class="form-group md-form">
            <label for="portal_pass">パスワード</label>
            <input type="password" id="portal_pass" class="form-control" pattern="^[!-~]+$" placeholder="" required>
          </div>
          <button id="login-with-portal-button" type="button" class="btn btn-primary btn-block" onclick="login_with_portal();">ログイン/新規登録</button>
          <hr>
          <button type="button" class="btn btn-danger btn-block" onclick="location.href='/auth?mode=login';">Googleアカウントでログイン</button>
        </div>
      </div>
    </div>
  </div>

  <div id="contents-box" class="container tab-content overflow-hidden px-0 py-5 pb-lg-1" style="min-height: 100vh;">
    <div class="tab-pane accordion fade" id="syllabus-content" role="tabpanel" aria-labelledby="nav-profile-tab">
      <div class="card z-depth-0 bg-transparent">
        <div class="card-header bg-transparent mb-0" data-toggle="collapse" data-target="#search-form" aria-expand="true" aria-controls="search-form">検索</div>
        <div id="search-form" class="collapse show p-2">
          <div class="alert alert-info text-center" role="alert">AND検索と大文字小文字混用に対応<br>(例:(1)と（１）)(教科名のみ)</div>
          <div class="form-row mb-2">
            <label class="col-3 text-right" for="search-nendo">年度(*必須)</label>
            <div class="col-9 d-flex">
              <select id="search-nendo" class="form-control stb">
                <option selected>2019</option>
                <option>2020</option>
              </select>
            </div>
          </div>
          <div class="form-row mb-2">
            <label class="col-3 text-right" for="search-jikanwariShozokuCode">時間割所属</label>
            <div class="col-9 d-flex">
              <select id="search-jikanwariShozokuCode" class="form-control">
                <?php foreach (jikanwariShozoku as $k => $v) echo("<option value=\"$k\">".(str_repeat('　', strlen($k) - 2))."$v</option>"); ?>
              </select>
            </div>
          </div>
          <div class="form-row mb-2">
            <label class="col-3 text-right" for="search-class_type">授業の種類</label>
            <div class="col-9 d-flex">
              <select id="search-class_type" class="form-control">
                <?php foreach (class_type as $k => $v) echo("<option value=\"$k\">".(str_repeat('　', strlen($k) - 1))."$v</option>"); ?>
              </select>
            </div>
          </div>
          <div class="form-row mb-2">
            <label class="col-3 text-right" for="search-term">ターム(複数可)</label>
            <div class="col-9 d-flex">
              <select id="search-term" class="form-control stb" multiple="multiple">
                <option value="" selected="selected">全て</option>
                <option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option>
              </select>
            </div>
          </div>
          <div class="form-row mb-2">
            <label class="col-3 text-right" for="search-grade">年次(複数可)</label>
            <div class="col-9 d-flex">
              <select id="search-grade" class="form-control stb" multiple="multiple">
                <option value="" selected="selected">全て</option>
                <option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option>
              </select>
            </div>
          </div>
          <div class="form-row mb-2">
            <label class="col-3 text-right" for="search-day">曜日</label>
            <div class="col-9 d-flex">
              <select id="search-day" class="form-control stb">
                <?php foreach (time_day as $k => $v) echo("<option value=\"$k\">$v</option>"); ?>
              </select>
            </div>
          </div>
          <div class="form-row mb-2">
            <label class="col-3 text-right" for="search-hour">時限</label>
            <div class="col-9 d-flex">
              <select id="search-hour" class="form-control stb">
                <option value="" selected="selected">全て</option>
                <option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option><option>7</option>
              </select>
            </div>
          </div>
          <div class="form-row mb-2">
            <label class="col-3 text-right" for="search-name">科目名</label>
            <div class="col-9 md-form my-0">
              <input id="search-name" type="text" class="form-control pt-0 mb-0">
            </div>
          </div>
          <div class="form-row mb-2">
            <label class="col-3 text-right" for="search-teacher">教師名</label>
            <div class="col-9 md-form my-0">
              <input id="search-teacher" type="text" class="form-control pt-0 mb-0">
            </div>
          </div>
          <div class="form-row mb-2">
            <button class="btn btn-primary btn-block mx-5" onclick="search_start();">検索</button>
          </div>
        </div>
      </div>
      <div class="card z-depth-0 bg-transparent">
        <div class="card-header bg-transparent mb-0" data-toggle="collapse" data-target="#favorite-box" aria-expand="true" aria-controls="favorite-box">お気に入り</div>
        <div id="favorite-box" class="collapse show p-2">
          <table id="favorite-table"></table>
        </div>
      </div>
      <div class="card z-depth-0 bg-transparent">
        <div class="card-header bg-transparent mb-0" data-toggle="collapse" data-target="#mincam-box" aria-expand="true" aria-controls="mincam-box">授業評価検索</div>
        <div id="mincam-box" class="collapse show p-2">
          <div class="form-group md-form">
            <label for="title-mincam">教科名</label>
            <input type="text" id="title-mincam" class="form-control">
          </div>
          <div class="form-group md-form">
            <label for="teacher-mincam">教師名(姓名はスペース区切り)</label>
            <input type="text" id="teacher-mincam" class="form-control">
          </div>
          <div class="form-group md-form">
            <label for="message-mincam">コメントから検索</label>
            <input type="text" id="message-mincam" class="form-control">
          </div>
          <button class="btn btn-primary btn-block" onclick="mincam_start();">検索</button>
          <p class="text-right mt-2">powered by <a target="_blank" href="https://campus.nikki.ne.jp/?module=lesson&univ=%C0%E9%CD%D5%C2%E7%B3%D8">みんなのキャンパス</a></p>
        </div>
      </div>
      <div class="card z-depth-0 bg-transparent">
        <div class="card-header bg-transparent mb-0" data-toggle="collapse" data-target="#comment-box" aria-expand="true" aria-controls="comment-box">最近のコメント</div>
        <div id="comment-box" class="collapse show p-2">
          Coming soon...
        </div>
      </div>
    </div>
    <div class="tab-pane fade p-2 pb-5" id="classes-content" role="tabpanel" aria-labelledby="nav-home-tab">
      <h5 id="classes-gp" class="my-2">現時点でのGP: 読み込み中...</h5>
      <h5 id="classes-gpa" class="my-2">現時点でのGPA: 読み込み中...</h5>
      <select id="classes-nendo" class="form-control"></select>
      <table id="classes-table"></table>
      <div class="overlay portal-overlay" style="display: block;">
        <div class="container d-flex flex-column justify-content-center p-5">
          <p class="text-white text-center">
            学生ポータルのIDとパスワードでのログインが必要です。
            履修登録や成績の閲覧、シラバスの確認がすばやく行えるようになります。
          </p>
          <button class="btn btn-secondary portal-login" onclick="login_proceed_button('portal');">ログイン/新規登録</button>
        </div>
      </div>
    </div>
    <div class="tab-pane fade p-2 active show" id="timetable-content" role="tabpanel" aria-labelledby="nav-contact-tab">
      <div class="d-flex justify-content-between" style="position:relative;">
        <button id="timetable-prev" class="btn" onclick="timetable_reload(-1);">＜前週</button>
        <h3 id="timetable-title" class="text-center">時間割</h3>
        <button id="timetable-next" class="btn" onclick="timetable_reload(1);">次週＞</button>
      </div>
      <div id="timetable-box" class="my-1">
        <table class="table tt-5">
          <thead>
            <tr id="timetable-date"><th></th><th></th><th></th><th></th><th></th><th></th></tr>
            <tr id="timetable-dow"><th></th><th>月</th><th>火</th><th>水</th><th>木</th><th>金</th></tr>
          </thead>
          <tbody>
            <tr><th>1</th><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><th>2</th><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><th>3</th><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><th>4</th><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><th>5</th><td></td><td></td><td></td><td></td><td></td></tr>
            <tr id="timetable-6th"><th>6</th><td></td><td></td><td></td><td></td><td></td></tr>
            <tr id="timetable-7th"><th>7</th><td></td><td></td><td></td><td></td><td></td></tr>
          </tbody>
        </table>
        <div id="classes-container"></div>
      </div>
      <div class="overlay portal-overlay" style="display: block;">
        <div class="container d-flex flex-column justify-content-center p-5">
          <p class="text-white text-center">
            学生ポータルのIDとパスワードでのログインが必要です。
            履修登録に基づき、時間割を自動生成します。
          </p>
          <button class="btn btn-secondary portal-login" onclick="login_proceed_button('portal');">ログイン/新規登録</button>
        </div>
      </div>
    </div>
    <div class="tab-pane fade" id="cicle-content" role="tabpanel" aria-labelledby="nav-contact-tab">
      <div class="card z-depth-0 bg-transparent">
        <div class="card-header bg-transparent mb-0" data-toggle="collapse" data-target="#circle_manage-box" aria-expand="true" aria-controls="circle_manage-box">団体申請</div>
        <div id="circle_manage-box" class="collapse show p-2">
          <button type="button" class="btn btn-success btn-block" onclick="apply_circle_show();">サークル等の団体登録申請</button>
        </div>
      </div>
    </div>
    <div class="tab-pane fade" id="others-content" role="tabpanel" aria-labelledby="nav-contact-tab">
      <div class="card z-depth-0 bg-transparent">
        <div class="card-header bg-transparent mb-0" data-toggle="collapse" data-target="#userdata-box" aria-expand="true" aria-controls="userdata-box">設定</div>
        <div id="userdata-box" class="collapse px-2 pb-1">
          <div id="new-alert" class="alert alert-warning text-center" role="alert" style="display:none;">新規登録のときは必ず一度保存してください。</div>
          <div class="form-group md-form">
            <label for="user-name">名前</label>
            <input type="text" id="user-name" class="form-control" value="" required>
            <span class="form-text text-muted">
              コメント以外の名前に使用されます。後から変更できます。
            </span>
          </div>
          <div class="form-group">
            <label>性別</label>
            <div class="custom-control custom-radio" style="display: inline-block;">
              <input class="custom-control-input user-sex-radio" type="radio" name="user-sex" id="user-sex-male" value="male" required>
              <label class="custom-control-label" for="user-sex-male">男性</label>
            </div>
            <div class="custom-control custom-radio" style="display: inline-block;">
              <input class="custom-control-input user-sex-radio" type="radio" name="user-sex" id="user-sex-female" value="female" required>
              <label class="custom-control-label" for="user-sex-female">女性</label>
            </div>
          </div>
          <h4>学生ポータルのログイン情報</h4>
          <h5>これらの項目を入力すると、履修登録や成績確認ができます。<br>
            匿名での履修/成績データの利用に同意したものとみなします。</h5>
          <div class="form-group md-form">
            <label for="user-portal_id">学生証番号</label>
            <input type="text" id="user-portal_id" class="form-control" maxlength="10" pattern="^[0-9A-Z]+$" placeholder="00A0000A" value="" required>
          </div>
          <div class="form-group md-form">
            <label for="user-portal_pass">パスワード</label>
            <input type="password" id="user-portal_pass" class="form-control" pattern="^[!-~]+$" placeholder="" required>
          </div>
          <div id="login-check-result"></div>
          <button id="userdata-save-button" type="button" class="btn btn-primary" onclick="userdata_save();">保存</button>
          <button id="portal-login-check-button" type="button" class="btn btn-secondary" onclick="login_with_portal(true);">ログインチェック</button>
          <a id="portal-login-check-result"></a>
        </div>
      </div>
      <div class="card z-depth-0 bg-transparent">
        <div class="card-header bg-transparent mb-0" data-toggle="collapse" data-target="#calendar-box" aria-expand="true" aria-controls="calendar-box">Googleカレンダーの管理</div>
        <div id="calendar-box" class="collapse show p-2">
          <button type="button" class="btn btn-danger btn-block mb-2" onclick="location.href='/auth?mode=login';">Googleアカウントでログイン</button>
          <button type="button" class="btn btn-success btn-block" onclick="calendar_show();">Googleカレンダーに追加済みの教科を確認</button>
        </div>
      </div>
      <div class="card z-depth-0 bg-transparent">
        <div class="card-header bg-transparent mb-0" data-toggle="collapse" data-target="#link-box" aria-expand="true" aria-controls="link-box">リンク集</div>
        <div id="link-box" class="collapse show p-2">
          <a class="btn btn-link btn-block text-left" href="https://cup.chiba-u.jp/campusweb/" target="_blank">千葉大学 学生ポータル</a>
          <a class="btn btn-link btn-block text-left" href="https://cup.chiba-u.jp/campusweb/campussquare.do?locale=ja_JP&_flowId=SYW3901100-flow" target="_blank">千葉大学 シラバス検索</a>
          <a class="btn btn-link btn-block text-left" href="https://moodle2.chiba-u.jp/moodle<?php echo($nendo - 2000); ?>/" target="_blank">千葉大学 Moodle(<?php echo($nendo); ?>年度版)</a>
          <a class="btn btn-link btn-block text-left" href="https://calendar.google.com/" target="_blank">Googleカレンダー</a>
        </div>
      </div>
      <div class="card z-depth-0 bg-transparent">
        <div class="card-header bg-transparent mb-0" data-toggle="collapse" data-target="#moodle-link-box" aria-expand="true" aria-controls="moodle-link-box">過去年度 Moodleリンク集</div>
        <div id="moodle-link-box" class="collapse p-2">
          <?php
            for ($n = 18; $n < $nendo - 2000; $n++)
              echo("<a class=\"btn btn-link btn-block text-left\" href=\"https://moodle2.chiba-u.jp/moodle$n/\" target=\"_blank\">".($n + 2000)."年度版</a>");
          ?>
        </div>
      </div>
    </div>
  </div>

  <footer class="zakuro text-white z-depth-2"> <!--flex-sm-row-->
    <div id="tabs-box" class="container px-0 nav nav-pills flex-row">
      <a class="flex-fill btn m-0 nav-link" data-toggle="tab" data-target="#syllabus-content">シラバス</a>
      <a class="flex-fill btn m-0 nav-link" data-toggle="tab" data-target="#classes-content">履修/成績</a>
      <a class="flex-fill btn m-0 nav-link active" data-toggle="tab" data-target="#timetable-content">時間割</a>
      <a class="flex-fill btn m-0 nav-link" data-toggle="tab" data-target="#cicle-content">サークル</a>
      <a class="flex-fill btn m-0 nav-link" data-toggle="tab" data-target="#others-content">その他</a>
    </div>
  </footer>

  <div class="modal modal-nomal fullsize" id="search-modal" tabindex="-1" role="dialog" aria-labelledby="search-title" aria-hidden="true" data-keyboard="false" data-backdrop="false">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="search-title">検索結果</h4>
          <button type="button" class="close" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p id="search-header" style="text-align:right;"></p>
          <table id="search-table"></table>
        </div>
      </div>
    </div>
  </div>

  <div class="modal modal-nomal fullsize" id="mincam-modal" tabindex="-1" role="dialog" aria-labelledby="search-title" aria-hidden="true" data-keyboard="false" data-backdrop="false">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="mincam-title">授業評価検索結果</h4>
          <button type="button" class="close" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p id="mincam-header" style="text-align:right;"></p>
          <table id="mincam-table"></table>
        </div>
      </div>
    </div>
  </div>

  <div class="modal modal-nomal fullsize" id="syllabus-modal" tabindex="-1" role="dialog" aria-labelledby="syllabus-title" aria-hidden="true" data-keyboard="false" data-backdrop="false" style="z-index: 1100;">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable can-scroll" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="syllabus-title">シラバス詳細</h4>
          <button type="button" class="close" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <textarea id="syllabus-memo" class="p-3 mb-3" placeholder="メモを入力..."></textarea>
          <h5 class="mb-2">詳細情報</h5>
          <div id="syllabus-body-detail"></div>
          <h5 class="mt-3 mb-2">コメント</h5>
          <div class="form-group m-0" style="display:none;">
            <label>年度:</label>
            <div class="custom-control custom-radio" style="display: inline-block;">
              <input class="custom-control-input" type="radio" name="commentNendo" id="commentNendo-one" value="false" required checked>
              <label class="custom-control-label" for="commentNendo-one" id="commentNendo-one-label">今年度のみ</label>
            </div>
            <div class="custom-control custom-radio" style="display: inline-block;">
              <input class="custom-control-input" type="radio" name="commentNendo" id="commentNendo-all" value="true" required>
              <label class="custom-control-label" for="commentNendo-all">全ての年度</label>
            </div>
          </div>
          <div id="syllabus-body-comment"></div>
          <button id="syllabus-body-comment-load" class="btn btn-primary btn-block my-3" onclick="commentGet(this);">さらに読み込む</button>
          <div id="syllabus-body-comment-post" style="max-width:768px;">
            <h5>コメントをどうぞ</h5>
            <div class="form-group md-form">
              <label for="comment-name">名前:</label>
              <input type="text" id="comment-name" class="form-control" placeholder="名無しの千葉大生">
            </div>
            <div class="form-group md-form">
              <label for="comment-text">コメント:</label>
              <textarea id="comment-text" class="md-textarea form-control" required></textarea>
            </div>
            <span class="form-text text-muted">
              不適切な発言は慎みましょう。
            </span>
            <button id="syllabus-comment-post-button" class="btn btn-primary btn-block my-3" onclick="comment_post(this);">投稿</button>
          </div>
          <h5 class="mt-3 mb-2">関連する授業評価</h5>
          <div id="syllabus-body-mincam"></div>
        </div>
        <div class="modal-footer">

        </div>
      </div>
    </div>
  </div>

  <div class="modal modal-nomal fade" id="syllabus-link-modal" tabindex="-1" role="dialog" aria-labelledby="syllabus-link-title" data-keyboard="false" data-backdrop="false" aria-hidden="true" style="z-index: 1200;">
    <div class="modal-dialog modal-dialog-centered modal-s modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="syllabus-link-title">シラバスをリンクで共有</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="qrcode" class="my-3 text-center"></div>
          <textarea class="form-control" readonly></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn" onclick="link_copy(this);">コピー</button>
          <button id="shareButton" type="button" class="btn" onclick="link_share(this);">アプリで共有</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal modal-nomal fullsize" id="calendar-modal" tabindex="-1" role="dialog" aria-labelledby="calendar-title" aria-hidden="true" data-keyboard="false" data-backdrop="false" style="z-index: 1100;">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="calendar-title">Googleカレンダー</h4>
          <button type="button" class="close" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <h5 id="calendar-credit" class="my-1" style="display:inline-block;">XXXX年度の単位数合計 : 読み込み中...</h5>
          <button id="switch-calendar-notification" class="ml-2 mb-2 btn btn-dark" onclick="cal_notify_toggle(this);">カレンダーの通知設定を変更(現在:オフ)</button>
          <div class="form-group">
            <label for="calendar-nendo">年度</label>
            <select id="calendar-nendo" class="form-control"></select>
          </div>
          <h5 id="calendar-h5" class="my-2">カレンダーに追加済みの教科一覧</h5>
          <table id="calendar-table"></table>
        </div>
      </div>
    </div>
  </div>

  <div class="modal modal-nomal fullsize" id="apply_circle-modal" tabindex="-1" role="dialog" aria-labelledby="apply_circle-title" aria-hidden="true" data-keyboard="false" data-backdrop="false" style="z-index: 1100;">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable can-scroll" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="apply_circle-title">サークル等の団体登録申請</h4>
          <button type="button" class="close" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <p class="mb-3" style="line-height:1.5;">登録するには代表者の方がchibasysにログイン済みの状態で以下のフォームに記入していただく必要があります。
              なお、現時点ではサークルのページができておりません。でき次第、Twitter(@chibasys)にてご案内いたします。
              また、どの項目も後に変更ができます。その場合はxperd00@gmail.comまたはTwitterのDMにお申し付けください。<br>
              <font color="red">また画像はメールでの提出をお願いしております。xperd00@gmail.comまで団体名を名乗ってお送りください。</font></p>
            <h4>団体について</h4>
            <div class="form-group md-form">
              <label for="circle-name">部活・サークル名*</label>
              <input type="text" id="circle-name" class="form-control" placeholder="XXXX部" required>
            </div>
            <div class="form-group md-form">
              <label for="circle-id">団体ID(半角英数字3文字以上)*</label>
              <input type="text" id="circle-id" class="form-control" placeholder="chibasys" pattern="^([a-zA-Z0-9]{3,})$" required>
            </div>
            <span class="form-text text-muted mb-3">付与されるURL(https://chibasys.xperd.net/circle?######)に影響します</span>
            <div class="form-group mb-form">
              <label for="circle-official">公認/順公認/非公認*</label>
              <div>
                <select id="circle-official" class="form-control" required>
                  <option value="1">公認</option>
                  <option value="2">準公認</option>
                  <option value="3">非公認</option>
                </select>
              </div>
            </div>
            <div class="form-group mb-form">
              <label for="circle-type">団体の種別*</label>
              <div>
                <select id="circle-type" class="form-control" required>
                  <option value="1">運動系部活</option>
                  <option value="2">文化系部活</option>
                  <option value="3">運動系サークル</option>
                  <option value="4">文化系サークル</option>
                  <option value="5">ボランティア団体</option>
                  <option value="-1">その他(備考欄にお書きください)</option>
                </select>
              </div>
            </div>
            <div class="form-group mb-form">
              <label for="circle-base">団体の本拠地*</label>
              <div>
                <select id="circle-base" class="form-control" value="1" required>
                  <option value="0">特になし</option>
                  <option value="1" selected>西千葉キャンパス</option>
                  <option value="2">亥鼻キャンパス</option>
                  <option value="3">松戸キャンパス</option>
                  <option value="4">柏の葉キャンパス</option>
                  <option value="5">インカレ(備考欄に主な所属大学をお書きください)</option>
                  <option value="-1">その他(備考欄にお書きください)</option>
                </select>
              </div>
            </div>
            <h4>団体のメンバーについて</h4>
            <div class="form-group md-form">
              <label for="circle-scale">人数*</label>
              <input type="text" id="circle-scale" class="form-control" placeholder="約100人" required>
            </div>
            <div class="form-group md-form">
              <label for="circle-sex">男女比*</label>
              <input type="text" id="circle-sex" class="form-control" placeholder="男:女=3:7" required>
            </div>
            <div class="form-group md-form">
              <label for="circle-active">出席率(1回の活動で来る人数の割合)</label>
              <input type="text" id="circle-active" class="form-control" placeholder="X割">
            </div>
            <div class="form-group md-form">
              <label for="circle-multi">兼サー率</label>
              <input type="text" id="circle-multi" class="form-control" placeholder="X割">
            </div>
            <div class="form-group mb-form">
              <label for="circle-international">留学生の在籍履歴*</label>
              <div>
                <select id="circle-international" class="form-control" value="-1" required>
                  <option value="0">ここ最近はなし</option>
                  <option value="1">昨年度あり</option>
                  <option value="2">過去にあったが昨年度はなし</option>
                  <option value="-1" selected>わからない</option>
                </select>
              </div>
            </div>
            <h4>団体の活動について</h4>
            <div class="form-group md-form">
              <label for="circle-place">活動場所*</label>
              <textarea id="circle-place" class="md-textarea form-control" placeholder="月 第一体育館&#13;&#10;水木 千葉市立XX体育館" required></textarea>
            </div>
            <div class="form-group md-form">
              <label for="circle-room">団体専有部屋の場所*</label>
              <textarea id="circle-room" class="md-textarea form-control" placeholder="文化系サークル会館1階" required></textarea>
            </div>
            <div class="form-group md-form">
              <label for="circle-freq">活動頻度*</label>
              <input type="text" id="circle-freq" class="form-control" placeholder="週に、月に、年にX回" required>
            </div>
            <div class="form-group md-form">
              <label for="circle-date">活動日時*</label>
              <textarea id="circle-date" class="md-textarea form-control" placeholder="月 17:00〜21:00&#13;&#10;水木 18:00〜19:30&#13;&#10;不定期" required></textarea>
            </div>
            <div class="form-group md-form">
              <label for="circle-cost">活動費用*</label>
              <textarea id="circle-cost" class="md-textarea form-control" placeholder="新規 XXXX円&#13;&#10;半期 XXXX円&#13;&#10;道具 XXXX円〜&#13;&#10;" required></textarea>
            </div>
            <div class="form-group md-form">
              <label for="circle-last_fes">昨年の大祭の活動内容*</label>
              <textarea id="circle-last_fes" class="md-textarea form-control" placeholder="たこ焼き販売&#13;&#10;各バンドの屋内ライブ&#13;&#10;ない場合は「なし」と記入" required></textarea>
            </div>
            <div class="form-group md-form">
              <label for="circle-content">主なの活動内容*</label>
              <textarea id="circle-content" class="md-textarea form-control" placeholder="100字を目安に" required></textarea>
            </div>
            <h5>年間スケジュール(合宿は内容も併記)</h5>
            <div class="form-group md-form">
              <label for="circle-schedule_4">4月</label>
              <input type="text" id="circle-schedule_4" class="form-control" placeholder="新歓">
            </div>
            <div class="form-group md-form">
              <label for="circle-schedule_5">5月</label>
              <input type="text" id="circle-schedule_5" class="form-control" placeholder="歓迎会">
            </div>
            <div class="form-group md-form">
              <label for="circle-schedule_6">6月</label>
              <input type="text" id="circle-schedule_6" class="form-control" placeholder="夏大会">
            </div>
            <div class="form-group md-form">
              <label for="circle-schedule_7">7月</label>
              <input type="text" id="circle-schedule_7" class="form-control" placeholder="夏ライブ">
            </div>
            <div class="form-group md-form">
              <label for="circle-schedule_8">8月</label>
              <input type="text" id="circle-schedule_8" class="form-control" placeholder="BBQ">
            </div>
            <div class="form-group md-form">
              <label for="circle-schedule_9">9月</label>
              <input type="text" id="circle-schedule_9" class="form-control" placeholder="夏合宿(バスケ)">
            </div>
            <div class="form-group md-form">
              <label for="circle-schedule_10">10月</label>
              <input type="text" id="circle-schedule_10" class="form-control" placeholder="秋合宿(北海道旅行)">
            </div>
            <div class="form-group md-form">
              <label for="circle-schedule_11">11月</label>
              <input type="text" id="circle-schedule_11" class="form-control" placeholder="大祭">
            </div>
            <div class="form-group md-form">
              <label for="circle-schedule_12">12月</label>
              <input type="text" id="circle-schedule_12" class="form-control" placeholder="クリスマス会">
            </div>
            <div class="form-group md-form">
              <label for="circle-schedule_1">1月</label>
              <input type="text" id="circle-schedule_1" class="form-control" placeholder="冬合宿(スノボ)">
            </div>
            <div class="form-group md-form">
              <label for="circle-schedule_2">2月</label>
              <input type="text" id="circle-schedule_2" class="form-control" placeholder="春合宿(バレー)">
            </div>
            <div class="form-group md-form">
              <label for="circle-schedule_3">3月</label>
              <input type="text" id="circle-schedule_3" class="form-control" placeholder="追いコン">
            </div>
            <h4>団体の広報について</h4>
            <div class="form-group md-form">
              <label for="circle-repre_name">代表者氏名*</label>
              <input type="url" id="circle-repre_name" class="form-control" placeholder="千葉 太郎" required>
            </div>
            <span class="form-text text-muted mb-3">※ログインした学生の氏名と一致する必要があります</span>
            <div class="form-group md-form">
              <label for="circle-repre_email">代表者連絡先メールアドレス*</label>
              <input type="url" id="circle-repre_email" class="form-control" placeholder="reolink@gmail.com" required>
            </div>
            <span class="form-text text-muted mb-3">※不備があった場合、連絡させていただきます</span>
            <div class="form-group md-form">
              <label for="circle-email">団体メールアドレス</label>
              <input type="url" id="circle-email" class="form-control" placeholder="chibasys@xperd.net">
            </div>
            <div class="form-group md-form">
              <label for="circle-website">公式サイト</label>
              <input type="url" id="circle-website" class="form-control" placeholder="https://chibasys.xperd.net/">
            </div>
            <div class="form-group md-form">
              <label for="circle-twitter">Twitter</label>
              <input type="text" id="circle-twitter" class="form-control" placeholder="@chibasys">
            </div>
            <div class="form-group md-form">
              <label for="circle-instagram">Instagram</label>
              <input type="text" id="circle-instagram" class="form-control" placeholder="@chibasys">
            </div>
            <div class="form-group md-form">
              <label for="circle-line">LINE(URL)</label>
              <input type="text" id="circle-line" class="form-control" placeholder="http://line.me/ti/p/%40abcdefg">
            </div>
            <span class="form-text text-muted">LINE@のアプリ▶友達追加▶URL▶URLをコピー</span>
            <div class="form-group md-form">
              <label for="circle-other_sns">その他のアカウント等</label>
              <textarea id="circle-other_sns" class="md-textarea form-control" placeholder="新歓Twitter @chibasys_2020"></textarea>
            </div>
            <div class="form-group md-form">
              <label for="circle-remark">備考</label>
              <textarea id="circle-remark" class="md-textarea form-control" placeholder=""></textarea>
            </div>
            <span class="form-text text-muted"></span>
            <button id="apply_circle_send-button" class="btn btn-primary btn-block my-3" onclick="apply_cricle_send(this);">送信</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="timeout-modal" tabindex="-1" role="dialog" aria-labelledby="timeout-title" aria-hidden="true" data-keyboard="false" data-backdrop="false" style="z-index: 2100;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="timeout-title">ログインしてください</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body h5">
          この先はGoogleアカウントでのログイン/登録が必要です。<br>
          ログインすると、時間割作成、単位数計算、お気に入り登録、カレンダーへの追加などが利用できるようになります。<br>
          ぜひ登録してお使いください。
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-dark h4" onclick="location.href='/welcome';">ようこそへ</button>
          <button type="button" class="btn btn-dark h4" onclick="localStorage['request']=getRequest(); location.href='/auth?mode=login';">ログイン</button>
        </div>
      </div>
    </div>
  </div>

  <div id="loading" class="overlay" style="z-index: 2000; display: block;">
    <div>
      <div class="spinner-border text-white" role="status"></div>
      <h4 class="text-white">読み込み中...</h4>
    </div>
  </div>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.4/umd/popper.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.8.2/js/mdb.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.4.0/js/bootstrap4-toggle.min.js"></script>
  <script type="text/javascript" src="https://unpkg.com/bootstrap-table@1.15.5/dist/bootstrap-table.min.js"></script>
  <script type="text/javascript" src="/select-togglebutton.js"></script>
  <script type="text/javascript" src="/jquery.qrcode.min.js"></script>
  <script type="text/javascript" src="/core.js"></script>
  <script type="text/javascript">
    <?php
    if (isset($_SESSION['request'])) {
      echo("var request = '$_SESSION[request]';\n");
      unset($_SESSION['request']);
    }
    ?>
  </script>
  <script async src="https://www.googletagmanager.com/gtag/js?id=UA-44630639-4"></script>
  <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
      dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', 'UA-44630639-4');
  </script>
</body>

</html>
