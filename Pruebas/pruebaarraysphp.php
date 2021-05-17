<?php
/* 
 Pruebas varias php relativas al proyecto.
 */


if(isset($_POST['array1']) && isset($_POST['array2'] )){
    
    $array1 = array();
    //$auxiliar=[10,11,13,14];    
    $array1 = $_POST['array1'];  
    $array2 = $_POST['array2'];  
    
   // $array3= explode(",", $array1);
    
    $response['array1']=$array1;
    
   //echo json_encode($auxiliar)$auxiliar;
    echo json_encode($response);
    
}
    
   