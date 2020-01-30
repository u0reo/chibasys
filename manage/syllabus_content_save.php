<?php
require_once(__DIR__.'/../core.php');
init();
$year = intval(date('Y'));
//一月までは今年度のものを取得。二、三月は次年度のものを取得
if (intval(date('n')) <= 1) $year -= 1;
$total = mysqli_fetch_assoc(maria_query("SELECT COUNT(*) AS 'total' FROM chibasys.syllabus_$year;"))['total'];
$error_list = [];
for ($i = 0; $i < $total; $i++) {
  set_time_limit(0);
  $result = portal_real_syllabus_save($year, $i);
  if (isset($result['error_code']))
    $error_list[$i] = $result;
  else
    echo("[$i/$total]:".$result['data']['name']."\n");
}
finalize();
var_dump($error_list);
echo("Complete!");

/*$nendo = intval($_GET['nendo']);
$index = intval($_GET['index']);
set_time_limit(300);
require_once($_SERVER['DOCUMENT_ROOT'] . '/core.php');
init();
portal_real_syllabus_save($nendo, $index);
finalize();

if ($index <= 0) exit;
echo('<html><head><meta http-equiv="refresh" content="0;URL=syllabus_content_save?nendo='.$nendo.'&index='.($index - 1).'"></head>');
echo('<body><h1>キャッシュ成功！ インデックス: '.($index + 1).'/6508</h1>');
echo('<h2>0秒後に一つ前のインデックスに遷移します...</h2></body></html>');*/
?>
