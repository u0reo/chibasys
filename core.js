//if (!initPass) {
$('.stb').togglebutton();
if (localStorage['request']) history.replaceState(null, null, localStorage['request']);
localStorage.clear();
if (!navigator.share) $('#shareButton').css('display', 'none');

ajax({ fav_list_get: { }, cal_list_get: { }, portal_reg_list_get: { }, portal_grade_list_get: { } }, 60, true);
//}

var require_google_login = false;
var poped = false; //ユーザーの手で主要ウィンドウを開いたかどうか
var fav_text = [ '★お気に入りから削除', '☆お気に入りに追加' ];
var fav_code; //お気に入りに登録済みのコードの配列
var fav_data; //お気に入りに登録済みのシラバスの主要データ
var cal_text = [ '✕カレンダーから削除', '○カレンダーに追加' ];
var cal_code; //追加済みのカレンダーのコードの配列
var cal_data; //追加済みのカレンダーデータ
var reg_text = [ '✕履修登録を削除', '◎履修登録', 'ポータル未登録' ];
var reg_code; //履修登録済みのコードの配列
var reg_data; //履修登録済みの教科のシラバス主要データ
var grade_data; //成績発表済みの教科データ


//////////////////////////////////////////////////
//////////ツール系メソッド////////////////////////////
//////////////////////////////////////////////////
var func_list = [];
var global_ajax;

function ajax(query, timeout = 60, init = false) {
  global_ajax = $.ajax({ url: 'ajax', type: 'POST', contentType: 'application/json; charset=utf-8',
    timeout: timeout * 1000, data: JSON.stringify(query), cache: false })
    .done((result, textStatus, xhr) => {
      let data;
      try { data = JSON.parse(result); }
      catch (e) {
        console.log(e, result, textStatus, xhr);
        try { data = JSON.parse(result.substr(result.lastIndexOf('<br />') + 7)); }
        catch (e) { return; }
      }
      let error_list = [];
      for (let row in data) {
        //if (func_list.indexOf(row) < 0) continue;
        let d = Object.assign(query[row], data[row]);
        if (data[row]['error_code']) {
          console.log(row + ': ' + data[row]['error_code'], d);
          if (error_list.indexOf(data[row]['error_code']) < 0 && d.show_error) {
            alert(data[row]['error_message']);
            error_list.push(data[row]['error_code']);
          }
        }
        eval(row + '_result(d);');
      }
    })
    .fail((xhr, textStatus) => {
      console.debug(xhr);
      console.debug(textStatus);
    })
    .always((xhr, textStatus) => {
      //必ずfail→done→alwaysとなるので、OK!
      if (init) {
        //dialogの保持はあきらめた
        let url = request_get().split('#')[0];
        let data = url.split('?');
        if (['search', 'syllabus', 'classes', 'mincam', 'calendar'].indexOf(data[0]) >= 0)
          eval(data[0] + (data.length <= 1 ? '();' : '(data[1]);'));
        else
          url = '';
        history.replaceState(null, null, '/' + url);
        if (typeof (registerWindow) === 'boolean' && registerWindow)
          $('#register-modal').modal('show');
      }
      endLoading();
    });
}

function text_get(source, bool) {
  if (source.length === 2) return (bool ? source[0] : source[1]);
  else if (source.length === 3) return (bool !== null ? (bool ? source[0] : source[1]) : source[2]);
}
function bool_get(list, code) {
  if (list) return list.indexOf(code) >= 0;
  else return null;
}
/**
 * URLのxperd.net/以降を取得
 */
function request_get(){
  return document.location.href.substr(document.location.href.lastIndexOf('/') + 1);
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
    $('#settings-button').prop('disabled', true).text('保存中...');
    ajax({ userdata_save: { method: 'register', studentName: $('#studentName').val(), studentSex: $('input[name="studentSex"]:checked').val(), studentID: $('#studentID').val(), studentPass: $('#studentPass').val() } });
  }
}

