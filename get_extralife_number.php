<?php
header("Access-Control-Allow-Origin: *");
// this is gross but a hack for the superteams not having json format
/* memcache prevents clients from hitting extralife more than once every 5 seconds to prevent bans */
$memcache = new Memcache;
$memcache->connect('localhost', 11211) or die ("Could not connect");

$number = $memcache->get('donation_number');
$data = ARRAY("amount-raised" => $number);
echo json_encode($data);

?>
