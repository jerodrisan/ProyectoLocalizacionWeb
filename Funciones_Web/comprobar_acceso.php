<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
        
        session_start();  
        require_once __DIR__ . '/../Funciones_Login/funciones_login.php';
        $db = new funciones_login();       
        $con = mysqli_connect(DB_SERVER , DB_USER, DB_PASSWORD, DB_DATABASE);
        
        if(empty($_POST['email']) || empty($_POST['password'])) {
           // echo "El usuario o la contraseña no han sido ingresados. <a href='javascript:history.back();'>Reintentar</a>";
            $response['mensaje']= 'El usuario o la contraseña no han sido ingresados';
            $response['true']='1';
            echo json_encode($response);
        }else {            
            // "limpiamos" los campos del formulario de posibles códigos maliciosos
            $email = mysqli_real_escape_string($con,$_POST['email']);
            $password = mysqli_real_escape_string($con,$_POST['password']);              
            $tipo_login = "isloggedweb"; //logueamos a traves de la web
            $response = $db->login($email, $password, $tipo_login);
            
            if($response["error"]===FALSE){
                
                $_SESSION['usuario_id'] = $response["uid"]; // creamos la sesion "usuario_id" y le asignamos como valor el campo id
                $_SESSION['usuario_nombre'] = $response["user"]["name"]; // creamos la sesion "usuario_nombre" y le asignamos como valor el campo nombre     
                $_SESSION['usuario_email'] =  $response["user"]["email"];                
                //$usu_id = $_SESSION['usuario_id'];    
                $response['usuario_id']= $response["uid"];
                $response['mensaje']= 'Usuario ingresado correctamente';
                $response['true']='0';                
               
                echo json_encode($response);
                       
               
            }else{              
               // Error usuario no dado de alta o datos incorrectos, <a href="index.php">Reintentar</a><?php
                $response['mensaje']= 'Usuario no registrado o datos incorrectos';
                $response['true']='1';
                echo json_encode($response);               
            }            
        }
        