<?php

	//La funcion de session_destroy() destruye toda la información registrada de una variable de sesión,
	// luego el header(‘location: index.php’) redireccionara al usuario al index.php 
	
    session_start();	
    require_once __DIR__ . '/../Funciones_Login/funciones_login.php';              
    $db = new funciones_login();
    
    if(isset($_SESSION['usuario_email'])) {
        
        $email = $_SESSION['usuario_email'];
        $tipo_login = "isloggedweb";
        $isClosed = $db->cerrarSesion($email, $tipo_login);
        session_destroy();
        if($isClosed){
           
             header("location: ../index.html");
        }
       
    }else {
        //En este caso la sesion ya ha caducado y cerramos la sesion 
        //echo "Sesion caducada. Ingrese de nuevo";
         header("location: ../perfil_mapa.html");
     
    }
  ?>