<?php
/* 
 
 */
class funciones_sesiones{    
    
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
    
    //BORRADO DE SESIONES
    public function borrarSesiones($cliente_id, $num_sesion){    
        
        //OJO 
        for($i=0; $i<count($num_sesion); ++$i){     
            
            $query = "SELECT * FROM sesiones WHERE unique_id='".$cliente_id."' && session_num='".$num_sesion[$i]."'";
            $reg = $this->db->preparar($query);
            
            $registro= $this->db->extraer_registros2($reg);
            
            if($registro!=FALSE){
                //Borramos los registros con el numero de sesion que viene determinado por el array 
                $query= "DELETE FROM sesiones WHERE unique_id='".$cliente_id."' && session_num='".$num_sesion[$i]."'";
                $reg= $this->db->preparar($query);
                if($reg){  //Si se borra la sesion correspondiente, borramos las coordenadas correspondientes
                    
                     //Ahora seleccionamos las coordenadas correspondientes al numero de sesion correspnidente:
                     $query="SELECT * FROM coordenadas WHERE unique_id='".$cliente_id."' && sesion_num='".$num_sesion[$i]."'";
                     $reg=$this->db->preparar($query);
                     $registro= $this->db->extraer_registros2($reg);
                     if($registro!=FALSE){
                         
                         $query="DELETE FROM coordenadas WHERE unique_id='".$cliente_id."' && sesion_num='".$num_sesion[$i]."'";
                         $reg= $this->db->preparar($query);
                         
                         if($reg){                             
                             //Borrado de coordenadas  exitoso
                                $response['exito']=1;
                                $response['mensaje']='Borrados las coordenadas y las sesiones';
                                $response['error']=FALSE;
                               // $response['sesion borrada'][]='Se borraron las coordenadas de la sesion: '.$registro['sesion_num'];  
                             
                         }else{ //No se han podido borrar las coordenadas                             
                                $response['exito']=0;
                                $response['mensaje']='No se borraron las coordenadas';
                                $response['error']=TRUE;
                                $response['sesion borrada'][]='No se ha podido borrar las coordenadas de la sesion: '.$registro['sesion_num'];                             
                         }                         
                     }else{//No hay coordenadas que borrar
                           $response['exito']=0;
                           $response['mensaje']='No hay coordenadas que borrar';
                           $response['error']=TRUE;
                           $response['sesion borrada'][]='No hay coordenadas en la sesion :'.$num_sesion[$i];                          
                     }                    
                }else{//No se ha podido borrar la sesion ni por tanto los registros de corrdenadas correspondientes
                    $response['exito']=0;
                    $response['mensaje']='No se borraron las seiones';
                    $response['error']=TRUE;
                    $response['sesion borrada'][]= 'No se ha podido borrar la sesion :'.$sesion_num[$i];
                }
            }else{  //No hay registros que cumplan la condicion que tengan esos numeros de sesion 
                
              $response['exito']=0;
              $response['mensaje']='No hay sesiones que borrar';
              $response['error']=TRUE;
              $response['sesion borrada'][]= 'La sesion '.$num_sesion[$i].' no existe.'; 
            }
        
        } //Fin bucle for      
        return $response;                   
        
    }
    
    
     //FUNCION get_numsesiones.php:
     public function getNumeroSesiones($cliente_id, $deleteLive){         
         
        //Antes de obtener todas las sesiones para cargarlas en el spinner de rutas, hay que hacer una limpia de las posibles sesiones que esten 
        //puestas en live=1 por un cerrado accidental de la aplicacion u otro motivo. una vez hecha la limpieza, se procede a obtener las sesiones
        // que tengan live =0.
        //Esto lo haremos solo al arrancar el programa. Cuando pulsamos el boton de empezar la localizacion, tambien obtenemos rutas, pero en este caso 
        //no podemos establecer la sesion live a 0 , entonces solo obtendremos las sesiones (rutas)
        //si el valor de $deleteLive es 1 -> usamos la funcion deleteSesionLive1, en caso de que sea cero , no la usamos
     
        $liv=1;     
        //Hacemos la limpieza en caso de arrancar el programa:    
        if($deleteLive ==='1'){
            $respuesta = $this->deleteSesionLive1($cliente_id, $liv);           
            //Una vez hecha , obtenemos las rutas:            
            if(isset($respuesta)){
                $respuesta2 = $this ->getnumSes($cliente_id);              
                return $respuesta2;        
            }            
            //En caso de no arrancar directamente el programa, obtememos los numeros de sesiones o rutas directamente.
        }else{
            $respuesta2 = $this ->getnumSes($cliente_id);
            return $respuesta2;
        }    
    //$respu = $dbfunciones->cleanSessionNum($cliente_id);   Limpieza de numero de sesion no lo usaremos por el momento
         
     }



    
    public function subirNumSesion($cliente_id, $date){        
        
        //seleccionamos todos los registros de la tabla sesiones correspondiente al id del cliente para meter una nueva sesion no repetida     
        $query = "SELECT session_num FROM sesiones WHERE unique_id='".$cliente_id."'";
        $reg = $this->db->preparar($query);
        $parametros=['0','session_num'];
        $datos2= $this->db->extraer_registros($reg, $parametros);   
         if (isset($datos2)){
            //sacamos el valor del ultimo valor del array, es decir, de la ultima sesion
            $ultimovalor = $datos2[count($datos2)-1]; 
            //subimos el valor de la sesion 
            ++$ultimovalor;         
        }else{ //Si no hay ningun dato y es la primera vez que se inserta la primera sesion
            $ultimovalor = 1;    
        }   
         //metemos el valor de la sesion , el id y tambien con el valor live=1 indicamos que el usuario esta en movimiento
        $live=1;
        $query = "INSERT INTO sesiones (unique_id, session_num, live, time_paused, creado_el) VALUES ('$cliente_id','$ultimovalor','$live',0,'$date')";
        $reg = $this->db->preparar($query);        
        if($reg){            
            //En caso de que no se haya cambiado el valor de live de 1 a 0 por algun tipo de error en la sesion anterior, lo cambiamos por si acaso
            //Aunque lo normal es que este cambiado pero puede ser que si se ha colgado la app o se ha apagado movil o algo raro pues no se ha podido cambiar
            //por lo tanto con este if nos aseguramos de hacerlo        
            $live=0;
            $penultimovalor= $ultimovalor-1;            
            $query="UPDATE sesiones set live='$live' WHERE unique_id='$cliente_id' && session_num='$penultimovalor'";
            $reg = $this->db->preparar($query);            
            if($reg){
                  $response['update']='updated conseguido';
            }else{
                  $response['update']='updated no conseguido';
            }         
            $response["exito"]=1;
            $response["mensaje"]="numero de sesion almacenado";
            $response["num_sesion"]=$ultimovalor;
            return $response;             
            
        }else{
            $response["exito"]=0;
            $response["mensaje"]="Error al insertar el numero de sesion";            
            return $response; 
        }
        
    }
    
     
    //OBTENCION DEL NUMERO DE SESION
    public function getnumSes($id){
        $response = array();  
        
        $live = 0;  // Solo obtenemos los registros en que no  haya movimiento
        $query = "SELECT * FROM sesiones WHERE unique_id='".$id."' && live='".$live."'";
        $reg = $this->db->preparar($query);
        
        //Queremos seleccionar las sesiones y las fechas de los registros con ese id y meterlas en dos array para devolver el json       
        $parametros=['sesiones','session_num','fechas','creado_el'];
        $response = $this->db->extraer_registros($reg, $parametros);
        //Otra forma de hacerlo:
          /*
        $registro= $this->db->extraer_registros2($reg);        
        for($i=0; $i<count($registro['datos_usuario']); ++$i){            
            $response['sesiones'][] = $registro['datos_usuario'][$i]['session_num']; 
            $response['fechas'][] = $registro['datos_usuario'][$i]['creado_el'];
        }
        */                
        if(isset($response['sesiones'])){
            $response['error'] = FALSE;
            $response['registros']='si';
            return $response;    
        }
        else{
            $response['error'] = FALSE;
            $response['registros']='no';
            return $response;    
        }        
    }
    
 
   
