<?php

require('users.php');

session_start();

if($_SERVER['REQUEST_METHOD'] == 'GET'){
    if(!isset($_SESSION['id'])){
        header('Location: ' . 'https://web.mctsu.kr/');
    }

    if(isset($_GET['action'])){
        // get id
        $id = $_SESSION['id'];

        //load:
        // username, discord_name, mc_name, register_date, (optional)twitch_name
        if($_GET['action'] == 'load'){
			// set header: json
        	header('Content-Type: application/json');
			
            // read
            $res = select_account_by_id($id);
            if($res){
                $result = array(
                    'status' => 'success',
                    'username' => $res['username'],
                    'discord_name' => $res['discord_name'],
                    'mc_name' => $res['mc_name'],
                    'register_date' => $res['register_date'],
                    'twitch_name' => $res['twitch_name']
                );
            }
            else {
                $result = array(
                    'status' => 'failure',
                    'error' => ['DBError', 'select']
                );
            }


            // encode as json
            $json = json_encode($result);
            if($json === false){
                $json = json_encode(array(
                    'jsonError', json_last_error_msg()
                ));
            }
			
			error_log('load result: ' . $json);
            
            echo $json;
            die();
        }
        

        //save:
        // username, mc_name, twitch_name
        if($_GET['action'] == 'save'){
            header('Content-Type: application/json');
            $values = [];
            $invalid = [];

            // validate inputs
            
            //username
            if(isset($_GET['username'])){
                if(preg_match("/(^[ㄱ-ㅎㅏ-ㅣ가-힣]|\w){2,16}$/s", $_GET['username'])){
                    // when valid
                    $values['username'] = $_GET['username'];
                }

                else {         
                    // when invalid
                    $invalid[] = 'username';
                }
            }

            //mc_name
            if(isset($_GET['mc_name'])){
                if(preg_match("/^[\w]{3,16}$/s", $_GET['mc_name'])){
                    // when valid
                    $values['mc_name'] = $_GET['mc_name'];
                }
                else {     
                    // when invalid
                    $invalid[] = 'mc_name';
                }
            }

            //twitch_name
            if(isset($_GET['twitch_name'])){
                if(preg_match("/^[a-zA-Z0-9][\w]{3,24}$/s", $_GET['twitch_name'])){
                    // when valid
                    $values['twitch_name'] = $_GET['twitch_name'];
                }
                else {
                    // when invalid
                    $invalid[] = 'twitch_name';
                }
            }

            // when no invalid inputs and at least one input performed
            if($invalid == null && $values != null){
                // update
                try{
                    $res = update_account($id, array_keys($values), array_values($values));
                    if(!$res){
                        throw new dbError('update');
                    }

                    if(isset($values['mc_name'])){
                        $res = select_account_by_id($id);
                        if(!$res){
                            throw new dbError('select');
                        }
                        
                        // when the account was waiting for verification
                        if($res['verification_code'] == 1){
                            if(!update_account($id, array('verification_code'), array(0))){
                                throw new dbError('update');
                            }
                        }
                    }

                    $result = array(
                        'status' => 'success'
                    );
                }
                catch (dbError $e){
                    $result = array(
                        'status' => 'failure',
                        'error' => ['DBError', $e->getMessage()]
                    );
                }
            }
            else{
                if($invalid != null){
                    $result = array(
                        'status' => 'failure',
                        'error' => ['FormatError', $invalid]
                    );
                }
                else{
                    $result = array(
                        'status' => 'failure',
                        'error' => ['EmptyInputError']
                    );
                }
            }


            $json = json_encode($result);
            if($json === false){
                $json = json_encode(array(
                    'jsonError', json_last_error_msg()
                ));
            }

            echo $json;
            die();
        }


        // request verify:
        // only when mc_name is filled, and 
        if($_GET['action'] == 'verify'){
            header('Content-Type: application/json');

            // read
            $account = select_account_by_id($id);
            $mc_name = $account['mc_name'];
            $verification = $account['verification_code'];

            if($verification != 0){
                // status 1: waiting for verification
                if($verification == 1){
                    $result = array(
                        'status' => 'failure',
                        'error' => 'waiting'
                    );
                }

                // status 2: already verified
                if($verification == 2){
                    $result = array(
                        'status' => 'failure', 
                        'error' => 'verified'
                    );
                }
            }
            else if($mc_name == null){
                $result = array(
                    'status' => 'failure',
                    'error' => 'mc_name_not_specified'
                );
            }
            else {
                if(update_account($id, array('verification_code'), array(1))){
                    $result = array(
                        'status' => 'success'
                    );

                    // send verify request to admin
                    $params = array(
                        'action' => 'request',
                        'secret' => 'McseKretTsuaDmiNi'
                    );
					
                    $ch = curl_init("mail.php");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    $response = curl_exec($ch);
                    if(curl_error($ch)){
                        error_log('error occured in curl');
                    }
                    else{
                        $result = json_decode($response);
                        if($result->status = 'success'){
                            error_log('successfully send verify request to admin');
                        }
                        else{
                            error_log('failed to send verify request to admin: ' . implode('/', $result->error));
                        }
                    }
                }
                else {
                    $result = array(
                        'status' => 'failure',
                        'error' => ['DBError', 'update']
                    );
                }
            }

            $json = json_encode($result);
            if($json === false){
                $json = json_encode(array(
                    'jsonError', json_last_error_msg()
                ));
            }

            echo $json;
            die();
        }

        if($_GET['action'] == 'cancel_verify'){
            header('Content-Type: application/json');

            // read
            $account = select_account_by_id($id);
            $verification = $account['verification_code'];

            if($verification != '0'){
                if(update_account($id, array('verification_code'), array(0))){
                    $result = array(
                        'status' => 'success'
                    );
                }
                else {
                    $result = array(
                        'status' => 'failure',
                        'error' => ['DBError', 'update']
                    );
                }
            }
            else{
                $result = array(
                    'status' => 'failure',
                    'error' => ['StateError', 'not verified or waiting']
				);
            }

            $json = json_encode($result);
            if($json === false){
                $json = json_encode(array(
                    'jsonError', json_last_error_msg()
                ));
            }

            echo $json;
            die();
        }


        if($_GET['action'] == 'check_profile'){
            header('Content-Type: application/json');

            $res = $select_account_by_id($id);
            if($res){
                $result = array(
                    'status' => 'failure',
                    'is_valid' => $res['mc_name'] != null
                );
            }
            else {
                $result = array(
                    'status' => 'failure',
                    'error' => ['DBError', 'select']
                );
            }

            $json = json_encode($result);
            if($json === false){
                $json = json_encode(array(
                    'jsonError', json_last_error_msg()
                ));
            }

            echo $json;
            die();
        }

        if($_GET['action'] == 'check_verification'){
            header('Content-Type: application/json');

            $res = $select_account_by_id($id);
            if($res){
                $result = array(
                    'status' => 'success',
                    'verification_code' => $res['verification_code']
                );
            }
            else {
                $result = array(
                    'status '=> 'failure',
                    'error' => ['DBError', 'select']
                );
            }

            $json = json_encode($result);
            if($json === false){
                $json = json_encode(array(
                    'jsonError', json_last_error_msg()
                ));
            }

            echo $json;
            die();
        }

    }
}

class dbError extends Exception {}


?>