<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/core.php');
$r = explode("?", getRequest());
$type = ($r[0] === '' ? 'website' : 'article');
$title = 'ようこそ、delisysへ';
$summary = '';
$image_url = 'icon.png';
if (!sessionCheck(true)) {
  //セッションにリクエストを保存 (syllabus?2000-AA-BBBBBB-ja_JP)
  session_start();
  $_SESSION['request'] = getRequest();

  if ($r[0] === 'syllabus') {
    $link = mysqli_connect();
    $data = getSyllabusTemp($link, $r[1]);
    mysqli_close($link);
    if ($data['status'] === 'success') {
      $teacher = $data['data']['teacher'];
      if (mb_substr_count($teacher, ',') > 2)
        $teacher = mb_substr($teacher, 0, mb_strpos($teacher, ',', mb_strpos($teacher, ',') + 1)) . '…';
      $title = $data['data']['name'] . ($teacher === '' ?  '' : '[' . $teacher . ']');
      $summary = $data['data']['term'] . '/' . $data['data']['time'] . '/' . $data['data']['credit'] . '単位/' . $data['data']['summary'];
    }
  } else if ($r[0] === 'search') {
    $title = 'シラバス検索';
    $summary = '';
  }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# <?php echo ($type); ?>: http://ogp.me/ns/<?php echo ($type); ?>#">
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta property="og:url" content="https://delisys.xperd.net/<?php echo (getRequest()); ?>" />
  <meta property="og:type" content="<?php echo ($type); ?>" />
  <meta property="og:title" content="<?php echo ($title) ?>" />
  <meta property="og:description" content="<?php echo ($summary) ?>" />
  <meta property="og:site_name" content="delisys  by reolink" />
  <meta property="og:image" content="<?php echo ($image_url); ?>" />
  <title>delisys by reolink</title>
  <link href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.8.2/css/mdb.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.4.0/css/bootstrap4-toggle.min.css" rel="stylesheet">
  <link href="core.css" rel="stylesheet">
</head>

<body>
  <nav class="navbar navbar-dark bg-dark">
    <a href="/" class="navbar-brand">delisys</a>
    <div class="dropdown">
      <button type="button" id="dropdownMenuButton" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <div id="username"><?php echo (isset($_SESSION['userdata']) ? $_SESSION['userdata']['studentName'] : '未ログイン'); ?></div>
        <img class="rounded-circle" src="<?php if (isset($_SESSION['google_photo_url'])) echo ($_SESSION['google_photo_url']); ?>" style="height: 30px;" />
      </button>
      <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton" style="z-index:9999">
        <a class="dropdown-item" href="https://calendar.google.com/" target="_blank">Googleカレンダーへ</a>
        <a class="dropdown-item" href="https://cup.chiba-u.jp/campusweb/" target="_blank">学生ポータルへ</a>
        <a class="dropdown-item" href="https://cup.chiba-u.jp/campusweb/campussquare.do?locale=ja_JP&_flowId=SYW3901100-flow" target="_blank">千葉大シラバス検索へ</a>
        <?php echo (isset($_SESSION['id']) ? '<a class="dropdown-item" href="/auth?mode=logout">ログアウト</a>' : '<a class="dropdown-item" href="/auth?mode=login">ログイン</a>'); ?>
      </div>
    </div>
  </nav>

  <div class="alert alert-info text-center m-4" role="alert" <?php if (isset($_SESSION['userdata'])) echo('style="display:none;"'); ?>>
    登録機能を修正しました、何度も登録画面が出る方は<a href="/auth?mode=login">再ログイン</a>をお願いします
  </div>
  <div class="d-lg-flex w-100 px-md-3">
    <div class="my-md-3 mr-lg-3">
      <div id="search-form" class="bg-light p-3 p-md-5 text-center overflow-hidden">
        <h2 class="display-5">シラバス検索</h2>
        <div class="alert alert-info" role="alert" style="display:none;">AND検索と大文字小文字混用に対応<br>(例:(1)と（１）)(教科名のみ)</div>
        <div class="form-group">
          <label for="nendo">年度(*必須):</label>
          <select id="nendo" class="form-control stb">
            <option value="2020">2020</option>
            <option value="2019" selected>2019</option>
            <option value="2018">2018</option>
            <option value="2017">2017</option>
            <option value="2016">2016</option>
          </select>
        </div>
        <div class="form-group inline-parent">
          <label for="jikanwariShozokuCode">時間割所属:</label>
          <select id="jikanwariShozokuCode" class="form-control">
            <option value="" selected>指定なし</option>
            <option value="G1">普遍教育</option>
            <option value="L1">文学部</option>
            <option value="E1">教育学部</option>
            <option value="A1">法経学部</option>
            <option value="B1">法政経学部</option>
            <option value="S1">理学部</option>
            <option value="S11">　数学・情報数理学科</option>
            <option value="S12">　物理学科</option>
            <option value="S13">　化学科</option>
            <option value="S14">　生物学科</option>
            <option value="S15">　地球科学科</option>
            <option value="S18">　先進科学プログラム</option>
            <option value="M1">医学部</option>
            <option value="M11">　医学科</option>
            <option value="P1">薬学部</option>
            <option value="P13">　薬学科</option>
            <option value="P14">　薬科学科</option>
            <option value="N1">看護学部</option>
            <option value="N11">　看護学科</option>
            <option value="T1">工学部</option>
            <option value="T1V">　総合工学科</option>
            <option value="T1V1">　　建築学コース</option>
            <option value="T1V2">　　都市環境システムコース</option>
            <option value="T1V3">　　デザインコース</option>
            <option value="T1V4">　　機械工学コース</option>
            <option value="T1V5">　　医工学コース</option>
            <option value="T1V6">　　電気電子工学コース</option>
            <option value="T1V7">　　物質科学コース</option>
            <option value="T1V8">　　共生応用化学コース</option>
            <option value="T1V9">　　情報工学コース</option>
            <option value="T1E">　都市環境システム学科</option>
            <option value="T1K">　先進科学プログラム</option>
            <option value="T1K2">　　工学部先進科学プログラム(フロンティア)</option>
            <option value="T1L">　メディカルシステム工学科</option>
            <option value="T1M">　共生応用化学科Aコース</option>
            <option value="T1N">　建築学科</option>
            <option value="T1P">　デザイン学科</option>
            <option value="T1Q">　機械工学科</option>
            <option value="T1R">　電気電子工学科</option>
            <option value="T1S">　ナノサイエンス学科</option>
            <option value="T1T">　画像科学科</option>
            <option value="T1U">　情報画像学科</option>
            <option value="T1F">　デザイン工学科Aコース</option>
            <option value="T1F4">　　建築コース</option>
            <option value="H1">園芸学部</option>
            <option value="Z1">国際教養学部</option>
            <option value="Z11">　国際教養学科</option>
            <option value="E2">教育学研究科</option>
            <option value="E21">　学校教育専攻</option>
            <option value="E215">　　学校心理学コース</option>
            <option value="E216">　　発達教育科学コース</option>
            <option value="E22">　国語教育専攻</option>
            <option value="E23">　社会科教育専攻</option>
            <option value="E24">　数学教育専攻</option>
            <option value="E25">　理科教育専攻</option>
            <option value="E26">　音楽教育専攻</option>
            <option value="E27">　美術教育専攻</option>
            <option value="E28">　保健体育専攻</option>
            <option value="E2A">　家政教育専攻</option>
            <option value="E2B">　英語教育専攻</option>
            <option value="E2C">　養護教育専攻</option>
            <option value="E2D">　学校教育臨床専攻</option>
            <option value="E2E">　カリキュラム開発専攻</option>
            <option value="E2F">　特別支援専攻</option>
            <option value="E2G">　スクールマネジメント専攻</option>
            <option value="E2H">　学校教育科学専攻</option>
            <option value="E2H1">　　教育発達支援系</option>
            <option value="E2H2">　　教育開発臨床系</option>
            <option value="E2I">　教科教育科学専攻</option>
            <option value="E2I1">　　言語・社会系</option>
            <option value="E2I2">　　理数・技術系</option>
            <option value="E2I3">　　芸術・体育系</option>
            <option value="S2">理学研究科</option>
            <option value="S21">　基盤理学専攻</option>
            <option value="S211">　　数学・情報数理学コース</option>
            <option value="S212">　　物理学コース</option>
            <option value="S213">　　化学コース</option>
            <option value="S22">　地球生命圏科学専攻</option>
            <option value="S221">　　生物学コース</option>
            <option value="S222">　　地球科学コース</option>
            <option value="S23">　基盤理学専攻</option>
            <option value="S231">　　数学・情報数理学コース</option>
            <option value="S232">　　物理学コース</option>
            <option value="S233">　　化学コース</option>
            <option value="S24">　地球生命圏科学専攻</option>
            <option value="S241">　　生物学コース</option>
            <option value="S242">　　地球科学コース</option>
            <option value="N2">看護学研究科</option>
            <option value="N21">　看護学専攻</option>
            <option value="N265">　　国際プログラム(訪問)</option>
            <option value="N266">　　国際プログラム(看護管理)</option>
            <option value="N267">　　国際プログラム(看護病態)</option>
            <option value="T2">工学研究科</option>
            <option value="T21">　建築・都市科学専攻</option>
            <option value="T211">　　建築学コース</option>
            <option value="T212">　　都市環境システムコース</option>
            <option value="T22">　デザイン科学専攻</option>
            <option value="T221">　　デザイン科学コース</option>
            <option value="T23">　人工システム科学専攻</option>
            <option value="T231">　　機械系コース</option>
            <option value="T232">　　電気電子系コース</option>
            <option value="T233">　　メディカルシステムコース</option>
            <option value="T24">　共生応用化学専攻</option>
            <option value="T241">　　共生応用化学コース</option>
            <option value="T25">　建築・都市科学専攻</option>
            <option value="T251">　　建築学コース</option>
            <option value="T252">　　都市環境システムコース</option>
            <option value="T26">　デザイン科学専攻</option>
            <option value="T261">　　デザイン科学コース</option>
            <option value="T27">　人工システム科学専攻</option>
            <option value="T271">　　機械系コース</option>
            <option value="T272">　　電気電子系コース</option>
            <option value="T273">　　メディカルシステムコース</option>
            <option value="T28">　共生応用化学専攻</option>
            <option value="T281">　　共生応用化学コース</option>
            <option value="H2">園芸学研究科</option>
            <option value="I2">人文社会科学研究科</option>
            <option value="I21">　地域文化形成専攻</option>
            <option value="I213">　　言語行動</option>
            <option value="I22">　公共研究専攻</option>
            <option value="I221">　　公共思想制度研究</option>
            <option value="I222">　　共生社会基盤研究</option>
            <option value="I23">　社会科学研究専攻</option>
            <option value="I232">　　経済理論・政策学(経</option>
            <option value="I233">　　経済理論・政策学(金</option>
            <option value="I24">　総合文化研究専攻</option>
            <option value="I241">　　言語構造</option>
            <option value="I243">　　人間行動</option>
            <option value="I25">　先端経営科学専攻</option>
            <option value="I26">　公共研究専攻</option>
            <option value="I261">　　公共哲学</option>
            <option value="I27">　社会科学研究専攻</option>
            <option value="I28">　文化科学研究専攻</option>
            <option value="I281">　　比較言語文化</option>
            <option value="Y2">融合科学研究科</option>
            <option value="Y21">　ナノサイエンス専攻</option>
            <option value="Y211">　　ナノ物性コース</option>
            <option value="Y212">　　ナノバイオロジーコー</option>
            <option value="Y22">　情報科学専攻</option>
            <option value="Y221">　　画像マテリアルコース</option>
            <option value="Y222">　　知能情報コース(前期</option>
            <option value="Y23">　ナノサイエンス専攻</option>
            <option value="Y231">　　ナノ物性コース(後期</option>
            <option value="Y232">　　ナノバイオロジーコー</option>
            <option value="Y24">　情報科学専攻</option>
            <option value="Y241">　　画像マテリアル 後期</option>
            <option value="Y242">　　知能情報コース</option>
            <option value="J2">医学薬学府</option>
            <option value="J21">　総合薬品科学専攻</option>
            <option value="J22">　医療薬学専攻</option>
            <option value="J23">　環境健康科学専攻</option>
            <option value="J231">　　医学領域</option>
            <option value="J232">　　薬学領域</option>
            <option value="J24">　先進医療科学専攻</option>
            <option value="J241">　　医学領域</option>
            <option value="J242">　　薬学領域</option>
            <option value="J25">　先端生命科学専攻</option>
            <option value="J251">　　医学領域</option>
            <option value="J252">　　薬学領域</option>
            <option value="J26">　創薬生命科学専攻</option>
            <option value="J27">　医科学専攻</option>
            <option value="J28">　先端医学薬学専攻</option>
            <option value="J281">　　先端生命(医学)</option>
            <option value="J282">　　先端生命(薬学)</option>
            <option value="J283">　　免疫統御(医学)</option>
            <option value="J284">　　免疫統御(薬学)</option>
            <option value="J285">　　先端臨床(医学)</option>
            <option value="J286">　　先端臨床(薬学)</option>
            <option value="J287">　　がん先端(医学)</option>
            <option value="J288">　　がん先端(薬学)</option>
            <option value="J29">　先端創薬科学専攻</option>
            <option value="J2A">　先進予防医学共同専攻</option>
            <option value="K2">専門法務研究科</option>
            <option value="W2">融合理工学府</option>
            <option value="W20">　数学情報科学専攻</option>
            <option value="W201">　　数学・情報数理学コース</option>
            <option value="W202">　　情報科学コース</option>
            <option value="W21">　地球環境科学専攻</option>
            <option value="W211">　　地球科学コース</option>
            <option value="W212">　　リモートセンシングコース</option>
            <option value="W213">　　都市環境システムコース</option>
            <option value="W22">　先進理化学専攻</option>
            <option value="W221">　　物理学コース</option>
            <option value="W222">　　物質科学コース</option>
            <option value="W223">　　化学コース</option>
            <option value="W224">　　共生応用化学コース</option>
            <option value="W225">　　生物学コース</option>
            <option value="W23">　創成工学専攻</option>
            <option value="W231">　　建築学コース</option>
            <option value="W232">　　イメージング科学コース</option>
            <option value="W233">　　デザインコース</option>
            <option value="W24">　基幹工学専攻</option>
            <option value="W241">　　機械工学コース</option>
            <option value="W242">　　医工学コース</option>
            <option value="W243">　　電気電子工学コース</option>
            <option value="W25">　数学情報科学専攻</option>
            <option value="W251">　　数学・情報数理学コース</option>
            <option value="W252">　　情報科学コース</option>
            <option value="W26">　地球環境科学専攻</option>
            <option value="W261">　　地球科学コース</option>
            <option value="W262">　　リモートセンシングコース</option>
            <option value="W263">　　都市環境システムコース</option>
            <option value="W27">　先進理化学専攻</option>
            <option value="W271">　　物理学コース</option>
            <option value="W272">　　物質科学コース</option>
            <option value="W273">　　化学コース</option>
            <option value="W274">　　共生応用化学コース</option>
            <option value="W275">　　生物学コース</option>
            <option value="W28">　創成工学専攻</option>
            <option value="W281">　　建築学コース</option>
            <option value="W282">　　イメージング科学コース</option>
            <option value="W283">　　デザインコース</option>
            <option value="W29">　基幹工学専攻</option>
            <option value="W291">　　機械工学コース</option>
            <option value="W292">　　医工学コース</option>
            <option value="W293">　　電気電子工学コース</option>
            <option value="D2">人文公共学府</option>
            <option value="D21">　人文科学専攻</option>
            <option value="D22">　公共社会科学専攻</option>
            <option value="D23">　人文公共学専攻</option>
            <option value="H3">園芸学部園芸別科</option>
            <option value="C1">留学生</option>
            <option value="G2">大学院共通教育</option>
          </select>
        </div>
        <div class="form-group">
          <label for="gakkiKubunCode">学期:</label>
          <select id="gakkiKubunCode" class="form-control stb">
            <option value="" selected="selected">なし</option>
            <option value="1">前期</option>
            <option value="2">後期</option>
          </select>
        </div>
        <div class="form-group">
          <label for="kaikoKubunCode">ターム(一部の選択肢省略):</label>
          <select id="kaikoKubunCode" class="form-control stb">
            <option value="" selected="selected">なし</option>
            <option value="1">前期</option>
            <option value="2">後期</option>
            <option value="3">通年</option>
            <option value="4">集中</option>
            <option value="5">年度跨り</option>
            <option value="6">T1</option>
            <option value="7">T2</option>
            <option value="8">T3</option>
            <option value="9">T4</option>
            <option value="A">T5</option>
            <option value="B">T6</option>
            <option value="C">T1-2</option>
            <option value="D">T4-5</option>
            <option value="E">前期集中</option>
            <option value="F">後期集中</option>
            <!--<option value="G">1・4T</option>
              <option value="H">1・5T</option>
              <option value="I">2・4T</option>
              <option value="J">2・5T</option>
              <option value="K">1-3T</option>
              <option value="L">2-3T</option>
              <option value="M">2-4T</option>
              <option value="N">4-6T</option>
              <option value="O">5-6T</option>
              <option value="P">1T集中</option>
              <option value="Q">2T集中</option>
              <option value="R">3T集中</option>
              <option value="S">4T集中</option>
              <option value="T">5T集中</option>
              <option value="U">6T集中</option>
              <option value="V">1-2T集中</option>
              <option value="W">4-5T集中</option>
              <option value="X">1-3T集中</option>
              <option value="Y">2-3T集中</option>
              <option value="Z">2-4T集中</option>
              <option value="a">4-6T集中</option>
              <option value="b">5-6T集中</option>-->
          </select>
        </div>
        <div class="form-group md-form inline-parent">
          <label for="kyokannmLike">教員名:</label>
          <input type="text" id="kyokannmLike" class="form-control">
        </div>
        <div class="form-group md-form inline-parent">
          <label for="jikanwaricdLike">授業コード:</label>
          <input type="text" id="jikanwaricdLike" class="form-control">
        </div>
        <div class="form-group md-form inline-parent">
          <label for="kaikoKamokunmLike">授業科目名:</label>
          <input type="text" id="kaikoKamokunmLike" class="form-control">
        </div>
        <div class="form-group">
          <label for="nenji">年次:</label>
          <select id="nenji" class="form-control stb">
            <option value="" selected="selected">なし</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
          </select>
        </div>
        <div class="form-group">
          <label for="yobi">曜日:</label>
          <select id="yobi" class="form-control stb">
            <option value="" selected="selected">なし</option>
            <option value="1">月</option>
            <option value="2">火</option>
            <option value="3">水</option>
            <option value="4">木</option>
            <option value="5">金</option>
            <option value="6">土</option>
            <!--<option value="9">その他</option>-->
          </select>
        </div>
        <div class="form-group">
          <label for="jigen">時限:</label>
          <select id="jigen" class="form-control stb">
            <option value="" selected="selected">なし</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
            <option value="7">7</option>
            <!--<option value="0">その他</option>-->
          </select>
        </div>
        <div class="form-group md-form inline-parent" style="display:none;">
          <label for="freeWord">フリーワード:</label>
          <input type="text" id="freeWord" class="form-control">
        </div>
        <div class="form-group inline-parent">
          <button class="btn btn-primary btn-block" onclick="startSearch();">検索</button>
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
        <button class="btn btn-primary btn-block" onclick="startMincam();">検索</button>
      </div>
    </div>
    <div class="my-md-3">
      <div class="star p-3 p-md-5 overflow-hidden">
        <h2 class="display-5 text-center">お気に入り</h2>
        <div id="favorite-box" class="my-3 mx-md-0" style="margin: 0 -.75rem;">

        </div>
        <h5>各教科をタップすると表示されるメニューから各操作ができます。</h5>
        <button class="btn btn-primary btn-block" onclick="showMultishare();" style="display:none;">複数の教科をまとめて共有する</button>
      </div>
      <div class="zakuro p-3 p-md-5 mt-md-3 overflow-hidden text-white">
        <h2 class="display-5 text-center">今週の時間割</h2>
        <div id="timetable-box" class="my-3 mx-md-0" style="margin: 0 -.75rem;">
          <table class="table tt-5">
            <thead>
              <tr>
                <th></th>
                <th>月</th>
                <th>火</th>
                <th>水</th>
                <th>木</th>
                <th>金</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th>1</th>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <th>2</th>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <th>3</th>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <th>4</th>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <th>5</th>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr id="timetable-6th">
                <th>6</th>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr id="timetable-7th">
                <th>7</th>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
            </tbody>
          </table>
          <div id="subjects-container" style="top:30px;left:30px;right:0;bottom:0;position:absolute;"></div>
        </div>
        <h5>各教科をタップするとシラバスを確認でき、時間割からの削除もできます。</h5>
        <p>ここに教科を追加する場合はシラバスを検索して、詳細情報へ行き、「カレンダーに追加」を行ってください。単位数集計にも必要です。</p>
        <p>お手持ちのカレンダーアプリで今ログインされているGoogleアカウントを登録すると10分前通知など、さらに便利な機能があります。</p>
        <button class="btn btn-success btn-block" onclick="editCalendar();">カレンダーに追加済みの教科を確認<br>
          <nav style="font-size:1.5rem;">単位数計算も</nav>
        </button>
      </div>
    </div>
  </div>

  <footer class="container py-5">
    <div class="row">
      <div class="col-12 col-md">
        <p>delisys -授業の予定を簡単にカレンダーへ挿入-</p>
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

  <div class="modal fade" id="register-modal" tabindex="-1" role="dialog" aria-labelledby="register-title" aria-hidden="true" data-keyboard="false" data-backdrop="false" style="z-index: 1500;">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="register-title">ようこそ、delisysへ</h4>
        </div>
        <div class="modal-body">
          <h5>簡単な質問にご協力ください</h5>
          <div class="form-group">
            <label for="studentName">名前:</label>
            <input type="text" id="studentName" class="form-control" value="<?php if (isset($_SESSION['google_user_name'])) echo ($_SESSION['google_user_name']); ?>" required>
            <span class="form-text text-muted">
              コメント以外の名前に使用されます。後から変更できます。(現時点では不可)
            </span>
          </div>
          <div class="form-group">
            <label for="studentID">学生証番号(左から5桁のみ):</label>
            <input type="text" id="studentID" class="form-control" maxlength="5" pattern="^[0-9A-Z]+$" placeholder="00A00" required>
            <span class="form-text text-muted">
              この5桁からは入学年度、学部、学科やコースを把握します。個人は特定されません。
            </span>
          </div>
          <div class="form-group">
            <label>性別:</label>
            <div class="custom-control custom-radio" style="display: inline-block;">
              <input class="custom-control-input" type="radio" name="studentSex" id="studentSex-male" value="male" required>
              <label class="custom-control-label" for="studentSex-male">男性</label>
            </div>
            <div class="custom-control custom-radio" style="display: inline-block;">
              <input class="custom-control-input" type="radio" name="studentSex" id="studentSex-female" value="female" required>
              <label class="custom-control-label" for="studentSex-female">女性</label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" onclick="register(this);">登録</button>
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

        </div>
      </div>
    </div>
  </div>

  <div class="modal modal-nomal fullsize" id="syllabus-modal" tabindex="-1" role="dialog" aria-labelledby="syllabus-title" aria-hidden="true" data-keyboard="false" data-backdrop="false" style="z-index: 1100;">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable" role="document">
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
            <button class="btn btn-primary btn-block my-3" onclick="postComment(this);">投稿</button>
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
          <button type="button" class="btn" onclick="copyLink(this);">コピー</button>
          <button id="shareButton" type="button" class="btn" onclick="shareLink(this);">アプリで共有</button>
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
            <input id="syllabus-calendar-notification" type="checkbox" data-toggle="toggle" data-size="sm" data-on="オン" data-off="オフ" <?php echo (isset($_SESSION['userdata']) ? ($_SESSION['userdata']['notification'] ? ' checked' : '') : ' checked'); ?>>
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
          <h4 class="modal-title" id="calendar-title">カレンダーに追加済みの教科</h4>
          <button type="button" class="close" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="alert alert-primary" role="alert">
            単位数合計は年度合計になります。
          </div>
          <div class="alert alert-warning" role="alert">
            単位数上限に関係のない教科、取得できなかった単位については考慮されません。
          </div>
          <button id="switch-calendar-notification" class="mx-0 btn btn-dark" onclick="toogleCalendarNotification(this);">カレンダーの通知設定を変更(現在:オフ)</button>
          <div id="year-box" class="form-group">

          </div>
          <h5 id="total-credit" class="my-3">XXXX年度の単位数合計 : 読み込み中...</h5>
          <h5 id="calendar-h5" class="my-2">XXXX年度の教科一覧</h5>
          <div id="calendar-box"></div>
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
  <script type="text/javascript" src="select-togglebutton.js"></script>
  <script type="text/javascript" src="jquery.qrcode.min.js"></script>
  <script type="text/javascript" src="core.js"></script>
  <script type="text/javascript">
    <?php
    if (isset($_SESSION['id']) && !isset($_SESSION['userdata']))
      echo ("var registerWindow = true;\n");
    if (isset($_SESSION['request'])) {
      echo ('var request = ' . $_SESSION['request'] + "\n");
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