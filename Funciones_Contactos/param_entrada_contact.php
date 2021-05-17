<?php
/* 
 COMO RECIBIR LOS PARAMETROS DE MANERA GLOBAL: 
 * http://stackoverflow.com/questions/8469767/get-url-query-string
 */
require_once 'Funciones_Contactos.php';
$db=new Funciones_Contactos();
$response=[];
//En caso de usar GET, recogeremos las variables con este comando: 
//parse_str($_SERVER['QUERY_STRING']);

//Usamos POST y no nos complicaremos , captaremos todas las variables posibles asi: 
/*
$param = $_POST['param'];
$solicitado = $_POST['solicitado'];
$solicitante = $_POST['solicitante'];
$array_contactos = $_POST['array_contactos'];
$aceptar_o_rechazar = $_POST['aceptar_o_rechazar'];
$texto =  $_POST['texto'];
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
    case 'borrar_contactos':
		$response= $db->borrar_contactos(${$variable[1]}, ${$variable[2]});
        //$response = $db->borrar_contactos($solicitante, $array_contactos, );
        echo json_encode($response);
        break;
    case 'buscar_solicitudes':
		$response=$db->buscar_solicitudes(${$variable[1]});
        //$response=$db->buscar_solicitudes($solicitado);
        echo json_encode($response);
        break;
    case 'aceptar_o_rechazar_solic':
		$response=$db->aceptar_o_rechazar_solic(${$variable[1]}, ${$variable[2]}, ${$variable[3]});
        //$response=$db->aceptar_o_rechazar_solic($aceptar_o_rechazar, $solicitado, $array_contactos);
        echo json_encode($response);
        break;
        
    case 'insertar_contactos';
		$response=$db->insertar_contactos(${$variable[1]}, ${$variable[2]}, ${$variable[3]});
        //$response=$db->insertar_contactos($solicitante, $solicitado, $texto);
        echo json_encode($response);
        break;
    
    case 'mostrar_contactos':
		$response= $db->mostrar_contactos(${$variable[1]});
        //$response =$db->mostrar_contactos($solicitante);
        echo json_encode($response);
        break;
    case 'insertToken':
        $response = $db->insertToken(${$variable[1]}, ${$variable[2]} );        
        echo json_encode($response);    
        break;
    case 'send_notif':
        $response =$db->send_notification(${$variable[1]}, ${$variable[2]});
        echo json_encode($response);
        break;
    
    default:
        $response["error"] = TRUE;
        $response["error_msg"] = "Parametros invalidos de entrada en param_entrada_contact.php!";       

        echo json_encode($response);
        
}
/*
if($param=='borrar_contactos'){
    
    $response = $db->borrar_contactos($array_contactos, $solicitante);
    echo json_encode($response);
    
}else if($param=='buscar_solicitudes'){
    
    $response=$db->buscar_solicitudes($email_solicitado);
    echo json_encode($response);
    
}else if($param=='aceptar_o_rechazar_solic'){
    
    $response=$db->aceptar_o_rechazar_solic($solicitado, $array_contactos, $aceptar_o_rechazar);
     echo json_encode($response);    
}
*/