function userdata_save_result(data) {
  if (data['result']) {
    $('#settings-modal').modal('hide');
    $('#settings-button').prop('disabled', false).text('保存');
    $('#username').text($('#studentName').val());
    ajax({ portal_reg_list_get: { refresh: true } });
    ajax({ portal_grade_list_get: { refresh: true } });
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
  let s = '';
  if (e.originalEvent.state === null){
    //最初の画面
    let r = request_get();
    if (r === '' || r === '#') {
      $('.modal-nomal.show').modal('hide');
      return;
    }
    else
      s = r.split('?');
  }
  else
    s = [ e.originalEvent.state['method'], (e.originalEvent.state['method'] === 'syllabus' ? e.originalEvent.state['code'] : e.originalEvent.state['query']) ];

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
  else if (s[0] === 'classes') {
    classes();
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
    gtag('config', 'UA-44630639-4',
{'page_path': request_get()});
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

//////////////////////////////////////////////////
//////////主要ウィンドウの呼び出しと表示/////////////////
//////////////////////////////////////////////////

/**
 * 手動で検索を開始、PushStateあり
 * @param {boolean} query 検索クエリ
 */
function search_start(query = null) {
  if (query === null) {
    let params = new URLSearchParams();
    $('#search-form .form-control').each((i, e) => {
      let value = '';
      if (e.id === 'search-term' || e.id === 'search-grade')
        $(`#${e.id}-btn .active`).each((ii, ee) => { value += ee.value; });
      else 
        value = e.value;
      if (value) params.append(e.id.substr(7), value);
    });
    query = params.toString();
  }
  history.pushState({ method: 'search', query: query }, '検索 -chibasys-', 'search?' + query);
  gtag('config', 'UA-44630639-4', {'page_path': request_get()});
  poped = true;
  search(query);
}

/**
 * シラバス検索をかけ、モーダルで結果一覧を表示
 * @param {string} query 検索クエリ
 */
function search(query) {
  $('#search-title').text('シラバス検索');
  $('#search-table').bootstrapTable('removeAll');
  $('.modal.show').modal('hide');
  $('#search-modal').data('query', query).modal('show');
  startLoading();
  ajax({ portal_search: { show_error: true, query: query } });
}

function portal_search_result(data) {
  if (data.query) {
    $('#search-modal').data('query', data.query);
    $('#search-title').text(`シラバス検索 (${data.query.substr(data.query.indexOf('nendo=') + 6, 4)}年度)`);
  }
  if (data.classes) {
    $('#search-header').text(`${data.num} 件の結果 (${data.time.toFixed(3)} 秒)`);
    if ($('#search-modal .bootstrap-table').length > 0)
      $('#search-table').bootstrapTable('destroy');
    table_create('search', data.classes, 50, [ 'term', 'time', 'name', 'teacher' ], false, false);
  }
}

/**
 * 手動で検索を開始、PushStateあり
 * @param {boolean} query 検索クエリ
 */
function mincam_start(query = null) {
  if (query === null) {
    let params = new URLSearchParams();
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
  gtag('config', 'UA-44630639-4', {'page_path': request_get()});
  poped = true;
  mincam(query);
}

/**
 * シラバス検索をかけ、モーダルで結果一覧を表示
 * @param {string} query 検索クエリ
 */
function mincam(query) {
  $('#mincam-title').text('授業評価検索結果');
  $('.modal.show').modal('hide');
  $('#mincam-modal').data('query', query).modal('show');
  startLoading();
  ajax({ mincam_search: { query: query } });
}

function mincam_search_result(data) {
  if (data.query) {
    //クエリを保存？
  }
  if (data.by_syllabus) {
    var bodyText = '';
    if (data.classes && data.classes.length > 0) {
      for (var i = 0; i < data.classes.length; i++) {
        var s = data.classes[i];
        bodyText += `<div class="mt-3 mb-2"><h5>${s['title']}  ${s['lastName']}${s['firstName']}</h5>` +
        `<p>充実度:${'★'.repeat(parseInt(s['richPoint']))}${'☆'.repeat(5 - parseInt(s['richPoint']))} / 楽単度:${'★'.repeat(parseInt(s['easyPoint']))}${'☆'.repeat(5 - parseInt(s['easyPoint']))}</p>` +
        (s['attend'] !== '未登録' ? `<p>出席:${s['attend']} 教科書:${s['textbook']}</p>` : '') +
        (s['attend'] !== '未登録' ? `<p>中間:${s['middleExam']} 期末:${s['finalExam']}  ${s['bringIn'].replace('テストなし', '')}` : '') +
        `<p>${s['message']}</p>` +
        `<p class="text-right small">by ${s['creditName']}  ` + (Math.floor((new Date().getTime() - new Date(s['postDate']).getTime()) / (1000 * 60 * 60 * 24)) + 1) + '日前</p></div>';
      }
    }
    else
      bodyText = '<h4 class="my-3 mx-1" algin="center">関連した授業評価はありません</h4>';
    $('#syllabus-body-mincam').html(bodyText);
  }
  else {
    $('#mincam-header').text(`${data.num} 件の結果 (${data.time.toFixed(3)} 秒)`);
    if ($('#mincam-modal .bootstrap-table').length > 0)
      $('#mincam-table').bootstrapTable('destroy');
    table_create('mincam', data.classes, 50, [ 'mincam_name', 'mincam_teacher', 'richPoint', 'easyPoint' ], true, true, (index, row) => (row['attend'] !== '未登録' ? `<p>出席:${row['attend']} 教科書:${row['textbook']}</p>` : '') +
    (row['attend'] !== '未登録' ? `<p>中間:${row['middleExam']} 期末:${row['finalExam']}  ${row['bringIn'].replace('テストなし', '')}` : '') +
    `<p>${row['message']}</p>` +
    `<p class="text-right small">by ${row['creditName']}  ` + (Math.floor((new Date().getTime() - new Date(row['postDate']).getTime()) / (1000 * 60 * 60 * 24)) + 1) + '日前</p>');
  }
}

/**
 * 手動でシラバス詳細を表示、PushStateあり
 * @param {string} code 教科コード
 * @param {string} name 教科の名前
 */
function syllabus_show(code, name) {
  history.pushState({ method: 'syllabus', code: code, name: name }, name + '詳細 -chibasys-', 'syllabus?' + code);
  gtag('config', 'UA-44630639-4',
{'page_path': request_get()});
  poped = true;
  syllabus(code, name);
}

/**
 * シラバス詳細を取得、モーダルで表示
 * @param {string} code 教科コード
 * @param {string} name 教科の名前
 */
function syllabus(code, name = null) {
  if (!code) return;
  $('#syllabus-modal .modal-footer').html(
    `<button class="btn btn-sm btn-star fav-${code}" onclick="fav_change('${code}')">${text_get(fav_text, bool_get(fav_code, code))}</button>` +
    `<button class="btn btn-sm btn-primary cal-${code}" onclick="cal_change('${code}');">${text_get(cal_text, bool_get(cal_code, code))}</button>` +
    `<button class="btn btn-sm btn-secondary" onclick="changePortalReg(this, '${code}');">${text_get(reg_text, bool_get(reg_code, code))}</button>` +
    `<button class="btn btn-sm btn-info" data-toggle="modal" data-target="#syllabus-link-modal">共有する</button>`);
  $('#syllabus-title').html((name !== null ? name : 'シラバス') + 'の詳細');
  $('#syllabus-modal').data('code', code).data('bool', bool_get(fav_code, code));
  $('#syllabus-body-detail, #syllabus-body-comment, #syllabus-body-mincam').html('<h4 class="my-3 mx-1" algin="center">読み込み中...</h4>');
  $('#syllabus-body-comment').data('index', 0);
  $('#comment-name, #comment-text').val('');
  $('#commentNendo-one-label').text(code.split('-')[0] + '年度のみ');
  $('.modal.show').modal('hide');
  $('#syllabus-modal').modal('show');
  $('#syllabus-memo').val('読み込み中...').prop('disabled', true);
  startLoading();
  ajax({ portal_syllabus_get: { show_error: true, code: code }, memo_get: { code: code }, comment_get: { code: code, index: 0 } });
}

function portal_syllabus_get_result(data) {
  bodyDetail = '';
  if (data.data) {
    //みんきゃんのデータを取得
    let params = new URLSearchParams();
    params.append('title', data.data['name'].replace(/  |[!-@]|[\[-`]|[{-~]|[Ⅰ-Ⅹ]|[A-Z]$|[a-z]$|[A-Z] |[a-z] /g, ' ').replace(/[A-Z]$|[a-z]$|  /g, ' ').trim());
    params.append('teacher', ('teacher' in data.data ? data.data['teacher'] : ''));
    ajax({ mincam_search: { query: params.toString(), by_syllabus: true } });
    
    $('#syllabus-modal').data('name', data.data['name']);
    $('#syllabus-title').text(data.data['name'] + 'の詳細');
    bodyDetail = '<table id="syllabus-table" class="table table-sm">' +
      '<thead><tr><th style="width:25%;">項目</th><th style="width:75%;">内容</th></tr></thead><tbody>';
    for (key in data.data)
      if (data.data[key] === null || data.data[key] === '') continue;
      else if (key === 'detail') {
        bodyDetail += '<tr><td>★授業計画詳細情報</td><td>';
        bodyDetail += data.data['detail'].join('<br>').replace(/\n/g, '<br>');
        bodyDetail += '</td></tr>';
      }
      else if (En2Ja[key])
        bodyDetail += '<tr><td>' + En2Ja[key] + '</td><td>' + data.data[key].replace(/\n/g, '<br>'); + '</td></tr>';
    bodyDetail += '</tbody></table>';
  }
  else
    bodyDetail += '<h4 class="my-3 mx-1" algin="center">シラバス取得エラー、しばらくたってからお試しください</h4>';
  if (data.url_ja)
    bodyDetail += '<a href="' + data.url_ja + '" target="_blank">元の千葉大学のシラバス(日本語)はこちら</a>';
  if (data.url_en)
    bodyDetail += '<a href="' + data.url_en + '" target="_blank">元の千葉大学のシラバス(英語)はこちら</a>';
  $('#syllabus-body-detail').html(bodyDetail);
}

function memo_get_result(data) {
  if (data.error_code)
    $('#syllabus-memo').val('').prop('disabled', true).attr('placeholder', 'ログインするとシラバスにメモができます');
  else
    $('#syllabus-memo').val(data.text.replace(/<br>/g, "\n")).prop('disabled', false).attr('placeholder', 'メモを入力...');
}

var memo_timeout = null;
$('#syllabus-memo').on('input', function (e) {
  let lines = ($(this).val() + '\n').match(/\n/g).length + 1;
  $(this).height(parseInt($(this).css('lineHeight')) * lines);
  if (memo_timeout !== null) clearTimeout(memo_timeout);
  memo_timeout = setTimeout(() => {
    let code = $('#syllabus-modal').data('code');
    ajax({ memo_save: { code: code, text: $('#syllabus-memo').val() }, memo_get: { code: code } });
    memo_timeout = null;
  }, 1000);
});

function memo_save_result(data) {
  if (data.result) {
    //if (memo_timeout === null) $('#syllabus-memo').val(data.text);
    //保存のトースト
  }
}

/**
 * 手動でカレンダー管理を表示、PushStateあり
 */
function calendar_show() {
  if (cal_data === null || cal_data === undefined) {
    //未ログインははじく
    alert('ログインしてください');
  }
  else {
    history.pushState({ method: 'calendar' }, 'カレンダー管理 -chibasys-', 'calendar');
    gtag('config', 'UA-44630639-4', {'page_path': request_get()});
    poped = true;
    calendar();
  }
}
/**
 * 追加済みのカレンダー管理をモーダルで表示
 */
function calendar(year = false) {
  $('#calendar-modal').modal('show');
  if (cal_code && cal_code !== null && cal_code !== undefined) {
    if (!year) {
      $('#calendar-nendo').html('');
      $('#calendar-nendo-btn').remove();
      let years = [];
      cal_code.forEach((value) => years.push(parseInt(value.split('-')[0])));
      //重複削除
      years = years.filter((x, i, self) => self.indexOf(x) === i);
      //降順にソート
      years.sort((a, b) => b - a);
      years.push(2020);///////////////////////////////////////
      //selectを生成
      years.forEach((value, index) => $('#calendar-nendo').append(`<option${index === 0 ? ' selected': ''}>${value}</option>`));
      $('#calendar-nendo').change((e) => calendar($(e.target).val())).togglebutton();
      if (years.length > 0) year = years[0];
    }
    let table_data = [];
    cal_code.forEach((value) => { if (value.split('-')[0] === year.toString()) table_data.push(cal_data[value]) });
    if ($('#calendar-modal .bootstrap-table').length > 0)
      $('#calendar-table').bootstrapTable('destroy');
    table_create('calendar', table_data, 160, [ 'check', 'term', 'time', 'credit', 'name', 'room' ]);
    $('#calendar-credit').html(`単位数合計: <b>${Object.values(cal_data).reduce((prev, current) => prev + parseInt(current.credit), 0)}</b>`);

    //通知スイッチ
    let notify_bool = false;
    cal_code.forEach((value) => { if (cal_data[value].notification) notify_bool = true; });
    $('#switch-calendar-notification')[0].className = 'ml-2 mb-2 btn ' + (notify_bool ? 'btn-success' : 'btn-dark');
    $('#switch-calendar-notification').data('bool', notify_bool).text('カレンダーの通知設定を変更(現在:' + (notify_bool ? 'オン' : 'オフ') + ')');
  }
  else {
    //未ログインははじく
    alert('ログインするか、設定からGoogleアカウントと連携してください');
    setTimeout(() => $('#calendar-modal .close').click(), 100);
  }
}

/**
 * 手動で履修管理を表示、PushStateあり
 */
function classes_show() {
    history.pushState({ method: 'classes' }, '履修管理 -chibasys-', 'classes');
    gtag('config', 'UA-44630639-4',
{ page_path: request_get() });
    poped = true;
    classes();
}
let grade_point_text = { null: '-', '0': '不可', '1': '可', '2': '良', '3': '優', '4': '秀' };
let grade_pass_text = { null: '-', '0': '×', '1': '○' };
/**
 * 追加済みのカレンダー管理をモーダルで表示
 */
function classes(year = false) {
  $('#classes-modal').modal('show');
  if (reg_code && reg_code !== null && reg_code !== undefined) {
    if (!year) {
      $('#classes-nendo').html('');
      $('#classes-nendo-btn').remove();
      let years = [];
      reg_code.forEach((value) => years.push(parseInt(value.split('-')[0])));
      //重複削除
      years = years.filter((x, i, self) => self.indexOf(x) === i);
      //降順にソート
      years.sort((a, b) => b - a);
      years.push(2020);///////////////////////////////////////
      //selectを生成
      years.forEach((value, index) => $('#classes-nendo').append(`<option${index === 0 ? ' selected': ''}>${value}</option>`));
      $('#classes-nendo').change((e) => {
        classes($(e.target).val());
      }).togglebutton();
      if (years.length > 0) year = years[0];
    }
    let table_data = [];
    reg_code.forEach((value) => { if (value.split('-')[0] === year.toString()) table_data.push(grade_data[value] ? Object.assign(reg_data[value], grade_data[value]) : reg_data[value]) });
    if ($('#classes-modal .bootstrap-table').length > 0)
      $('#classes-table').bootstrapTable('destroy');
    table_create('classes', table_data, 160, [ 'check', 'term', 'time', 'credit', 'name', 'point', 'pass' ]);
    $('#classes-grade').html(`現時点でのGP: <b>${gp_calc(grade_data)}</b> / GPA: <b>${gpa_calc(grade_data)}</b>`);
  }
  else {
    //未ログインははじく
    alert('ログインするか、学生ポータルのIDとパスワードを設定から入力してください');
    setTimeout(() => $('#classes-modal .close').click(), 100);
  }
}

function table_create(id, data, margin, columns = [ 'check', 'term', 'time', 'credit', 'name', 'teacher' ], search = true, footer = true, detail = false) {
  let col = [
    { field: 'check', checkbox: true, width: 3, widthUnit: 'em', align: 'center', footerFormatter: () => '合計',  },
    { field: 'term', title: '開講', sortable: true, width: 3, widthUnit: 'em', footerFormatter: (data) => Object.keys(data).length + '件' },
    { field: 'time', title: '曜時', sortable: true, width: 5, widthUnit: 'em', footerFormatter: (data) => Object.keys(data).reduce((prev, current) => prev += data[current].time.split(',').length, 0) + '時間' },
    { field: 'credit', title: '単位', width: 3, widthUnit: 'em', footerFormatter: (data) => data.reduce((prev, current) => prev + parseInt(current.credit), 0) },
    { field: 'name', title: '教科名', sortable: true, footerFormatter: () => '' },
    { field: 'teacher', title: '教師', footerFormatter: () => '' },
    { field: 'room', title: '教室', footerFormatter: () => '' },
    { field: 'point', title: '成績', sortable: true, width: 3, widthUnit: 'em', align: 'center', formatter: (value) => grade_point_text[value], footerFormatter: gp_calc },
    { field: 'pass', title: '合否', sortable: true, width: 3, widthUnit: 'em', align: 'center', formatter: (value) => grade_pass_text[value], footerFormatter: gpa_calc },

    { field: 'mincam_name', title: '教科名', sortable: true, formatter: (value, row) => row.title, footerFormatter: () => '平均' },
    { field: 'mincam_teacher', title: '教師', formatter: (value, row) => `${row.lastName} ${row.firstName}`, footerFormatter: () => '' },
    { field: 'richPoint', title: '充実度', sortable: true, width: 5, widthUnit: 'em', algin: 'center', formatter: (value) => '★'.repeat(parseInt(value)) + '☆'.repeat(5 - parseInt(value)), footerFormatter: (data) => `${data.length > 0 ? (data.reduce((prev, current) => prev += parseInt(current['richPoint']), 0) / data.length).toFixed(2) : '-'}` },
    { field: 'easyPoint', title: '楽単度', sortable: true, width: 5, widthUnit: 'em', algin: 'center', formatter: (value) => '★'.repeat(parseInt(value)) + '☆'.repeat(5 - parseInt(value)), footerFormatter: (data) => `${data.length > 0 ? (data.reduce((prev, current) => prev += parseInt(current['easyPoint']), 0) / data.length).toFixed(2) : '-'}` }
  ];
  let height = ($(`#${id}-modal .modal-body`).length > 0 ? $(`#${id}-modal .modal-body`).height() - margin : margin);
  $(`#${id}-table`).bootstrapTable({ data: Object.values(data), columns: col.filter((value) => columns.indexOf(value.field) >= 0), height: height, search: search, showFooter: footer, detailView: detail !== false, detailFormatter: detail,
    onClickRow: (row, element, field) => field !== 'check' && row.code ? syllabus_show(row.code) : '',
    onPostHeader: () => $(`#${id}-table`).parent().parent().find('.fixed-table-header').find('th').each((i, e) => $(`#${id}-table`).parent().parent().find('.fixed-table-footer').find('th').eq(i).attr('style', $(e).attr('style'))),
    locale: 'ja-JP', sortable: true, showSearchButton: true, showColumns: true, detailViewByClick: true, MaintainMetaData: true, MultipleSelectRow: true, formatLoadingMessage: () => '読み込み中...', formatSearch: () => '検索', formatNoMatches: () => 'データがありません' });
}

function gp_calc(data) {
  return Object.values(data).reduce((prev, current) => prev + (parseInt(current.point) >= 0 ? (current.point * parseInt(current.credit)) : 0), 0);
}

function gpa_calc(data) {
  let credit = Object.values(data).reduce((prev, current) => prev + (parseInt(current.point) >= 0 ? parseInt(current.credit) : 0), 0);
  return (credit > 0 ? (gp_calc(data) / credit).toFixed(2) : '-');
}

//各タームの始まる日付と年度、タームとの対応表
const StartTerm = { '2019':{ 1:'2019-04-08', 2:'2019-06-11', 3:'2019-08-07', 4:'2019-10-01', 5:'2019-12-03', 6:'2020-02-06', 7:'2020-04-01' } };
//各タームの終わる日付と年度、タームとの対応表
const EndTerm = { '2019':{ 1:'2019-06-10', 2:'2019-08-06', 3:'2019-09-30', 4:'2019-12-02', 5:'2020-02-05', 6:'2019-03-31' } };
//ターム内で休日と示されている日一覧
const Holiday = [ '2019-04-29', '2019-04-30', '2019-05-01', '2019-05-02', '2019-05-03', '2019-05-06', '2019-07-15', '2019-08-12', '2019-09-16', '2019-09-23',
  '2019-10-14', '2019-10-22', '2019-10-31', '2019-11-01', '2019-11-04', '2019-12-30', '2019-12-31', '2020-01-01', '2020-01-02', '2020-01-03', '2020-01-13', '2020-01-17', '2020-02-11', '2020-02-24', '2020-03-20' ];
//月曜休日が続いたなどで振替が起きるときの曜日と日付の対応表
const RDATE = { '2019-07-16':'月', '2019-10-16':'月', '2019-01-14':'月', '2019-01-15':'金' };
const DOW = ['月', '火', '水', '木', '金', '土'];

var TimetableWeek = 0;
function timetable_reload(addWeek = 0){
  $('.subject-box').remove();
  TimetableWeek += addWeek;
  let addHTML= '';
  if (reg_code !== undefined && reg_code.length > 0){
    let endMax = 5;
    for (i in reg_data) {
      let sub = reg_data[i];
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
    let days = [];
    //月火水木金土のターム
    let terms = [];
    //年度を計算
    let date = new Date();
    let year = (date.getMonth() < 3 ? date.getFullYear() - 1 : date.getFullYear());
    //月曜日の日付を取得
    date.setDate(date.getDate() - date.getDay() + 1 + TimetableWeek * 7);
    for (let i = 0; i < 5; i++, date.setDate(date.getDate() + 1)) {
      let dateString = date.getFullYear() + '-' + ('0' + (date.getMonth() + 1)).slice(-2) + '-' + ('0' + date.getDate()).slice(-2);
      //休みかどうか判定
      if (Holiday.indexOf(dateString) >= 0)
        days[i] = false;
      //振替曜日ならそれを優先
      else if (Object.keys(RDATE).indexOf(dateString) >= 0)
        days[i] = RDATE[dateString];
      else
        days[i] = DOW[i];
      //日付が属するタームを決める
      for (let j = 1; j <= 6; j++)
        if (new Date(StartTerm[year][j]) <= date && date < new Date(StartTerm[year][j + 1])) {
          terms[i] = j.toString();
          break;
        }
      //日付表示
      $('#timetable-date th:eq(' + (i + 1) + ')').text((date.getMonth() + 1) + '/' + date.getDate());
      //曜日とターム表示
      $('#timetable-dow th:eq(' + (i + 1) + ')').text((days[i] ? days[i] : '休') + '(T' + terms[i] + ')');
    }
    for (let i = 0; i < reg_code.length; i++) {
      let sub = reg_data[reg_code[i]];
      if (sub['term'].indexOf('T') < 0 || sub['term'].indexOf('集') >= 0) continue;
      else if (sub['time'].indexOf('他') >= 0) continue;
      sub['time'] = sub['time'].replace(/、/g, ',');
      
      //教科の属する曜時配列
      let subTimes = sub['time'].split(',');
      //教科の属するターム
      let subTerms = sub['term'].replace('1-3', '123').replace('4-6', '456').replace(/[T\-]/g, '').split('');
      for (let ii in subTimes) {
        //曜日ごとにタームチェック
        //if (terms.indexOf(subTerms[ii]) < 0) continue;

        for (let iii in days) {
          //曜日が一致しなければスルー
          if (days[iii] !== subTimes[ii].substr(0, 1)) continue;
          else if (subTerms.indexOf(terms[iii]) < 0) continue;

          //重なる教科チェック
          let count = 0, start = 0;
          for (let iiii = 0; iiii < reg_code.length; iiii++) {
            let sub2 = reg_data[reg_code[iiii]];
            if (sub2['term'].indexOf('T') < 0 || sub['term'].indexOf('集') >= 0) continue;
            else if (sub2['time'].indexOf('他') >= 0) continue;
            sub2['time'] = sub2['time'].replace(/、/g, ',');

            //教科の属する曜時配列
            let subTimes2 = sub2['time'].split(',');
            //教科の属するターム
            let subTerms2 = sub2['term'].replace('1-3', '123').replace('4-6', '456').replace(/[T\-]/g, '').split('');
            
            //曜日ごとにタームチェック
            if (subTerms2.indexOf(terms[iii]) < 0) continue;
            //曜時ごとに重なりチェック
            if (subTimes2.indexOf(subTimes[ii]) >= 0){
              count++; //重なる教科数をカウント
              if (iiii < i) start++; //インデックスが後なら右にずれていく
            }
          }
          
          let ts = parseInt(subTimes[ii].substr(1, 1)) - 1;//, te = timetable_top_calc(timetable_min_calc(sub['end']));
          addHTML += '<div class="subject-box waves-effect waves-light" data-code="' + reg_code[i] + '" style="' +
            'left:' + (20 * iii + 20 / count * start) + '%;' +
            'top:' + ((100 / endMax) * ts) + '%;' +
            'height:' + ((100 / endMax) * (1 /*te - ts*/)) + '%;' +
            'width:' + 20 / count + '%;' +
            '" onclick="syllabus_show(\'' + reg_code[i] + '\');">' + sub['name'] + '<small>' + sub['room'] + '</small>' + '</div>';
        }
      }
    }
    if (reg_code.length === 0){
      //時間割に教科がないとき
      addHTML = '<div class="w-100 h-100 p-5 text-white" style="font-size: 1.8rem;">検索をしてシラバス詳細画面から「カレンダーに追加」をして時間割を作成しよう！</div>';
    }
  }
  else {
    //未ログインの時
    addHTML = '<div class="w-100 h-100 p-5 text-white" style="font-size: 1.8rem;">ログインして時間割も便利かつ簡単に作成しよう！</div>';
  }
  $('#subjects-container').html(addHTML);
  /*$('.subject-box').on('click', (e) => {
    let code = ($(e.target).hasClass('subject-box') ? $(e.target).data('code') : $(e.target).parent().data('code'));
    if (code !== 'undefined') syllabus_show(code);
  });*/
}
//////////////////////////////////////////////////
//////////コメント関連メソッド/////////////////////////
//////////////////////////////////////////////////

/**
 * コメントの投稿
 */
function comment_post() {
  if ($('#comment-text').val().trim() === '')
    alert('コメントを記入してください');
  else {
    let code = $('#syllabus-modal').data('code');
    $('#comment-text, #comment-name').prop('disabled', true);
    $('#syllabus-comment-post-button').prop('disabled', true).text('投稿しています...');
    ajax({ comment_post: { show_error: true, code: code, name: $('#comment-name').val(),
      text: $('#comment-text').val() }, comment_get: { show_error: true, code: code } });
  }
}

function comment_post_result(data) {
  $('#comment-text, #comment-name').prop('disabled', false);
  $('#syllabus-comment-post-button').prop('disabled', false).text('投稿');
  if (data.result) {
    $('#comment-text').val('');
    //成功のトースト
  }
}

function comment_get_result(data) {
  /*if (reload || $('#syllabus-body-comment').data('index') === 0) {
    reload = true;
    $('#syllabus-body-comment').html('');
    $('#syllabus-body-comment-load').css('display', 'block');
  }*/
  let reload = true;
  let body = '';
  //if ($('#syllabus-body-comment').html().trim() === '') reload = true; //初回取得時にnumが入るように
  if (data.comment && data.comment.length > 0) {
    for (let i in data.comment) {
      let s = data.comment[i];
      if (reload && i === 0) $('#syllabus-body-comment').data('num', parseInt(s['num'])); //numの最大値
      body += '<h6>' + s['num'] + '. ' + s['name'] + ': ' + s['datetime'] + '</h6>' +
        '<p>' + s['text'].replace(/\n/g, '<br>') + '</p>';
      if (s['num'] <= 1) $('#syllabus-body-comment-load').css('display', 'none'); ////////////////////全学年対応
    }
  }
  else { //コメントがないとき
    body = '<h2 class="my-5" align="center">コメントなし</h2>';
    $('#syllabus-body-comment-load').css('display', 'none');
    $('#syllabus-body-comment').data('num', 0);
  }
  $('#syllabus-body-comment').data('index', data.index).html(body);//append(body);
}


//////////////////////////////////////////////////
//////////カレンダー関連メソッド///////////////////////
//////////////////////////////////////////////////

/**
 * カレンダーに追加済みの全ての教科一覧を取得
 * @param {boolean} init 初回フラグ
 */
function reloadAllCalendarSubjects(init = false, beforeYear = false){
  ajax({ cal_list_get: {} });
}

function cal_list_get_result(data) {
  //Google再ログイン必要
  require_google_login = (data.error_code === 1 || data.error_code === 2);
  if (data.cal_code) cal_code = data.cal_code;
  if (data.cal_data) cal_data = data.cal_data;
}

/**
 * カレンダーに追加済みの週間の教科一覧を取得、時間割を生成
 * @param {boolean} init 初回フラグ
 */
function reloadWeekCalendarSubjects(init = false){
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
              if (sub['start'].substr(0, 10) === s['start'].substr(0, 10) && (timetable_min_calc(sub['start']) < timetable_min_calc(s['end']) && timetable_min_calc(sub['end']) > timetable_min_calc(s['start']))){
                count++;
                if (ii < i) start++;
              }
            }
            var wod = new Date(sub['start']).getDay() - 1;
            if (wod < 0 || wod >= 5) continue;
            var ts = timetable_top_calc(timetable_min_calc(sub['start'])), te = timetable_top_calc(timetable_min_calc(sub['end']));
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
            syllabus_show($(e.target).data('code'));
          else
            syllabus_show($(e.target).parent().data('code'));
        });*/
}

function timetable_min_calc(datetime){
  return parseInt(datetime.substr(11, 2)) * 60 + parseInt(datetime.substr(14, 2));
}

/**
 * 上からどのくらいの距離か計算
 * @param {int} time Hour * 60 + Minute
 */
function timetable_top_calc(time) {
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

function cal_notify_toggle(button){
  let bool = !$(button).data('bool');
  if (confirm('カレンダーの通知設定を' + (bool ? 'オン' : 'オフ') + 'に切り替えますか？少し時間がかかります。')){
    let id = [];
    for (index in cal_data) {
      let sub = cal_data[index];
      if (sub['notification'] !== bool)
        id.push(sub['id']);
    }
    startLoading();
    ajax({ calender_notification_toggle: { notification: bool } });
  }
}

function calender_notification_toggle_result(data) {
  console.log(data);
  ajax({ cal_list_get: {}, cal_week_list_get: {} });
}

function cal_change(code) {
  let bool = !bool_get(cal_code, code);
  //カレンダーから削除する時は確認
  if (!bool && !confirm('本当に「' + cal_data[code].name + '」をカレンダーから削除しますか？\n' +
    'メモの変更や手動で削除した一部のイベントなどが失われます。')) return;

  $('.cal-' + code).prop('disabled', true).text('読み込み中...');
  ajax({ cal_change: { show_error: true, code: code, bool: bool, id: bool === false ? cal_data[code].id : null,
    notification: ($('#syllabus-calendar-notification').prop('checked') ? true : false) } });
}

function cal_change_result(data) {
  $('.cal-' + data.code).prop('disabled', false).text(text_get(cal_text, data.bool));
  if (data.result) {
    ajax({ cal_list_get: {}, cal_week_list_get: {} });
  }
}


//////////////////////////////////////////////////
//////////お気に入り関連メソッド///////////////////////
//////////////////////////////////////////////////


/**
 * お気に入り一覧の再読み込み
 */
function fav_list_get_result(data) {
  if (data.data) {
    fav_data = data.data;
    fav_code = Object.keys(data.data);
  }
  else {
    fav_data = [];
    fav_code = [];
  }
  if ($('.star .bootstrap-table').length > 0)
    $('#favorite-table').bootstrapTable('destroy');
  table_create('favorite', fav_data, window.innerHeight - 100, [ 'term', 'time', 'credit', 'name', 'teacher' ], true, false);
}

/**
 * お気に入りの切り替え
 * @param {string} code 教科コード
 */
function fav_change(code) {
  let bool = !bool_get(fav_code, code);
  $('.fav-' + code).prop('disabled', true).text('処理中...');
  ajax({ fav_change: { code: code, bool: bool }, fav_list_get: {} });
}

function fav_change_result(data) {
  if (data.result) {
    let tr = $('.tr-' + data.code);
    if (data.bool) tr.addClass('star');
    else tr.removeClass('star');
    $('.fav-' + data.code).prop('disabled', false).text(text_get(fav_text, data.bool));
  }
}


//////////////////////////////////////////////////
////////ポータル関連のデータを更新し取得するメソッド/////////
//////////////////////////////////////////////////

function reloadPortalRegistrationList(refresh = false, publicID = null){
  ajax({ portal_reg_list_get: {} });
}

function portal_reg_list_get_result(data) {
  if (!data.refresh) ajax({ portal_reg_list_get: { init: (data.init === true), refresh: true,
    publicID: (data.publicID ? data.publicID : null), nendo: new Date().getFullYear() + (new Date().getMonth() < 3 ? -1 : 0) } });
  if (data.reg_code) reg_code = data.reg_code;
  if (data.reg_data) reg_data = data.reg_data;
  timetable_reload();
}

function portal_grade_list_get_result(data) {
  if (!data.refresh) ajax({ portal_grade_list_get: { init: (data.init === true), refresh: true } });
  if (data.grade_data) grade_data = data.grade_data;
}

function changePortalReg(button, code, bool = true) {
  if (portal_reg_bool_get(code) === false) {
    $(button).prop('disabled', true).text('処理中...');
    startLoading();
    ajax({ portal_reg: { code: code }, portal_reg_list_get: { refresh: true } });
  }
}

function portal_reg_result(data) {
  if (data.code && data.bool && data.name) {
    alert(data.name + (data.bool ? 'の履修登録完了' : 'の履修登録の削除完了'));
    let button = $('.portal-reg-button');
    if (button.data(code) === data.code)
      button.prop('disabled', false).text(text_get(reg_text, bool));
  }
}


//////////////////////////////////////////////////
//////////サブ画面の準備とそれに付随するメソッド////////////
//////////////////////////////////////////////////

/**
 * シラバスを共有する際のモーダルの表示準備
 */
$('#syllabus-link-modal').on('show.bs.modal', (e) => {
  let code = '', name = 'chibasys';
  if (e.relatedTarget){
    if (e.relatedTarget.id !== 'share-button') {
      //ボタンから表示した場合
      let p = $(e.relatedTarget).parent().parent().parent();
      let syllabusWindow = p.hasClass('modal-dialog');
      //シラバス画面からの時
      if (syllabusWindow)  p = p.parent();
      code = p.data('code');
      name = p.data('name');
    }
    //履歴に追加
    history.pushState({ method: 'dialog', dialog: 'syllabus-link', code: code, name: name },
      name + 'を共有 -chibasys-', request_get() + '#syllabus-link');
    gtag('config', 'UA-44630639-4', {'page_path': request_get()});
  }
  else if (history.state && history.state && history.state['method'] === 'dialog' && history.state['dialog'] === 'syllabus-link') {
    //進むボタンからの復元
    code = history.state['code'];
    name = history.state['name'];
  }

  let url = (code === '' ? 'https://' + document.domain + '/welcome' : 'https://' + document.domain + '/syllabus?' + code);
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
function link_copy(button) {
  if (copyTimeout !== null) clearTimeout(copyTimeout);
  $(button).text('コピーしています...');
  $(button).prop('disabled', true);
  let textarea = $('#syllabus-link-modal textarea');
  textarea.readonly = false;
  textarea.select();
  let result = document.execCommand('copy');
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
function link_share(button) {
  //リンクを共有
  let text = (($('#syllabus-link-modal').data('name') === '') ? 'chibasysをはじめよう！' : $('#syllabus-link-modal').data('name') + 'をchibasysで見る');
  navigator.share({
    text: text,
    url: $('#syllabus-link-modal textarea').val()
  });
}

const En2Ja = {"jikanwaricd":"授業コード","department":"所属学部","subject":"所属学科","course":"所属コース等",
  "class_type":"学科(専攻)・科目の種別等","name":"授業科目","subject_code":"科目コード","numbering_code":"ナンバリングコード",
  "method":"授業の方法","language":"使用言語","credit":"単位数","hour":"時間数","period":"期別","grade":"履修年次",
  "term":"ターム","time":"曜日・時限","sub_major":"副専攻","sub_title":"副題","student_count":"受入人数",
  "teacher":"担当教員","target_student":"受講対象","room":"教室","update_date":"更新日","summary":"概要",
  "purpose":"目的・目標","content":"授業計画・授業内容","homework":"授業外学習","keyword":"キーワード",
  "textbook":"教科書・参考書","evaluation_method":"評価方法・基準","related_subject":"関連科目",
  "requirement":"履修要件","remark":"備考","related_url":"関連URL","detail":"授業計画詳細情報"};
/**
 * カレンダーに追加する際のモーダルの表示準備
 */
$('#syllabus-calendar-modal').on('show.bs.modal', (e) => {
  if (cal_data === null || cal_data === undefined){
    checkResponse({ status: 'expired' }, true);
    e.preventDefault();
    e.stopImmediatePropagation();
    return false;
  }
  $('#syllabus-calendar-notification').bootstrapToggle($('#syllabus-calendar-notification').data('init'));
  if (e.relatedTarget){
    //ボタンから表示した場合、履歴に追加
    history.pushState({ method: 'dialog', dialog: 'syllabus-calendar' }, 'カレンダーに追加 -chibasys-', request_get() + '#syllabus-calendar');
    gtag('config', 'UA-44630639-4',
{'page_path': request_get()});
  }

  /*var list = ["学科(専攻)・科目の種別等", "授業コード", "授業の方法", "単位数", "時間数", "履修年次/ターム", "曜日・時限", "担当教員", "受講対象", "教室",
    "概要", "目的・目標", "授業計画・授業内容", "授業外学習", "教科書・参考書", "評価方法・基準", "履修要件", "備考", "関連URL", "授業計画詳細情報"];*/
  let bodyDetail = '<thead><tr><th style="width:5%;">&#9745;</th><th style="width:25%;">項目</th><th style="width:70%;">内容</th></tr></thead><tbody>';
  for (key in syllabusResult['data'])
    if (syllabusResult['data'][key] !== null && Object.keys(En2Ja).indexOf(key) >= 0)
      bodyDetail += '<tr ' + (Object.keys(En2Ja).indexOf(key) < 0 ? 'data-bool="false"' : 'class="lime-green" data-bool="true"') +
        ' data-key="' + key + '"><td class="check">&#974' + (Object.keys(En2Ja).indexOf(key) < 0 ? 4 : 5) + ';</td><td>' + En2Ja[key] + '</td><td>' +
        syllabusResult['data'][key] + '</td></tr>';
  /*for (key in syllabusResult['detail-1'])
    bodyDetail += '<tr ' + (list.indexOf(key) < 0 ? 'data-bool="false"' : 'class="lime-green" data-bool="true"') + ' data-index="1"><td class="check">&#974' + (list.indexOf(key) < 0 ? 4 : 5) + ';</td><td>' + key + '</td><td>' + syllabusResult['detail-1'][key] + '</td></tr>';
  for (key in syllabusResult['detail-2'])
    bodyDetail += '<tr ' + (list.indexOf(key) < 0 ? 'data-bool="false"' : 'class="lime-green" data-bool="true"') + ' data-index="2"><td class="check">&#974' + (list.indexOf(key) < 0 ? 4 : 5) + ';</td><td>' + key + '</td><td>' + syllabusResult['detail-2'][key].replace(/<br>/g, ' ') + '</td></tr>';*/
  if (syllabusResult['data']['detail']) {
    bodyDetail += '<tr ' + (Object.keys(En2Ja).indexOf(key) < 0 ? 'data-bool="false"' : 'class="lime-green" data-bool="true"') + ' data-key="' + key + '"><td class="check">&#974' + (Object.keys(En2Ja).indexOf(key) < 0 ? 4 : 5) + ';</td><td colspan="2">授業計画詳細情報<br>';
    for (index in syllabusResult['data']['detail'])
      bodyDetail += syllabusResult['data']['detail'][index] + '<br>';
  }
  bodyDetail += '</tbody>';

  $(e.target).find('table').html(bodyDetail);
  $(e.target).find('tbody tr').on('click', (e) => {
    let tr = $(e.target).parent();
    let bool = tr.data('bool');
    if (typeof (bool) === 'string') bool = (bool === 'true');
    tr.data('bool', !bool);
    if (!bool) tr.addClass('lime-green');
    else tr.removeClass('lime-green');
    tr.find('.check').html(!bool ? '&#9745;' : '&#9744;');
  });
});

$('#year').change((e) => { console.log(e); });