<?php
header("Access-Control-Allow-Origin: *");
// this is gross but a hack for the superteams not having json format
/* memcache prevents clients from hitting extralife more than once every 5 seconds to prevent bans */
$memcache = new Memcache;
$memcache->connect('localhost', 11211) or die ("Could not connect");

if ($data = $memcache->get('memory')){
        echo json_encode($data);
}else{ // 5 seconds have expired; request a new number.
        $last_known = $memcache->get('memory');
        $file = file_get_contents("https://www.extra-life.org/index.cfm?fuseaction=widgets.ajaxWidgetCompileHTML&callback=jsonpCallback&language=en&teamGroup=rooster&type0=login&showheader0=false&type1=login&showheader1=false&type2=thermometer&orientation2=horizontal&currencyformat2=&showheader3=false&currencyformat3=&type3=quickstats&showtotalteams3=false&showtotalparticipants3=false&showheader4=false&type4=quickstats&showtotalteams4=false&showtotalraised4=false&showheader5=false&type5=quickstats&showtotalparticipants5=false&showtotalraised5=false&showheader6=false&type6=topFundraisers&showviewmore6=false&showheader7=false&type7=topTeams&showviewmore7=false&format=json");
        $pattern= '/data-total-raised=\\\"(.*?)\\\">/';
        preg_match($pattern,$file,$matches);
        $new_number = intval(round($matches[1]));

        // if the last known number is larger than what we just got, use that instead of the new number.
        if ($last_known > $new_number){
                $data = ARRAY("amount-raised" => $last_known);
                $memcache->set('memory', $data, false, 5) or die ("Failed to save data at the server");
                $memcache->set('last_known', intval(round($last_known)), false, 0) or die ("Failed to save data at the server");
                echo json_encode($data);
        }else{ // if the last known number is SMALLER, use the new number and update the old number.
                $data = ARRAY("amount-raised" => $new_number);
                $memcache->set('memory', $data, false, 5) or die ("Failed to save data at the server");
                $memcache->set('last_known', intval(round($new_number)), false, 0) or die ("Failed to save data at the server");
                echo json_encode($data);
        }


}
?>
