<pre><?php
include('config.php');
include('includes/resources.php');
include('lib/geocoder.php');

# MySQL with PDO_MYSQL  
$DBH = new PDO("mysql:host=".DB_HOST.";dbname=" . DB_NAME, DB_USER, DB_PASS);  
$sql = 'SELECT * FROM `calls` WHERE `latitude` IS NULL AND `longitude` IS NULL LIMIT 0, 10';  
$stmt = $DBH->prepare($sql);
$stmt->execute();
while ($row = $stmt->fetch()) {
//    var_dump();
    
    $address=urlencode($row['address'] . ', '. $row['location']);
    $loc = geocoder::getLocation($address);
   
    var_dump($address);
    var_dump($loc);
    echo '-----';
   
    
    try {
        
        $lat = $loc['lat'];
        $lng = $loc['lng'];
        
        if(is_null($loc)) {
            $lat = 0;
            $lng = 0;
        }
        
        $data = array( 
            'id' => $row['id'], 
            'latitude' => $lat,
            'longitude' => $lng
        ); 
        
        $STH = $DBH->prepare("UPDATE calls SET latitude=:latitude, longitude=:longitude WHERE id=:id");
        $STH->execute($data);
    }
    catch(PDOException $e) {  
        var_dump($e);
    }  
}  


//
?>
</pre>