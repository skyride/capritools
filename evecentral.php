<?php

//Proxy script incase eve-central ever get unhappy about their API getting hit by the site
//SHOULD PROBABLY IMPLEMENT INTELLIGENT ITEM CACHING IF WE EVER USE THIS
$url = "http://api.eve-central.com/api/marketstat/xml?" . $_SERVER['QUERY_STRING'];

echo file_get_contents($url);
?>