<?php

/**
 * Description of DB_Funciones
 *
 * @author chukk
 */

class funciones_login {       
    
    private $db;
    private $conn;
    
    function __construct() {            
     // require_once 'DB_Config.php'; //en el mismo directorio 
     // require_once 'DB_Servidor_Conex.php';     
     // require_once __DIR__ . '/DB_Config.php';  
    //  require_once __DIR__ . '/DB_Servidor_Conex.php';  
      require_once __DIR__ . '/../DB_Config.php';   //Requerimiento de un archivo en un directorio superior
      require_once __DIR__ . '/../DB_Servidor_Conex.php';  
      $this->db= new DB_Servidor_Conex(DB_SERVER,DB_USER,DB_PASSWORD,DB_DATABASE);      
      $this->conn = $this->db->conectar();
  }
  // destructor
    function __destruct() {         
    }    
    
    
    
    public function login ($email, $password, $tipo_login){        
      // get the user by email and password
      $user = $this->getUserByEmailAndPassword($email, $password, $tipo_login);      
     //echo json_encode($user);    
      if ($user != false) {
        
        if($user ==="logueado"){
            //Si esta logueado, lo devolvemos
            $response["error"] = TRUE;
            $response["error_msg"]="Usted esta logueado en otro dispositivo, desconectese  y vuelva a intentarlo";
            return $response;
            
        }else{           
            //En caso de que no este logueado , devolvemos los datos
            $response["error"] = FALSE;
            $response["uid"] = $user["datos_usuario"]["unique_id"];
            $response["user"]["name"] = $user["datos_usuario"]["name"];
            $response["user"]["email"] = $user["datos_usuario"]["email"];
            $response["user"]["created_at"] = $user["datos_usuario"]["created_at"];
            $response["user"]["updated_at"] = $user["datos_usuario"]["updated_at"];
           
             return $response;            
        }       
      } else {
        // user is not found with the credentials
        $response["error"] = TRUE;
        $response["error_msg"] = "Usuario o contrasena incorrectos. Intentelo de nuevo!";
         return $response;
      }        
    }
    
    public function registro ($email,$password,$name){
        
        if($this->isUserExisted($email)){
            $response["error"]=TRUE;
            $response["message"]="El usuario ya existe";
            return $response;
            
        }else{     
                
            //Almacenamos el usuario
            $user=$this->storeUser($name, $email, $password);
            if(isset($user)){
                $response["error"] = FALSE;
                $response["uid"] = $user["datos_usuario"]["unique_id"];
                $response["user"]["name"] = $user["datos_usuario"]["name"];
                $response["user"]["email"] = $user["datos_usuario"]["email"];
                $response["user"]["created_at"] = $user["datos_usuario"]["created_at"];
                $response["user"]["updated_at"] = $user["datos_usuario"]["updated_at"];
                return $response;
            }else{
                $response["error"]=TRUE;
                $response["message"]="Error desconocido en el registro";
                return $response;
            }        
        }  
        
    }
        
    
    public function storeUser ($name, $email, $password){     
        $uuid = uniqid('', true); //Encriptamos id que sera asignada a cada usuario        
        $hash = $this->hashSSHA($password); //Con la funcion hashSSHA sacamos el array hash con salt y encripted
        $encrypted_password = $hash["encrypted"];
        $salt = $hash["salt"];
        $query = "INSERT INTO users(unique_id, name, email, encrypted_password, salt, created_at) VALUES"
                . "('$uuid',' $name','$email','$encrypted_password','$salt', NOW())";
        $smtp = $this->db->preparar($query);
        if($smtp){
            $user= $this->getUserByEmail($email); 
            return $user;               
        }else{
            return false; 
        }
    }
    
    
    
    public function getUserByEmail ($email){        
         $query= "SELECT * FROM users WHERE email = '$email'";
         $smtp = $this->db->preparar($query);
         if($smtp){
            $user = $this->db->extraer_registro($smtp); //ojo que el metodo extraer_registro() devuelve los datos en el indice: $user["datos_usuario"]
            return $user;
         }else{
            return false;
        }            
    }    
    
    public function isUserExisted ($email){     
        $response=$this->getUserByEmail($email);
        if(gettype($response)=="array"){
            return true;
        }else{
            return false;
        }
    }
    
    public function getUserByEmailAndPassword ($email, $password, $tipo_login){    
         
         $query= "SELECT * FROM users WHERE email = '$email'";
         $smtp = $this->db->preparar($query);
         if($smtp){
            $user = $this->db->extraer_registro($smtp); 
            $salt=$user["datos_usuario"]["salt"];
            $encript = $user["datos_usuario"]["encrypted_password"];            
            $islogeado = $user["datos_usuario"][$tipo_login]; // $tipo_login = islogged (para movil), = isloggedweb (para web)
            
            //Si el usuario esta ya logueado en movil o en web, es posible que este logueado en otro sistema. En ese caso no se puede loguear en mas de uno.
            if($tipo_login==='islogged'){
                if($islogeado==='1'){
                    $user="logueado";
                    return $user;                
                }else{
                    //En caso de que no este logueado , lo logueamos , pero primeor desemcriptamos                  
                    $encrypted = $this->checkhashSSHA($salt, $password);
                    //Comprobamos que coincidan las contraseñas
                    if($encript===$encrypted){
                        //Si coinciden , ponemos el parametro islogged a 1 
                        //De momento solo usaremos el tipo de login en movil y no en web ya que es engorroso debido a cierre de sesion, etc                   
                        $query= "UPDATE users set "."$tipo_login"." ='1' WHERE email = '$email'";
                        $smtp2 = $this->db->preparar($query);
                        if($smtp2){
                            return $user;
                        }else{
                            return false;
                        }                                                    
                    }else{
                        return false;
                    }                
                }
            }else if($tipo_login==='isloggedweb'){
                $encrypted = $this->checkhashSSHA($salt, $password);
                if($encript===$encrypted){
                    return $user;
                }else{
                    return false;
                }
            }    
         }else{
            return false;
        }            
    }
        
       /**
     * Encriptamos la contraseña
     * @param password
     * Devuelve en un array el salt y la encriptacion
     */
    public function hashSSHA($password) {
 
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }
    /**
     * Desencriptamos la contraseña
     * @param salt, password
     * returns hash string
     */
    public function checkhashSSHA($salt, $password) { 
        $hash = base64_encode(sha1($password . $salt, true) . $salt); 
        return $hash;
    }
    
    public function cerrarSesion ($email, $tipo_login){
          //De momento solo usaremos el tipo de login en movil y no en web ya que es engorroso debido a cierre de sesion, etc       
             $query= "UPDATE users SET "."$tipo_login"."='0' WHERE email = '$email'";
             $smtp = $this->db->preparar($query);
             if($smtp){
                return TRUE;
             }else{
                return FALSE;
             }              
    }
    
    //FUNCIONES RELATIVAS A SESIONES
    
    function obtener_ultimo_acceso(){
        $ultimo_acceso=0;
        if(isset($_SESSION['login_date'])){
            $ultimo_acceso=$_SESSION['login_date'];            
        }
        return $ultimo_acceso;
    }
    
    function sesion_Activa(){
        $estado_activo=false;
        $ultimo_acceso= $this->obtener_ultimo_acceso();
        
        $limite_ultimo_acceso = $ultimo_acceso+60;
        
        if($limite_ultimo_acceso > time()){
            $estado_activo=true;
            $_SESSION['login_date']= time();
        }
        return $estado_activo;
    }
    
}
