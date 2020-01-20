<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/core.php');
init();
$r = explode("?", get_request());
$type = ($r[0] === '' ? 'website' : 'article');
$title = 'ようこそ、chibasysへ';
$summary = '';
$image_url = 'icon.png';
session_start();
if (isset($_SESSION['id']) && $_SESSION['id']) {
  $result = maria_query("SELECT * FROM user WHERE id=$_SESSION[id];");
  if (!$result) {  }
  if (mysqli_num_rows($result) !== 1) locate_welcome('?error=user_not_found');
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
  <title>chibasys by reolink</title>
  <link href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.8.2/css/mdb.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.4.0/css/bootstrap4-toggle.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.15.5/dist/bootstrap-table.min.css">
  <link href="core.css" rel="stylesheet">
</head>

<body>
  <nav class="navbar navbar-dark bg-dark">
    <a href="/" class="navbar-brand">chibasys</a>
    <div class="dropdown">
      <button type="button" id="dropdownMenuButton" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <div id="username"><?php echo(isset($_SESSION['id']) ? $userdata['studentName'] : '未ログイン'); ?></div>
        <img class="rounded-circle" src="<?php if (isset($_SESSION['google_photo_url'])) echo ($_SESSION['google_photo_url']); ?>" style="height: 30px;" />
      </button>
      <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton" style="z-index:9999">
        <a class="dropdown-item" href="https://calendar.google.com/" target="_blank">Googleカレンダーへ</a>
        <a class="dropdown-item" href="https://cup.chiba-u.jp/campusweb/" target="_blank">学生ポータルへ</a>
        <a class="dropdown-item" href="https://cup.chiba-u.jp/campusweb/campussquare.do?locale=ja_JP&_flowId=SYW3901100-flow" target="_blank">千葉大シラバス検索へ</a>
        <a class="dropdown-item" data-toggle="modal" data-target="#settings-modal"<?php if (!isset($userdata)) echo(' style="display:none;"'); ?>>設定</a>
        <?php echo (isset($_SESSION['id']) ? '<a class="dropdown-item" href="/auth?mode=logout">ログアウト</a>' : '<a class="dropdown-item" href="/auth?mode=login">ログイン</a>'); ?>
      </div>
    </div>
  </nav>

  <div class="d-lg-flex w-100 px-md-3">
    <div class="my-md-3 mr-lg-3">
      <div id="search-form" class="bg-light p-3 p-md-5 text-center overflow-hidden">
        <h2 class="display-5">シラバス検索</h2>
        <div class="alert alert-info" role="alert">AND検索と大文字小文字混用に対応<br>(例:(1)と（１）)(教科名のみ)</div>
        <div class="form-group">
          <label for="search-nendo">年度(*必須)</label>
          <select id="search-nendo" class="form-control stb">
            <option selected>2019</option>
          </select>
        </div>
        <div class="form-group inline-parent">
          <label for="search-jikanwariShozokuCode">時間割所属</label>
          <select id="search-jikanwariShozokuCode" class="form-control">
            <?php foreach (jikanwariShozoku as $k => $v) echo("<option value=\"$k\">".(str_repeat('　', strlen($k) - 2))."$v</option>"); ?>
          </select>
        </div>
        <div class="form-group inline-parent">
          <label for="search-class_type">授業の種類</label>
          <select id="search-class_type" class="form-control">
            <?php foreach (class_type as $k => $v) echo("<option value=\"$k\">".(str_repeat('　', strlen($k) - 1))."$v</option>"); ?>
          </select>
        </div>
        <div class="form-group">
          <label for="search-term">ターム(複数可)</label>
          <select id="search-term" class="form-control stb" multiple="multiple">
            <option value="" selected="selected">全て</option>
            <option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option>
          </select>
        </div>
        <div class="form-group">
          <label for="search-grade">年次(複数可)</label>
          <select id="search-grade" class="form-control stb" multiple="multiple">
            <option value="" selected="selected">全て</option>
            <option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option>
          </select>
        </div>
        <div class="form-group">
          <label for="search-day">曜日</label>
          <select id="search-day" class="form-control stb">
            <?php foreach (time_day as $k => $v) echo("<option value=\"$k\">$v</option>"); ?>
          </select>
        </div>
        <div class="form-group">
          <label for="search-hour">時限</label>
          <select id="search-hour" class="form-control stb">
            <option value="" selected="selected">全て</option>
            <option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option><option>7</option>
          </select>
        </div>
        <div class="form-group md-form inline-parent">
          <label for="search-name">科目名</label>
          <input id="search-name" type="text" class="form-control">
        </div>
        <div class="form-group md-form inline-parent">
          <label for="search-teacher">教師名</label>
          <input id="search-teacher" type="text" class="form-control">
        </div>
        <div class="form-group inline-parent">
          <button class="btn btn-primary btn-block" onclick="search_start();">検索</button>
        </div>
        <div class="form-group inline-parent">
          <button class="btn btn-secondary btn-block" onclick="classes_show();">履修登録や成績を確認</button>
        </div>
      </div>
      <div id="mincam-form" class="bg-info p-3 p-md-5 mt-md-3 overflow-hidden text-white text-center">
        <h2 class="display-5">授業評価検索</h2>
        <h5>powered by <a class="text-white-50" target="_blank" href="https://campus.nikki.ne.jp/?module=lesson&univ=%C0%E9%CD%D5%C2%E7%B3%D8">みんなのキャンパス</a></h5>
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
      </div>
    </div>
    <div class="my-md-3">
      <div class="star p-3 p-md-5 overflow-hidden">
        <h2 class="display-5 text-center">お気に入り</h2>
        <table id="favorite-table"></table>
      </div>
      <div class="zakuro p-3 p-md-5 mt-md-3 overflow-hidden text-white">
        <div class="mx-md-0" style="margin: 0 -.75rem; position:relative;">
          <button id="timetable-prev" class="btn" onclick="timetable_reload(-1);">＜前週</button>
          <h2 id="timetable-title" class="display-5 text-center">時間割</h2>
          <button id="timetable-next" class="btn" onclick="timetable_reload(1);">次週＞</button>
        </div>
        <div id="timetable-box" class="my-3 mx-md-0" style="margin: 0 -.75rem;">
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
          <div id="subjects-container" style="top:60px;left:30px;right:0;bottom:0;position:absolute;"></div>
        </div>
        <h5 class="text-center mb-3">タップしてシラバスを確認</h5>
        <div class="form-group inline-parent">
          <button class="btn btn-success btn-block" onclick="calendar_show();">カレンダーに追加済みの教科を確認</button>
        </div>
      </div>
    </div>
  </div>

  <footer class="container py-5">
    <div class="row">
      <div class="col-12 col-md">
        <p>chibasys -授業の予定を簡単にカレンダーへ挿入-</p>
        <small class="d-block mb-3 text-muted">© 2019 xperd</small>
      </div>
    </div>
  </footer>

  <div style="position:fixed;bottom:0;width:100%;z-index:10;" class="bg-dark">
    <button id="share-button" class="btn btn-block text-white" data-toggle="modal" data-target="#syllabus-link-modal">このサイトを共有する</button>
  </div>

  <div class="text-white mr-1 mb-1" style="position:fixed;bottom:0;right:0;font-size:.5rem;z-index:10;">
    VER.<?php
        $dateJS = filemtime('core.js');
        $dateCSS = filemtime('core.css');
        echo (date('ymdHi', ($dateJS > $dateCSS ? $dateJS : $dateCSS)));
        ?>
  </div>

  <div class="modal fade" id="settings-modal" tabindex="-1" role="dialog" aria-labelledby="settings-title" aria-hidden="true" data-keyboard="false" data-backdrop="false" style="z-index: 1500;">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="settings-title">chibasys 設定</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="studentName">名前</label>
            <input type="text" id="studentName" class="form-control" value="<?php
              if (isset($userdata)) echo($userdata['studentName']); else if (isset($_SESSION['google_user_name'])) echo($_SESSION['google_user_name']); ?>" required>
            <span class="form-text text-muted">
              コメント以外の名前に使用されます。後から変更できます。
            </span>
          </div>
          <div class="form-group">
            <label>性別</label>
            <div class="custom-control custom-radio" style="display: inline-block;">
              <input class="custom-control-input" type="radio" name="studentSex" id="studentSex-male" value="male" required<?php if (isset($userdata) && $userdata['studentSex'] === 'male') echo(' checked'); ?>>
              <label class="custom-control-label" for="studentSex-male">男性</label>
            </div>
            <div class="custom-control custom-radio" style="display: inline-block;">
              <input class="custom-control-input" type="radio" name="studentSex" id="studentSex-female" value="female" required<?php if (isset($userdata) && $userdata['studentSex'] === 'female') echo(' checked'); ?>>
              <label class="custom-control-label" for="studentSex-female">女性</label>
            </div>
          </div>
          <h4>学生ポータルのログイン情報</h4>
          <h5>これらの項目を入力すると、履修登録や成績確認ができます。<br>匿名での履修/成績データの利用に同意したものとみなします。</h5>
          <div class="form-group">
            <label for="studentID">学生証番号</label>
            <input type="text" id="studentID" class="form-control" maxlength="10" pattern="^[0-9A-Z]+$" placeholder="00A0000A" value="<?php if (isset($userdata) && $userdata['studentID']) echo($userdata['studentID']); ?>" required>
          </div>
          <div class="form-group">
            <label for="studentPass">パスワード</label>
            <input type="password" id="studentPass" class="form-control" pattern="^[!-~]+$" placeholder="<?php if (isset($userdata) && $userdata['studentPass']) echo('空欄で変更しない'); ?>" required>
          </div>
          <div id="login-check-result"></div>
          <button type="button" class="btn btn-secondary" onclick="loginCheck(this);" style="display:none;">ログインチェック</button>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" onclick="register(this);">保存</button>
        </div>
      </div>
    </div>
  </div>

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

  <div class="modal modal-nomal fade" id="syllabus-calendar-modal" tabindex="-1" role="dialog" aria-labelledby="syllabus-calendar-title" aria-hidden="true" data-keyboard="false" data-backdrop="false" style="z-index: 1200;">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="syllabus-calendar-title">カレンダーへの追加確認</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="alert alert-primary" role="alert">
            講義日時をログイン中のGoogleカレンダーに追加できます。<br>
            好きなカレンダーアプリで講義内容、評価方法などをすぐに確認でき、授業前に通知でお知らせを受け取れます。
            また、単位数にカウントできるようになります。
          </div>
          <div class="alert alert-danger" role="alert">
            時限がその他やタームが集中などのシラバスではカレンダーに追加できません。通期は1年ずっとになります。
          </div>
          <div class="form-group inline-paprent">
            <label>カレンダーの通知:</label>
            <input id="syllabus-calendar-notification" type="checkbox" data-toggle="toggle" data-size="sm" data-on="オン" data-off="オフ" <?php echo (isset($userdata) ? ($userdata['notification'] ? ' checked' : '') : ' checked'); ?>>
          </div>
          <button class="btn btn-block btn-info text-left px-3 py-2" data-toggle="collapse" data-target="#syllabus-calendar-collapse" aria-expand="false" aria-controls="syllabus-calendar-collapse">
            ▼カレンダーのメモ欄をカスタマイズ</button>
          <div id="syllabus-calendar-collapse" class="p-2 collapse" style="border: solid 1px #33b5e5;">
            <p>タップして切り替えられます、多すぎると見にくくなる可能性があります。この画面では内容は省略されています。</p>
            <table id="syllabus-calendar-table" class="table table-sm" style="white-space: nowrap;"></table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn" onclick="addCalendar(this);">カレンダーに追加する</button>
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

  <div class="modal modal-nomal fullsize" id="classes-modal" tabindex="-1" role="dialog" aria-labelledby="classes-title" aria-hidden="true" data-keyboard="false" data-backdrop="false" style="z-index: 1100;">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="classes-title">履修登録と成績</h4>
          <button type="button" class="close" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <h5 id="classes-grade" class="my-2">現時点でのGP : 読み込み中... / 現時点でのGPA : 読み込み中...</h5>
          <div class="form-group">
            <label for="classes-nendo">年度</label>
            <select id="classes-nendo" class="form-control"></select>
          </div>
          <h5 id="classes-h5" class="my-2">履修登録済み教科一覧</h5>
          <table id="classes-table"></table>
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

  <div id="loading" style="display:block;">
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
  <script type="text/javascript" src="select-togglebutton.js"></script>
  <script type="text/javascript" src="jquery.qrcode.min.js"></script>
  <script type="text/javascript" src="core.js"></script>
  <script type="text/javascript">
    <?php
    if (isset($_SESSION['id']) && !isset($userdata))
      echo ("var registerWindow = true;\n");
    if (isset($_SESSION['request'])) {
      echo ('var request = \''.$_SESSION['request']."';\n");
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