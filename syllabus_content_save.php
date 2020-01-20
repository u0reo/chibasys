<?php
$nendo = intval($_GET['nendo']);
$index = intval($_GET['index']);
set_time_limit(300);
require_once($_SERVER['DOCUMENT_ROOT'] . '/core.php');
init();
portal_real_syllabus_save($nendo, $index);
finalize();

if ($index <= 0) exit;
echo('<html><head><meta http-equiv="refresh" content="0;URL=syllabus_content_save?nendo='.$nendo.'&index='.($index - 1).'"></head>');
echo('<body><h1>キャッシュ成功！ インデックス: '.($index + 1).'/6508</h1>');
echo('<h2>0秒後に一つ前のインデックスに遷移します...</h2></body></html>');
?>