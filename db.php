<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$dsn = "mysql:host=localhost;port=3306;dbname=users;charset=utf8";
$database_username = "hlemont01";
$database_password = "4x104ObktXor6wmH";

function connect_sql(){
    try{
        $db = new PDO($dsn, $GLOBALS['database_username'], $GLOBALS['database_password']);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $d){
        error_log('Failed to connect to MySQL:' . $e->getMessage());
    }
    
    return $db;
}

// database
function create_db($db, $dbname){
    $query = "CREATE DATABASE IF NOT EXISTS {$dbname}";
    $stmt = $db->prepare($query);
    return $stmt->execute();
}

function delete_db($db, $dbname){
    $query = "DELETE DATABASE {$dbname}";
    $stmt = $db->prepare($query);
    return $stmt->execute();
}

function use_db($db, $dbname){
    $query = "USE {$dbname}";
    $stmt = $db->prepare($query);
    return $stmt->execute();
}

function exist_db($db, $dbname){
    $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '\${$dbname}'";
    $stmt = $db->prepare($query);
    return $stmt->execute();
}

// table
function create_table($db, $table){
    $query = "CREATE TABLE IF NOT EXISTS {$table}";
    $stmt = $db->prepare($query);
    return $stmt->execute();
}

function delete_table($db, $table){
    $query = "DELETE TABLE {$table}";
    $stmt = $db->prepare($query);
    return $stmt->execute();
}

function exist_table($db, $table){
    $query = "SHOW TABLES LIKE '\${$table}";
    $stmt = $db->prepare($query);
    if($stmt->execute()){
        $rows = $stmt->fetchAll();
        return count($rows) > 0;
    }
    else{
        return FALSE;
    }
}


// column
function create_column($db, $table, $columnname, $datatype){
    $query = "ALTER TABLE {$table} ADD {$columnname} {$datatype}";
    $stmt = $db->prepare($query);
    return $stmt->execute();
}

function drop_column($db, $table, $columnname){
    $query = "ALTER TABLE {$table} DROP COLUMN {$columnname}";
    $stmt = $db->prepare($query);
    return $stmt->execute();
}

function exist_column($db, $table, $columnname){
    $query = "SHOW COLUMNS FROM {$table} LIKE '\${$columnname}'";
    $stmt = $db->prepare($query);
    if($stmt->execute()){
        $rows = $stmt->fetchAll();
        return count($rows) > 0;
    }
    else{
        return FALSE;
    }
}

// records
function insert_record($db, $table, $columns, $values){
    if($columns == NULL || $values == NULL){
        return FALSE;
    }
    $column_formatted = implode(", ", $columns);
    $value_formatted = implode(", ", $values);
    
    $query = "INSERT INTO {$table} ({$column_formatted}) VALUES ({$value_formatted})";
    $stmt = $db->prepare($query);
    return $stmt->execute();
}

function update_record($db, $table, $columns, $values, $conditions = ''){
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
    $stmt = $db->prepare($query);
    return $stmt->execute();
}

function delete_record($db, $table, $conditions){
    $query = "DELETE FROM {$table} ";
    if($conditions == NULL){
        $query = $query . "WHERE {$conditions}";
    }
    $stmt = $db->prepare($query);
    return $stmt->execute();
}

function select_record($db, $table, $columns=[], $conditions='', $limit=-1){
    if($columns == NULL){
        $column_formatted = '*'; 
    }
    else{
        $column_formatted = implode(", ", $columns);
    }

    $query = "SELECT {$column_formatted} FROM {$table} ";
    if($conditions != NULL){
        $query = $query . "WHERE " . implode(' AND ', $conditions) . " ";
    }
    if($limit != -1){
        $query = $query . "LIMIT $limit";
    }

    $stmt = $db->prepare($query);

    if($stmt->execute()){
        $data = $stmt->fetchAll();
        error_log(var_export($data));
		return $data;
	}
    else
        return FALSE;
}

?>