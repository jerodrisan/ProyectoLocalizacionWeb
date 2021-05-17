<?php

/* 

 */

require_once 'funciones_sesiones.php';
$db = new funciones_sesiones(); 
 
if(isset($_GET['id'])){
    $id = $_GET['id'];
    $datos = $db->getnumSes($id);
    echo json_encode($datos);
}

if(isset($_GET['id_selected']) && isset($_GET['num_sesion'])){
    $id = $_GET['id_selected'];
    $num_sesion = $_GET['num_sesion'];
    $response = $db->borrarSesiones($id, $num_sesion);    
    echo json_encode($response);    
       
}