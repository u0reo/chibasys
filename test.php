<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/core.php');
$link = mysqli_connect();
$curl = curl_init();
//var_dump(portal_registration($link, $curl, [ 'code' => '2019-G17343103' ]));
$result = maria_query($link, 'SELECT id, register FROM chibasys.user');
use Hashids\Hashids;
while ($row = mysqli_fetch_assoc($result)){
  $publicId = (new Hashids('e4KrxdB2', 6))->encode(strtotime($row['register']));
  maria_query($link, "UPDATE chibasys.user SET publicID='$publicId' WHERE id='$row[id]';");
}
mysqli_close($link);
curl_close($curl);
?>