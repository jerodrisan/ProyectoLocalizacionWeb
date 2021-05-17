<?php

/* 
 COMO RECIBIR LOS PARAMETROS DE MANERA GLOBAL: 
 * http://stackoverflow.com/questions/8469767/get-url-query-string
 */
require_once 'funciones_coordenadas.php';
$db=new funciones_coordenadas();
$response=[];

//En caso de recoger los parametros por GET: 
//parse_str($_SERVER['QUERY_STRING']);

//En caso de recoger parametros por POST:
/*
$param = $_POST['param'];
$latitud = $_POST['latitud'];
$longitud = $_POST['longitud'];
$altitud = $_POST['altitud'];
$id = $_POST['id'];
$sesion_num = $_POST['sesion_num'];
$date = $_POST['date'];

$id_cliente = $_POST['id_cliente'];
$numeroSesion = $_POST['numeroSesion'];
$correo = $_POST['correo'];  
*/

/* En caso de recoger todas las variables POST que entran, usamos este sistema de variables:*/
$i=0;
foreach ($_POST as $key => $value){     
    $variable[$i]=$key;         //asignamos la clave dentro de un array
    ${$variable[$i]} = $value;  //creamos dinamicamente la variable con su valor
    ++$i;     
} 


//switch($param){
switch(${$variable[0]}){
    
     case 'subir_coordenadas':
		$response=$db->subir_coordenadas(${$variable[1]},${$variable[2]},${$variable[3]},${$variable[4]},${$variable[5]},${$variable[6]});
        //$response=$db->subir_coordenadas($latitud, $longitud, $altitud, $id, $sesion_num, $date);
        // $response['valores']=$latitud; // antes de usar el nuevo sistema de post 
        echo json_encode($response);
        break;
    case 'getCoordenadas':
		$respuesta= $db->getCoordenadas(${$variable[1]},${$variable[2]});
        //$respuesta= $db->getCoordenadas($id_cliente, $numeroSesion);
        echo json_encode($respuesta);  
        break;
    case 'getCoordenadas_amigo':
		$response = $db->getCoordenadas_amigo(${$variable[1]});
        //$response = $db->getCoordenadas_amigo($correo);
        echo json_encode($response);  
        break;
    
    default:
        $response["error"] = TRUE;
        $response["error_msg"] = "Parametros invalidos de entrada en param_entrada_contact.php!";
        echo json_encode($response);    
}



