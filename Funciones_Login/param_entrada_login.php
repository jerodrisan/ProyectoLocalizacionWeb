<?php

/* 
 COMO RECIBIR LOS PARAMETROS DE MANERA GLOBAL: 
 * http://stackoverflow.com/questions/8469767/get-url-query-string
 */
require_once 'funciones_login.php';
$db=new funciones_login();
$response=[];

//Si usamos parametros GET, recogeremos todas las variables con este comando: 
//parse_str($_SERVER['QUERY_STRING']);

//Si usamos POST:
/*
$param = $_POST['param'];
$email = $_POST['email'];
$password = $_POST['password'];
$name = $_POST['name'];
$tipo_login = $_POST['tipo_login'];
*/

/* En caso de recoger todas las variables POST que entran, usamos este sistema de variables:*/
$i=0;
foreach ($_POST as $key => $value){     
    $variable[$i]=$key;         //asignamos la clave dentro de un array
    ${$variable[$i]} = $value;  //creamos dinamicamente la variable con su valor
	//echo ${$variable[$i]}.'  '.$variable[$i].'  ';
    ++$i;     
	
} 


//switch($param){    
switch(${$variable[0]}){  //En caso de usar 
    case 'login':
		$response= $db->login(${$variable[1]}, ${$variable[2]},${$variable[3]});
        //$response = $db->login($email,$password,$tipo_login );		
        echo json_encode($response);
        break;
    case 'cerrarSesion':
		$respuesta= $db->cerrarSesion(${$variable[1]}, ${$variable[2]});
        //$respuesta = $db->cerrarSesion($email, $tipo_login);
        $response["hecho"]=$respuesta;    
        echo json_encode($response);   
        break;
    case 'registro':
		$response= $db->registro(${$variable[1]}, ${$variable[2]},${$variable[3]});
        //$response = $db->registro($email, $password, $name);
        echo json_encode($response);
        break;
    
    default:        
        $response["error"] = TRUE;
        $response["error_msg"] = "Parametros invalidos de entrada en param_entrada_login.php!";      
        echo json_encode($response);    
}