  //Borramos las sesiones que esten en live = 1 . Solo al arrancar la aplicacion!!
  public function deleteSesionLive1 ($cliente_id, $live){  
      
      $query = "SELECT session_num FROM sesiones WHERE unique_id='".$cliente_id."' && live='".$live."'";
      $reg = $this->db->preparar($query);
      $num_filas = $this->db->num_rows($reg);
      if($num_filas>0){
            //obtenemos el array con el numero de sesiones con live = 1 
            $parametros = ['0','session_num'];
            $num_sesion= $this->db->extraer_registros($reg, $parametros);   
            //En cada numero de sesion = 1:
            for($i=0; $i<count($num_sesion); ++$i){                  
                
                $query= "SELECT * FROM coordenadas WHERE unique_id='".$cliente_id."' && sesion_num='".$num_sesion[$i]."'";
                $reg = $this->db->preparar($query);
                $num_filas = $this->db->num_rows($reg);
                 //En caso de que haya mas de 1 registro, cambiamos el numero parametro live de 1 a 0 y no borramos ningun registro de coordenadas
                if($num_filas>1){
                     $live_cero=0;
                     $query = "UPDATE sesiones SET live='$live_cero' WHERE unique_id='$cliente_id' && session_num='$num_sesion[$i]'";
                     $reg = $this->db->preparar($query);                     
                                           
                     if($reg){
                        $response['tipo_borrado']='live a cero';
                        $response["cambiado"][$i]=1;
                        $response["mensaje"][$i]='El valor de live ha sido cambiado a 0 en la sesion:'.$num_sesion[$i];     
                        //return $response;
                    }else{
                        $response['tipo_borrado']='no live a cero';
                        $response["cambiado"][$i]=0;
                        $response["mensaje"]='No se ha podido cambiar el valor de live en la sesion: '.$num_sesion[$i];   
                        //return $response;
                    }   
                     
                  ///En caso de que solo haya un registro, borramos tanto el registro de coordenadas como el numero de sesion    
                 }else if($num_filas===1){
                     $query="DELETE FROM sesiones WHERE unique_id='".$cliente_id."' && session_num='".$num_sesion[$i]."'";
                     $reg = $this->db->preparar($query);
                     $query2= "DELETE FROM coordenadas WHERE unique_id='".$cliente_id."' && sesion_num='".$num_sesion[$i]."'";
                     $reg2 = $this->db->preparar($query2);
                     if($reg && $reg2){
                        $response['tipo_borrado']='1 coordenada 1 sesion';
                        $response['sesion'][$i]= $num_sesion[$i];
                        $response["borrado"][$i]=1;
                        $response['mensaje']='se ha borrado el registro de coordenadas y la sesion correspondiente a '.$num_sesion[$i];
                       // return $response;                
                    }else{
                        $response['tipo_borrado']='no borrado';
                        $response["borrado"][$i]=0;
                        $response['mensaje']='no se ha podido borrar el registro ni la sesion correspondiente a'.$num_sesion[$i];
                        //return $response;
                    }
                 }else{
                  //No hay registros de coordenadas , con lo cual solo borramos la sesion
                    $query= "DELETE FROM sesiones WHERE unique_id='".$cliente_id."' && session_num='".$num_sesion[$i]."'";
                    $reg = $this->db->preparar($query);
                    if($reg){
                        $response['tipo_borrado']='0 coordenada 1 sesion';
                        $response['sesion'][$i]= $num_sesion[$i];
                        $response["borrado"][$i]=1;
                        $response['mensaje']='se ha borrado la sesion '.$num_sesion[$i].' que no tenia ningun registro';
                        //return $response;                
                    }else{
                        $response['tipo_borrado']='no borrado';
                        $response["borrado"][$i]=0;
                        $response['mensaje']='no se ha podido borrar la sesion'.$num_sesion[$i];
                        //return $response;
                    }                     
                 }
                 
            }//fin bucle for
        
        }else{
           $response['tipo_borrado']='no registros';
           $response['exito']=0;
           $response['mensaje']="no hay sesiones con live =1";
          
        }
      return $response;     
   
      
  }  
  
