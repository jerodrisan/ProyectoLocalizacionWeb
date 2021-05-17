<?php

/* 
 
 */

require_once 'Funciones_Contactos.php';
$db = new Funciones_Contactos(); 
 
$email = $_GET['email'];
$datos = $db->mostrar_contactos($email);

echo json_encode($datos);