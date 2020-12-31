<?php
require('users.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', 300);

error_reporting(E_ALL);

$discord_oauth_json = file_get_contents("discord_client.json");
$discord_oauth = json_decode($discord_oauth_json, true);

$authorizeURL = 'https://discord.com/api/oauth2/authorize';
$tokenURL = 'https://discord.com/api/oauth2/token';
$apiURLBase = 'https://discord.com/api/';

session_start();

$_SESSION['current_page'] = $_SERVER['REQUEST_URI'];

if($_SERVER['REQUEST_METHOD'] == 'GET'){
    // save location
    if(isset($_GET['location'])){
        $_SESSION['location'] = $_GET['location'];
    }
    else if(isset($_SESSION['location'])){
        $_SESSION['location'] = '';
    }

    // when redirected back from discord oauth authorize
    if(isset($_GET['code'])){
        $token = request_token($_GET['code']);
        $user = discord_get_user();

        if(!exist_account($user->id)){
            insert_account(array('id', 'discord_name', 'username', 'mc_name', 'twitch_name',  'verification_code',  'register_date'), 
                array($user->id, $user->username, $user->username, '', '', 0, (new DateTime())->format('Y-m-d')));
        }
        else{
            update_account($user->id, array('discord_name'), array($user->username));
        }

        $_SESSION['id'] = $user->id;
        $_SESSION['access_token'] = $token->access_token;
        $expire_date = new DateTime();
        $term = new DateInterval("PT{$token->expires_in}S");
        $expire_date->add($term);
        $_SESSION['expire_date'] = $expire_date;
        $_SESSION['refresh_token'] = $token->refresh_token;
        
        header('Location: ' . 'https://web.mctsu.kr/' . $_SESSION['location']);
        unset($_SESSION['location']);
        die();
    }

    if(isset($_GET['action'])){

        // login
        if($_GET['action'] == 'login'){
            if(array_key_exists('access_token', $_SESSION)){
                header('Content-Type: text/html');
                echo '<script>alert("이미 로그인하셨습니다."); window.location.replace("https://web.mctsu.kr");</script>';
            }
            else{
                redirect_authorize(); 
            }
            die();
        }

        // logout
        if($_GET['action'] == 'logout') {
            $params = array(
                'access_token' => $_SESSION['access_token']
            );

            $token = apiRequest('https://discord.com/api/oauth2/token/revoke', array(
                'access_token' => $_SESSION['access_token']
            ));
            header('Location: ' . 'https://web.mctsu.kr/' . $_SESSION['location']);
            unset($_SESSION['id']);
            unset($_SESSION['access_token']);
            unset($_SESSION['expire_date']);
            unset($_SESSION['refresh_token']);
            unset($_SESSION['location']);
            die();
        }

        //check
        if($_GET['action'] == 'check'){
            header('Content-Type: application/json');

            if(array_key_exists('access_token', $_SESSION)){
                // refresh when access token expired: expire_date is smaller than 2 day
                $now = new DateTime();
                $difference = (indv t)($_SESSION['expire_date']->diff($now))->format("%r%d");
                if($difference <= 1){
                    $token = refresh_token($_SESSION['refresh_token']);

                    $_SESSION['access_token'] = $token->access_token;
                    $expire_date = new DateTime();
                    $term = new DateInterval("PT{$token->expires_in}S");
                    $expire_date->add($term);
                    $_SESSION['expire_date'] = $expire_date;
                    $_SESSION['refresh_token'] = $token->refresh_token;
                }

                $user = discord_get_user();
                $data = array(
                    'login_status' => TRUE,
                    'username' => $user->username
                );
                } else {
                $data = array(
                    'login_status' => FALSE,
                    'username' => ''
                );
            }
            
            $json = json_encode($data);
            if($json === false) {
                $json = json_encode(array("jsonError", json_last_error_msg()));
                http_response_code(500);
            }
            echo $json;
            die();
        }
    }
    
}

function redirect_authorize(){
    $params = array(
        'client_id' => $GLOBALS['discord_oauth']['client_id'],
        'redirect_uri' => 'https://web.mctsu.kr/login.php',
        'response_type' => 'code',
        'scope' => 'identify guilds'
    );
    header('Location: https://discord.com/api/oauth2/authorize' . '?' . http_build_query($params));
}

function request_token($code){
    $token = apiRequest($GLOBALS['tokenURL'], array(
        "grant_type" => "authorization_code",
        'client_id' => $GLOBALS['discord_oauth']['client_id'],
        'client_secret' => $GLOBALS['discord_oauth']['client_secret'],
        'redirect_uri' => 'https://web.mctsu.kr/login.php',
        'code' => $code,
        'scope' => 'identify guilds'
    ));
    return $token
}


function refresh_token($refresh_token){
    $token = apiRequest($GLOBALS['tokenURL'], array(
        "grant_type" => "refresh_token",
        'client_id' => $GLOBALS['discord_oauth']['client_id'],
        'client_secret' => $GLOBALS['discord_oauth']['client_secret'],
        'refresh_token' => $refresh_token,
        'redirect_uri' => 'https://web.mctsu.kr/login.php',
        'scope' => 'identify guilds'
    ));
    return $token;
}

function discord_get_user(){
    return apiRequest($GLOBALS['apiURLBase'] . 'users/@me');
}

function apiRequest($url, $post=FALSE, $headers=array()) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    $response = curl_exec($ch);


    if($post)
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

    $headers[] = 'Accept: application/json';

    if(isset($_SESSION['access_token']))
        $headers[] = 'Authorization: Bearer ' . $_SESSION['access_token'];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    return json_decode($response);
}

?>