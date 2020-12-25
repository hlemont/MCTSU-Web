<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', 300);

error_reporting(E_ALL);

$discord_oauth_json = file_get_contents("discord_client.json");
$discord_oauth = json_decode($discord_oauth_json, true);

$authorizeURL = 'https://discord.com/api/oauth2/authorize';
$tokenURL = 'https://discord.com/api/oauth2/token';
$apiURLBase = 'https://discord.com/api/users/@me';

session_start();


if($_SERVER['REQUEST_METHOD'] == 'GET'){
    // when redirected from discord oauth authorize
    if(get('code')){

        $token = apiRequest($tokenURL, array(
            "grant_type" => "authorization_code",
            'client_id' => discord_oauth['client_id'],
            'client_secret' => discord_oauth['client_secret'],
            'redirect_uri' => 'https://web.mctsu.kr/login.php'
            'code' => get('code')
        ));
        $_SESSION['access_token'] = $token->access_token;

        header('Location: ' . 'https://web.mctsu.kr/');
    }

    // login
    if(get('action') == 'login'){

        $params = array(
            'client_id' => discord_oauth['client_id'],
            'redirect_uri' => 'https://web.mctsu.kr/login.php',
            'response_type' => 'code',
            'scope' => 'identify guilds'
        );
        error_log('login executed');
        header('Location: https://discord.com/api/oauth2/authorize' . '?' . http_build_query($params));
        die();
    }

    // logout
    if(get('action') == 'logout') {
        // This must to logout you, but it didn't worked(

        $params = array(
            'access_token' => $_SESSION['access_token']
        );

        // Redirect the user to Discord's revoke page
        header('Location: https://discord.com/api/oauth2/token/revoke' . '?' . http_build_query($params));
        unset($_SESSION['access_token']);
        die();
    }
    
} else if($_SERVER['REQUEST_METHOD'] == 'POST'){
    // check
    if(post('action') == 'check'){
        
        $data = array(
            'login_status' => array_key_exists('access_token', $_SESSION)
        );

        $json = json_encode($data);
        
        header('Content-Type: application/json');
        echo $json;
        die();
    }
}




function apiRequest($url, $post=FALSE, $headers=array()) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    $response = curl_exec($ch);


    if($post)
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

    $headers[] = 'Accept: application/json';

    if(session('access_token'))
        $headers[] = 'Authorization: Bearer ' . session('access_token');

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    return json_decode($response);
}

function get($key, $default=NULL) {
    return array_key_exists($key, $_GET) ? $_GET[$key] : $default;
}

function post($key, $default=NULL){
    return array_key_exists($key, $_GET) ? $_post[$key] : $default;
}

function session($key, $default=NULL) {
    return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
}

?>