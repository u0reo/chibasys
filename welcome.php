<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>chibasys  by reolink</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  </head>
  <body>
    <nav class="navbar navbar-dark bg-dark">
      <a href="/" class="navbar-brand">welcome to chibasys</a>
    </nav>
    <div id="login_error" class="alert alert-danger text-center m-4" role="alert" style="display:none;">
      ログイン失敗
    </div>
    <div id="login_required" class="alert alert-warning text-center m-4" role="alert" style="display:none;">
      Googleで<a href="/auth?mode=login">ログイン/新規登録</a>してください
    </div>
    <div id="logout_completed" class="alert alert-info text-center m-4" role="alert" style="display:none;">
      ログアウトしました、<a href="/auth?mode=login">再ログイン</a>する
    </div>
    <div class="position-relative overflow-hidden p-3 p-md-5 m-md-3 text-center bg-light">
      <div class="col-md-5 p-lg-5 mx-auto my-5">
        <h1 class="display-4 font-weight-normal">どの端末でも<br>時間割を</h1>
        <p class="lead font-weight-normal">chibasys(チバシス)では簡単なステップであなたのGoogleカレンダーに授業を予定として追加します。
          スマホ、タブレット、PC、スマートウォッチで次の授業の場所が簡単にわかります。
          登録の必要はありません、不要な時はGoogleカレンダーの予定を消すだけです。
        </p>
        <a class="btn btn-outline-primary btn-lg mb-3" href="/">試してみる<br><small>(シラバス検索のみ可能)</small></a>
        <a class="btn btn-outline-secondary btn-lg" href="/auth?mode=login">ログインして始める<br><small>(Googleアカウントが必要)</small></a>
      </div>
      <div class="product-device shadow-sm d-none d-md-block"></div>
      <div class="product-device product-device-2 shadow-sm d-none d-md-block"></div>
    </div>

    <div class="d-md-flex flex-md-equal w-100 my-md-3 pl-md-3">
      <div class="bg-dark mr-md-3 pt-3 px-3 pt-md-5 px-md-5 text-center text-white overflow-hidden">
        <div class="my-3 p-3">
          <h2 class="display-5">すぐに確認</h2>
          <p class="lead">授業開始の忙しい時間に必要な情報をすぐに確認できるようになります。通知やウィジェットからもサッとアクセス！</p>
        </div>
        <img class="mx-auto" src="img/sample1.png" style="width: 80%; max-width: 500px;"/>
      </div>
      <div class="bg-light mr-md-3 pt-3 px-3 pt-md-5 px-md-5 text-center overflow-hidden">
        <div class="my-3 py-3">
          <h2 class="display-5">好きな端末で</h2>
          <p class="lead">カレンダーならどんな端末でも対応してるので、例外なく手元の端末で確認できます。もちろん、PCでも見られるので、つい授業を逃すなんてこともなし！</p>
        </div>
        <img class="mx-auto" src="img/sample2.png" style="width: 40%; max-width: 250px;"/>
        <img class="mx-auto" src="img/sample3.png" style="width: 40%; max-width: 250px;"/>
      </div>
    </div>

    <div class="d-md-flex flex-md-equal w-100 my-md-3 pl-md-3">
      <div class="bg-dark mr-md-3 pt-3 px-3 pt-md-5 px-md-5 text-center text-white overflow-hidden">
        <div class="my-3 py-3">
          <h2 class="display-5">好きなアプリで</h2>
          <p class="lead">Googleカレンダーに対応しているカレンダーアプリならすべて使えます。使い慣れた、もともと入っていたカレンダーアプリでログインするだけですぐに見られます。</p>
        </div>
        <img class="mx-auto" src="img/sample4.png" style="width: 80%; max-width: 500px;"/>
      </div>
      <div class="bg-light mr-md-3 pt-3 px-3 pt-md-5 px-md-5 text-center overflow-hidden">
        <div class="my-3 p-3">
          <h2 class="display-5">シラバスを簡単閲覧</h2>
          <p class="lead">シラバスに書かれている内容や評価方法、教科書の一覧をすぐに確認できます。この内容はもちろん、自動で引っ張ってくるので面倒な作業の必要なし！教科書の変更があったら書き加えも。</p>
        </div>
        <img class="mx-auto" src="img/sample5.png" style="width: 80%; max-width: 500px;"/>
      </div>
    </div>

    <div class="d-md-flex flex-md-equal w-100 my-md-3 pl-md-3">
      <div class="bg-dark mr-md-3 pt-3 px-3 pt-md-5 px-md-5 text-white text-center overflow-hidden">
        <div class="my-3 py-3">
          <h2 class="display-5">単位数の計算も</h2>
          <p class="lead">面倒な単位数も自動で計算します。手で数えるより、楽で、正確です。うっかり、1年間で50以上取ることのないように...(もちろん、単位は落とさない前提で考えます)</p>
        </div>
        <img class="mx-auto" src="img/sample6.png" style="width: 80%; max-width: 500px;"/>
      </div>
      <div class="bg-light mr-md-3 pt-3 px-3 pt-md-5 px-md-5 text-center overflow-hidden">
        <div class="my-3 p-3">
          <h2 class="display-5">完全無料(千葉大専用)</h2>
          <p class="lead">もとは自分が欲しかったので作っただけです。
            でも、ツイートなどして広めてくれると嬉しいです。
            少しでも多くの人に使われるのは開発者の喜びです。
          </p>
        </div>
        <img class="mx-auto" src="img/sample7.png" style="width: 80%; max-width: 500px;"/>
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

    <script>
      var params = getParams();
      if (params['error']) document.getElementById('login_error').innerHTML = 'ログイン失敗: ' + params['error'];
      else if (params['msg']) document.getElementById(params['msg']).style.display = 'block';
      history.replaceState('','','welcome');

      function getParams(){
        var url = document.location.href;
        if(url.match(/#/)) url = RegExp.leftContext;
        if(url.match(/\?/)) var params = RegExp.rightContext;
        else return new Array();
        var tmp = params.split('&');
        var param = new Array();
        var tmp2, key, val;
        for(var i = 0; i < tmp.length; i++){
            tmp2 = new Array();
            key = '';
            val = '';

            tmp2 = tmp[i].split('=');
            key = tmp2[0];
            val = tmp2[1];
            param[key] = val;
        }
        return param;
      }
    </script>
  </body>
</html>
