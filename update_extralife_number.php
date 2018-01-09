<?php
include 'secrets.php';
header("Access-Control-Allow-Origin: *");
// this is gross but a hack for the superteams not having json format
/* memcache prevents clients from hitting extralife more than once every 5 seconds to prevent bans */
$memcache = new Memcache;
$memcache->connect('localhost', 11211) or die ("Could not connect");

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.donordrive.com/extralife/export/RT2017totals.json",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache",
    "donordrive-email: ".$DDemail,
    "donordrive-password: ".$DDpw,
    "postman-token: d58ff0dc-33fb-d65b-eafd-70b8bf4d8586"
  ),
));

$response = json_decode(curl_exec($curl));
$err = curl_error($curl);
curl_close($curl);
$new_number = round($response->result[0]->totalraised);
$old_number = $memcache->get('donation_number_api');

echo "old number: $old_number \t new number: $new_number \n\n";

// if the new number is bigger than the memcached number, we need to update.
if ($new_number > $old_number){
        $memcache->set('donation_number', $new_number, false, 0) or die ("Failed to save data at the server");
        $data = ARRAY("amount-raised" => $new_number);
        echo json_encode($data);
}

?>
