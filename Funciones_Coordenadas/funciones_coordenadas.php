<?php

/* 
 
 */
class funciones_coordenadas{    
    
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
    
    public function subir_coordenadas($latitud,$longitud,$altitud, $id, $sesion_num, $fecha){        
    
        //Pasamos las fechas de formato String a date:        
        for($i=0; $i<count($fecha); ++$i){
             $date[$i] =  date("Y-m-d H:i:s",strtotime($fecha[$i]));
        }        
        //$date = date_create_from_format('Y-M-d H:i:s', $fecha);
        //$date->getTimestamp();        
        for($i=0; $i<count($latitud); ++$i){            
            
            $query = "INSERT INTO coordenadas (unique_id, sesion_num, latitud, longitud, altitud, date)"
                    . " VALUES('$id[$i]','$sesion_num[$i]','$latitud[$i]','$longitud[$i]','$altitud[$i]','$date[$i]')";
             $reg= $this->db->preparar($query);            
        }        
        if($reg){         
            $response["exito"]=TRUE;
            $response["mensaje"] = "coordenadas almacenadas";      
            return $response;
        }else{
            $response["exito"]=FALSE;
            $response["mensaje"] = "imposible almacenar coordenadas";  
            $response["latitud"] =  $latitud ;
            $response["longitud"] =  $longitud ;
            $response["altitud"] =  $altitud ;         
         
            return $response;
        }         
    }
    
    
    
    public function getCoordenadas($id, $num_sesion){         
        
        $query = "SELECT time_paused FROM sesiones WHERE unique_id='".$id."' AND session_num='".$num_sesion."'";
        $reg= $this->db->preparar($query);
        $parametros=['0','time_paused'];
        $time_paused=$this->db->extraer_registros($reg, $parametros);  //OJO SACAR EL PRIMER ELEMENTO DEL ARRAY        
        if($time_paused!=false){
            $time_paused= $time_paused[0]; //tiempo pausado
        }      
        
        //sacamos los datos de posiciones
        $query="SELECT distinct latitud, longitud, altitud, date FROM coordenadas"
                                        . " WHERE unique_id='".$id."' AND sesion_num='".$num_sesion."'";
         //Con distinct evitamos repeticion del mismo registro que se haya subido mas de una vez a la base de datos   
        
         $reg= $this->db->preparar($query);
         $parametros=['2','null'];
         $vec0=$this->db->extraer_registros($reg, $parametros);
         if($vec0!=false){
             
            function ordenar( $a, $b ) {
                if($a['date'] == $b['date']) return 0; //si son iguales regresa 0
                if($a['date'] < $b['date']) return -1; //si $a es menor regresa -1
                return 1; //si $a es mayor regresa 1
                }
             //Ordenamosm todos los datos recibidos de menor a mas fechas por si hay alguno dato mal ordenado:
            usort($vec0, 'ordenar');    
            $vec['registros']=$vec0;
            
            for ($i=0; $i<count($vec0); ++$i){
                //sacamos las distancias entre los puntos consecutivos del array ordenado    
                if($i<count($vec0)-1){
                    $array_dist[]= $this->distance($vec0[$i]['latitud'], $vec0[$i]['longitud'], $vec0[$i+1]['latitud'], $vec0[$i+1]['longitud'] );                     
                }
                //pasamos la hora de cada punto a segundos. 
                $fecha=$vec0[$i]['date'];
                //Sacamos la hora en formato de hora y almacenamos en array la diferencia de tiempo entre dos puntos para luego calcular la velocidad
                //http://stackoverflow.com/questions/9970584/getting-time-and-date-from-timestamp-with-php
                //Solo nos interesa la diferencia en segundos entre un punto y otro
                $fechayhora = explode(" ",$fecha);
                $hora = $fechayhora[1]; // sacamos la hora en formato (01:25:15)
                $arrayhora = explode(":",$hora); //Separamos hora de minu y de seg
                $array_segundos[] = $this->horaToSeg($arrayhora[0], $arrayhora[1],$arrayhora[2]); //calculamos la diferencia en segundos ente dos puntos
                
                //sacamos la diferencia en segundos de cada punto:            
                if($i>0){
                     if($array_segundos[$i]>$array_segundos[$i-1]){
                         {$array_diftime[]= $array_segundos[$i]-$array_segundos[$i-1];}
                    }else{ //En caso de que lleguemos a las 00:00 la diferencia entre dos puntos es negativa
                        $array_diftime[] =$array_segundos[$i] + (86400 - $array_segundos[$i-1]); // Ejemplo: 00:00:07 - 23:59:57 --> 7 - 86397 -> 10 seg de dif 
                    }                    
                    //sacamos la diferencia de altitud entre dos puntos consecutivos:
                    //$array_diff_altura[] = $vec0[$i]['altitud']-$vec0[$i-1]['altitud'];
                }
                
                
            }
                 //POnemos 0 en la primera posicion y subimos el resto hacia arriba con array_unshift
            array_unshift($array_diftime, 0);
            $vec['tiempos'][]=$array_diftime;
            //Hacemos lo mismo con las distancias
            array_unshift($array_dist,0 );            
            $vec['distancias'][]=$array_dist; //AÑADIMOS LAS DISTANCIAS AL ARRAY
            $vec['time_paused']= $time_paused; //tiempo pausado
            //AÑADIMOS LOS SIGUIENTES VALORES AL FINAL DEL ULTIMO REGISTRO
            $vec['error']=FALSE;
            $vec['exito']=1;       
            $vec['mensaje']='coordenadas obtenidas';
            return $vec;     
             
         }else{
             //En este caso no hay registro de coordenadas que mostrar, 
            $vec['error']=TRUE;
            $vec['exito']=0;       
            $vec['mensaje']='No hay movimientos';
            return $vec;
        }        
       
    }
    
    
    public function getCoordenadas_amigo($correo){
        
        $query = "SELECT unique_id FROM users WHERE email='".$correo."'";
        $reg= $this->db->preparar($query);
        $parametros=['0','unique_id'];
        $unique_id = $this->db->extraer_registros($reg, $parametros);
        if($unique_id!=false){  //si hay algun registro:  
            
            $unique_id=$unique_id[0];
             //Una vez tenemos el id del cliente, comprobamos que esta en movimiento, es decir, tiene el parametro live = 1 para sacar el numero de sesion:
        
            $query = "SELECT session_num FROM sesiones WHERE unique_id='".$unique_id."' && live = 1 ";
            $reg= $this->db->preparar($query);
            $parametros=['0','session_num'];
            $session_num= $this->db->extraer_registros($reg, $parametros);
            if($session_num!=false){
                $session_num=$session_num[0];
                  // echo json_encode('numero de sesion :'.$session_num.' numero de id '.$unique_id);     
                // OBTENEMOS LOS REGISTROS DE COORDENADAS RELATIVAS A LA SEESION. 
                //Pero hay que comprobar que haya algun registro de coordenadas ya que puede estar empezando la sesion y no se haya subido nada:
                $response = $this->getCoordenadas($unique_id, $session_num);            
                if($response['error']===FALSE){
                      $response['mensaje']='Tu amigo: '.$correo.' se encuentra en movimiento';
                       return $response;  
                }else{
                     $response['mensaje']='Tu amigo: '.$correo.' no esta todavia en movimiento';
                     return $response;  
                } 
            }else{
                    $response['error']=TRUE;
                    $response['mensaje']='Tu amigo: '.$correo.' no se encuentra en movimiento';              
                    return $response;
            }
        }else{
             $response['error']=TRUE;
             $response['mensaje']='No hay ninguna id con correo: '.$correo;
             return $response;
            
        }        
       
    }    
    
   
    //Creamos una funcion para calcular la distancia entre dos puntos
 //http://programacion.net/articulo/calcular_la_distancia_entre_dos_puntos_utilizando_su_latitud_y_longitud_dada_530
 //Devuelve la distancia en Km
 function distance($lat1, $lon1, $lat2, $lon2) { 
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        return ($miles * 1.609344);        
  }
 
