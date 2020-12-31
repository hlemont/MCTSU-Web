<?php
$database_host = "localhost";
$database_username = "hlemont01";
$database_password = "4x104ObktXor6wmH";
$database_db = "users";

function connect_sql(){
    $con = mysqli_connect($GLOBALS["database_host"], $GLOBALS["database_username"], $GLOBALS["database_password"], $GLOBALS["database_db"]);
    
    if (mysqli_connect_errno())
    {
        error_log("Failed to connect to MySQL: ". mysqli_connect_error());
    }

    return $con;
}

function close_sql($con){
    mysqli_close($con);
}

function enter_query($con, $query){
    $result = mysqli_query($con, $query);
    if($result){
        log("Query succeed: {$query}");
    }
    else{
        error_log("Query failed: '{$query}' with error: " . mysqli_error($con));
    }
    return $result;
}


// database
function create_db($con, $dbname){
    $query = "CREATE DATABASE IF NOT EXISTS {$dbname}";
    $result = enter_query($con, $query);
    return $result;
}

function delete_db($con, $dbname){
    $query = "DELETE DATABASE {$dbname}";
    $res = enter_query($con, $query);
    return $result;
}

function use_db($con, $dbname){
    $query = "USE {$dbname}";
    $result = enter_query($con, $query);
    return $result;
}

function exist_db($con, $dbname){
    $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '\${$dbname}'";
    $result = enter_query($con, $query);
    return mysqli_num_rows($result) == 0;
}

// table
function create_table($con, $table){
    $query = "CREATE TABLE IF NOT EXISTS {$table}";
    $result = enter_query($con, $query);
    return $result;
}

function delete_table($con, $table){
    $query = "DELETE TABLE {$table}";
    $result = enter_query($con, $query);
    return $result;
}

function exist_table($con, $table){
    $query = "SHOW TABLES LIKE '\${$table}";
    $result = enter_query($con, $query);
    return mysqli_num_rows($result);
}


// column
function create_column($con, $table, $columnname, $datatype){
    $query = "ALTER TABLE {$table} ADD {$columnname} {$datatype}";
    $result = enter_query($con, $query);
    return $result;
}

function drop_column($con, $table, $columnname){
    $query = "ALTER TABLE {$table} DROP COLUMN {$columnname}";
    $result = enter_query($con, $query);
    return $result;
}

function exist_column($con, $table, $columnname){
    $query = "SHOW COLUMNS FROM {$table} LIKE '\${$columnname}'";
    $result = enter_query($con, $query);
    return mysqli_num_rows($result);
}

// records
function insert_record($con, $table, $columns, $values){
    if($columns == NULL || $values == NULL){
        return FALSE;
    }
    $column_formatted = implode(", ", $columns);
    $value_formatted = implode(", ", $values);
    
    $query = "INSERT INTO {$table} ({$column_formatted}) VALUES ({$value_formatted})";
    $result = enter_query($con, $query);
    return $result; 
}

function update_record($con, $table, $columns, $values, $conditions = ''){
    $pairs = [];
    $i = 0;

    for($i=0; $i < count($columns); $i++){
        array_push($pairs, "{$columns[$i]}={$values[$i]}");
    }
    $pair_formatted = implode(", ", $pairs);

    $query = "UPDATE {$table} SET {$pair_formatted} ";
    if($conditions == NULL){
        $query = $query . "WHERE {$conditions}";
    }
    $result = enter_query($con, $query);
    return $result; 
}

function delete_record($con, $table, $conditions){
    $query = "DELETE FROM {$table} ";
    if($conditions == NULL){
        $query = $query . "WHERE {$conditions}";
    }
    $result = enter_query($con, $query);
    return $result; 
}

function select_record($con, $table, $columns=[], $conditions='', $limit=-1){
    if($columns == NULL){
        $column_formatted = '*';
    }
    else{
        $column_formatted = implode(", ", $columns);
    }

    $query = "SELECT {$column_formatted} FROM {$table}";
    if($conditions != NULL){
        $query = $query . "WHERE {$conditions} ";
    }
    if($limit != -1){
        $query = $query . "LIMIT $limit";
    }

    $result = enter_query($con, $query);    

    if($result)
        return $result -> fetch_array();
    else
        return FALSE;
}

?>