<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'funciones_login.php';
$db = new funciones_login();

$email = $_GET["email"];
$tipo_login = $_GET["tipo_login"];

$respuesta = $db->cerrarSesion($email, $tipo_login);

echo json_encode($respuesta);