  function horaToSeg($horas, $minutos, $segundos){
      return ($horas*3600)+($minutos*60)+$segundos;
  }
 
  //FUNCIONES ORDENAMIENTO DEL ARRAY PARA CORRECCION DE TIEMPOS Y DISTANCIAS. NO LAS USAREMOS YA QUE ESTA HECHO EN EL CODIGO DE LA FUNCION get_coordenadas
  //Ejemplo:
  // $principal = array(101, 110,120,165, 130,140,150,160, 125,155,185,235, 215, 10, 20,200,40,30,50,250, 25, 45,70 );
   // $princip = partirArrays($principal);
   // $arrayOrdenado = crearArrayOrdenado($princip);
  
   function moveElement(&$array2, $a, $b) {
            $out = array_splice($array2, $a, 1);
            array_splice($array2, $b, 0, $out);            
   }
   
    function partirArrays($principal){
            
            for($i=0; $i<count($principal)-1;$i++){      
                if(abs($principal[$i+1]-$principal[$i])>90){  //salto importante entre dos puntos     
                    list($parte1, $parte2) = array_chunk($principal, ceil($i+1));   
                    echo 'se repiteeee';
                    
                    $part1=ordenarArray($parte1);                    
                    $part2= ordenarArray($parte2);
                    
                    return array($part1,$part2);                            
                }           
            }   
            return ordenarArray($principal);   
    }
    
     function crearArrayOrdenado($princip){
            
            if(count($princip) == 2){
            
                $parte1= $princip[0];
                $parte2= $princip[1];
           
                //Ordenamos la parte segunda (en caso de que se pueda)
                $parte22 = ordenarParte2($parte2);            
                if(count($parte22)==2){
                    $parte22_1=$parte22[0];
                    $parte22_2=$parte22[1];
                    
                    $parte1 = partirArrays (array_merge($parte1,$parte22_2)); //Unimos parte1 con parte 2 de la parte 2 y lo ordenamos
                    $parte1= array_merge($parte1,$parte22_1); //Unimos la parte anterior con la primera parte de la parte 2 pero sin ordenar
                    return $parte1;
                }else{
                   //En caso de que la segunda parte sea unica, la unimos con la primera pero sin ordenarla
                    $parte1 = array_merge($parte1,$parte22);   
                    return $parte1;
                }
            }else{
                $parte1= $princip;
                return $parte1;
            }            
     }
     
     
      //Dividimos la parte2 en dos
        function ordenarParte2($array){            
             for($i=0; $i<count($array)-1;$i++){
                 if(abs($array[$i+1]-$array[$i])>90){                     
                      list($parte1, $parte2) = array_chunk($array, ceil($i+1));   
                      return array($parte1,$parte2);                     
                 }                 
             }
             return $array; //En caso de que no sea necesario partirla
        }
        
       //Ordenamos cada array:
        function ordenarArray($array){
            //$elem=array();
            for($i=0; $i<count($array)-1;$i++){                     
                    if($array[$i+1]<$array[$i]  ){                        
                        
                        for ($j=$i; $j>=0; $j--){                      
                            if($array[$i+1]> $array[$j]){                         
                                //movemos el elemento en la posicion que le corresponde                                
                                moveElement($array,$i+1, $j+1);
                                break;                          
                            }                     
                        }                        
                    }                 
            }
           return $array;
        }
    
}
