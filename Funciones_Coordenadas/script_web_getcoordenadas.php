<?php

/* 

 */

 require_once 'funciones_coordenadas.php';
 $db = new funciones_coordenadas(); 
 
 if(isset($_GET['email'])){
     
    $email = $_GET['email'];
    $datos = $db->getCoordenadas_amigo($email);
    echo json_encode($datos);
    
 }if(isset($_GET['id_selected']) && isset($_GET['num_sesion'])){
     
     $id_selected = $_GET['id_selected'];
     $num_sesion = $_GET['num_sesion'];     
     $datos = $db->getCoordenadas($id_selected, $num_sesion);
     echo json_encode($datos);
 }
    
 
 
 