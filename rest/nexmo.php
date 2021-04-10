<?php   

        $access_token=hash('sha256', 'user_aldin');

        $url = 'https://ibu2020.adnan.dev/api/sms?username=user_aldin&access_token='.$access_token.'&from=SSSD&to=38762823873&text=A+text+message+sent+using+the+IBU+SMS+API';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        echo(curl_error ($ch));
    
        echo($response);
        curl_close ($ch);    
?>