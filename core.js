$('.stb').togglebutton();
if (localStorage['comment-name']) $('#comment-name').val(localStorage['comment-name']);
if (localStorage['comment-text']) $('#comment-text').val(localStorage['comment-text']);
if (localStorage['request']) history.replaceState(null, null, localStorage['request']);
localStorage.clear();
if (!navigator.share) $('#shareButton').css('display', 'none');

reloadFavorite(true);
reloadAllCalendarSubjects(true);
reloadWeekCalendarSubjects(true);
reloadPortalRegistrationList(true);
reloadPortalGrade(true);
setTimeout(() => { if (loadedCount < 5) initPage(); }, 20000);

var loadedCount = 0;
var poped = false; //ユーザーの手で主要ウィンドウを開いたかどうか
var favorite = []; //お気に入りに登録済みのコードの配列
var favoriteData = {}; //お気に入りに登録済みのシラバスの主要データ
var allCalSub; //追加済みのカレンダーデータ
var allRegSub; //履修登録済みの教科データ
var allRegSubData; //履修登録済みの教科のシラバス主要データ
var allGradeSub; //成績発表済みの教科データ


//////////////////////////////////////////////////
//////////ツール系メソッド////////////////////////////
//////////////////////////////////////////////////

/**
 * サーバー処理がうまくいったかをチェック、再ログインへの遷移
 * @param {JSON} data レスポンスデータ
 * @param {boolean} commentSave 投稿時、コメントを一時セーブするフラグ
 * @param {boolean} alertBool アラート、画面遷移をするか否か
 */
function checkResponse(data, loginCheck = true, commentSave = false, alertBool = true) {
  if (data['status'] === 'success') return true;
  else if (data['status'] === 'error') {
    alert(data['message']);
    console.log(data);
    return false;
  }
  else if (data['status'] === 'failed') {
    alert('サーバーエラー');
    return false;
  }
  else if (data['status'] === 'portal_non_register') {
    if (loginCheck)
      alert('ポータルに登録されていません、サイト一番右上のアイコンの設定からログインしてください');
    allRegSub = false;
    allGradeSub = false;
    return false;
  }
  else if (data['status'] === 'portal_cannot_login') {
    if (loginCheck)
      alert('ポータルにログインできません、サイト一番右上のアイコンの設定からログインしなおしてください');
    allRegSub = false;
    allGradeSub = false;
    return false;
  }
  else if (data['status'] === 'expired') {
    if (!loginCheck) return true;
    if (commentSave) {
      localStorage['comment-name'] = $('#comment-name').val();
      localStorage['comment-text'] = $('#comment-text').val();
    }
    if (alertBool && !$('#timeout-modal').hasClass('show')) {
      $('#timeout-modal').modal('show');
      //setTimeout(() => { location.href = data['url']; }, 2500);
    }
    return false;
  }
}

/**
 * 更新、ログイン時のページを復帰(データ読み込み完了後)
 */
