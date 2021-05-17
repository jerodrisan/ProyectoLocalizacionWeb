<?php

/* 
 
 */
class Funciones_Contactos{    
    
    private $db;
    private $conn;
    
    function __construct() {        
        require_once __DIR__ . '/../DB_Config.php';   //Requerimiento de un archivo en un directorio superior
        require_once __DIR__ . '/../DB_Servidor_Conex.php';  
        $this->db= new DB_Servidor_Conex(DB_SERVER,DB_USER,DB_PASSWORD,DB_DATABASE);
        $this->conn = $this->db->conectar();  
    }
    
    function __destruct() {
       
    }
    
    
    public function mostrar_contactos($solicitante){
        
        
        $query = "SELECT * from contactados WHERE (solicitante = '".$solicitante."' || solicitado ='".$solicitante."') && aceptado='1'";
        $reg=$this->db->preparar($query);        
        $parametros =['2','null'];     
        $registro= $this->db->extraer_registros($reg, $parametros);
        
        if($registro!=false){
            for($i=0; $i<count($registro); ++$i){      
                
                if($registro[$i]['solicitante'] ==$solicitante){
                    $response['aceptados'][$i] = $registro[$i]['solicitado'];  
                }else if ($registro[$i]['solicitado']==$solicitante){
                    $response['aceptados'][$i] = $registro[$i]['solicitante'];      
                } 
               
            }
             $i=0;
            foreach ($response['aceptados'] as $key => $value) {
                $query= "SELECT name , unique_id from users WHERE email='".$value."'";
                $reg=$this->db->preparar($query);
                $parametros=['2','null'];
                $result = $this->db->extraer_registros($reg, $parametros);
                if($result!=false){
                  $response['nombres'][]=$result[$i]['name'];  
                  $response['id'][]=$result[$i]['unique_id'];
                  $response['email'][]=$value;
                  
                }
            }
            $response['error']=FALSE;
            $response['mensaje']='Contactos obtenidos con exito';
        }else{
            $response['error']=TRUE;
            $response['mensaje']='Todavia no hay contactos en su lista';          
        }
        return $response;                      
        
    }
    
    
    public function insertar_contactos($solicitante, $solicitado, $texto){
        
        $pendiente =1;
        $aceptado=1;
    
        //Solicitante se sabe que esta en la base de datos, pero hay que comprobar que el solicitado se encuentar dado de alta en el sistema:
        $query="SELECT id FROM users WHERE email ='".$solicitado."'";
        $reg=$this->db->preparar($query);
        $num_filas=$this->db->num_rows($reg);
        if($num_filas>0){
            
            $query0="SELECT * FROM contactados WHERE solicitante ='".$solicitante."' && solicitado = '".$solicitado."'";
            $query1="SELECT * FROM contactados WHERE solicitante ='".$solicitado."' && solicitado = '".$solicitante."'";
            $reg0=$this->db->preparar($query0);
            $reg1=$this->db->preparar($query1);
           
            $parametros0=['pendiente','pendiente','aceptado','aceptado','solicitado','solicitado','solicitante','solicitante'];
            $result0=$this->db->extraer_registros($reg0, $parametros0);
            
            $parametros1=['pendiente','pendiente','aceptado','aceptado','solicitado','solicitado','solicitante','solicitante'];
            $result1=$this->db->extraer_registros($reg1, $parametros1);
            
             //En caso de que haya registros no podemos insertar la solicitud y vemos el motivo:
             //Caso A:
            if($result0!=false){
                 if($result0['pendiente'][0]==1 && $result0['aceptado'][0]==0){
                     
                       $response['error']=TRUE;
                       $response['mensaje'] = 'Usted ya envio una solicitud pendiente de aceptar por '.$result0['solicitado'][0];   
                       
                 }else if($result0['pendiente'][0]==0 && $result0['aceptado'][0]==1){
                     
                      $response['error']=TRUE;
                      $response['mensaje']='Usted ya ha aceptado a '.$result0['solicitado'][0].'. Mire su lista de contactos o actualicela';                     
                 }
               
                 
             //CASO B:   Cuando el solicitado es el que envio la solicitud a solicitante:
            }else if($result1!=false){                
                
                 if($result1['pendiente'][0]==1 && $result1['aceptado'][0]==0){
                       $response['error']=TRUE;
                       $response['mensaje']='Usted tiene una solicitud pendiente por parte de '.$result1['solicitante'][0].'. Mire su lista para aceptarla o rechazarla';                     
                       
                 }else if($result1['pendiente'][0]==0 && $result1['aceptado'][0]==1){
                       
                       $response['error']=TRUE;                       
                       $response['mensaje']='Usted ya ha sido aceptado por '.$result1['solicitante'][0].'. Mire su lista de contactos o actualicela';
                       
                   }
                   
              //En este caso ya podemos agregar el contacto       
            }else{
                 $pendiente=1;
                 $aceptado=0;
                 $query="INSERT INTO contactados (solicitante, solicitado, texto_solicitante, pendiente, aceptado) values "
                            . "('$solicitante','$solicitado','$texto','$pendiente','$aceptado')";
                 
                  $reg=$this->db->preparar($query);
                  if($reg){
                        $response['error']=FALSE;
                        $response['mensaje']='La solicitud a '.$solicitado.' se envio con exito';            
                    }else{
                        $response['error']=TRUE;
                        $response['mensaje']='Error en la solicitud';
                    }                
            }
                       
        }else{
             //el solicitado a quien se envia la solicitud no se encuentra dado de alta en el sistema
            $response['error']=TRUE;
            $response['mensaje']='EL usuario '.$solicitado.' no se cuentra dado de alta en el sistema';            
        }
        return $response;
    }
    
    
    
    
    
