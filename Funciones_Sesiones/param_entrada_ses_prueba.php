<?php


define('DB_USER', "root");
define('DB_PASSWORD', ""); //creado por el generador de contraseñas
define('DB_DATABASE', "localiz3_database"); //nombre de la base de datos
define('DB_SERVER', "localhost"); //nombre del servidor

/*
define('DB_USER', "localiz3_user");
define('DB_PASSWORD', "chuso&1999"); //creado por el generador de contrase���as
define('DB_DATABASE', "localiz3_database"); //nombre de la base de datos
define('DB_SERVER', "localhost"); //nombre del servidor
*/

$conexion = mysqli_connect(DB_SERVER ,DB_USER, DB_PASSWORD, DB_DATABASE);

//$query = "INSERT INTO sesiones (unique_id, session_num, live, time_paused, creado_el) VALUES ('$cliente_id','$ultimovalor','$live',0,'$date')";
$query = "INSERT INTO sesiones (unique_id, session_num, live, creado_el) VALUES ('5a95a2ce36e611.88572237','95','1','2023-09-03 23:03:25')";

$consulta = mysqli_query($conexion, $query);  


