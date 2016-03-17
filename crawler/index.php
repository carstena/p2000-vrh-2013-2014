<?php

include('lib/simple_html_dom.php');
include('config.php');
include('includes/resources.php');

# MySQL with PDO_MYSQL  
$DBH = new PDO("mysql:host=".DB_HOST.";dbname=" . DB_NAME, DB_USER, DB_PASS);  


$afk = array();
foreach($afkortingen as $key => $val) {
    $afk[] = $key;
}

function get_url($url) {
   // create a new cURL resource
    $ch = curl_init();
    
    $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
    
    
    // cookies to be sent
    curl_setopt($ch, CURLOPT_COOKIE, "s_types=3");
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);
    $data = curl_exec($ch);
    curl_close($ch);
    
    return $data;
}




$urls = array(
    "http://p2000mobiel.nl/15/26/haaglanden.html"
);

$src = '';
foreach($urls as $url) {
   $src .= get_url($url);
}

$html = new simple_html_dom();

// Load HTML from a string
$html->load($src);

foreach($html->find('.call') as $call) {
    
    foreach($call->find('.date') as $date) {
        $the_date = $date->plaintext;
    }
    
    foreach($call->find('.message') as $message) {
        $str = $message->plaintext;
        $prio = mb_substr($str, 0, 3);
        if($prio == 'P 1' || $prio == 'P 2') {
            $the_message = $str;
            $hash = md5($the_date.$str);
            $the_clear_message = str_replace('P 1','',$str);
            $the_clear_message = str_replace('P 2','',$the_clear_message);
            
            // Tekst tussen haakjes ( ) verwijderen
            preg_match_all('/\((.*?)\)/', $the_clear_message, $out);
           
            foreach($out[0] as $item) {
                
                  $the_clear_message = str_replace($item,'', $the_clear_message);
            }
            
            // vreemde karakters verwijderen
            $the_clear_message = str_replace('.','', $the_clear_message);
            $the_clear_message = str_replace('!','', $the_clear_message);
            $the_clear_message = str_replace('(','', $the_clear_message);
            $the_clear_message = str_replace(')','', $the_clear_message);
            $the_clear_message = str_replace(':','', $the_clear_message);
            
            // Kazerne Afkorting vinden
            $message_chunks = explode(' ', $the_clear_message);
            foreach ($message_chunks as $chunk) {     
                $chunk = trim($chunk);
                if (in_array($chunk, $afk)) {
                  $split_chunk = $chunk;
                    $message_afkorting = $chunk;
               }
            }
            
            // Splitten op kazerne afkorting
            $message_chunks = explode($split_chunk, $the_clear_message);
            $the_clear_message = $message_chunks[0];
        }
    }
    
    foreach($call->find('.called') as $called) {
        
        $codes = array();
       
        $capcodes = explode('<br />', $called->innertext);
        foreach($capcodes as $capcode) {
           
            if(strstr($capcode,'Lichtkrant')) {
                $capcode = strip_tags($capcode);
                $capcode = str_replace('[','', $capcode);
                $capcode = str_replace(']','', $capcode);
                $capcode = mb_substr($capcode, 0, 7);
                $codes[] = $capcode;  
            }
        }
    }
    
    // Sanitize call
    $str = array();
    $chunks = explode(' ',$the_clear_message);
    foreach($chunks as $chunk) {
        if( !in_array(strtolower($chunk), $call_keywords ) ) {
            $str[] = trim($chunk);
        }
    }
    
    if(($prio == 'P 1' || $prio == 'P 2') && count($codes) > 0 ) {
        
        $city = $afkortingen[$message_afkorting];//         
        $location = $a_capcodes[$codes[0]];
        
        echo $the_date . ' - ('. $city . ') '. implode(' ', $str) . ' - (' . $location['label'] . ') - '.$hash.'<br>';
////        echo 'draw_line('.$location['lat'] .','.$location['lon'].',0,0);<br>';
//        
        foreach($codes as $code) {
            
            # Insert row
            $data = array( 
                'hash' => $hash, 
                'capcode' => $code,
                'body' => trim($the_message), 
                'timestamp' => $the_date,
                'address' => trim(implode(' ', $str)),
                'location' => $city
            ); 
//          
            try {
                $STH = $DBH->prepare("INSERT INTO calls  
                (hash, capcode, body, timestamp, address, location) values
                (:hash, :capcode, :body, :timestamp, :address, :location)");
                $STH->execute($data);
            }
                catch(PDOException $e) {  
                    var_dump($e);
                }  
        }

    }
}

?>