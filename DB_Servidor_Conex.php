<?php


class DB_Servidor_Conex {
    
    private $servidor;
    private $password;
    private $usuario;
    private $base_datos;
    private $conexion;
    private $consulta;
    
    function __construct($servidor, $usuario, $password, $base_datos) {
        $this -> servidor = $servidor;
        $this -> password = $password;
        $this ->usuario = $usuario;
        $this -> base_datos =$base_datos;        
    }
    
    public function conectar(){ 
        /* Sentencia convencional:  
        $this->conexion = mysql_connect($this->servidor, $this->usuario, $this->password)or  die("Problemas en la conexion");
        mysql_select_db($this->base_datos, $this->conexion) or  die("Problemas en la selecci贸n de la base de datos"); 
        */
       //Sentencia mysqli: 
        $this->conexion = mysqli_connect($this->servidor , $this->usuario, $this->password, $this->base_datos);
      
    }
    
    public function preparar($query){
        /* Sentencia convencional:
        //$this->consulta = mysql_query($query,$this->conexion) or die("Problemas en el select:".mysql_error());
        */
        //Sentencia mysqli:
        $this->consulta = mysqli_query($this->conexion, $query);     
        return $this->consulta;
        
    }
    
    //Funcion para extraer un solo registro:
    public function extraer_registro($resultado){       
        /*
         // Sentencia convencional:
        while($reg = mysql_fetch_array($resultado)){
            $array["datos_usuario"] = $reg;
            }       
            if(isset($array)){
                 return $array;    
            }else{
                return false;
            }
        */        
        //Sentencia mysqli:
         while($reg = mysqli_fetch_array($resultado,MYSQLI_ASSOC)){      
         //while($reg = mysqli_fetch_array($resultado)){      
            $array["datos_usuario"] = $reg;
            }       
            if(isset($array)){
                 return $array;    
            }else{
                return false;
            }               
    }
    
    
    /*
    //Funcion para extraer varios registros:    
    //Esto es para extraer los registros seleccionados y meterlos en DOS ARRAYS NOMINALES donde se estraen dos campos determinados
    public function extraer_registros3($resultado, $param1, $param11, $param2,$param22){       
                
         while($reg = mysqli_fetch_array($resultado,MYSQLI_ASSOC)){
             
             if($param1 =='0' && $param2 =='0'){
                $array[] = $reg[".$param11"];
                $array[] = $reg[".$param22"];                
             }else{             
                $array["".$param1.""][] = $reg["".$param11.""];
                $array["".$param2.""][] = $reg["".$param22.""];                
            }            
         }
         if(isset($array)){
                 return $array;    
            }else{
                return false;
            }              
    }
    */
    
     //Funcion para extraer varios registros:    
    //Esto es para extraer los registros seleccionados y meterlos en DOS ARRAYS NOMINALES donde se estraen dos campos determinados    
    public function extraer_registros($resultado, $parametros){
         
         while($reg = mysqli_fetch_array($resultado,MYSQLI_ASSOC)){
             
                 if($parametros[0]=='0'){
                     for($i=0; $i<(count($parametros)-1); ++$i){    //Tener en cuenta que el primer parametro es un '0' con lo cual restamos 1
                          $array[] = $reg["".$parametros[$i+1].""];           
                     }                               
                 }else if($parametros[0]=='1'){
                     for($i=0; $i<(count($parametros)-1); ++$i){    //Tener en cuenta que el primer parametro es un '1' con lo cual restamos 1
                          $array["".$parametros[$i+1].""][] = $reg;           
                     }                     
                 }else if($parametros[0]=='2'){
                         $array[] = $reg;     
						
                     
                 }else {                     
                      for($i=0; $i<count($parametros); $i=$i+2){
                           $array["".$parametros[$i].""][] = $reg["".$parametros[$i+1].""];
                      }                      
                 }
         }           
         
         if(isset($array)){
                 return $array;    
            }else{
                return false;
            }         
        
    }
    
    
    
    //FUNCION PARA EXTRAER VARIOS REGISTROS y meterlos en UN SOLO ARRAY QUE SEA NO NOMINAL. Para meter en varios arrays NOMIALES ver la funcion anterior
    //Fijarse que a diferencia de la funcion para extraer un solo registro, aqui ponemos un doble array en  $array["datos_usuario"][] = $reg;
   // En esta funcion se extran todos los campos de cada registro
    public function extraer_registros2($resultado){       
        /*
         // Sentencia convencional:
        while($reg = mysql_fetch_array($resultado)){
            $array["datos_usuario"][] = $reg;
            }       
            if(isset($array)){
                 return $array;    
            }else{
                return false;
            }
        */        
        //Sentencia mysqli:
         while($reg = mysqli_fetch_array($resultado,MYSQLI_ASSOC)){      
         //while($reg = mysqli_fetch_array($resultado)){      
            $array["datos_usuario"][] = $reg;
            }       
            if(isset($array)){
                 return $array;    
            }else{
                return false;
            }               
    }
    
    
    
    //SENTENCIA mysqli PARA SELECCIONAR EL NUMERO DE FILAS : 
    
    public function num_rows($resultado){   
        //Sentencia mysql
      // return  mysql_num_rows($reg2); 
         //Sentencia mysqli:     
        return mysqli_num_rows($resultado);
    }
    
    
    
    
    
}