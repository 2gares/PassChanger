<?php
//coded By Agares Twitter.com/2gares 

set_time_limit(0);
ini_set('max_execution_time', 0);
ini_set('memory_limit', -1);

$target_url = "https://example.com/login.php";
$username = "admin";
$password = "password123";
$users_file = "/path/to/users.txt";
$log_file = "/path/to/log.txt";

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $target_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_COOKIESESSION, true);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36");


curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, "username=$username&password=$password");
curl_exec($curl);


if (!file_exists($users_file)) {
    die("Error: Users file not found\n");
}
$users = array_map('trim', file($users_file));


foreach ($users as $user) {
    
    $new_password = generate_random_password();

    
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "username=$user&password=$new_password");
    $result = curl_exec($curl);

    
    if (strpos($result, "success") !== false) {
        $message = "Password for user $user has been changed to $new_password\n";
    } else {
        $message = "Error changing password for user $user\n";
    }
    file_put_contents($log_file, $message, FILE_APPEND);
}


require_once('/path/to/zap/Zapv2.php');
$zap = new Zapv2('localhost', 8080);
$zap->spider->scan($target_url);
sleep(10); // wait for spider to finish
$alerts = $zap->core->alerts();
foreach ($alerts as $alert) {
    $message = "ZAP Alert: [$alert->risk] $alert->name\n";
    file_put_contents($log_file, $message, FILE_APPEND);
}


curl_close($curl);


function generate_random_password($length = 12) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+{}[];,./?';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}
