<pre>
<?php

include('config.php');
include('includes/resources.php');
include('lib/geocoder.php');

# MySQL with PDO_MYSQL  
$DBH = new PDO("mysql:host=".DB_HOST.";dbname=" . DB_NAME, DB_USER, DB_PASS);  
$sql = 'SELECT * FROM `calls` WHERE `latitude` IS NOT NULL AND `longitude` IS NOT NULL ORDER BY capcode';  
$stmt = $DBH->prepare($sql);
$stmt->execute();
while ($row = $stmt->fetch()) {
    $location = $a_capcodes[$row['capcode']];
    echo 'draw_line('.$location['lat'] .','.$location['lon'].','.$row['latitude'].','.$row['longitude'].');<br>';
}
?>
</pre>