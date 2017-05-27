<?php

$create_db = mysql_query("CREATE DATABASE IF NOT EXIST $_GET['database_name']");
if($create_db != true) {
	mysql_query("DROP $_GET['database_name']");
	call_user_func($create_db);
}

$add_tables = mysql_query("CREATE TABLE users (
				id int(4) auto_increment primary key,
				name varchar(20) not null,
				email varchar(100) not null,
				paypal varchar(50)
				);
				CREATE TABLE groceries (
				id int(4) primary key,
				item varchar(20) not null,
				next varchar(6) not null
				);
				CREATE TABLE bills (
				id int(4) auto_increment primary key,
				bill varchar(40),
				amount varchar(6),
				description varchar(200)
				);
				");

?>