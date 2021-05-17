<?php

    $resp = $_GET["radares"];

    if ($resp =="si"){


        $data = file_get_contents("fijostotal2.json");

        $datos = json_decode($data, true);
    
        echo json_encode($datos);

    }
    /*
    else if ($resp == "no"){
        $dato['activado']='no';
        echo json_encode(['kml'=>$dato]);        
    }
    */




