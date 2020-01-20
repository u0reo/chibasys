<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/core.php');
set_time_limit(4000);
$link = mysqli_connect();
for ($i=0; $i<6508; $i++) {
  $result = mysqli_fetch_assoc(maria_query($link, 'SELECT * FROM chibasys.syllabus_2019 LIMIT 1 OFFSET '.$i));
  
	$d = [];
  foreach ($result as $k => $v) $d[] = $k.'=\''.mysqli_real_escape_string($link, preg_replace('/([a-zA-Z0-9])。/s', '$1.', preg_replace('/([a-zA-Z0-9])、/s', '$1,', $v))).'\'';
  
  maria_query($link, 'UPDATE chibasys.syllabus_2019 SET '.implode(',', $d).
		" WHERE jikanwaricd='$result[jikanwaricd]';");
}
mysqli_close($link);
?>