    public function borrar_contactos($contactos, $solicitante){
        
        //Recorremos el array y borramos en cada pasada:
        foreach ($contactos as $key => $value) {   
             
            //Seleccionamos tanto el caso de que tu hayas aceptado al contacto como que haya sido el contacto el que te haya aceptado a ti
            $query0= "SELECT * FROM contactados WHERE solicitante ='".$solicitante."' && solicitado = '".$contactos[$key]."' && aceptado = '1'";
            $query1= "SELECT * FROM contactados WHERE solicitado ='".$solicitante."' && solicitante = '".$contactos[$key]."' && aceptado = '1'";
            $reg0= $this->db->preparar($query0);
            $reg1= $this->db->preparar($query1);
            
            $parametros0=['2','null'];
            $result0 = $this->db->extraer_registros($reg0, $parametros0);
            $parametros1=['2','null'];
            $result1 = $this->db->extraer_registros($reg1, $parametros1);
            
            if($result0!=false){
                 //Entonces hay registro que borramos
                $query="DELETE FROM contactados WHERE solicitante ='".$solicitante."' && solicitado = '".$contactos[$key]."' && aceptado = '1'";
                $reg=$this->db->preparar($query);
                if($reg){
                    $response['mensaje'][]='Borrado contacto '.$value;
                    $response['error'][]=FALSE;
        
                }else{
                    $response['mensaje'][]='No se pudo borrar el contacto '.$value;
                    $response['error'][]=TRUE;
                }                
            }else if($result1!=false ){
                
                $query="DELETE FROM contactados WHERE solicitado ='".$solicitante."' && solicitante = '".$contactos[$key]."' && aceptado = '1'";
                $reg=$this->db->preparar($query);
                if($reg){
                    $response['mensaje'][]='Borrado contacto '.$value;
                    $response['error'][]=FALSE;
        
                }else{
                    $response['mensaje'][]='No se pudo borrar el contacto '.$value;
                    $response['error'][]=TRUE;
                }                 
            }else{
                $response['mensaje'][]='no hay contactos que borrar';
                $response['error'][]=TRUE;
            }             
         }
          return $response;
        
    }
    
    
    
    public function buscar_solicitudes($email_solicitado){
        //En esta parte retornamos un array con las solicitudes pendientes
       
         //Hacemos una busqueda de las solicitudes que ha recibido el usuario. En este caso eres tu el solicitado que tiene que aceptar o rechazar:     
        $query = "SELECT * from contactados WHERE solicitado = '".$email_solicitado."'  && pendiente='1'";
        $reg=$this->db->preparar($query);
        $parametros=['solicitudes','solicitante','textos','texto_solicitante'];
        $response=$this->db->extraer_registros($reg, $parametros);
        if($response!=false){            
            $numSolicitudes=$this->db->num_rows($reg);            
            $response['error']=FALSE;
            $response['mensaje']='Se han obtenido '.$numSolicitudes.' solicitudes';            
        }else{
            $response['error']=TRUE;
            $response['mensaje']='No hay  ninguna solicitudd';
        }
        return $response;        
        
    }
    
    
    
    
    
