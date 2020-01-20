<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/core.php');
$link = mysqli_connect();
$r = explode("?", getRequest());
$result = maria_query($link, "SELECT id, studentName FROM chibasys.user WHERE publicID='$r[1]';");
if (!$result) locateWelcome();
$data = mysqli_fetch_assoc($result);
mysqli_close($link);
$title = $data['studentName'].'の時間割';
$summary = '';
$image_url = 'icon.png';
?>
<!DOCTYPE html>
<html lang="ja">
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#">
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta property="og:url" content="https://chibasys.xperd.net/<?php echo(getRequest()); ?>" />
  <meta property="og:type" content="article" />
  <meta property="og:title" content="<?php echo ($title) ?>" />
  <meta property="og:description" content="<?php echo ($summary) ?>" />
  <meta property="og:site_name" content="chibasys  by reolink" />
  <meta property="og:image" content="<?php echo ($image_url); ?>" />
  <title><?php echo($title); ?> - chibasys  by reolink</title>
  <link href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.8.2/css/mdb.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.4.0/css/bootstrap4-toggle.min.css" rel="stylesheet">
  <link href="core.css" rel="stylesheet">
  <script>var initPass = true;</script>
</head>

<body>
  <div class="d-lg-flex w-100 px-md-3">
    <div class="my-md-3">
      <div class="zakuro p-3 p-md-5 mt-md-3 overflow-hidden text-white">
        <div class="mx-md-0" style="margin: 0 -.75rem; position:relative;">
          <button id="timetable-prev" class="btn" onclick="reloadTimetable(-1);">＜前週</button>
          <h2 id="timetable-title" class="display-5 text-center">時間割</h2>
          <button id="timetable-next" class="btn" onclick="reloadTimetable(1);">次週＞</button>
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
        <h5>各教科をタップするとシラバスを確認でき、時間割からの削除もできます。</h5>
        <p>ここに教科を追加する場合はシラバスを検索して、詳細情報へ行き、「カレンダーに追加」を行ってください。単位数集計にも必要です。</p>
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
          <button type="button" class="btn" onclick="copyLink(this);">コピー</button>
          <button id="shareButton" type="button" class="btn" onclick="shareLink(this);">アプリで共有</button>
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
  <script type="text/javascript" src="select-togglebutton.js"></script>
  <script type="text/javascript" src="jquery.qrcode.min.js"></script>
  <script type="text/javascript" src="core.js"></script>
  <script type="text/javascript">
    <?php
    if (isset($_SESSION['request'])) {
      echo ('var request = ' . $_SESSION['request'] + "\n");
      unset($_SESSION['request']);
    }
    echo("reloadPortalRegistrationList(false, '$r[1]');");
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