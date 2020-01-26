<?php
const FUNC_QUERY = [ 'portal_syllabus_get', 'portal_real_syllabus', 'portal_subject_change_refresh',
  'mincam_search', 'comment_get', 'login_with_portal' ];
const FUNC_ID = [ 'portal_student_info_get', 'fav_list_get', 'cal_list_get', 'cal_week_get' ];
const FUNC_ID_QUERY = [ 'portal_search', 'portal_registration', 'portal_reg_list_get', 'portal_grade_list_get',
  'portal_reg', 'memo_get', 'memo_save', 'comment_post', 'fav_change', 'cal_change',
  'calender_notification_toggle', 'userdata_save' ];

$request = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $request === 'xmlhttprequest') {
  require_once($_SERVER['DOCUMENT_ROOT'].'/core.php');
  init();

  session_start();
  $user_id = (isset($_SESSION['user_id']) && $_SESSION['user_id'] ? $_SESSION['user_id'] : null);
  session_write_close();
  $result = [];
  foreach (json_decode(file_get_contents('php://input'), true) as $func => $data) {
    if (in_array($func, FUNC_QUERY))
      $result[$func] = $func($data);
    else if (in_array($func, FUNC_ID))
      $result[$func] = $func($user_id);
    else if (in_array($func, FUNC_ID_QUERY))
      $result[$func] = $func($user_id, $data);
  }
  //header('Content-Type: application/json; charset=utf-8');
  echo(json_encode($result));

  finalize();
}
else
  http_response_code(400);
?>