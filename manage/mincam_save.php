<?php
require_once(__DIR__.'/../core.php');
init(true);
for ($i = 1; ; $i++) {
  set_time_limit(0);
  $result = mincam_data_get($i);
  if (count($result) > 0)
    echo("[$i]:".json_encode($result)."\n");
  else
    break;
}
echo("Success!!\n");
finalize();

/*require_once('core.php');
$curl = curl_init();
$link = mysqli_connect();
$data = getMincamData($link, $curl, $_REQUEST['page']);
if (addMincamData($link, $data) && intval($_REQUEST['page']) > 1){
  echo('<html><head><meta http-equiv="refresh" content="1;URL=test?page='.(intval($_REQUEST['page']) - 1).'"></head>');
  echo('<body><h1>キャッシュ成功！ ページ: '.$_REQUEST['page'].'</h1>');
  echo('<h2>1秒後に一つ前のページに遷移します...</h2></body></html>');
}
curl_close($curl);
mysqli_close($link);
*/
?>