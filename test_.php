<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/core.php');
init(true);
set_time_limit(4000);
$result = maria_query('SELECT * FROM chibasys.grade');
while ($row = mysqli_fetch_assoc($result)) {
  $syllabus = portal_syllabus_get([ 'code'=>"$row[nendo]-$row[jikanwariCd]" ]);
  $r = maria_query("UPDATE chibasys.grade SET credit=".$syllabus['data']['credit']." WHERE id='$row[id]' AND nendo='$row[nendo]' AND jikanwariCd='$row[jikanwariCd]';");
}
finalize();
?>