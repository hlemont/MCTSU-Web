<?php
require('db.php');

function mc_name_to_uuid($mc_name){
    $ch = curl_init("https://api.mojang.com/users/profiles/minecraft/{$mc_name}");
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $response = curl_exec($ch);

    if(curl_error($ch)){
        error_log('error occured in uuid request');
        $result = array(
            status => 'failure',
            error => 'request_failure'
        );
    }
    else if(curl_getinfo($ch, CURLINFO_HTTP_CODE) == 204){
        error_log('no matching user found');
        $result = array(
            status => 'failure',
            error => 'not_found'
        );
    }
    else{
        $res = json_decode($response);
        $result = array(
            status => 'success',
            uuid => $res->id
        );

        return $result;
    }
}


// accounts table
// id: INT()
// username: TEXT(16)
// mc_name: TEXT(16)
// twitch_name: TEXT(24)
// verification_code: TINYINT(1)
// register_date: DATE


//create accounts table
function create_accounts(){
    $db = connect_sql();
    $result = create_table($db, 'accounts');
    return $result;
}

// user operation
function add_account($columns, $values){
    $db = connect_sql();
    $result = insert_record($db, 'accounts', $columns, $values);
    return $result;
}

function delete_account($id){
    $db = connect_sql();
    $result = delete_record($db, 'accounts', "id='$id'");
    return $result;
}

function update_account($id, $columns, $values){
    $db = connect_sql();
    $result = update_record($db, 'accounts', $columns, $values, "id='$id'");
    return $result; 
}

function select_account($conditions, $columns = [], $limit = -1){
    $db = connect_sql();
    $result = select_record($db, 'users.accounts', $columns, $conditions, $limit);
    return $result;
}

function select_account_by_id($id, $columns = [], $limit = 1){
    return select_account(["id='$id'"], $columns, $limit); 
}

function exist_account($id){
    $result = select_account_by_id($id);
    return $result;
}

// create email_notice table
function create_email_notice(){
    $db = connect_sql();
    $result = create_table($db, 'email_notice');
    return $result;
}

// email_notice table operation
function add_notification($id, $email_addr){
    $db = connect_sql();
    $result = insert_record($db, 'email_notice', array('id', 'email_addr'), array("'$id'", "'$email_addr'"));
    return $result;
}

function get_notification($id){
    $db = connect_sql();
    $result = select_record($db, 'email_notice', array('email_addr'), "id='$id'");
    return $result;
}

function remove_notification($id){
    $db = connect_sql();
    $result = delete_record($db, 'email_notice', "id='$id'");
    return $result;
}


?>