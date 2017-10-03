<?php
  require '../vendor/autoload.php';

  function getGandiStatus() {
    $client = new GuzzleHttp\Client();
    $res = $client->get('https://status.gandi.net/api/status');
    $gandi_status = json_decode($res->getBody(), true);
    return $gandi_status["status"];
  };
  
  function currentStatus() {
    switch(getGandiStatus()) {
      case "SUNNY": return "All Gandi services are operational";
      default: return "Gandi is experiencing a bit of trouble";
    };
  };
?>
<html>
  <head>
    <title>Gandi Status Check</title>
  </head>
  <body>
    <h1><?php echo currentStatus(); ?></h1>
    <p><a href="http://status.gandi.net">More info</a></p>
  </body>
</html>

