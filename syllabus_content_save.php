<?php
$index = intval($_GET['index']);
require_once($_SERVER['DOCUMENT_ROOT'] . '/core.php');
set_time_limit(300);
$link = mysqli_connect();
$curl = curl_init();
var_dump(syllabus_save($link, $curl, $index));
mysqli_close($link);
curl_close($curl);

if ($index <= 0) exit;
echo('<html><head><meta http-equiv="refresh" content="0;URL=https://chibasys.xperd.net/syllabus_content_save?index='.($index - 1).'"></head>');
echo('<body><h1>キャッシュ成功！ インデックス: '.($_GET['index'] + 1).'/6508</h1>');
echo('<h2>1秒後に一つ前のインデックスに遷移します...</h2></body></html>');
?>