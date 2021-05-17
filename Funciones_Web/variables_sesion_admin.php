<?php    
     session_start();  
     if(isset($_GET['admin'])){       
        
        if(isset($_SESSION['usuario_id'])){
          //valores del administrador del sitio   
          $id = $_SESSION['usuario_id']; 
          $nombre = $_SESSION['usuario_nombre']; 
          $email = $_SESSION['usuario_email'];    
          $sesion_iniciada=1;  
          echo "{
          \"id\":\"$id\",
          \"nombre\":\"$nombre\",
          \"email\":\"$email\",
          \"sesion_iniciada\":\"$sesion_iniciada\"
           }"; 
        }else{
          $response['sesion_iniciada']=0;
          echo json_encode($response);
        } 
                  
     }
     
/*ojo Cómo solucionar el problema "Headers already sent" de PHP     
https://uniwebsidad.com/foro/pregunta/128/como-solucionar-el-problema-headers-already-sent-de-php/
*/
     