    public function aceptar_o_rechazar_solic($aceptar_o_rechazar, $solicitado, $array_contactos){
        
        //En esta parte recibimos el email del solicitado y un array con los contactos seleccionados a ser ACEPTADOS  O RECHAZADOS
        //EN CASO DE ACEPTAR:
        if($aceptar_o_rechazar==1){          
            //Seleccionamos los contactos de la base datos y cambiamos su estado de pendiente a aceptado:                
            foreach ($array_contactos as $key => $value) {
                
                $query= "SELECT * FROM contactados WHERE"
                        . " solicitado ='".$solicitado."' && solicitante ='".$array_contactos[$key]."' && pendiente ='1' ";
                $reg=$this->db->preparar($query);
                $num_reg=$this->db->num_rows($reg);
                if($num_reg>0){
                    //En caso de que haya alguna solicitud o mas se cambia la el estado pendiete de 1 a 0 y aceptado de 0 a 1
                    
                    $query = "UPDATE contactados SET"
                                . " pendiente= '0', aceptado='1' WHERE"
                                . " solicitado='".$solicitado."' && solicitante = '".$array_contactos[$key]."' && pendiente='1' && aceptado='0' ";
                    $reg=$this->db->preparar($query);
                    if($reg){
                        $response['error'][]=FALSE;
                        $response['mensaje'][]='El contacto '.$value.' ha sido aceptado';
                    }else{
                         $response['error'][]=TRUE;
                         $response['mensaje'][]='El contacto '.$value.' no se puede aceptar';
                    }                    
                }else{
                    $response['error']=TRUE;
                    $response['mensaje']='No hay contactos para aceptar por error interno';
                }
            }
            return $response;            
            
        //En caso pulsar el boton de rechazar las solicitudes , lass borramos de la base de datos.  
        }else if($aceptar_o_rechazar==2){                
         //Recorremos el array y borramos en cada pasada:
            foreach ($array_contactos as $key => $value) {            
                
                $query="SELECT * FROM contactados WHERE solicitado ='".$solicitado."' && solicitante = '".$array_contactos[$key]."' && pendiente = '1'";
                $reg=$this->db->preparar($query);
                $num_reg=$this->db->num_rows($reg);
                if($num_reg>0){
                    
                    $query="DELETE FROM contactados WHERE solicitado ='".$solicitado."' && solicitante = '".$array_contactos[$key]."' && pendiente = '1'";
                    $reg=$this->db->preparar($query);
                    if($reg){
                        $response['mensaje'][]='Se rechazó el contacto '.$value;
                        $response['error'][]=FALSE;                        
                    }else{
                         $response['mensaje'][]='No se pudo rechazar el contacto '.$value;
                         $response['error'][]=TRUE;
                    }
                }else{
                    $response['mensaje'][]='no hay contactos que borrar';
                    $response['error'][]=TRUE;
                }
            }
            return $response;   
        }      
    }


    public function insertToken ($email, $token){
        
        $query = "UPDATE users SET "."Token = '$token' WHERE "."email = '$email'";
        $reg=$this->db->preparar($query);
        if($reg){
            $response['error']=FALSE;
            $response['mensaje']='Insercion de token ok';
        }else{
            $response['error']=TRUE;
            $response['mensaje']='Error en insercion token';
        }
        return $response;
    }



    public function send_notification($token, $message){

        define( 'API_ACCESS_KEY', 'AAAABLm_RV4:APA91bFTGDpJ5NcJpqOoOU0sZGOUsqyytWBh3dDhV4EOl7n6ZjffTJrqrFj_99NWHF9PHgDZkWzv9qp2VPWDL0vu8ry-YuE2ZYii4C_EJnU6Ic9l-AKAbNy1KRtRgkqYzkA8AKoxv10j' );
        //define ('API_ACCESS_KEY', 'AIzaSyDCJo6XThRE3UUMPErlxHKHonIEYV29Z4U');
        $url = 'https://fcm.googleapis.com/fcm/send';
       // $url = 'https://android.googleapis.com/gcm/send';
        //$url ='https://fcm.googleapis.com/v1/projects/pr-localizacion-1-a4fde/messages:send';
        $fields = array(
            'registration_id' => $token,
            'data' => $message
        );
        $headers = array(
			'Authorization:key ='.API_ACCESS_KEY, 
			'Content-Type: application/json'
		);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);  
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);           
        if ($result === FALSE) {
           die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);

        return $result;
    }
      
}
