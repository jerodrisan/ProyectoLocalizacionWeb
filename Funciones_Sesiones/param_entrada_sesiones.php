<?php

/* 
 COMO RECIBIR LOS PARAMETROS DE MANERA GLOBAL: 
 * http://stackoverflow.com/questions/8469767/get-url-query-string
 */
require_once 'funciones_sesiones.php';
$db=new funciones_sesiones();
$response=[];

//En caso de recoger los parametros por GET: 
//parse_str($_SERVER['QUERY_STRING']);

//En caso de recoger parametros por POST:
/*
$param=$_POST['param'];
$id =$_POST['id'];
$array_numeroSesion = $_POST['array_numeroSesion'];
$deleteLive = $_POST['deleteLive'];
$timepaused = $_POST['timepaused'];
$date = $_POST['date'];
$numeroSesion = $_POST['numeroSesion'];
$deleteruta = $_POST['deleteruta'];
*/

/* En caso de recoger todas las variables POST que entran, usamos este sistema de variables:*/
$i=0;
foreach ($_POST as $key => $value){     
    $variable[$i]=$key;         //asignamos la clave dentro de un array
    ${$variable[$i]} = $value;  //creamos dinamicamente la variable con su valor
    ++$i;     
} 


//switch($param){
switch(${$variable[0]}){  //En caso de usar 
    
    case 'borrarSesiones':
		$response = $db->borrarSesiones(${$variable[1]},${$variable[2]});    
        //$response = $db->borrarSesiones($id, $array_numeroSesion);    
        echo json_encode($response);
        break;
    case 'getNumeroSesiones':
		$response = $db->getNumeroSesiones(${$variable[1]},${$variable[2]});
        //$response = $db->getNumeroSesiones($id, $deleteLive);
        echo json_encode($response);    
        break;
    case 'subir_numeroliveacero_y_deleteruta':	
		$response = $db->subir_numeroliveacero_y_deleteruta(${$variable[1]},${$variable[2]},${$variable[3]},${$variable[4]});
        //$response = $db->subir_numeroliveacero_y_deleteruta($id, $numeroSesion, $timepaused,$deleteruta);
        echo json_encode($response);
        break;
    
    case 'subirNumSesion':
        $response = $db->subirNumSesion(${$variable[1]},${$variable[2]});  
        //$response = $db->subirNumSesion($id,$date);       
        echo json_encode($response);
        break;
    case 'deleteSesionLive1':
		$response = $db->deleteSesionLive1(${$variable[1]}, ${$variable[2]});
        //$response = $db->deleteSesionLive1($id, $deleteLive);
        echo json_encode($response);
        break;
    
    default:
        $response["error"] = TRUE;
        $response["error_msg"] = "Parametros invalidos de entrada en param_entrada_login.php!";
        echo json_encode($response);      
    
}