function initPage() {
  var url = getRequest(), window = null, query = null;//, dialog = null;
  if (url.match(/#/)){
    url = RegExp.leftContext;
    //dialog = RegExp.rightContext;
  }
  //dialogの保持はあきらめた
  history.replaceState(null, null, '/' + url);
  if (url.match(/\?/)) {
    window = RegExp.leftContext;
    query = RegExp.rightContext;
  }
  else
    window = url;
  if (window)
    eval(window + (query === null ? '()' : '("' + query + '")'));
  if (typeof (registerWindow) === 'boolean' && registerWindow)
    $('#register-modal').modal('show');
  endLoading();
}

/**
 * Ajax簡易化メソッド
 * @param {JSON} data データ
 * @param {integer} timeout タイムアウト(デフォルト:20秒)
 */
function getAjax(data, timeout = 20){
  return $.ajax({ url: 'ajax', type: 'POST', timeout: timeout * 1000, data: data });
}

/**
 * URLのxperd.net/以降を取得
 */
function getRequest(){
  return document.location.href.substr(document.location.href.lastIndexOf('/') + 1);
}

/**
 * 教科一覧の1行のHTMLを生成
 * @param {string} code 教科コード
 * @param {string} name 教科の名前
 * @param {string} tr 1行目の<tr>内のHTML
 * @param {int} spanCount 1行目の<td>の数、カラム数
 */
function createSubjectRow(code, name, tr, spanCount) {
  var fav = getFavoriteBool(code);
  return '<tr class="subject-body tr-' + code + (fav ? ' star' : '') + '">' + tr + '</tr>' +
    '<tr class="subject-next next-horizontal tr-' + code + (fav ? ' star' : '') + '" data-code="' + code + '" data-name="' + name + '"><td colspan="' + spanCount + '"><div class="collapse"><button class="btn btn-sm btn-star btn-star-' + code + '" onclick="changeFavorite(\'' + code + '\', this)">' + getFavoriteText(getFavoriteBool(code)) + '</button>' +
    '<button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#syllabus-link-modal">リンクを共有</button><button class="btn btn-sm btn-info" onclick="showSyllabus(\'' + code + '\', \'' + name + '\')">詳細情報へ</button></div></td></tr>';
}

/**
 * 表の教科ごとの展開メニューのイベント
 * @param {string} name #****-table
 */
function triggerSubjectNext(name) {
  $('#' + name + '-table .subject-body').on('click', (e) => {
    var element = $(e.target).parent().next().find('.collapse'); //.children[0].children[0];
    if (!element || element === undefined)
      return;
    else if (element.hasClass('show'))
      element.collapse('hide');
    else {
      $('#' + name + '-table .collapse.show').collapse('hide');
      element.collapse('show');
    }
  });
}

/**
 * 初回登録処理
 * @param {Element} button ボタンの要素
 */
function register(button) {
  if ($('#studentName').val() === '')
    alert('名前を入力してください');
  /*else if (!(new RegExp(/[0-9][0-9][A-Z][0-9][0-9]/).test($('#studentID').val())))
    alert('正しい学生証番号の一部を入力してください');*/
  else if ($('input[name="studentSex"]:checked').val() === undefined)
    alert('性別を選択してください');
  else {
    $(button).prop('disabled', true).text('保存中...');
    getAjax({ method: 'register', studentName: $('#studentName').val(), studentSex: $('input[name="studentSex"]:checked').val(),
      studentID: $('#studentID').val(), studentPass: $('#studentPass').val() })
      .done((result, textStatus, xhr) => {
        $('#register-modal').modal('hide');
        $(button).prop('disabled', false).text('保存');
        $('#username').text($('#studentName').val());
        reloadPortalRegistrationList();
        reloadPortalGrade();
      })
      .fail((xhr, textStatus) => {
        alert('保存できませんでした');
        $(button).prop('disabled', false).text('保存');
        console.debug(textStatus);
      });
  }
}




//////////////////////////////////////////////////
//////////画面遷移の管理メソッド///////////////////////
//////////////////////////////////////////////////

/**
 * ブラウザの戻る進むを監視し、ウィンドウを変更
 */
$(window).on('popstate', (e) => {
  //if (!e.originalEvent.state) return; //初回アクセス時に再読み込みしてしまう対策
  endLoading();
  if (e.originalEvent.state === null){
    //最初の画面
    var r = getRequest();
    if (r === '' || r === '#') {
      $('.modal-nomal.show').modal('hide');
      return;
    }
    else
      var s = r.split('?');
  }
  else
    var s = [ e.originalEvent.state['method'], (e.originalEvent.state['method'] === 'syllabus' ? e.originalEvent.state['code'] : e.originalEvent.state['query']) ];

  if (s[0] === 'search') {
    $('.modal-nomal.show').modal('hide');
    if ($('#search-modal').data('query') === s[1])
      //クエリが同じ
      $('#search-modal').modal('show');
    else
      //クエリが違う→検索
      search(s[1]);
  }
  else if (s[0] === 'syllabus') {
    $('.modal-nomal.show').modal('hide');
    if ($('#syllabus-modal').data('code') === s[1])
      //コードが同じ
      $('#syllabus-modal').modal('show');
    else
      //コードが違う→取得
      syllabus(s[1], (e.originalEvent.state === null ? null : e.originalEvent.state['name']));
  }
  else if (s[0] === 'mincam') {
    $('.modal-nomal.show').modal('hide');
    if ($('#mincam-modal').data('query') === s[1])
      //クエリが同じ
      $('#mincam-modal').modal('show');
    else
      //クエリが違う→検索
      mincam(s[1]);
  }
  else if (s[0] === 'calendar') {
    calendar();
  }
  else if (s[0] === 'subjects') {
    subjects();
  }
  else if (s[0] === 'dialog') {
    $('#' + e.originalEvent.state['dialog'] + '-modal').modal('show');
  }
});
/**
 * 主要ウィンドウの閉じるボタンはpop済みの時は戻る
 */
$('.fullsize .close').click(() => {
  if (poped)
    history.back();
  else {
    history.pushState(null, null, '/');
    gtag('config', 'UA-44630639-4', {'page_path': getRequest()});
    $('.modal.show').modal('hide');
  }
});
/**
 * サブウィンドウの閉じるボタンは常に戻る
 */
$('.modal-nomal.fade .close').click(() => {
  history.back();
});

/**
 * 読み込みオーバーレイを表示
 */
function startLoading() {
  $('#loading').css('display', 'block');
}
/**
 * 読み込みオーバーレイを非表示
 */
function endLoading() {
  $('#loading').css('display', 'none');
}

$('.modal.fade').on('hidden.bs.modal', () => { if ($('.modal.show').length > 0) $('body').addClass('modal-open'); });
$('#search-modal').on('hidden.bs.modal', () => { if (searchAjax.readyState > 0 && searchAjax.readyState < 4) searchAjax.abort(); });
$('#syllabus-modal').on('hidden.bs.modal', () => { if (syllabusAjax.readyState > 0 && syllabusAjax.readyState < 4) syllabusAjax.abort(); });

//////////////////////////////////////////////////
//////////主要ウィンドウの呼び出しと表示/////////////////
//////////////////////////////////////////////////

/**
 * 手動で検索を開始、PushStateあり
 * @param {boolean} query 検索クエリ
 */
function startSearch(query = null) {
  if (query === null) {
    var params = new URLSearchParams();
    $('#search-form .form-control').each((i, e) => {
      if (e.value) params.append(e.id, e.value);
    });
    query = params.toString();
  }
  history.pushState({ method: 'search', query: query }, '検索 -chibasys-', 'search?' + query);
  gtag('config', 'UA-44630639-4', {'page_path': getRequest()});
  poped = true;
  search(query);
}
var searchAjax;
/**
 * シラバス検索をかけ、モーダルで結果一覧を表示
 * @param {string} query 検索クエリ
 */
function search(query) {
  $('#search-title').text('シラバス検索');
  $('#search-modal .modal-body').html('<h4 class="my-3 mx-1" algin="center">読み込み中...</h4>');
  $('.modal.show').modal('hide');
  $('#search-modal').data('query', query).modal('show');
  startLoading();
  var data = { method: 'search', query: query };
  /*var params = new URLSearchParams(query);
  if (params.has('kaikoKamokunmLike')){
    data['proc_kaikoKamokunmLike'] =
      params.get('kaikoKamokunmLike').replace(/[!#\$%&\(\)\*\+,\-\.\/:;<=>\?@\[\]\^_`{\|}0-9A-Za-z！＃＄％＆（）＊＋，－．／：；＜＝＞？＠［］＾＿｀｛｜｝ ０-９Ａ-Ｚａ-ｚ]+/g, ' ');
    if (params.get('kaikoKamokunmLike') === data['proc_kaikoKamokunmLike']) delete data['proc_kaikoKamokunmLike'];
    else
    {
      data['real_kaikoKamokunmLike'] = params.get('kaikoKamokunmLike');
      var list = data['proc_kaikoKamokunmLike'].split(' ').sort(function(a, b) { return b.length - a.length; });
      params.set('kaikoKamokunmLike', list[0]);
      data['query'] = params.toString();
    }
  }*/
  searchAjax = getAjax(data, 60)
    .done((result, textStatus, xhr) => {
      var searchResult = JSON.parse(result);
      if (checkResponse(searchResult, false)){
        var bodyText = '';
        if (searchResult['subjects'] && searchResult['subjects'].length > 0) {
          $('#search-title').text('シラバス検索(' + searchResult['subjects'][0][0] + '年度)');
          bodyText += '<h5>気になる教科をタップ！！</h5>' +
            '<table id="search-table" class="table table-sm">' +
            '<thead><tr><th style="width:10%;">開講</th><th style="width:15%;">曜時</th><th style="width:50%;">科目名</th><th style="width:25%;">担当</th><th class="d-none d-md-table-cell" style="width:150%;">授業概要</th></tr></thead><tbody>';
          for (var i = 0; i < searchResult['subjects'].length; i++){
            var s = searchResult['subjects'][i];
            var code = s[0] + '-' + s[4];//s[0] + '-' + s[11] + '-' + s[4] + '-' + s[10];
            var fav = getFavoriteBool(code);
            bodyText += '<tr class="subject-body tr-' + code + (fav ? ' star' : '') + '"><td>' + s[2].replace(/(.*?)ターム/g, 'T$1') + '</td><td>' + s[3] + '</td><td>' + s[5] + '</td><td style="width:50%;">' + s[6] + '</td><td class="d-none d-md-table-cell" style="width:150%;">' + s[8].replace(/・・・・/g, '・…').replace(/・・・/g, '…') + '</td></tr>' +
              '<tr class="subject-next next-horizontal tr-' + code + (fav ? ' star' : '') + '" data-code="' + code + '"><td colspan="5"><div class="collapse"><button class="btn btn-sm btn-star" onclick="changeFavorite(\'' + code + '\', this)">' + getFavoriteText(fav) + '</button>' +
              '<button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#syllabus-link-modal">シラバスを共有</button><button class="btn btn-sm btn-info" onclick="showSyllabus(\'' + code + '\',\'' + s[5] + '\')">詳細情報へ</button></div></td></tr>';
          }
          bodyText += '</tbody></table>';
        }
        else
          bodyText = '<h5 class="my-3 mx-1" algin="center">条件に合ったシラバスが見つかりませんでした</h4>';
      }
      else
        bodyText = '<h5 class="my-3 mx-1" algin="center">シラバス検索に失敗しました、条件をもっと指定するかしばらくたってからお試しください</h4>';
      $('#search-modal .modal-body').html(bodyText);
      triggerSubjectNext('search');
      endLoading();
    })
    .fail((xhr, textStatus) => {
      $('#search-modal').data('query', '');
      if (textStatus === 'abort') return;
      $('#search-modal .modal-body').html('<h5 class="my-3 mx-1" algin="center">シラバス検索に失敗しました、条件をもっと指定するかしばらくたってからお試しください</h4>');
      endLoading();
      console.debug(textStatus);
    });
}

/**
 * 手動で検索を開始、PushStateあり
 * @param {boolean} query 検索クエリ
 */
function startMincam(query = null) {
  if (query === null) {
    var params = new URLSearchParams();
    $('#mincam-form .form-control').each((i, e) => {
      if (e.value) params.append(e.id.replace('-mincam', ''), e.value);
    });
    query = params.toString();
  }
  if (query === '') {
    alert('条件を最低一つ入力してください。');
    return;
  }
  history.pushState({ method: 'mincam', query: query }, '授業評価検索 -chibasys-', 'mincam?' + query);
  gtag('config', 'UA-44630639-4', {'page_path': getRequest()});
  poped = true;
  mincam(query);
}
var mincamAjax;
/**
 * シラバス検索をかけ、モーダルで結果一覧を表示
 * @param {string} query 検索クエリ
 */
function mincam(query) {
  $('#mincam-title').text('授業評価検索結果');
  $('#mincam-modal .modal-body').html('<h4 class="my-3 mx-1" algin="center">読み込み中...</h4>');
  $('.modal.show').modal('hide');
  $('#mincam-modal').data('query', query).modal('show');
  startLoading();
  var data = { method: 'mincam', query: query };
  mincamAjax = getAjax(data)
    .done((result, textStatus, xhr) => {
      var mincamResult = JSON.parse(result);
      if (checkResponse(mincamResult, false)) {
        var bodyText = '';
        if (mincamResult['subjects'] && mincamResult['subjects'].length > 0) {
          bodyText += '<table id="mincam-table" class="table table-sm">' +
            '<thead><tr><th style="width:35%;">教科名</th><th style="width:25%;">教師</th><th style="width:20%;max-width:100px;">充実度</th><th style="width:20%;max-width:100px;">楽単度</th></tr></thead><tbody>';
          var richTotal = 0, easyTotal = 0; 
          for (var i = 0; i < mincamResult['subjects'].length; i++) {
            var s = mincamResult['subjects'][i];
            richTotal += parseInt(s['richPoint']);
            easyTotal += parseInt(s['easyPoint']);
            bodyText += '<tr class="subject-body"><td>' + s['title'] + '</td><td>' + s['lastName'] + s['firstName'] + '</td><td>' + '★'.repeat(s['richPoint']) + '</td><td>' + '★'.repeat(s['easyPoint']) + '</td></tr>' +
              '<tr class="subject-next next-vertical"><td colspan="4"><div class="collapse">' +
              (s['attend'] !== '未登録' ? '<p>出席:' + s['attend'] + ' 教科書:' + s['textbook'] + '</p>' : '') +
              (s['attend'] !== '未登録' ? '<p>中間:' + s['middleExam'] + ' 期末:' + s['finalExam'] + '  ' + s['bringIn'].replace('テストなし', '') : '') +
              '<p>' + s['message'] + '</p>' +
              '<p class="text-right small">by ' + s['creditName'] + ' ' + (Math.floor((new Date().getTime() - new Date(s['postDate']).getTime()) / (1000 * 60 * 60 * 24)) + 1) + '日前</p>' +
              '</div></td></tr>';
          }
          bodyText += '</tbody></table>';
          $('#mincam-title').text('授業評価(充実:' + (Math.round(richTotal / mincamResult['subjects'].length * 10) / 10) + ' 楽単:' + (Math.round(easyTotal / mincamResult['subjects'].length * 10) / 10) + ')');
        }
        else
          bodyText = '<h4 class="my-3 mx-1" algin="center">条件に合った授業評価が見つかりませんでした</h4>';
      }
      else
        bodyText = '<h4 class="my-3 mx-1" algin="center">授業評価検索に失敗しました、条件をもっと指定するかしばらくたってからお試しください</h4>';
      $('#mincam-modal .modal-body').html(bodyText);
      triggerSubjectNext('mincam');
      endLoading();
    })
    .fail((xhr, textStatus) => {
      $('#mincam-modal').data('query', '');
      if (textStatus === 'abort') return;
      $('#mincam-modal .modal-body').html('<h4 class="my-3 mx-1" algin="center">授業評価検索に失敗しました、条件をもっと指定するかしばらくたってからお試しください</h4>');
      endLoading();
      console.debug(textStatus);
    });
}

/**
 * 手動でシラバス詳細を表示、PushStateあり
 * @param {string} code 教科コード
 * @param {string} name 教科の名前
 */
function showSyllabus(code, name) {
  history.pushState({ method: 'syllabus', code: code, name: name }, name + '詳細 -chibasys-', 'syllabus?' + code);
  gtag('config', 'UA-44630639-4', {'page_path': getRequest()});
  poped = true;
  syllabus(code, name);
}
var syllabusResult;
var syllabusAjax;
/**
 * シラバス詳細を取得、モーダルで表示
 * @param {string} code 教科コード
 * @param {string} name 教科の名前
 */
function syllabus(code, name = null) {
  var data = code.split('-');
  writeSyllabusFooter(code);
  $('#syllabus-title').text((name !== null ? name : 'シラバス') + 'の詳細');
  $('#syllabus-modal').data('code', code).data('bool', getFavoriteBool(code));
  $('#syllabus-body-detail, #syllabus-body-comment, #syllabus-body-mincam').html('<h4 class="my-3 mx-1" algin="center">読み込み中...</h4>');
  $('#syllabus-body-comment').data('index', 0);
  $('#comment-name, #comment-text').val('');
  $('#commentNendo-one-label').text(data[0] + '年度のみ');
  $('.modal.show').modal('hide');
  $('#syllabus-modal').modal('show');
  $('#syllabus-memo').val('読み込み中...').prop('disabled', true);
  startLoading();
  getComment(code, false);
  getAjax({ method: 'memo', command: 'get', code: code })
    .done((result, textStatus, xhr) => {
      var memoResult = JSON.parse(result);
      if (checkResponse(memoResult, true, false, false))
        $('#syllabus-memo').val(memoResult['text'].replace(/<br>/g, "\n")).prop('disabled', false).attr('placeholder', 'メモを入力...');
      else
        $('#syllabus-memo').val('').prop('disabled', true).attr('placeholder', 'ログインするとシラバスにメモができます');
    })
    .fail((xhr, textStatus) => {
      if (textStatus === 'abort') return;
      $('#syllabus-memo').val('').prop('disabled', true).attr('placeholder', 'エラーが発生しました');
      console.debug(textStatus);
    });
  syllabusAjax = getAjax({ method: 'syllabus', /*command: 'get',*/ code: code })
    .done((result, textStatus, xhr) => {
      /*var bodyDetail = '';
      syllabusResult = JSON.parse(result);
      if (checkResponse(syllabusResult, false)) {
        var params = new URLSearchParams();
        params.append('title', syllabusResult['detail-1']['授業科目'].replace(/  |[!-@]|[\[-`]|[{-~]|[Ⅰ-Ⅹ]|[A-Z]$|[a-z]$|[A-Z] |[a-z] /g, ' ').replace(/[A-Z]$|[a-z]$|  /g, ' ').trim());
        params.append('teacher', ('担当教員' in syllabusResult['detail-1'] ? syllabusResult['detail-1']['担当教員'] : ''));
        getAjax({ method: 'mincam', command: 'shape', query: params.toString() })
          .done((result, textStatus, xhr) => {
            var mincamResult = JSON.parse(result);
            if (checkResponse(mincamResult, false)){
              var bodyText = '';
              if (mincamResult['subjects'] && mincamResult['subjects'].length > 0) {
                for (var i = 0; i < mincamResult['subjects'].length; i++) {
                  var s = mincamResult['subjects'][i];
                  bodyText += '<div class="mt-3 mb-2"><h5>' + s['title'] + '  ' + s['lastName'] + s['firstName'] + '</h5>' +
                  '<p>充実度:' + '★'.repeat(s['richPoint']) + '</td><td>' + ' 楽単度:' + '★'.repeat(s['easyPoint']) + '</p>' +
                  (s['attend'] !== '未登録' ? '<p>出席:' + s['attend'] + ' 教科書:' + s['textbook'] + '</p>' : '') +
                  (s['attend'] !== '未登録' ? '<p>中間:' + s['middleExam'] + ' 期末:' + s['finalExam'] + '  ' + s['bringIn'].replace('テストなし', '') : '') +
                  '<p>' + s['message'] + '</p>' +
                  '<p class="text-right small">by ' + s['creditName'] + ' ' + (Math.floor((new Date().getTime() - new Date(s['postDate']).getTime()) / (1000 * 60 * 60 * 24)) + 1) + '日前</p></div>';
                }
              }
              else
                bodyText = '<h4 class="my-3 mx-1" algin="center">関連した授業評価はありません</h4>';
            }
            else
              bodyText = '<h4 class="my-3 mx-1" algin="center">授業評価検索に失敗しました</h4>';
            $('#syllabus-body-mincam').html(bodyText);
          })
          .fail((xhr, textStatus) => {
            if (textStatus === 'abort') return;
            $('#syllabus-body-mincam').html('<h4 class="my-3 mx-1" algin="center">授業評価検索に失敗しました</h4>');
            console.debug(textStatus);
          });
        $('#calButton').prop('disabled', false);
        if (name === null) $('#syllabus-title').text(syllabusResult['detail-1']['授業科目'] + 'の詳細');
        $('#syllabus-modal')
          .data('term', syllabusResult['detail-1']['履修年次/ターム'].substr(syllabusResult['detail-1']['履修年次/ターム'].indexOf('/') + 1).replace(/(.*?)ターム/g, 'T$1'))
          .data('time', syllabusResult['detail-1']['曜日・時限'])
          .data('credit', ('単位数' in syllabusResult['detail-1'] ? syllabusResult['detail-1']['単位数'] : '0'))
          .data('name', syllabusResult['detail-1']['授業科目'])
          .data('teacher', ('担当教員' in syllabusResult['detail-1'] ? syllabusResult['detail-1']['担当教員'] : ''))
          .data('room', ('教室' in syllabusResult['detail-1'] ? syllabusResult['detail-1']['教室'] : ''))
          .data('summary', syllabusResult['detail-2']['概要']);
        bodyDetail = '<table id="syllabus-table" class="table table-sm">' +
          '<thead><tr><th style="width:25%;">項目</th><th style="width:75%;">内容</th></tr></thead><tbody>';
        for (key in syllabusResult['detail-1'])
          bodyDetail += '<tr><td>' + key + '</td><td>' + syllabusResult['detail-1'][key] + '</td></tr>';
        for (key in syllabusResult['detail-2'])
          bodyDetail += '<tr><td>' + key + '</td><td>' + syllabusResult['detail-2'][key].replace(/\n/g, '<br>') + '</td></tr>';
        if (syllabusResult['detail-3']) {
          bodyDetail += '<tr><td colspan="2"><b>[授業計画詳細情報]</b></td></tr>';
          for (index in syllabusResult['detail-3'])
            bodyDetail += '<tr><td colspan="2">' + syllabusResult['detail-3'][index].replace(/\n/g, '<br>') + '</td></tr>';
        }
        bodyDetail += '</tbody></table>';
      }
      else
        bodyDetail += '<h4 class="my-3 mx-1" algin="center">シラバス取得エラー、しばらくたってからお試しください</h4>';
      bodyDetail += '<a href="' + syllabusResult['original'] + '" target="_blank">元の千葉大学のシラバスはこちら</a>';
      $('#syllabus-body-detail').html(bodyDetail);
      endLoading();*/
      var bodyDetail = '';
      syllabusResult = JSON.parse(result);
      if (checkResponse(syllabusResult, false)) {
        var params = new URLSearchParams();
        params.append('title', syllabusResult['data']['name'].replace(/  |[!-@]|[\[-`]|[{-~]|[Ⅰ-Ⅹ]|[A-Z]$|[a-z]$|[A-Z] |[a-z] /g, ' ').replace(/[A-Z]$|[a-z]$|  /g, ' ').trim());
        params.append('teacher', ('teacher' in syllabusResult['data'] ? syllabusResult['data']['teacher'] : ''));
        getAjax({ method: 'mincam', command: 'shape', query: params.toString() })
          .done((result, textStatus, xhr) => {
            var mincamResult = JSON.parse(result);
            if (checkResponse(mincamResult, false)){
              var bodyText = '';
              if (mincamResult['subjects'] && mincamResult['subjects'].length > 0) {
                for (var i = 0; i < mincamResult['subjects'].length; i++) {
                  var s = mincamResult['subjects'][i];
                  bodyText += '<div class="mt-3 mb-2"><h5>' + s['title'] + '  ' + s['lastName'] + s['firstName'] + '</h5>' +
                  '<p>充実度:' + '★'.repeat(s['richPoint']) + '</td><td>' + ' 楽単度:' + '★'.repeat(s['easyPoint']) + '</p>' +
                  (s['attend'] !== '未登録' ? '<p>出席:' + s['attend'] + ' 教科書:' + s['textbook'] + '</p>' : '') +
                  (s['attend'] !== '未登録' ? '<p>中間:' + s['middleExam'] + ' 期末:' + s['finalExam'] + '  ' + s['bringIn'].replace('テストなし', '') : '') +
                  '<p>' + s['message'] + '</p>' +
                  '<p class="text-right small">by ' + s['creditName'] + ' ' + (Math.floor((new Date().getTime() - new Date(s['postDate']).getTime()) / (1000 * 60 * 60 * 24)) + 1) + '日前</p></div>';
                }
              }
              else
                bodyText = '<h4 class="my-3 mx-1" algin="center">関連した授業評価はありません</h4>';
            }
            else
              bodyText = '<h4 class="my-3 mx-1" algin="center">授業評価検索に失敗しました</h4>';
            $('#syllabus-body-mincam').html(bodyText);
          })
          .fail((xhr, textStatus) => {
            if (textStatus === 'abort') return;
            $('#syllabus-body-mincam').html('<h4 class="my-3 mx-1" algin="center">授業評価検索に失敗しました</h4>');
            console.debug(textStatus);
          });
        $('#calButton').prop('disabled', false);
        if (name === null) $('#syllabus-title').text(syllabusResult['data']['name'] + 'の詳細');
        $('#syllabus-modal')
          .data('term', syllabusResult['data']['term'].replace(/(.*?)ターム/g, 'T$1'))
          .data('time', syllabusResult['data']['time'])
          .data('credit', ('credit' in syllabusResult['data'] ? syllabusResult['data']['credit'] : '0'))
          .data('name', syllabusResult['data']['name'])
          .data('teacher', ('teacher' in syllabusResult['data'] ? syllabusResult['data']['teacher'] : ''))
          .data('room', ('room' in syllabusResult['data'] ? syllabusResult['data']['room'] : ''))
          .data('summary', syllabusResult['data']['概要']);
        bodyDetail = '<table id="syllabus-table" class="table table-sm">' +
          '<thead><tr><th style="width:25%;">項目</th><th style="width:75%;">内容</th></tr></thead><tbody>';
        for (key in syllabusResult['data'])
          if (syllabusResult['data'][key] === null || syllabusResult['data'][key] === '') continue;
          else if (key === 'detail') {
            bodyDetail += '<tr><td>授業計画詳細情報</td><td>';
            bodyDetail += syllabusResult['data']['detail'].join('<br>').replace(/\n/g, '<br>');
            bodyDetail += '</td></tr>';
          }
          else if (eng[key])
            bodyDetail += '<tr><td>' + eng[key] + '</td><td>' + syllabusResult['data'][key].replace(/\n/g, '<br>'); + '</td></tr>';
        bodyDetail += '</tbody></table>';
      }
      else
        bodyDetail += '<h4 class="my-3 mx-1" algin="center">シラバス取得エラー、しばらくたってからお試しください</h4>';
      if (syllabusResult['url_ja'])
        bodyDetail += '<a href="' + syllabusResult['url_ja'] + '" target="_blank">元の千葉大学のシラバス(日本語)はこちら</a>';
      if (syllabusResult['url_en'])
        bodyDetail += '<a href="' + syllabusResult['url_en'] + '" target="_blank">元の千葉大学のシラバス(英語)はこちら</a>';
      $('#syllabus-body-detail').html(bodyDetail);
      endLoading();
    })
    .fail((xhr, textStatus) => {
      if (textStatus === 'abort') return;
      $('#syllabus-modal').data('code', '');
      $('#syllabus-body-detail').html('<h4 class="my-3 mx-1" algin="center">シラバス取得エラー、元のページに戻ってやり直してください</h4>');
      endLoading();
      console.debug(textStatus);
    });
}

function writeSyllabusFooter(code, bool = false){
  var calSub = false;
  if (allCalSub) allCalSub.forEach(e => {
    if (e['code'] === code) calSub = e;
  });
  var regSub = getPortalRegBool(code);
  $('#syllabus-modal .modal-footer').html(
    '<button class="btn btn-sm btn-star" onclick="changeFavorite(\'' + code + '\', this)">' + getFavoriteText(getFavoriteBool(code)) + '</button>' +
    (calSub ? '<button id="calButton" class="btn btn-sm btn-primary" onclick="deleteCalendar(this, \'' + calSub['id'] + '\', \'' + calSub['name'] + '\');">×カレンダーから削除</button>' :
      '<button id="calButton" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#syllabus-calendar-modal"' + (bool ? '' : ' disabled') + '>＋カレンダーに追加</button>') +
    '<button class="btn btn-sm btn-secondary" onclick="changePortalReg(this, \'' + code + '\');"' + (regSub === false ? '' : ' disabled' ) + '>' + getPortalRegText(regSub) + '</button>' +
    '<button class="btn btn-sm btn-info" data-toggle="modal" data-target="#syllabus-link-modal">共有する</button>');
}

var memoTimeout = null;
$('#syllabus-memo').on('input', function (e) {
  var lines = ($(this).val() + '\n').match(/\n/g).length + 1;
  $(this).height(parseInt($(this).css('lineHeight')) * lines);
  if (memoTimeout !== null) clearTimeout(memoTimeout);
  memoTimeout = setTimeout(() => {
    getAjax({ method: 'memo', command: 'save', code: $('#syllabus-modal').data('code'), text: $('#syllabus-memo').val() })
    memoTimeout = null;
  }, 1000);
});

/**
 * 手動でカレンダー管理を表示、PushStateあり
 */
function editCalendar() {
  if (allCalSub === null || allCalSub === undefined) {
    //未ログインははじく
    checkResponse({ status: 'expired' }, true);
  }
  else {
    history.pushState({ method: 'calendar' }, 'カレンダー管理 -chibasys-', 'calendar');
    gtag('config', 'UA-44630639-4', {'page_path': getRequest()});
    poped = true;
    calendar();
  }
}
/**
 * 追加済みのカレンダー管理をモーダルで表示
 */
function calendar(beforeYear = false) {
  var bodyText = '';
  if (allCalSub && allCalSub.length > 0) {
    if (beforeYear) $('#year').val(beforeYear);
    var year = $('#year').val();
    var credit = 0;
    bodyText += '<table id="calendar-table" class="table table-sm">' +
      '<thead><tr style="width:7.5%;"><th>削除</th><th style="width:10%;">開講</th><th style="width:15%;">曜時</th><th style="width:7.5%;">単位</th><th style="width:35%;">科目名</th><th style="width:25%;">場所</th></tr></thead><tbody>';
    for (i in allCalSub) {
      var sub = allCalSub[i];
      if (sub['nendo'] === year && sub['index']['term'] === '0' && sub['index']['time'] === '0'){
        bodyText += createSubjectRow(sub['code'], sub['name'], '<td><button class="btn del-cal" onclick="deleteCalendar(this, \'' + sub['id'] + '\', \'' + sub['name'] + '\');">&times;</button></td><td>' + sub['term'] + '</td><td>' + sub['time'] + '</td><td>' + sub['credit'] + '</td><td>' + sub['name'] + '</td><td style="width:50%;">' + sub['room'] + '</td>', 6);
        credit += parseInt(sub['credit']);
      }
    }
    bodyText += '</tbody></table>';
    $('#total-credit').text(year + '年度の単位数合計 : ' + credit);
    $('#calendar-h5').text(year + '年度の教科一覧');
  }
  else if (allCalSub === null || allCalSub === undefined) {
    //未ログインははじく
    checkResponse({ status: 'expired' }, true);
    history.replaceState(null, null, localStorage['request']);
    return;
  }
  else {
    $('#total-credit').text('');
    $('#calendar-h5').text('');
    bodyText = '<h4 class="my-3 mx-1" algin="center">カレンダーに追加済みの教科はありません</h4>';
  }
  $('#calendar-box').html(bodyText);
  triggerSubjectNext('calendar');
  $('#calendar-modal').modal('show');
}

/**
 * 手動で履修管理を表示、PushStateあり
 */
function showSubjects() {
  if (allRegSub === null || allRegSub === undefined) {
    //未ログインははじく
    checkResponse({ status: 'portal_non_register' }, true);
  }
  else {
    history.pushState({ method: 'subjects' }, '履修管理 -chibasys-', 'subjects');
    gtag('config', 'UA-44630639-4', {'page_path': getRequest()});
    poped = true;
    subjects();
  }
}
/**
 * 追加済みのカレンダー管理をモーダルで表示
 */
function subjects(beforeYear = false) {
  var bodyText = '';
  if (allRegSub && allRegSub.length > 0) {
    //if (beforeYear) $('#year').val(beforeYear);
    var year = 2019;//$('#year').val();
    var credit = 0;
    bodyText += '<table id="subjects-table" class="table table-sm">' +
      '<thead><tr style="width:7.5%;"><th>削除</th><th style="width:10%;">開講</th><th style="width:15%;">曜時</th><th style="width:7.5%;">単位</th><th style="width:35%;">科目名</th><th style="width:25%;">教師</th></tr></thead><tbody>';
    for (i in allRegSub) {
      var sub = allRegSubData[i];
      bodyText += createSubjectRow(allRegSub[i], sub['name'], '<td><button class="btn del-sub" onclick="changePortalReg(this, \'' + allRegSub[i] + '\', \'' + sub['name'] + '\');">&times;</button></td><td>' + sub['term'] + '</td><td>' + sub['time'] + '</td><td>' + sub['credit'] + '</td><td>' + sub['name'] + '</td><td style="width:50%;">' + sub['teacher'] + '</td>', 6);
      credit += parseInt(sub['credit']);
    }
    bodyText += '</tbody></table>';
    $('#subjects-credit').text(year + '年度の単位数 : ' + credit);
    $('#subjects-h5').text(year + '年度の教科一覧');
  }
  else if (allRegSub === null || allRegSub === undefined) {
    //未ログインははじく
    checkResponse({ status: 'portal_non_register' }, true);
    history.replaceState(null, null, localStorage['request']);
    return;
  }
  else {
    $('#subjects-credit').text('');
    $('#subjects-h5').text('');
    bodyText = '<h4 class="my-3 mx-1" algin="center"履修登録済みの教科はありません</h4>';
  }
  $('#subjects-box').html(bodyText);
  triggerSubjectNext('subjects');
  $('#subjects-modal').modal('show');
}

//各タームの始まる日付と年度、タームとの対応表
var StartTerm = { '2019':{ 1:'2019-04-08', 2:'2019-06-11', 3:'2019-08-07', 4:'2019-10-01', 5:'2019-12-03', 6:'2020-02-06', 7:'2020-04-01' } };
//各タームの終わる日付と年度、タームとの対応表
var EndTerm = { '2019':{ 1:'2019-06-10', 2:'2019-08-06', 3:'2019-09-30', 4:'2019-12-02', 5:'2020-02-05', 6:'2019-03-31' } };
//ターム内で休日と示されている日一覧
var Holiday = [ '2019-04-29', '2019-04-30', '2019-05-01', '2019-05-02', '2019-05-03', '2019-05-06', '2019-07-15', '2019-08-12', '2019-09-16', '2019-09-23',
  '2019-10-14', '2019-10-22', '2019-10-31', '2019-11-01', '2019-11-04', '2019-12-30', '2019-12-31', '2020-01-01', '2020-01-02', '2020-01-03', '2020-01-13', '2020-01-17', '2020-02-11', '2020-02-24', '2020-03-20' ];
//月曜休日が続いたなどで振替が起きるときの曜日と日付の対応表
var RDATE = { '2019-07-16':'月', '2019-10-16':'月', '2019-01-14':'月', '2019-01-15':'金' };
var DOW = ['月', '火', '水', '木', '金', '土'];

var week = 0;
function reloadTimetable(addWeek = 0){
  $('.subject-box').remove();
  week += addWeek;
  var addHTML= '';
  if (allRegSub !== undefined && allRegSub.length > 0){
    var endMax = 5;
    for (i in allRegSubData) {
      var sub = allRegSubData[i];
      if (sub['term'].indexOf('T') < 0) continue;
      else if (sub['time'].indexOf('他') >= 0) continue;
      sub['time'] = sub['time'].replace(/,/g, '、');

      //6限があるなら最大時限を6に
      if (sub['time'].indexOf('6') >= 0) endMax = (endMax <= 5 ? 6 : endMax);
      //7限があるなら最大時限を7に
      else if (sub['time'].indexOf('7') >= 0) endMax = 7; //7限開始より後
    }
    $('#timetable-box table').removeClass('tt-5').removeClass('tt-6').removeClass('tt-7').removeClass('tt-' + endMax);
    $('#timetable-6th').css('display', (endMax >= 6 ? 'table-row' : 'none'));
    $('#timetable-7th').css('display', (endMax >= 7 ? 'table-row' : 'none'));

    //月火水木金土の時間割上の曜日
    var days = [];
    //月火水木金土のターム
    var terms = [];
    //年度を計算
    var date = new Date();
    var year = (date.getMonth() < 3 ? date.getFullYear() - 1 : date.getFullYear());
    //月曜日の日付を取得
    date.setDate(date.getDate() - date.getDay() + 1 + week * 7);
    for (var i = 0; i < 5; i++, date.setDate(date.getDate() + 1)) {
      var dateString = date.getFullYear() + '-' + ('0' + (date.getMonth() + 1)).slice(-2) + '-' + ('0' + date.getDate()).slice(-2);
      //休みかどうか判定
      if (Holiday.indexOf(dateString) >= 0)
        days[i] = false;
      //振替曜日ならそれを優先
      else if (Object.keys(RDATE).indexOf(dateString) >= 0)
        days[i] = RDATE[dateString];
      else
        days[i] = DOW[i];
      //日付が属するタームを決める
      for (var j = 1; j <= 6; j++)
        if (new Date(StartTerm[year][j]) <= date && date < new Date(StartTerm[year][j + 1])) {
          terms[i] = j.toString();
          break;
        }
      $('#timetable-date th:eq(' + (i + 1) + ')').text((date.getMonth() + 1) + '/' + date.getDate());
      $('#timetable-dow th:eq(' + (i + 1) + ')').text((days[i] ? days[i] : '休') + '(T' + terms[i] + ')');
    }
    for (var i in allRegSubData) {
      var sub = allRegSubData[i];
      if (sub['term'].indexOf('T') < 0 || sub['term'].indexOf('集') >= 0) continue;
      else if (sub['time'].indexOf('他') >= 0) continue;
      sub['time'] = sub['time'].replace(/、/g, ',');
      
      //教科の属する曜時配列
      var subTimes = sub['time'].split(',');
      //教科の属するターム
      var subTerms = sub['term'].replace('1-3', '123').replace('4-6', '456').replace(/[T\-]/g, '').split('');
      for (var ii in subTimes) {
        //曜日ごとにタームチェック
        //if (terms.indexOf(subTerms[ii]) < 0) continue;

        for (var iii in days) {
          //曜日が一致しなければスルー
          if (days[iii] !== subTimes[ii].substr(0, 1)) continue;
          else if (subTerms.indexOf(terms[iii]) < 0) continue;

          //重なる教科チェック
          var count = 0, start = 0;
          for (var iiii in allRegSubData) {
            var sub2 = allRegSubData[iiii];
            if (sub2['term'].indexOf('T') < 0 || sub['term'].indexOf('集') >= 0) continue;
            else if (sub2['time'].indexOf('他') >= 0) continue;
            sub2['time'] = sub2['time'].replace(/、/g, ',');

            //教科の属する曜時配列
            var subTimes2 = sub2['time'].split(',');
            //教科の属するターム
            var subTerms2 = sub2['term'].replace('1-3', '123').replace('4-6', '456').replace(/[T\-]/g, '').split('');
            
            //曜日ごとにタームチェック
            if (subTerms2.indexOf(terms[iii]) < 0) continue;
            //曜時ごとに重なりチェック
            if (subTimes2.indexOf(subTimes[ii]) >= 0){
              count++; //重なる教科数をカウント
              if (iiii < i) start++; //インデックスが後なら右にずれていく
            }
          }
          
          var ts = parseInt(subTimes[ii].substr(1, 1)) - 1;//, te = getTop(getMinutes(sub['end']));
          addHTML += '<div class="subject-box waves-effect waves-light" data-code="' + allRegSub[i] + '" style="' +
            'left:' + (20 * iii + 20 / count * start) + '%;' +
            'top:' + ((100 / endMax) * ts) + '%;' +
            'height:' + ((100 / endMax) * (1 /*te - ts*/)) + '%;' +
            'width:' + 20 / count + '%;' +
            '">' + sub['name'] + '<small>' + sub['room'] + '</small>' + '</div>';
        }
      }
    }
    if (allRegSub.length === 0){
      //時間割に教科がないとき
      addHTML = '<div class="w-100 h-100 p-5 text-white" style="font-size: 1.8rem;">検索をしてシラバス詳細画面から「カレンダーに追加」をして時間割を作成しよう！</div>';
    }
  }
  else {
    //未ログインの時
    addHTML = '<div class="w-100 h-100 p-5 text-white" style="font-size: 1.8rem;">ログインして時間割も便利かつ簡単に作成しよう！</div>';
  }
  $('#subjects-container').html(addHTML);
  $('.subject-box').on('click', (e) => {
    if ($(e.target).hasClass('subject-box'))
      showSyllabus($(e.target).data('code'));
    else
      showSyllabus($(e.target).parent().data('code'));
  });
}
//////////////////////////////////////////////////
//////////コメント関連メソッド/////////////////////////
//////////////////////////////////////////////////

var commentAjax;
/**
 * コメントを別のAjaxで取得、負荷分散
 * @param {string} code 教科コード
 * @param {boolean} reload 再読み込みフラグ
 */
function getComment(code, reload) {
  syllabusAjax = getAjax(generateCommentData(code, 'get', reload))
    .done((result, textStatus, xhr) => {
      var data = JSON.parse(result);
      if (checkResponse(data, false, false, true))
        addCommentElement(data, reload);
    })
    .fail((xhr, textStatus) => {
      if (textStatus === 'abort') return;
      $('#syllabus-body-comment').html('<h4 class="my-3 mx-1" algin="center">コメント取得エラー</h4>');
      $('#syllabus-body-comment-load').css('display', 'block');
      console.debug(textStatus);
    });
}

/**
 * コメントの投稿、完了後再読み込み
 * @param {Element} button 投稿ボタン
 */
function postComment(button) {
  if ($('#comment-text').val().trim() === '') {
    alert('コメントを記入してください');
    return;
  }
  var code = $('#syllabus-modal').data('code');
  var data = generateCommentData(code, 'post', true);
  data['num'] = parseInt($('#syllabus-body-comment').data('num')) + 1;
  data['name'] = $('#comment-name').val();
  data['text'] = $('#comment-text').val().replace(/\n/g, '<br>');
  $('#comment-text, #comment-name').prop('disabled', true);
  $(button).prop('disabled', true).text('投稿しています...');
  syllabusAjax = getAjax(data)
    .done((result, textStatus, xhr) => {
      var data = JSON.parse(result);
      if (checkResponse(data, true, true)) {
        addCommentElement(data, true);
        $('#comment-text').val('');
      }
    })
    .fail((xhr, textStatus) => {
      if (textStatus === 'abort') return;
      alert('コメントを投稿できませんでした');
      console.debug(textStatus);
    }).always(() => {
      $('#comment-text, #comment-name').prop('disabled', false);
      $(button).prop('disabled', false).text('投稿');
    });
}

/**
 * コメントを取得、投稿する際のデータ生成
 * @param {string} code 教科コード
 * @param {string} command コマンド
 * @param {boolean} reload 再読み込みフラグ
 */
function generateCommentData(code, command, reload) {
  return {
    method: 'comment', command: command, reload: reload, code: code,
    index: $('#syllabus-body-comment').data('index'),
    all_nendo: ($('input[name="commentNendo"]:checked').val() === 'true' ? true : false)
  };
}

/**
 * 取得したコメントデータを挿入
 * @param {JSON} commentResult コメント一覧
 * @param {boolean} reload 再読み込みフラグ
 */
function addCommentElement(commentResult, reload) {
  if (reload || $('#syllabus-body-comment').data('index') === 0) {
    reload = true;
    $('#syllabus-body-comment').html('');
    $('#syllabus-body-comment-load').css('display', 'block');
  }
  $('#syllabus-body-comment').data('index', commentResult['index']);
  var bodyComment = '';
  //if ($('#syllabus-body-comment').html().trim() === '') reload = true; //初回取得時にnumが入るように
  if (commentResult['comment'].length === 0) { //コメントがないとき
    $('#syllabus-body-comment').html('<h2 class="my-5" align="center">コメントなし</h2>');
    $('#syllabus-body-comment-load').css('display', 'none');
    $('#syllabus-body-comment').data('num', 0);
  }
  $.each(commentResult['comment'], (i, s) => {
    if (reload && i === 0) $('#syllabus-body-comment').data('num', parseInt(s['num'])); //numの最大値
    bodyComment += '<h6>' + s['num'] + '. ' + s['name'] + ': ' + s['datetime'] + '</h6>' +
      '<p>' + s['text'] + '</p>';
    if (s['num'] <= 1) $('#syllabus-body-comment-load').css('display', 'none'); ////////////////////全学年対応
  });
  $('#syllabus-body-comment').append(bodyComment);
}


//////////////////////////////////////////////////
//////////カレンダー関連メソッド///////////////////////
//////////////////////////////////////////////////

/**
 * カレンダーに追加済みの全ての教科一覧を取得
 * @param {boolean} init 初回フラグ
 */
function reloadAllCalendarSubjects(init = false, beforeYear = false){
  getAjax({ method: 'calendar', command: 'get' })
    .done((result, textStatus, xhr) => {
      var data = JSON.parse(result);
      if (checkResponse(data, false, true)) {
        allCalSub = data['result'];
        if (allCalSub && allCalSub.length > 0){
          allCalSub.sort((a, b) => {
            var ad = new Date(a['start']), bd = new Date(b['start']);
            if (ad > bd) return +1;
            else if (ad < bd) return -1;
            else return 0;
          });
          var year = [], notifyBool = false;
          var bodyText = '<label class="mr-2" for="year">年度:</label>' +
            '<select id="year" class="form-control stb">';
          for (index in allCalSub){
            var sub = allCalSub[index];
            if (sub['notification']) notifyBool = true;
            if (year.indexOf(sub['nendo']) < 0)
              year.push(sub['nendo']);
          }
          var i = year.indexOf(new Date().getFullYear().toString());
          if (i < 0) i = 0;
          for (index in year)
            bodyText += '<option value="' + year + '"' + (index === i ? ' selected' : '') + '>' + year + '</option>';
          bodyText += '</select>';
          $('#year-box').html(bodyText);
          $('#year').togglebutton();

          //通知スイッチ
          $('#switch-calendar-notification')[0].className = 'mx-0 btn ' + (notifyBool ? 'btn-success' : 'btn-dark');
          $('#switch-calendar-notification').data('bool', notifyBool).text('カレンダーの通知設定を変更(現在:' + (notifyBool ? 'オン' : 'オフ') + ')');
        }
        else
          $('#year-box').html('');
        $('#switch-calendar-notification').css('display', (allCalSub && allCalSub.length > 0 ? 'block' : 'none'));
        
        if (init){
          loadedCount++;
          if (loadedCount >= 5) initPage();
        }
        else endLoading();
        if (beforeYear) calendar(beforeYear);
        if (getRequest().split('?')[0] === 'syllabus')
          writeSyllabusFooter($('#syllabus-modal').data('code'), true);
      }
    })
    .fail((xhr, textStatus) => {
      if (textStatus === 'abort') return;
      alert('カレンダーを取得できませんでした');
      console.debug(textStatus);
      if (init) {
        loadedCount++;
        if (loadedCount >= 5) initPage();
      }
    });
}

/**
 * カレンダーに追加済みの週間の教科一覧を取得、時間割を生成
 * @param {boolean} init 初回フラグ
 */
function reloadWeekCalendarSubjects(init = false){
  getAjax({ method: 'calendar', command: 'week' })
    .done((result, textStatus, xhr) => {
      var data = JSON.parse(result);
      if (checkResponse(data, false, true)) {
        /*
        $('.subject-box').remove();
        var addHTML= '';
        if (data['result']){
          var endMax = 5;
          for (i in data['result']) {
            var sub = data['result'][i];
            var timeEnd = parseInt(sub['end'].substr(11, 2)) * 60 + parseInt(sub['end'].substr(14, 2));
            if (timeEnd < 1070) continue; //6限開始より早く終わるなら無視
            else if (1070 <= timeEnd && timeEnd < 1170) endMax = (endMax <= 5 ? 6 : endMax) //6限開始から7限開始
            else if (1170 <= timeEnd) endMax = 7; //7限開始より後
          }
          $('#timetable-box table').removeClass('tt-5').removeClass('tt-6').removeClass('tt-7').removeClass('tt-' + endMax);
          $('#timetable-6th').css('display', (endMax >= 6 ? 'table-row' : 'none'));
          $('#timetable-7th').css('display', (endMax >= 7 ? 'table-row' : 'none'));
          for (var i in data['result']) {
            var sub = data['result'][i];
            var count = 0, start = 0;
            for (var ii in data['result']) {
              var s = data['result'][ii];
              if (sub['start'].substr(0, 10) === s['start'].substr(0, 10) && (getMinutes(sub['start']) < getMinutes(s['end']) && getMinutes(sub['end']) > getMinutes(s['start']))){
                count++;
                if (ii < i) start++;
              }
            }
            var wod = new Date(sub['start']).getDay() - 1;
            if (wod < 0 || wod >= 5) continue;
            var ts = getTop(getMinutes(sub['start'])), te = getTop(getMinutes(sub['end']));
            addHTML += '<div class="subject-box" data-code="' + sub['code'] + '" style="' +
              'left:' + (20 * wod + 20 / count * start) + '%;' +
              'top:' + ((100 / endMax) * ts) + '%;' +
              'height:' + ((100 / endMax) * (te - ts)) + '%;' +
              'width:' + 20 / count + '%;' +
              '">' + sub['name'] + '<small>' + sub['room'] + '</small>' + '</div>';
          }
          if (data['result'].length === 0){
            //時間割に教科がないとき
            addHTML = '<div class="w-100 h-100 p-5 text-white" style="font-size: 1.8rem;">検索をしてシラバス詳細画面から「カレンダーに追加」をして時間割を作成しよう！</div>';
          }
        }
        else {
          //未ログインの時
          addHTML = '<div class="w-100 h-100 p-5 text-white" style="font-size: 1.8rem;">ログインして時間割も便利かつ簡単に作成しよう！</div>';
        }
        $('#subjects-container').html(addHTML);
        $('.subject-box').on('click', (e) => {
          if ($(e.target).hasClass('subject-box'))
            showSyllabus($(e.target).data('code'));
          else
            showSyllabus($(e.target).parent().data('code'));
        });*/
      }
      if (init){
        loadedCount++;
        if (loadedCount >= 5) initPage();
      }
    })
    .fail((xhr, textStatus) => {
      if (textStatus === 'abort') return;
      alert('時間割を取得できませんでした');
      console.debug(textStatus);
      if (init) {
        loadedCount++;
        if (loadedCount >= 5) initPage();
      }
    });
}

function getMinutes(datetime){
  return parseInt(datetime.substr(11, 2)) * 60 + parseInt(datetime.substr(14, 2));
}

/**
 * 上からどのくらいの距離か計算
 * @param {int} time Hour * 60 + Minute
 */
function getTop(time) {
  if (time <= 530) return 0;
  else if (530 <= time && time <= 620) return (time - 530) / 90;
  else if (620 < time && time < 630) return 1;
  else if (630 <= time && time <= 720) return (time - 630) / 90 + 1;
  else if (720 < time && time < 770) return 2;
  else if (770 <= time && time <= 860) return (time - 770) / 90 + 2;
  else if (860 < time && time < 870) return 3;
  else if (870 <= time && time <= 960) return (time - 870) / 90 + 3;
  else if (960 < time && time < 970) return 4;
  else if (970 <= time && time <= 1060) return (time - 970) / 90 + 4;
  else if (1060 < time && time < 1070) return 5;
  else if (1070 <= time && time <= 1160) return (time - 1070) / 90 + 5;
  else if (1160 < time && time < 1170) return 6;
  else if (1170 <= time && time <= 1260) return (time - 1070) / 90 + 6;
  else if (1260 < time) return 7;
}

function toogleCalendarNotification(button){
  var bool = !$(button).data('bool');
  if (confirm('カレンダーの通知設定を' + (bool ? 'オン' : 'オフ') + 'に切り替えますか？少し時間がかかります。')){
    var id = [];
    for (index in allCalSub) {
      var sub = allCalSub[index];
      if (sub['notification'] !== bool)
        id.push(sub['id']);
    }
    startLoading();
    getAjax({ method: 'calendar', command: 'notification', bool: bool, id: id })
      .done((result, textStatus, xhr) => {
        var data = JSON.parse(result);
        if (checkResponse(data, true)) {
          reloadAllCalendarSubjects(false, $('#year').val());
        }
        else
          endLoading();
      })
      .fail((xhr, textStatus) => {
        if (textStatus === 'abort') return;
        alert('通知設定変更に失敗しました');
        console.debug(textStatus);
        endLoading();
      });
  }
}

/**
 * カレンダーイベントを削除
 * @param {string} id イベントID
 * @param {string} name 教科名
 */
function deleteCalendar(button, id, name){
  if (confirm('本当に「' + name + '」をカレンダーから削除しますか？時間割から消去されます。\n後から、再度カレンダーに追加することはできますが、メモに変更を加えた場合はそれが失われます。')){
    startLoading();
    getAjax({ method: 'calendar', command: 'delete', id: id })
      .done((result, textStatus, xhr) => {
        var data = JSON.parse(result);
        if (checkResponse(data, true)) {
          if ($(button).hasClass('del-cal')) reloadAllCalendarSubjects(false, $('#year').val());
          else reloadAllCalendarSubjects(false);
          reloadWeekCalendarSubjects();
        }
        else {
          alert('カレンダーの教科削除に失敗しました');
          endLoading();
        }
      })
      .fail((xhr, textStatus) => {
        if (textStatus === 'abort') return;
        alert('カレンダーの教科削除に失敗しました');
        endLoading();
        console.debug(textStatus);
      });
  }
}


//////////////////////////////////////////////////
//////////お気に入り関連メソッド///////////////////////
//////////////////////////////////////////////////

/**
 * お気に入りリストの取得
 * @param {boolean} init 初回フラグ
 */
function reloadFavorite(init = false) {
  getAjax({ method: 'favorite', command: 'get' })
    .done((result, textStatus, xhr) => {
      var data = JSON.parse(result);
      if (checkResponse(data, false)) {
        favorite = data['list'];
        favoriteData = data['data'];
        reloadFavoriteBox();
        if (init){
          loadedCount++;
          if (loadedCount >= 5) initPage();
        }
      }
    })
    .fail((xhr, textStatus) => {
      if (textStatus === 'abort') return;
      alert('お気に入りを取得できませんでした');
      console.debug(textStatus);
      if (init) {
        loadedCount++;
        if (loadedCount >= 5) initPage();
      }
    });
}

/**
 * お気に入り一覧の再読み込み
 */
function reloadFavoriteBox() {
  var bodyText = '';
  if (favoriteData && favoriteData.length > 0) {
    bodyText += '<table id="favorite-table" class="table table-sm">' +
      '<thead><tr><th class="d-none d-md-table-cell" style="width:7.5%;">年度</th><th style="width:10%;">開講</th><th style="width:15%;">曜時</th><th style="width:7.5%;">単位</th><th style="width:37.5%;">科目名</th><th style="width:30%;">担当</th></tr></thead><tbody>';
    for (var i = 0; i < favoriteData.length; i++){
      var sub = favoriteData[i];
      sub['nendo'] = 2019;
      bodyText += createSubjectRow(favorite[i], sub['name'], '<td class="d-none d-md-table-cell">' + sub['nendo'] + '</td><td>' + sub['term'] + '</td><td>' + sub['time'] + '</td><td>' + sub['credit'] + '</td><td>' + sub['name'] + '</td><td style="width:50%;">' + sub['teacher'] + '</td>', 6);
    }
    bodyText += '</tbody></table>';
  }
  else
    bodyText = '<h4 class="my-3 mx-1" algin="center">お気に入りがありません</h4>';
  $('#favorite-box').html(bodyText);
  triggerSubjectNext('favorite');
}

/**
 * お気に入りの切り替え
 * @param {string} code 教科コード
 * @param {Element} button ボタンの要素
 */
function changeFavorite(code, button) {
  var tr = $('.tr-' + code);
  var bool = !getFavoriteBool(code);
  $(button).prop('disabled', true).text('処理中...');
  getAjax({ method: 'favorite', command: 'change', code: code, bool: bool })
    .done((result, textStatus, xhr) => {
      var data = JSON.parse(result);
      if (checkResponse(data, true)) {
        favorite = data['list'];
        favoriteData = data['data'];
        reloadFavoriteBox();
        bool = getFavoriteBool(code);
        if (bool) tr.addClass('star');
        else tr.removeClass('star');
        $(button).prop('disabled', false).text(getFavoriteText(bool));
        $('.btn-star-' + code).text(getFavoriteText(bool));
      }
    })
    .fail((xhr, textStatus) => {
      if (textStatus === 'abort') return;
      alert('お気に入りを変更できませんでした');
      console.debug(textStatus);
    });
}

/**
 * お気に入りボタンのテキスト
 * @param {boolean} bool お気に入りかどうか
 */
function getFavoriteText(bool) {
  return (bool ? '★お気に入りから削除' : '☆お気に入りに追加');
}

/**
 * コードからお気に入りかどうかを判定
 * @param {string} code 教科コード
 */
function getFavoriteBool(code) {
  return favorite.indexOf(code) >= 0;
}


//////////////////////////////////////////////////
////////ポータル関連のデータを更新し取得するメソッド/////////
//////////////////////////////////////////////////

function reloadPortalRegistrationList(init = false){
  getAjax({ method: 'portal', command: 'registration_list' }, 40)
    .done((result, textStatus, xhr) => {
      var data = JSON.parse(result);
      if (checkResponse(data, false)) {
        allRegSub = data['subjects'];
        allRegSubData = data['subjectsData'];
        reloadTimetable();
      }
        if (init){
          loadedCount++;
          if (loadedCount >= 5) initPage();
        }
    })
    .fail((xhr, textStatus) => {
      if (textStatus === 'abort') return;
      alert('履修登録を取得できませんでした');
      console.debug(textStatus);
      if (init) {
        loadedCount++;
        if (loadedCount >= 5) initPage();
      }
    });
}

function reloadPortalGrade(init = false){
  getAjax({ method: 'portal', command: 'grade' })
    .done((result, textStatus, xhr) => {
      var data = JSON.parse(result);
      if (checkResponse(data, false)) {
        allGradeSub = data['subjects'];
      }
        if (init){
          loadedCount++;
          if (loadedCount >= 5) initPage();
        }
    })
    .fail((xhr, textStatus) => {
      if (textStatus === 'abort') return;
      alert('成績を取得できませんでした');
      console.debug(textStatus);
      if (init) {
        loadedCount++;
        if (loadedCount >= 5) initPage();
      }
    });
}

function changePortalReg(button, code) {
  if (getPortalRegBool(code) === false) {
    $(button).prop('disabled', true).text('処理中...');
    startLoading();
    getAjax({ method: 'portal', command: 'registration', code: code }, 50)
    .done((result, textStatus, xhr) => {
      var data = JSON.parse(result);
      if (checkResponse(data, false)) {
        allRegSub = data['subjects'];
        allRegSubData = data['subjectsData'];
        reloadTimetable();
        if (allRegSub.indexOf(code) >= 0)
          $(button).prop('disabled', false).text(getPortalRegText(true));
        else {
          $(button).prop('disabled', false).text(getPortalRegText(false));
          alert(data['error']);
        }
      }
      endLoading();
    })
    .fail((xhr, textStatus) => {
      if (textStatus === 'abort') return;
      alert('履修登録できませんでした');
      console.debug(textStatus);
      endLoading();
    });
  }
}

/**
 * 履修登録ボタンのテキスト
 * @param {boolean} bool お気に入りかどうか
 */
function getPortalRegText(bool) {
  if (bool === null || bool === undefined) return 'ポータル未登録';
  else if (bool) return '●履修登録済み';
  else if (!bool) return '◎履修登録';
}

/**
 * コードからお気に入りかどうかを判定
 * @param {string} code 教科コード
 */
function getPortalRegBool(code) {
  var bool;
  if (allRegSub)
    bool = (allRegSub.indexOf(code) >= 0);
  else
    bool = null;
  return bool;
}

//////////////////////////////////////////////////
//////////サブ画面の準備とそれに付随するメソッド////////////
//////////////////////////////////////////////////

/**
 * シラバスを共有する際のモーダルの表示準備
 */
$('#syllabus-link-modal').on('show.bs.modal', (e) => {
  if (e.relatedTarget){
    if (e.relatedTarget.id === 'share-button'){
      var code = '', name = 'chibasys';
    }
    else {
      //ボタンから表示した場合
      var p = $(e.relatedTarget).parent().parent().parent();
      var syllabusWindow = p.hasClass('modal-dialog');
      //シラバス画面からの時
      if (syllabusWindow)  p = p.parent();
      var code = p.data('code'), name = p.data('name');
    }
    //履歴に追加
    history.pushState({ method: 'dialog', dialog: 'syllabus-link', code: code, name: name },
      name + 'を共有 -chibasys-', getRequest() + '#syllabus-link');
    gtag('config', 'UA-44630639-4', {'page_path': getRequest()});
  }
  else if (history.state && history.state && history.state['method'] === 'dialog' && history.state['dialog'] === 'syllabus-link') {
    //進むボタンからの復元
    var code = history.state['code'], name = history.state['name'];
  }

  var url = (code === '' ? 'https://chibasys.xperd.net/welcome' : 'https://chibasys.xperd.net/syllabus?' + code);
  $(e.target).data('name', name);
  $(e.target).find('textarea').text(url);
  $('#qrcode').html('').qrcode(url);
  $('#syllabus-link-title').text(((code === '') ? 'chibasysを共有' : name + 'を共有'));
});
var copyTimeout = null;
/**
 * シラバスリンクのコピーを試みる
 * @param {Element} button ボタンの要素
 */
function copyLink(button) {
  if (copyTimeout !== null) clearTimeout(copyTimeout);
  $(button).text('コピーしています...');
  $(button).prop('disabled', true);
  var textarea = $('#syllabus-link-modal textarea');
  textarea.readonly = false;
  textarea.select();
  var result = document.execCommand('copy');
  $(button).prop('disabled', false);
  textarea.readonly = true;
  if (result) $(button).text('コピー済み');
  else $(button).text('コピー失敗');
  copyTimeout = setTimeout(() => {
    $(button).text('コピー');
    copyTimeout = null;
  }, 2000);
}
/**
 * リンクの共有
 * @param {Element} button ボタンの要素
 */
function shareLink(button) {
  //リンクを共有
  var text = (($('#syllabus-link-modal').data('name') === '') ? 'chibasysをはじめよう！' : $('#syllabus-link-modal').data('name') + 'をchibasysで見る');
  navigator.share({
    text: text,
    url: $('#syllabus-link-modal textarea').val()
  });
}

var eng = {"jikanwaricd":"授業コード","department":"所属学部","subject":"所属学科","course":"所属コース等","class_type":"学科(専攻)・科目の種別等","name":"授業科目","false":"授業コード",
"subject_code":"科目コード","numbering_code":"ナンバリングコード","method":"授業の方法","language":"使用言語",
"credit":"単位数","hour":"時間数","period":"期別","grade":"履修年次","term":"ターム","time":"曜日・時限",
"sub_major":"副専攻","sub_title":"副題","student_count":"受入人数","teacher":"担当教員",
"target_student":"受講対象","room":"教室","update_date":"更新日","summary":"概要","purpose":"目的・目標",
"content":"授業計画・授業内容","homework":"授業外学習","keyword":"キーワード","textbook":"教科書・参考書",
"evaluation_method":"評価方法・基準","related_subject":"関連科目","requirement":"履修要件","remark":"備考",
"related_url":"関連URL","detail":"授業計画詳細情報"};
/**
 * カレンダーに追加する際のモーダルの表示準備
 */
$('#syllabus-calendar-modal').on('show.bs.modal', (e) => {
  if (allCalSub === null || allCalSub === undefined){
    checkResponse({ status: 'expired' }, true);
    e.preventDefault();
    e.stopImmediatePropagation();
    return false;
  }
  $('#syllabus-calendar-notification').bootstrapToggle($('#syllabus-calendar-notification').data('init'));
  if (e.relatedTarget){
    //ボタンから表示した場合、履歴に追加
    history.pushState({ method: 'dialog', dialog: 'syllabus-calendar' }, 'カレンダーに追加 -chibasys-', getRequest() + '#syllabus-calendar');
    gtag('config', 'UA-44630639-4', {'page_path': getRequest()});
  }

  /*var list = ["学科(専攻)・科目の種別等", "授業コード", "授業の方法", "単位数", "時間数", "履修年次/ターム", "曜日・時限", "担当教員", "受講対象", "教室",
    "概要", "目的・目標", "授業計画・授業内容", "授業外学習", "教科書・参考書", "評価方法・基準", "履修要件", "備考", "関連URL", "授業計画詳細情報"];*/
  var bodyDetail = '<thead><tr><th style="width:5%;">&#9745;</th><th style="width:25%;">項目</th><th style="width:70%;">内容</th></tr></thead><tbody>';
  for (key in syllabusResult['data'])
    if (syllabusResult['data'][key] !== null && Object.keys(eng).indexOf(key) >= 0)
      bodyDetail += '<tr ' + (Object.keys(eng).indexOf(key) < 0 ? 'data-bool="false"' : 'class="lime-green" data-bool="true"') +
        ' data-key="' + key + '"><td class="check">&#974' + (Object.keys(eng).indexOf(key) < 0 ? 4 : 5) + ';</td><td>' + eng[key] + '</td><td>' +
        syllabusResult['data'][key] + '</td></tr>';
  /*for (key in syllabusResult['detail-1'])
    bodyDetail += '<tr ' + (list.indexOf(key) < 0 ? 'data-bool="false"' : 'class="lime-green" data-bool="true"') + ' data-index="1"><td class="check">&#974' + (list.indexOf(key) < 0 ? 4 : 5) + ';</td><td>' + key + '</td><td>' + syllabusResult['detail-1'][key] + '</td></tr>';
  for (key in syllabusResult['detail-2'])
    bodyDetail += '<tr ' + (list.indexOf(key) < 0 ? 'data-bool="false"' : 'class="lime-green" data-bool="true"') + ' data-index="2"><td class="check">&#974' + (list.indexOf(key) < 0 ? 4 : 5) + ';</td><td>' + key + '</td><td>' + syllabusResult['detail-2'][key].replace(/<br>/g, ' ') + '</td></tr>';*/
  if (syllabusResult['data']['detail']) {
    bodyDetail += '<tr ' + (Object.keys(eng).indexOf(key) < 0 ? 'data-bool="false"' : 'class="lime-green" data-bool="true"') + ' data-key="' + key + '"><td class="check">&#974' + (Object.keys(eng).indexOf(key) < 0 ? 4 : 5) + ';</td><td colspan="2">授業計画詳細情報<br>';
    for (index in syllabusResult['data']['detail'])
      bodyDetail += syllabusResult['data']['detail'][index] + '<br>';
  }
  bodyDetail += '</tbody>';

  $(e.target).find('table').html(bodyDetail);
  $(e.target).find('tbody tr').on('click', (e) => {
    var tr = $(e.target).parent();
    var bool = tr.data('bool');
    if (typeof (bool) === 'string') bool = (bool === 'true');
    tr.data('bool', !bool);
    if (!bool) tr.addClass('lime-green');
    else tr.removeClass('lime-green');
    tr.find('.check').html(!bool ? '&#9745;' : '&#9744;');
  });
});
/**
 * 教科をカレンダーに追加
 * @param {Element} button ボタンの要素
 */
function addCalendar(button){
  var data = { method: 'calendar', command: 'add', allDay: false, ignoreExdate: false };
  var list = ['code', 'term', 'time', 'name', 'room', 'credit'];
  $(button).text('処理中…').prop('disabled', true);
  for (i in list)
    data[list[i]] = $('#syllabus-modal').data(list[i]);
  var description = '';
  $('#syllabus-calendar-modal .lime-green').each((i, e) => {
    var key = $(e).data('key');
    if (!syllabusResult['data'][key] || syllabusResult['data'][key] === null) return;
    else if (key === 'detail')
      description += '[授業計画詳細情報]\n' + syllabusResult['data'][key].join("\n").replace(/<br>/g, "\n") + "\n";
    else if (key !== 'detail')
      description += eng[key] + ': ' + syllabusResult['data'][key] + "\n";
  });
  data['description'] = description;
  data['notification'] = ($('#syllabus-calendar-notification').prop('checked') ? true : false);
  //console.log(data);

  startLoading();
  getAjax(data)
    .done((result, textStatus, xhr) => {
      var data = JSON.parse(result);
      if (checkResponse(data, true)) {
        history.back();
        $(button).text('カレンダーに追加');
        reloadAllCalendarSubjects();
        reloadWeekCalendarSubjects();
      }
      else {
        $(button).text('カレンダーに追加失敗');
        setTimeout(() => {$(button).text('カレンダーに追加');}, 2000)
      }
    })
    .fail((xhr, textStatus) => {
      if (textStatus === 'abort') return;
      $(button).text('カレンダーに追加');
      alert('カレンダーに追加できませんでした');
      console.debug(textStatus);
    })
    .always(() => {
      $(button).prop('disabled', false);
    });
}

$('#year').change((e) => { console.log(e); });