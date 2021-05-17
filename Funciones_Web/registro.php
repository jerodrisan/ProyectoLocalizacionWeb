<?php
/* 
 
 */
    require_once __DIR__ . '/../Funciones_Login/funciones_login.php';                
    $db = new funciones_login(); 
    $con = mysqli_connect(DB_SERVER , DB_USER, DB_PASSWORD, DB_DATABASE);
    
    // creamos una función que nos parmita validar el email    
    function valida_email($correo) {
       if (preg_match('/^[A-Za-z0-9-_.+%]+@[A-Za-z0-9-.]+\.[A-Za-z]{2,4}$/', $correo))
               return true;
        else return false;
    }
    
    
    // if(isset($_POST['btnEnviar'])) {
                    
        // Procedemos a comprobar que los campos del formulario no estén vacíos
        $sin_espacios = count_chars($_POST['nombre'], 1); //devuelve un array con el valor ASCII de cada caracter introducido y cuantas veces se repite. 
        //https://www.w3schools.com/php/func_string_count_chars.asp
        if(!empty($sin_espacios[32]) || strlen($_POST['nombre'])>10 || empty($_POST['nombre'])) { //Es espacio en blanco corresponde al valor ASCII 32.
            //echo "El campo <em>nombre</em> no debe contener espacios en blanco o tener mas de 10 caracteres ni estar vacio. <a href='javascript:history.back();'>Reintentar</a>";            
            $response['mensaje'] = "El campo <em>nombre</em> no debe contener espacios en blanco o tener mas de 10 caracteres ni estar vacio";
            $response['true'] ='1';
            echo json_encode($response);
            
        }else if(!valida_email($_POST['email'])) { // validamos que el email ingresado sea correcto            
            //echo "El email ingresado no es válido o esta en blanco. <a href='javascript:history.back();'>Reintentar</a>";
            $response['mensaje'] = "El email ingresado no es válido o esta en blanco";
            $response['true'] ='1';
            echo json_encode($response);            
            
        }else if(empty($_POST['password']) || strlen($_POST['password'])>32) { // comprobamos que el campo password no esté vacío
           // echo "No haz ingresado contraseña o es demasiado larga. <a href='javascript:history.back();'>Reintentar</a>";
            $response['mensaje'] = "No has ingresado contraseña o es demasiado larga";
            $response['true'] ='1';
            echo json_encode($response);
        }else if($_POST['password'] != $_POST['password2']) { // comprobamos que las contraseñas ingresadas coincidan
            //echo "Las contraseñas ingresadas no coinciden. <a href='javascript:history.back();'>Reintentar</a>";
            $response['mensaje'] = "Las contraseñas ingresadas no coinciden";
            $response['true'] ='1';
            echo json_encode($response);            
        }else{         
            // "limpiamos" los campos del formulario de posibles códigos maliciosos
            $name = mysqli_real_escape_string($con,$_POST['nombre']);
            $password = mysqli_real_escape_string($con,$_POST['password']);                       
            $email = mysqli_real_escape_string($con,$_POST['email']);            
                       
            $response = $db->registro($email, $password, $name);            
            if($response['error']===false){                
                //echo 'Usuario dado de alta correctamente';
                $response['mensaje'] = "Usuario dado de alta correctamente";
                $response['true'] ='0';
                echo json_encode($response);    
            }else{
                //echo 'Datos erroneos, vuelva a intentarlo';
                $response['mensaje'] = "Datos erroneos. El cliente ya existe o contraseña incorrecta. Vuelva a intentarlo";
                $response['true'] ='1';
                echo json_encode($response);
            }        
            
        }                    
   // }