   //Subimos Numero Live poniendolo a Cero y tambien borramos las coordenadas relativas a la ruta y luego borrar la ruta
  public function subir_numeroliveacero_y_deleteruta($id,$ses_num,$time_paused, $deleteruta){      
     
      //Lo que haremos aqui es actualizar el registro cuyo valor de live sea 1 , cambiarlo a 0:
      $live=0;   
      $query = "UPDATE sesiones set live='$live', time_paused='$time_paused' WHERE unique_id='$id' && session_num='$ses_num'";
      $reg = $this->db->preparar($query);     
      if($deleteruta==='si'){
          if($reg){
           $query = "DELETE FROM coordenadas WHERE unique_id='".$id."' && sesion_num='".$ses_num."'";
           $reg = $this->db->preparar($query);
           
            if($reg){
                $response["exito"]=1;
                $response["mensaje"]='El valor de live ha sido cambiado a 0';
                return $response;           
            }else{
                $response["exito"]=0;
                $response["mensaje"]='El valor de live NO ha sido cambiado a 0';
                return $response;           
            }           
        }          
      }else if($deleteruta==='no'){
          $response["exito"]=1;
          $response["mensaje"]='no se borro ninguna ruta en el servidor';
          return $response;
      }           
  }
  
  
  
  //funcion para limpiar numero de sesion. Se hace al principio y al final. Ver get_numsesiones.php.
  // EN principio no lo usaremos porque lo normal es subir el numero de sesion al arrancar
  public function cleanSessionNum ($id){
      
       $live = 0;
       $cliente_id=$id;
       $array1 = [];
       $array2 = [];
       $response =[];
       
       //http://stackoverflow.com/questions/4073923/select-last-row-in-mysql      
       //Seleccionamos el ultimo registro de la tabla y vemos cual es su sesion num
       $reg = mysql_query("SELECT session_num FROM sesiones WHERE unique_id='".$cliente_id."' && live='".$live."'");
       //$reg = mysql_query("SELECT * FROM sesiones ORDER BY id DESC LIMIT 1");
       while($respm = mysql_fetch_array($reg)){
           $ses_num[]=$respm['session_num'];
       }
       //ordenamos el array de menos a mas
       asort($ses_num);
       foreach ($ses_num as $key => $value) {
           $array1[]=$value;
       }       
       $lastSesion=  $array1[count($array1)-1] ;  //Sacamos el ultimo valor del numero de sesion del usuario.   
       $lastSesion++; 
       //Hay veces que no se sube el numero de sesion en la tabla de sesiones  pero sí las coordenadas en la tabla de coordenadas, 
       //entonces hay que pillar la primera coordenada del grupo , pillar la hora y subir luego el numero de sesion a la tabla de sesiones:
      
       $reg2 = mysql_query("SELECT * FROM coordenadas WHERE unique_id='".$cliente_id."' && sesion_num='".$lastSesion."'");     
       //en caso de que haya registros entonces no se ha subido el numero de sesion y hay que subirlo:
       if($reg2){
           
           $numColum = mysql_num_rows($reg2);
           //solo guardaremos si hay mas de 1 registro:
           if($numColum>1){               
                 while($respo = mysql_fetch_array($reg2)){
                        $coorde['fechas'][]= $respo['date'];
                        $coorde['ses_num'][] = $respo['sesion_num'];               
                }           
            //obtenemos el primer registro del vector y pillamos la fecha . Este array si esta ordenado ya que las coordenadas se suben secuencialmente
            $firstSesion2 = $coorde['ses_num'][0];
            $firstDate = $coorde['fechas'][0];            
             
            //a continuacion insertamos el valor en la tabla de sesiones:           
            $registrar = mysql_query("INSERT INTO sesiones (unique_id, session_num, live, time_paused, creado_el) VALUES ('$cliente_id','$firstSesion2','$live',0,'$firstDate')");
            if($registrar){
                $response['code']=1;
            }else{
                $response['code']=2;
            }               
                
           }else if($numColum===1){
               //En caso de que haya un solo registro , los eliminamos:
               $elim = mysql_query("DELETE FROM coordenadas WHERE unique_id='".$cliente_id."' && sesion_num='".$lastSesion."'");
               if($elim){
                   $response['code']=3;                   
               }else{
                   $response['code']=4;
               }               
           }else{
                //En este caso esta todo bien. No hay registros que se hayan subido con un numero de sesion que no exista
                $response['code']=0;
           }         
       }
      return $response;      
  } 
    
}
