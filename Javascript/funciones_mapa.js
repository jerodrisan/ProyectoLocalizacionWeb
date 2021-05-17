/* 

 */

 //Ojo sobre pasar variables de php a javascript. Es mejor usarlo con ajax ya que directamente no es seguro.
                 // Ver: http://stackoverflow.com/questions/23740548/how-to-pass-variables-and-data-from-php-to-javascript
                
                 var lat1 = 40.39375430294055; //datos de casa
                 var long1 = -3.691840751827427;                 
                 var lat2 = 38.35905850534741;  //datos de la playa
                 var long2 =-0.41120632543667873;
                 var longit =[lat1, long1, lat2, long2];
                 //var myLatLong = new google.maps.LatLng(lat1,long1 );
                 var map;               
                 var myLatLong;
                 //arrays para las coordenadas guardadas en el servidor
                 var arrayLatit = new Array();
                 var arrayLongit = new Array();
                 var arrayAltit = new Array();
                 var arrayPoint = new Array();
                 var arraySesionCreated = new Array();
                 var arrayPuntos = new Array();
                 var objBoton;
                 
                 var setFirst=false;
                 var interval_timeout;                
                   
                  /* Si queremos cargar el mapa una vez despues se haya cargado la pagina, no inmediatamente. 
                  function loadScript(){                      
                      var scripti = document.createElement('script');
                      scripti.type = "text/javascript";
                      scripti.src = "https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true&callback=initialize";                      
                      document.body.appendChild(scripti);       
                  }
                  */                       
                
                var arrayLocalizaciones; 
                function setLocalizaciones(localizaciones){
                    arrayLocalizaciones = localizaciones;
                }
                function getLocalizaciones(){
                    return arrayLocalizaciones;
                }
                
                function crearArrayPuntos(datos){ //datos desde el principio al final de la ruta
                        var arrayLatit = []; var arrayLongit=[];  var arrayAltit =[];                         
                        var arrayDate =[];  var arrayDistancias=[]; var arrayTiempos=[];
                        var arrayLocations =[];
                        for(d=0;d<datos.registros.length;++d){
                               //Metemos los datos en arrays
                               arrayLongit[d] = datos.registros[d].longitud;                                 
                               arrayLatit[d] = datos.registros[d].latitud;    
                               arrayAltit[d]= datos.registros[d].altitud;
                               
                               arrayDate[d]=datos.registros[d].date;       
                               //Metemos datos de las distancias: En este caso datos.registros.length es igual a datos.distancias.length por lo cual no hacemos otro bucle for para distancias
                               arrayDistancias[d]=datos.distancias[0][d];
                               //Metemos los datos de los tiempos en cada paso para luego hacer las restas y calcular la velocidad
                               arrayTiempos[d]=datos.tiempos[0][d];
                               //Metemos los datos en filas para posterior manipulacion 
                               arrayLocations[d] = [arrayLatit[d],arrayLongit[d],arrayAltit[d],arrayDate[d], arrayDistancias[d],arrayTiempos[d]];
                        }      
                       //alert('horas '+arrayTiempos);
                        setLocalizaciones(arrayLocations);
                        //return arrayLocations;                       
                }
                
                //Funcion para crear un array personalzado de localizaciones entre dos valores inicio y final 
                //Sera usada cuando queramos ver un segmento de la ruta en concreto: 
                function crearArrayPuntos_InicioFin(inic, fin){
                    var locations = getLocalizaciones(); //Cargamos las localizaciones de principio al fin     
                     //En caso de no hacer esta conversion, cuando pasamos algun valor de dos digitos, no funciona bien , con lo cual parseamos
                     
                    var inicio=parseFloat(inic);
                    var final = parseFloat(fin);
                    var distancia_total_ruta = (calc(locations,0,locations.length).longitud_total).toFixed(4); //distancia total de la ruta                 
                    //alert(distancia_total_ruta+"  "+final);
                    if( inicio >= final ){
                            alert("el valor inicial tiene que ser menor que el final");                          
                    }else if(inicio<0){                           
                             alert("valor de inicio no valido");                            
                    }else if(isNaN(inicio) || isNaN(final)){                        
                            alert("ha de introducir algun valor en las inicial y final");                                    
                    }else{                        
                        //alert("valores correctos "+inicio+"  "+final);   
                            //console.log("inicio "+inicio+" , final "+final);
                            var longtot=0; 
                            var long_parcial_1=0;
                            var i,j;
                            for (i=0; i<locations.length; ++i){                                
                                longtot+=locations[i][4];                                
                                if( longtot >= inicio){
                                    var from=longtot; 
                                    var m=i;                                       
                                    if(m>0){                                       
                                       longtot = longtot-locations[m][4];  
                                       long_parcial_1 = longtot;  //pillamos la longitud parcial del tramo y luego lo sumamos en setInforutas 
                                    }
                                   // console.log("longtot1 "+longtot+" m: "+m);
                                    break;
                                }                        
                            }                    
                            for (j=m; j<locations.length; ++j){                                
                                longtot+=locations[j][4];                               
                                //console.log("longtot2 "+longtot);
                                if(longtot >= final){
                                    var to = longtot;
                                    var n=j;                                  
                                    break;
                                }                          
                            }
                            //En caso de que nos pasemosm poniendo un final que sobrepase la distancia total de la ruta:                            
                            if(final>=distancia_total_ruta){ 
                                   // console.log("sobrepasada distancia total ruta, distancia total:"+distancia_total_ruta+" , final :"+ final);
                                    var to = distancia_total_ruta;
                                    var n=locations.length;                                                          
                            }                         
                            //Creamos el array del tramo que queremos entre los puntos j y k+1
                            var locations_segment = locations.slice(m,n); 
                            //console.log(locations_segment.length+"  "+m+"   "+n+"    from: "+from+"     to: "+to+" , longtot "+longtot);     
                            setInfoRutas(locations_segment, long_parcial_1);                                         
                            setPolylineas2(map,locations_segment);                         
                            
                    }                    
                }               
                
                   
                //Ponemos los marcadores, En este caso el ultimo marcador de un evento en directo sin las polilineas.
                var centrarMarcador;
                var markerArray;   
                var markerArrayAux=null;
                function setMarcadores(mapa, locations){
                      
                    //Prueba para poner todos los marcadores de cada punto                     
                    var distancia=0;
                    var tiempo=0;
                    var infowindow_timeout_close;
                    var elevator = new google.maps.ElevationService;                        
                    var velocidad;                        
                      
                    if(markerArrayAux!==null){
                        //console.log("punto0");
                        markerArrayAux.setMap(null);
                    }    
                                               
                    var point = locations[locations.length-1];     
                               //  alert(points[0]+points[1]); 
                    var miLatLong = new google.maps.LatLng(point[0],point[1]);   
                    markerArray = new google.maps.Marker({ 
                        position: miLatLong,
                        map:mapa      
                    });
                    //console.log(markerArrayAux+"  "+markerArray);
                    //markerArray.setMap(mapa);
                            
                    distancia+=locations[locations.length-1][4];
                    tiempo+=locations[locations.length-1][5];                            
                           //console.log(locations[i][4]);                      
                             //Ponemos el marcador que queramos. En este caso el Ultimo:
                           
                              
                    velocidad = Math.round(((locations[locations.length-1][4]*1000*3.6/locations[locations.length-1][5])*100)/100); //velocidad de tramo                                
                    var infowindowOp ={content: "Velocidad del tramo : "+velocidad+" km/h" +"<br>"+
                                                            " Distancia Total:"+format_espacio_tiempo('longitud',distancia).longitud+"<br>"+
                                                            "Tiempo Total:"+format_espacio_tiempo('tiempo',tiempo).tiempo+"<br>"
                                                            +"Elevacion actual :"+elevaciones[0]
                                                };                                        
                                                
                    var infowindow = new google.maps.InfoWindow(infowindowOp);                                
                    var miLatLong2 =[miLatLong];                           
                    get_altitudes(miLatLong2, elevator,0);                                
                    setInfoMarcador(markerArray,'always');//ponemos el marcador                                
                    infowindow_timeout_close = setTimeout(function(){ //Quitamos la informacion pasados x segundos
                        infowindow.close(mapa,markerArray); 
                    },5000);
                                
                    markerArray.addListener('mouseover', function(){                                   
                        setInfoMarcador(this,'open');                               
                    });
                    markerArray.addListener('mouseout',function(){
                        setInfoMarcador(this,'close'); 
                    }); 
                                                               
                          
                    function setInfoMarcador(marcador,param){                                   
                                   // console.log('distancia '+calc(locations,0,i).longitud_total);                                   
                                    if(param==='open'){                                      
                                        infowindow.open(mapa,marcador);    
                                    }if(param==='close'){
                                         infowindow.close(mapa,marcador);      
                                    }if(param==='always'){ 
                                        //console.log('eleva'+elevaciones[0]);
                                        
                                          ito = setTimeout(function(){
                                             infowindow.open(mapa,marcador);
                                             //console.log('elevaciones' +elevaciones[0]);
                                        },400);                                                                      
                                    } 
                                    
                    }  
                    markerArrayAux = markerArray;  
                                      
                    $("#label_desnivel").text("  -  ");
                    $("#label_pendiente").text(" -  ");
                    centrarMarcador = setTimeout(function(){ //Ver https://developers.google.com/maps/documentation/javascript/events?hl=es                         
                        map.panTo(markerArray[markerArray.length-1].getPosition());//Una vez puestos los marcadores, movemos el mapa a los marcadores
                    }, 500);                        
                }                            
          
                 
                var calc = function (locations, from, to ){
                           
                      var longitudTot=0; //Longitud total de toda la ruta
                      var tiempoTot=0; //Tiempo total 
                      var velocMedia=0; //Velocidad media                 
                      var veloMax=0;  //Velocidad maxima                     
                      for(var i=from; i<to; ++i){     //Empezamos desde 1 ya que en i=0 hay una division 0/0
                           //console.log(locations[i][5] + "  "+ locations[i][4]+"    "+locations.length);
                           var veloaux=0;
                           longitudTot+=locations[i][4]; //Suma de las distancias entre todos los puntos en metros                           
                           tiempoTot+=locations[i][5]; //Suma de todos los tiemposm parciales, seria el tiempo total en segundos                        
                           veloaux=locations[i][4]*1000/locations[i][5];
                           
                           if(isFinite(veloaux)){ //Evitamos los posibles divisiones por cero , errores subiendo dos puntos en el mismo instante
                              if(veloaux>veloMax){
                                 veloMax=veloaux;
                              }
                           }                          
                       velocMedia=(longitudTot*1000*3.6/tiempoTot); //Pasamos a Km/h                       
                       //console.log("setinforutas "+longitudTot+" "+locations[i][4]+", tiempos :"+tiempoTot+" "+locations[i][5]);
                       
                      }
                      return {
                                longitud_total:longitudTot,
                                tiempo_total: tiempoTot,
                                velocidad_media: velocMedia,
                                velocidad_maxima: veloMax
                            };                     
                 };
                 
                 function format_espacio_tiempo(magnitud, variable){
                       var longitud_formateada =0;
                       var tiempo_formateado =0;
                       
                       switch(magnitud){
                           case 'longitud':
                                if(variable*1000 <= 1000){ //Redondeamos a dos decimales con:  Math.round(num * 100) / 100                          
                                    longitud_formateada = Math.round((variable*1000)*100)/ 100+' metros';
                                 }else{                         
                                    longitud_formateada = Math.round(variable*100)/100+' kilometros';                                    
                                }
                                return{
                                     longitud:longitud_formateada
                                };
                            break;
                        case 'tiempo':
                                if(variable >=60 && variable <3600 ){ //tiempo en min y seg. Tiempo Con Math.floor sacamos la parte entera:                       
                                   tiempo_formateado =Math.floor(variable/60)+' min '+variable%60+' seg';
                                }else if(variable >= 3600){  ////Tiempo en horas min y seg                         
                                   tiempo_formateado = Math.floor(variable/3600)+' Horas '+Math.floor((variable%3600)/60)+' min '+(variable%3600)%60+' seg';                          
                                }else{ //Tiempo en segundos                       
                                    tiempo_formateado = variable+' seg';                                    
                                }
                                return{
                                     tiempo :tiempo_formateado
                                };
                            break;
                       }                     
                 }
                  
                var z=0;
                var geocoder = new google.maps.Geocoder;
                  //Funcion para poner datos de velocidad, velocidad maxima, distancia, tiempo, ...
                function setInfoRutas(locations, long_parcial_1){     
                
                    var longitudTot = calc(locations,0,locations.length).longitud_total;                    
                    var tiempoTot =  calc(locations,0,locations.length).tiempo_total;                   
                    var veloMax = calc(locations,0,locations.length).velocidad_maxima;                    
                    var velocMedia = calc(locations,0,locations.length).velocidad_media;                  
                    //console.log(longitudTot+"  "+tiempoTot+"   "+veloMax+"   "+velocMedia);
                    
                      //DISTANCIA:
                      var distancia_total_ruta = format_espacio_tiempo('longitud', longitudTot).longitud;                     
                      $("#label_distancia").text(distancia_total_ruta);
                      //dist_total_ruta= calc(locations,0,locations.length).longitud_total; //variable global que usaremos para calculo de segmentos
                      
                      var m_o_km = distancia_total_ruta.slice(-10); //se coge los ultimos 10 caracteres del array
                      var distanciatotalruta;
                      if(m_o_km ==="kilometros"){
                            distanciatotalruta = distancia_total_ruta.slice(0,-11); //se pillan los primeros caracteres contando " kilometros" en el array desde el final
                             //var distot_longparcial = distanciatotalruta+long_parcial_1;
                             $("#segment_to").val((Number(distanciatotalruta)+long_parcial_1).toFixed(2));                          
                         
                      }else{ //en caso de metros:
                            distanciatotalruta = distancia_total_ruta.slice(0,-7); //contando " metros" en el array desde el final.
                            $("#segment_to").val(((distanciatotalruta/1000)+long_parcial_1).toFixed(2));                           
                      }
                      $("#segment_from").val((long_parcial_1).toFixed(2));                        
                     
                      //TIEMPO:
                      $("#label_tiempo").text(format_espacio_tiempo('tiempo',tiempoTot).tiempo);                  
                      //VELOCIDAD:       
                      //Caso de Ruta polilineas
                      if(objBoton==="cargarPolylines" || objBoton==="ver_segmento"){
                          
                          //alert('cargar '+longitudTot+' '+tiempoTot+' '+velocMedia);                          
                          $("#label_veloc").text('VELOCIDAD MEDIA TOTAL');                         
                          $("#label_velocidad").text(Math.round(velocMedia*100)/100+' km/h');
                          //Lugar incial y final de la ruta:                         
                          var latlongFinal={lat: parseFloat(locations[locations.length-1][0]), lng: parseFloat(locations[locations.length-1][1])}; //Pillamos la ultima posicion
                          var latlongInicial= {lat: parseFloat(locations[0][0]), lng: parseFloat(locations[0][1])}; //Pillamos la primera posicion                         
                          $("#label_lugar").html('INICIO&nbsp&nbsp&nbsp:'); getAdress(latlongInicial,'#label_lugar2',0);                          
                          $("#label_lugar3").html('FINAL&nbsp&nbsp&nbsp:'); getAdress(latlongFinal,'#label_lugar4',0);
                          
                      }
                       //En caso de Evento ponemos la velociad del momento
                      if(objBoton==="eventoLive" || objBoton==="cargarMarcad"){   
                          
                         var longParcial=0; var tiempParcial=0; var velocParcial=0;
                         longParcial=locations[locations.length-1][4]; //sacamos la ultima diferencia entre distancias
                         tiempParcial=locations[locations.length-1][5]; //sacamos la ultima diferencia entre tiempos
                         velocParcial = (longParcial*1000*3.6/tiempParcial); //Pasamos tambien la longParcial a metros ya que sale en km por defecto                       
                         //document.getElementById('label_veloc').innerHTML= 'VELOCIDAD ACTUAL';
                         $("#label_veloc").text('VELOCIDAD ACTUAL');
                         //document.getElementById('label_velocidad').innerHTML = Math.round(velocParcial*100)/100+' km/h';
                         $("#label_velocidad").text(Math.round(velocParcial*100)/100+' km/h');
                        //Ponemos el lugar de paso en caso de evento en directo:
                        /*                      
                        http://stackoverflow.com/questions/19511597/how-to-get-address-location-from-latitude-and-longitude-in-google-map
                        https://developers.google.com/maps/documentation/javascript/examples/geocoding-reverse
                        Hay varias formas de poner la direccion , dependiendo de la posicion del vector results[], Se puede ver en el 
                        json en la siguiente direccion:
                        http://maps.googleapis.com/maps/api/geocode/json?latlng=40.394697,-3.694635&sensor=truehttp://maps.googleapis.com/maps/api/geocode/json?latlng=40.394697,-3.694635&sensor=true
                        */    
                       
                        var latlong={lat: parseFloat(locations[locations.length-1][0]), lng: parseFloat(locations[locations.length-1][1])}; //Pillamos la ultima posicion                       
                        getAdress(latlong,'#label_lugar',0); //La mostramos en el div correspondiente                         
                    }
                    //En ambos casos ponemos la velocidad maxima obtenida:
                    veloMax=veloMax*3.6;
                    //document.getElementById('label_velmax').innerHTML= Math.round(veloMax*100)/100+' km/h';
                    $("#label_velmax").text(Math.round(veloMax*100)/100+' km/h');
                    
                }
                 
                function getAdress(latlong, label, pos){                    
                     geocoder.geocode({'location': latlong}, function(results, status){ 
                      if(status === google.maps.GeocoderStatus.OK){
                               if (results[pos]){                //results[0] pos da bastante info sobre la direccion                          
                                     //document.getElementById(label).innerHTML= results[0].formatted_address;
                                     $(label).html(results[0].formatted_address);
                               }else{
                                    alert('sin resultados');
                                       }
                       }else{
                           //alert('Geocoder failed');
                       }
                      });
                }     
                
                
               
               
                var elevaciones =[];
                function get_altitudes(locations, elevator,inic){                     
                   // console.log("holaaaaaaa");
                    var locations_clon = locations.slice();                   
                    var partir = locations_clon.length/500 ;
                    if(partir>1){
                      // console.log ("locations antes "+locations); 
                       var parte1 = locations_clon.splice(0,500); //     
                      // console.log("parte1 "+parte1+" locations despues "+locations+" partir "+partir);     
                       console.log("parte 1 "+parte1.length);
                       set_altitudes(parte1,elevator,inic);
                       
                    }else{
                        console.log("parte 2 "+locations_clon.length);
                        set_altitudes(locations_clon, elevator,inic);
                        
                    }                    
                    function set_altitudes (loc, elevator,inic){                        
                                       
                         elevator.getElevationForLocations({
                                    'locations':loc  //locations vendria en formato array                              
                                }, function(results,status ){
                                    //var time = new Date();
                                    if (status === 'OK' && results[0]) {                                     
                                       for (var i=0; i<results.length; ++i){                                                
                                                elevaciones[i+inic]=results[i].elevation;       
                                              //console.log(elevaciones[i+inic]+"  "+inic); 
                                       }
                                       //console.log("numero elevaciones "+elevaciones.length);
                                       if (partir>1) {
                                           //console.log("loca inside "+locations.length+" partir "+partir);
                                           get_altitudes(locations_clon,elevator,i+inic);
                                       }                                       
                                       //var diff = new Date()- time;
                                       //console.log("Tiempo "+diff+" numero registros "+elevaciones.length );
                                    }else{
                                        //infowindow1.setContent('Elevation service failed due to: ' + status);
                                        //console.log('Elevation service failed due to: ' + status);
                                    }
                                }                     
                        );  
                
                    }
                    
                        /*
                        elevator.getElevationForLocations({
                                    'locations':locations  //locations vendria en formato array                              
                                }, function(results,status){
                                    //var time = new Date();
                                    if (status === 'OK' && results[0]) {                                     
                                       for (var i=0; i<results.length; ++i){                                                
                                                elevaciones[i]=results[i].elevation;       
                                              console.log(elevaciones[i]);
                                       }
                                                                              
                                       //var diff = new Date()- time;
                                       //console.log("Tiempo "+diff+" numero registros "+elevaciones.length );
                                    }else{
                                        //infowindow1.setContent('Elevation service failed due to: ' + status);
                                        //console.log('Elevation service failed due to: ' + status);
                                    }
                                }                     
                        );     
                        */
                        
                }    
                  
                var pan_to;
                //Creamos un array de arrayPath para crear polilineas de un punto a otro, es decir entre dos puntos. Lo creamos mas arriba               
                var subPoliArrays=[];
                var arrayPathLength;  //longitud del array de coordenadas para ver si cambia o no                  
                //Funcion para poner la polininea de evento en directo
                function setPolylineas2(mapa,locations){
                    
                      var elevator = new google.maps.ElevationService; 
                      var arrayPath= new Array();
                      for(i=0; i<locations.length; ++i){                     
                           arrayPath[i]= new google.maps.LatLng(locations[i][0], locations[i][1]);                           
                      }
                      //calculamos las alturas de cada punto y lo ponemos en las etiquetas
                      get_altitudes(arrayPath,elevator,0);
                      var timeout_desnivel = setTimeout(function(){ //Ya que la funcion get_altitudes es asincrona, esperamos un rato a poner los valores en los label
                            var dist = calc(locations,0,locations.length).longitud_total;
                            var desnivel = (elevaciones[locations.length-1]-elevaciones[0]);  //desnivel desde el principio al final
                            var pendiente = (desnivel/(dist*1000))*100;                     //pendiente desde el principio al final
                            $("#label_desnivel").text(" "+desnivel.toFixed(2)+' m');
                            $("#label_pendiente").text(" "+pendiente.toFixed(2)+' %');
                      },3000);
                                            
                      if(arrayPath.length>1){                          
                          //Troceamos el array en subarrays de dos unidades:
                          if( subPoliArrays.length===0){ //si la longitud es cero, empezamos a cortar todos
                               for(i=0;i<arrayPath.length-1; ++i){
                                  subPoliArrays[i] = arrayPath.slice(i,i+2);
                              }                              
                              //Dibujamos cada linea:                              
                               setEveryPoliLine(subPoliArrays,0,subPoliArrays.length,mapa,arrayPath,locations); //(array,inicio,final,mapa)
                            
                          }else if (subPoliArrays.length>0){ //tomamos el ultimo elemento para añadir al array solo las ultimas polilineas en cada llamada
                                 //Tenemos dos posibilidades, que no obtengamos dato en la llamada a ajax, o que obtengamos dato en la llamada
                                 //si no obtenemos dato en la  llamada entonces la longitud del array es la misma y no añadimos linea a subPoliArrays
                              if(arrayPathLength===arrayPath.length){
                                      //no añadimos linea al array
                                      //alert('no hay mas datos'); 
                              }else{ 
                                //añadimos los ultimos datos obtenidos a las ultimas posiciones de subPoliArrays
                                for(i=arrayPathLength-1; i<arrayPath.length-1; ++i){ 
                                    //alert('arrayPathLength '+arrayPathLength);
                                    subPoliArrays[i]= arrayPath.slice(i,i+2); 
                                }
                                //Dibujamos cada linea nueva que recibimos;
                                setEveryPoliLine(subPoliArrays,arrayPathLength-1,subPoliArrays.length,mapa,arrayPath,locations); 
                              } 
                          }
                          arrayPathLength=arrayPath.length; 
                          //alert('arrayPathLength '+arrayPathLength);
                      }  
                      //alert(locations[0][4]);
                      var contenString = ["Position Inicial : "+locations[0][0]+"<br>"+locations[0][1]+"<br><br>"+" Fecha y  hora : "+ locations[0][3],
                           "Position Actual : "+locations[locations.length-1][0]+"<br>"+locations[locations.length-1][1]+"<br><br>"+" Fecha y  hora : "+ locations[locations.length-1][3]];
                      var infowindowOptions =[
                           {content:contenString[0]},
                           {content: contenString[1]}
                       ];                       
                     //ponemos en la primera pasada solo el marcador de inicio 
                     if(!setFirst)
                        setFirstMarker(arrayPath[0], infowindowOptions[0],mapa);                    
                                           
                    //En las sisguientes solo la ultima
                    addMarker(arrayPath[locations.length-1], infowindowOptions[1], mapa);
                      
                    //Movemos el mapa al primer punto de la ruta                   
                    if(objBoton==="cargarPolylines" || objBoton==="ver_segmento"){
                           var pan_to1= setTimeout(function() { //Ver https://developers.google.com/maps/documentation/javascript/events?hl=es                         
                                map.panTo(arrayPath[0]);//Una vez puestos la polilinea, movemos el mapa al ultimo marcador
                            }, 500);
                             $("#centrarVista").click(function(){
                                map.panTo(arrayPath[0]);
                            });                                
                            setMapaAltitudes(locations,elevaciones);                              
                            
                    } 
                    //Movemos al ultimo punto 
                   
                    if(objBoton==="eventoLive"){                        
                            map.addListener('drag',function(){                               
                                 mapa_clicado=true;
                            });                        
                            if(mapa_clicado===false){
                                centrar();
                            }else{
                                clearTimeout(pan_to);
                            }
                            $("#centrarVista").click(function(){
                                mapa_clicado=false;
                                centrar();
                            });
                            function centrar(){
                                     var pan_to= setTimeout(function() { //Ver https://developers.google.com/maps/documentation/javascript/events?hl=es                                          
                                       // centrarVista(arrayPath);   
                                       map.panTo(arrayPath[arrayPath.length-1]);
                                    }, 200);
                            }
                    }        
                }
                var mapa_clicado =false;                   
                   
                                                    
                var arrayPoliLines =[]; //Array domte metemos todas las polilineas 
                var j=1; //contador para poner flechas cada x intervalo.Se empiezana a poner las flechas en el valor inicial 
                var jx=2;  //variable para ponerlas cada x intervalo
                function setEveryPoliLine(subPoliArrays,inicio,final,mapa,arrayPath,locations){
                      
                      for(i=inicio; i<final; ++i){      
                            var lineSymbol = [
                                    {path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW},
                                    {path: google.maps.SymbolPath.CIRCLE}
                            ];                                    
                            var polyOptions =[
                                    { //array con tres componentes, la primera tiene flecha, la segunda es el circulo que se mueve y la tercera no tiene nada
                                    icons: [{
                                        icon: lineSymbol[0],
                                        offset: '50%'                                      
                                    }], path:subPoliArrays[i], geodesic:true, strokeColor:'#FF0000', strokeOpacity: 1.0, strokeWeight: 2
                                    },
                                    {
                                    icons: [{
                                        icon: lineSymbol[1],
                                        offset: '99%'                                      
                                    }], path:subPoliArrays[i], geodesic:true, strokeColor:'#FF0000', strokeOpacity: 1.0, strokeWeight: 3
                                        
                                    },
                                    {
                                       path:subPoliArrays[i], geodesic:true, strokeColor:'#FF0000', strokeOpacity: 1.0, strokeWeight: 2
                                    }
                                 ];
                            //Establecemos la poliLinea                             
                            var polyLines = new google.maps.Polyline(polyOptions[0]); //Con icono o flecha
                            var polyLines1 = new google.maps.Polyline(polyOptions[1]); //El circulo que se mueve
                            var polyLines2 = new google.maps.Polyline(polyOptions[2]); //sin icono ni flecha
                            var poliLineArray = [polyLines,polyLines1,polyLines2];
                            arrayPoliLines.push(poliLineArray);                             
                           
                       }                     
                        //FINALMENTE PONEMOS LAS POLILINEAS                     
                       //alert('inicio '+inicio+' final '+final+'arraypolilength '+arrayPoliLines.length);  
                       var longi_untercio = Math.round(final/3);
                       var longi_dostercios = Math.round(final*2/3);
                       
                       if(objBoton==="cargarPolylines" || objBoton==="ver_segmento"){ //En caso de que pulsemos el boton de Cargar polilineas                                        
                            for (var i=inicio; i<final; ++i ){        //Cada tercio y dos tercios del recorrido ponemos una flecha                            
                                    if(i===longi_untercio || i===longi_dostercios){
                                        arrayPoliLines[i][0].setMap(mapa); //Flecha     
                                         google.maps.event.addListener(arrayPoliLines[i][0], "mouseover", function(e){                                        
                                                setInfoBanderas(e,this);
                                            });                                        
                                    }else{
                                        arrayPoliLines[i][2].setMap(mapa); //sin icono ni flecha       
                                         google.maps.event.addListener(arrayPoliLines[i][2], "mouseover", function(e){                                        
                                                setInfoBanderas(e,this);
                                            });
                                    }                                 
                                
                            }                            
                       }                      
                       if(objBoton==="eventoLive"){ //En caso de ser un evento en direccto:
                            for ( i=inicio; i<final;++i){                                
                                if(i<arrayPoliLines.length-1){  //En los tramos previos al ultimo tramo usamos dos flechas una al tercio y otra al segundo tercio                                  
                                    if(i===longi_untercio || i===longi_dostercios){    
                                        arrayPoliLines[i][0].setMap(mapa); //Flecha                                        
                                    }else {                                  
                                        arrayPoliLines[i][2].setMap(mapa); //sin icono ni flecha                                        
                                    }    
                                }else
                                    if(i===arrayPoliLines.length-1){   //En el ultimo tramo, usamos una animacion de una bola moviendose                             
                                        //alert('valor i '+i);
                                        arrayPoliLines[i][1].setMap(mapa); //Circulo que se mueve
                                        //arrayPoliLines[i][0].setMap(mapa); //flecha que se mueve
                                        var now,before = new Date();  
                                        var elapsedTime;
                                        var interval=100; //interval*100 = tiempo en milisegundos  
                                        var count = 0;   
                                        var intervalo = setInterval(function(){
                                            //alert('val i '+i+' val j '+j);      
                                            now = new Date();     
                                            elapsedTime = now - before; 
                                            if(elapsedTime-((count+1)*interval)<1000){
                                                count = (count + 1) % 100;                
                                            }else{                
                                                count= Math.floor(elapsedTime/interval);
                                                }                                                 
                                            var icons = poliLineArray[1].get('icons'); //caso de usar flecha moviendose
                                            icons[0].offset = count+'%';
                                            poliLineArray[1].set('icons', icons);  
                                            //un poco antes del tiempo total limpiamos la funcion con clearinterval para que no coincida con el tiempo de llamada al servidor
                                            if(count ===95 || elapsedTime >(interval*95)){ 
                                                --i;// restamos 1 a i ya que pasado el bucle al estar dentro de interval , se suma 1
                                                // alert('valor i '+i+' valor j '+j);
                                                clearInterval(intervalo); 
                                                //Quitamos la variable polyLines que tiene icono moviendose, y ponemos la que no tiene  
                                                //dependiendo del criterio par o impar de i o el criterio que pongamos                                             
                                                if(i===j){  //se quita el circulo moviendose y se pone una flecha a mitad de camino   
                                                    j+=jx;     
                                                    arrayPoliLines[i][1].setMap(null); //si usamos circulo
                                                   // arrayPoliLines[i][0].setMap(null); //usamos flecha
                                                    arrayPoliLines[i][0].setMap(mapa);    
                                                                                
                                                }else{
                                                    arrayPoliLines[i][1].setMap(null); //si usamos circulo
                                                    //arrayPoliLines[i][0].setMap(null); //usamos flecha
                                                    arrayPoliLines[i][2].setMap(mapa);    
                                                }
                                            }    
                                        },interval);    
                                    }                             
                            }
                        }
                        function setInfoBanderas(e,polyline){                           
                            var infowindowOp;                                                       
                            var infowindow ;                         
                            var image = { //imagen sacada de developers
                                    url: 'https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png',                                  
                                    size: new google.maps.Size(20, 32),                                  
                                    origin: new google.maps.Point(0, 0),                                    
                                    anchor: new google.maps.Point(0, 32)
                            };                                        
                            var arr=[];
                            arr=polyline.getPath().getArray();  //cada path tiene dos puntos arr[0] y arr[1]. Cada uno con su lat y long                                     
                            var x=0;                                        
                            for (var i=0; i<arrayPath.length; ++i){                                            
                                if(arr[0].lat()===arrayPath[i].lat()){
                                    x=i; //punto en cuya linea pasamos por encima el raton. 
                                    var velocidad = Math.round(((locations[x+1][4]*1000*3.6/locations[x+1][5])*100)/100);
                                    var distancia = calc(locations,0,x+2).longitud_total;
                                    var tiempo = calc(locations,0,x+2).tiempo_total;                                    
                                    var desnivel_tramo = (elevaciones[x+1]-elevaciones[x]);
                                    var distancia_tramo = locations[x+1][4]
                                    var pendiente_tramo = (desnivel_tramo/(distancia_tramo*1000))*100; //https://lenoscopia.wordpress.com/2009/10/17/%C2%BFcomo-se-mide-la-pendiente-de-las-rampas/
                                    var desnivel_acumulado= (elevaciones[x+1]-elevaciones[0]);
                                    var pendiente_acumulada = (desnivel_acumulado/(distancia*1000))*100;
                                    var altura = elevaciones[x+1];
                                    
                                    infowindowOp ={content: "Velocidad del tramo : "+velocidad+" km/h" +"<br>"+
                                                            " Distancia :"+format_espacio_tiempo('longitud',distancia).longitud+"<br>"+
                                                            "Tiempo :"+format_espacio_tiempo('tiempo',tiempo).tiempo+"<br>"+
                                                            "Altura :"+altura.toFixed(2)+' m'+"<br>"+
                                                            "Desnivel tramo: "+desnivel_tramo.toFixed(2)+' m'+"<br>"+
                                                            "Pendiente tramo: "+pendiente_tramo.toFixed(2)+' %'+'<br>'+
                                                            "Pendiente acumulada "+pendiente_acumulada.toFixed(2)+' %' 
                                                           
                                                };  
                                    infowindow = new google.maps.InfoWindow(infowindowOp);                                    
                                    break;
                                }                                            
                            }                           
                                var marker_bandera = new google.maps.Marker({
                                position: arr[1],                                               
                                map: mapa,                               
                                icon:image                                          
                            });                            
                            //marker_bandera.setMap(mapa);
                            google.maps.event.addListener(marker_bandera,'mouseover',function(){
                                    //console.log(elevaciones[1]+' metros');
                                    infowindow.open(mapa,marker_bandera);
                            });
                             google.maps.event.addListener(marker_bandera,'mouseout',function(){
                                    infowindow.close(mapa,marker_bandera);
                                    marker_bandera.setMap(null);
                                  
                            });                               
                        } 
                }
                
                //Funcion para poner el primer Marcador en la polilinea
                function setFirstMarker(arraypath,infowin, mapa){
                     addMarker(arraypath,infowin ,mapa );
                     setFirst=true;
                     return setFirst;
                }
                   
                  //Funcion para añadir los marcadores  
                var markersArray=[]; 
                function addMarker(location, infowindowOptions, mapa ){
                    var marker = new google.maps.Marker({
                        position: location,                         
                         /*icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 10
                        },*/
                         map: mapa
                        });                           
                    var infowindow = new google.maps.InfoWindow(infowindowOptions);  
                    google.maps.event.addListener(marker,'mouseover',function(){ //Pasamos por encima raton y aparece la info
                          infowindow.open(mapa,marker);
                    });
                    google.maps.event.addListener(marker,'mouseout',function(){ //quitamos la info al quitar el raton
                          infowindow.close(mapa,marker);
                    });
                   //Almacemamos todos los marcadores en un array
                    markersArray.push(marker);
                    //eliminamos el marcador anterior
                    if(markersArray.length>2){
                          markersArray[markersArray.length-2].setMap(null);
                    }                     
                }
                
                //Reiniciamos la informacion de las etiquetas de velocidad maxima, distancia , etc 
                function init_labels(){
                    $("#label_velocidad").text("0 Km/h");  $("#label_distancia").text("0 m"); $("#label_tiempo").text("0 s");
                    $("#label_velmax").text("0 Km/h");  $("#label_desnivel").text("0 m"); $("#label_pendiente").text("0%");
                    $("#label_lugar2").html(" "); $("#label_lugar4").html(" ");                    
                }
                    
                //Funcion para inicializar todos los arrays por si se vuelve a pulsar el boton de cargar polilineas
                //Tambien eliminamos los marcadores y las polilineas. Primero eliminamos marcadores y polilineas y luego, se inicializa
                function initArrays(){ 
                      //Marcadores:                   
                     if(markersArray.length>0){
                          for (i=0; i<markersArray.length;++i){
                            markersArray[i].setMap(null);
                          }
                          markersArray=[];
                          setFirst=false;
                     }                    
                     
                      if(subPoliArrays.length>0){
                            subPoliArrays=[];
                      }
                      
                     if(arrayPoliLines.length>0){
                           for(i=0; i<arrayPoliLines.length;++i){
                                arrayPoliLines[i][0].setMap(null);
                                arrayPoliLines[i][1].setMap(null);
                                arrayPoliLines[i][2].setMap(null);
                           }
                           arrayPoliLines=[];
                     }
                     
                      if(interval_timeout !== null){
                         clearTimeout(interval_timeout);
                     }
                     elevaciones=[]; //vaciamos el array de las elevaciones
                     
                     //Booramos el marcador de cargar marcadores                     
                     if(markerArray!=null){
                       markerArray.setMap(null);
                     }
                     
                     //quitamos el centraje de dicho marcador en caso que este corriendo:                     
                     if(centrarMarcador !== null){
                         clearTimeout(centrarMarcador);
                     }                     
                  }   
                  
                  function initialize() {                     
                    myLatLong = new google.maps.LatLng(longit[0],longit[1]);
                    var mapOptions = {
                      // center: { lat:lat1 , lng:long1 },  //o bien: center: new google.maps.LatLng(-34.397, 150.644)
                        center: myLatLong,
                        zoom: 16
                                //disableDefaultUI: true // You may instead wish to turn off the API's default UI settings. To do so, set the Map's disableDefaultUI property (within the MapOptions object) to true. 
                    };
                    map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);               
                    
                }
                
                var myChart;
                function setMapaAltitudes(locations, elev){
                            //Usamos la libreria ChartJS en este caso lineas de tipo 'scatter'. http://www.chartjs.org/docs/latest/charts/line.html
                            var h, sumdist=0.0;                            
                            var distancias=[];
                            var elevaciones =[]
                            var plotdata =[]; //metemosm datos de distancias y elevaciones en un objeto literal para pasarlo luego a data
                            //var maxElevacion, minElevacion;  
                            var stout = setTimeout(function(){
                                for(h=0; h<elev.length; ++h){                                         
                                    sumdist+=locations[h][4];
                                    distancias[h]=sumdist.toFixed(2);
                                    elevaciones[h]=elev[h].toFixed(1);
                                    plotdata[h]={'x':distancias[h],'y':elevaciones[h]};                                      
                                    //console.log(elevaciones[h]+"  "+distancias[h]+" "+plotdata[h].x);
                                }
                                var maxElevacion = Math.max.apply(null,elevaciones); //Sacamos valor maximo y minimo de las elevaciones                                    
                                var minElevacion = Math.min.apply(null,elevaciones);
                                var maxEjeY;
                                var diffElev = maxElevacion - minElevacion;
                                     //Establecemos una escala para que a menor diferencia de alturas, 
                                     //el limite maximo sea menor para dar mayor sensacion de distancia plana. Y a mayor dif de alturas que se note.                                      
                                switch(true){
                                    case (diffElev >= 100 && diffElev <= 500):
                                        maxEjeY = (maxElevacion*2)-minElevacion;                                            
                                        break;
                                    case (diffElev >= 20 && diffElev <100):
                                        maxEjeY = (maxElevacion*3)-minElevacion;
                                        break;
                                    case (diffElev < 20):
                                        maxEjeY = (maxElevacion*4)-minElevacion;
                                        break;
                                    default:   //Para mayor de 500 metros
                                        maxEjeY = maxElevacion;                                                                                      
                                };      
                                /*
                                var plotdata2 =[ //prueba de carga de datos 
                                    {'x':1,'y':5},
                                    {'x':2,'y':10},
                                    {'x':3,'y':12},
                                    {'x':4,'y':15},
                                    {'x':5,'y':40}                                    
                                ];
                                */
                                //AGREGAMOS GRAFICO DE ALTURAS DEBAJO DEL MAPA                                
                                //tipo scatter:
                                var data_altitudes={
                                    datasets: 
                                    [{
                                        label: 'Grafico de Alturas',
                                        data: plotdata,
                                        fill:true,
                                        borderColor:"rgb(75, 192, 192)",
                                        lineTension:0.1,
                                        showLine:true
                                    }]                                    
                                };
                                var chartOptions={
                                    scales: {
                                        xAxes: [{
                                            type: 'linear',                                               
                                            position: 'bottom',
                                            scaleLabel: {
                                                display: true,
                                                labelString: 'Distancia (Km)'
                                            }
                                        }],
                                        yAxes:[{
                                            ticks: {
                                                //suggestedMin: 50,
                                                suggestedMax: maxEjeY //maximo del eje Y
                                            },
                                            scaleLabel: {
                                                display: true,
                                                labelString: 'Altura (metros)'
                                            }
                                        }]
                                    }
                                };                                    
                                //var ctx = document.getElementById("myChart").getContext('2d');                                    
                                var ctx= $("#myChart");   
                                //En caso de que este instanciada previamente , la destruimos y creamos una nueva. 
                                //Para ellos hay que declarar myChart de forma global. De esta forma se crean nuevas instancias
                                if (myChart) myChart.destroy(); 
                                myChart = new Chart(ctx, {
                                    type: 'scatter',
                                    data: data_altitudes ,                                       
                                    options: chartOptions
                                });                                  
                                $("#myChart").show(2000);
                            },3000);                    
                }
                
                            
                //--------------------------------------                  
                //Funciones para ver EVENTO EN DIRECTO
                //--------------------------------------
                 
                var id_Admin, email_Admin, nombre_Admin;
                function cargar_select_contactos(){                     
                        //**************
                        //CARGAMOS LOS DATOS DEL ADMINISTRADOR DEL SITIO
                        //*************
                        $.getJSON("Funciones_Web/variables_sesion_admin.php",{admin:"admin"}, function(datos){     
                            id_Admin = datos.id; //Cargamos la variable global id del administrador
                            email_Admin = datos.email;
                            nombre_Admin = datos.nombre;                            
                            var arraydatos = ["Nombre : "+datos.nombre,"Email : "+datos.email];                            
                            $("#div_cabecera strong").each(function(i){
                                $(this).text(arraydatos[i]);
                            });
                            //$("#div_logout strong").text("Bienvenido "+datos.nombre);                                    
                            
                            //***********
                            //CARGAMOS EL SELECT DE CONTACTOS NADA MAS ABRIR LA PAGINA
                            //************
                            var select_contactos = $("#contactos");                              
                            $.getJSON("Funciones_Contactos/script_web_getcontactos.php",{email:email_Admin},function (datos){
                                var error = datos.error;
                                var mensaje = datos.mensaje;          
                                select_contactos.append($("<option></option>").val(id_Admin).html(nombre_Admin+" (Administrador)"));
                                getSesiones(id_Admin); //Cargamos las sesiones del administrador
                                if(error===false){                                  
                                    //select_contactos.append("<option>Seleccione contacto: </option>");                                   
                                    for (var i=0; i<datos.nombres.length; ++i){                                                                          
                                        select_contactos.append($("<option></option>").val(datos.id[i]).html(datos.nombres[i]));                                  
                                    }                                    
                                }else{
                                    select_contactos.append($("<option></option>").val("sincontactos").html(mensaje));                                    
                                }      
                                
                            });            
                            
                            //***************
                            //CARGAMOS EL SELECT DE LAS SESIONES . Una vez seleccionado el contacto se cargan sus sesiones 
                            //***************
                                                                               
                            var select_sesion = $("#numSesion");    
                            var id_selected=id_Admin; //En caso de que no hayamos hecho ningun change en el select, pillamos la id_Admin por defecto                            
                            //select_sesion.append("<option>Seleccione sesion : </option>");
                            select_contactos.change(function(){                              
                                id_selected = $(this).val(); //Pillamos la id del contacto seleccionado para luego con el numero de sesion conseguir las coordenadas
                                //Pillamos las sesionies correspondientes al contacto seleccionado:                                
                                getSesiones(id_selected);   
                                
                            });                             
                            
                            
                           function getSesiones(id_seleccionada){                                 
                               //var primer_contact = $('#contactos option:eq(0)');   //$('.selDiv option[value="SEL1"]')  //Seleccionar una opcion particular en un select                                     
                                $.getJSON("Funciones_Sesiones/script_web_sesiones.php",{id:id_seleccionada}, function(datos){
                                  
                                    var registros = datos.registros;
                                    var fechas = datos.fechas;
                                    var sesiones = datos.sesiones;
                                    
                                    if(registros==='si'){        
                                       
                                        var cantidad = $('#numSesion').children('option').length; //o bien: //var cantidad = $('#numSesion > option').length;
                                        if(cantidad>0){
                                           //remove last or first item of a select:
                                           // $('#numSesion > option').first().remove(); // Para eliminar una opcion:  //$("#numSesion option[value='option1']").remove(); 
                                           // 
                                           //Remove multiple options in select: https://stackoverflow.com/questions/28229858/how-to-remove-multiple-options-in-a-select-tag-using-jquery
                                           //$("#selectionid option[value='option1']").remove();
                                           //$("#selectionid option[value='option2']").remove();
                                           
                                           $("#numSesion").find('option').remove(); //Esta es la mejor opcion 
                                        }                                           
                                        
                                        var x;                                        
                                        for(x=0; x<fechas.length; ++x){                                           
                                           select_sesion.append($("<option></option>").val(sesiones[x]).html(fechas[x])); //var valor = $(this).val();                                                          
                                        }        
                                    }else{
                                      //alert('todavia no hay sesiones');
                                    }
                                });                                
                           }
                                
                            //Una vez cargadas las sesiones , agregamos el evento de pulsar el boton para ver las rutas
                            var selected_sesion;                             
                            select_sesion.change(function(){                                      
                                selected_sesion = $(this).val();  //al tener puesto el select en multiple, selected_sesion seria un array de sesiones         
                                 //console.log(selected_sesion[0]);
                            });
                            $("#cargarPolylines").click(function(event){                                    
                                initArrays();
                                init_labels();
                                objBoton = $(event.target).attr("id");                                
                                if(selected_sesion.length>1){
                                    alert("solo puede seleccionar una ruta para visualizar");
                                }else{ //En caso de  haber seleccionado solo una, la visualizamos pasando el primer elemento del array                                   
                                    $.getJSON("Funciones_Coordenadas/script_web_getcoordenadas.php",{id_selected:id_selected,
                                                                                                      num_sesion:selected_sesion[0]},
                                                                                                      getCoordenadas);
                                }
                                
                            });
                            $("#ver_segmento").click(function(event){                                                
                                initArrays();
                                init_labels();
                                objBoton = $(event.target).attr("id");  
                                //Como ya tenemos todos los datos de todas las localizaciones cargados en la variable arrayLocalizaciones
                                //lo que hacemos es personalizar un array de localizaciones entre los valores inicio y final 
                                //a traves de la funcion crearArrayPuntos_InicioFin(inicio, final);                                                
                                var from = $("#segment_from").val();
                                var to = $("#segment_to").val();   
                                crearArrayPuntos_InicioFin(from, to);
                                                                   
                            });    
                            
                            //Borramos la sesion seleccionada. Solo se pueden borrar las rutas del administrador
                            $("#borrarSesion").click(function(){
                                var numses =[];
                                var id_selected= $("#contactos").val();
                                numses = $("#numSesion").val(); //Un select con opcion multiple, retorna un array con todos los valores seleccioandos
                                
                                if(id_selected === id_Admin){                                     
                                    var confirmar = confirm("Desea borrar las rutas seleccionadas ?\n"+numses);
                                    if(confirmar===true){                                        
                                        initArrays();
                                        init_labels(); //llamamos a la funcion para borrar los datos.                                        
                                        $.getJSON("Funciones_Sesiones/script_web_sesiones.php",{id_selected:id_selected,
                                                                                           num_sesion:numses},function(datos){                                                                                            
                                           // var error = datos.error;
                                            var mensaje = datos.mensaje;      
                                            alert(mensaje);                                            
                                            for(var i=0; i<numses.length;++i){
                                                $("#numSesion option[value ='"+numses[i]+"']").remove();     //eliminamos los elementos selecionados   
                                            }                                            
                                        });
                                    }                                                                                             
                                }else{
                                            alert('Solo puede borrar las rutas del Administrador '+nombre_Admin); 
                                }
                                $("#ver_segmento").prop('disabled',true); // deshabilitamos boton de ver segmento                               
                                $("#segment_from").val(" "); $("#segment_to").val(" ");                                
                                if($("#myChart").is(":visible")){//En caso de que este visible el mapa de alturas, lo borramos
                                    $("#myChart").hide(1000);
                                }
                            });
                                
                            $("#limpiarMapa,#limpiarMapa2").click(function(){
                                initArrays();
                                init_labels();
                                $("#ver_segmento").prop('disabled',true); // deshabilitamos boton de ver segmento                               
                                $("#segment_from").val(" "); $("#segment_to").val(" ");                                
                                if($("#myChart").is(":visible")){//En caso de que este visible el mapa de alturas, lo borramos
                                    $("#myChart").hide(1000);
                                }
                               
                            });                            
                            //***********************
                            //CARGAMOS EL SELECT DE LOS CONTACTOS PARA VER EVENTO EN DIRECTO:
                            //************************                    
                            
                            var select_live = $("#selectLive");
                            $.getJSON("Funciones_Contactos/script_web_getcontactos.php",{email:email_Admin}, function(datos){
                                var error = datos.error;
                                var mensaje = datos.mensaje;
                                select_live.append($("<option></option>").val(email_Admin).html(nombre_Admin+" (Administrador)"));   //Ponemos como primer email el del administrador
                                if(error===false){                                     
                                    //select_live.append("<option >Seleccione contacto: </option>");                                    
                                    for (var i=0; i<datos.nombres.length; ++i){                                                                          
                                        select_live.append($("<option></option>").val(datos.email[i]).html(datos.nombres[i]));                                  
                                    }
                                }else{
                                    select_live.append($("<option></option>").val("sincontactos").html(mensaje));
                                }                                
                            });   
                            //Una vez cargado el select, al pulsar el boton cargamos el mapa:                            
                            var email_selected; 
                            email_selected = email_Admin; 
                            select_live.change(function(){     //En caso de cambiar de opcion en el select, cambiamos el  email seleccionado   
                               email_selected = $(this).val();
                            });                            
                            
                            $(".evento").click(function(event){
                                    objBoton = $(event.target).attr("id"); //Pillamos el atributo id del boton pulsado. (podriamos haber pillado el atributo value , da igual
                                    initArrays(); 
                                    init_labels();
                                    $.getJSON("Funciones_Coordenadas/script_web_getcoordenadas.php",{email:email_selected},getCoordenadas);   
                                    $("#ver_segmento").prop('disabled',true); // deshabilitamos boton de ver segmento                               
                                    $("#segment_from").val(" "); $("#segment_to").val(" ");
                                     //Tambien quitamos el mapa de altitudes en caso de que este mostrado:
                                    if($("#myChart").is(":visible")){//En caso de que este visible el mapa de alturas, lo borramos
                                        $("#myChart").hide(1000);
                                    }
                                                              
                            });                                                       
                            function getCoordenadas(datos){
                                   
                                    var error = datos.error;
                                    var mensaje = datos.mensaje;
                                    
                                    if (error===false ){                                        
                                        //alert(datos.distancias[0][5]);                                       
                                        //var locations= crearArrayPuntos(datos);
                                        crearArrayPuntos(datos);
                                        var locations= getLocalizaciones();
                                        setInfoRutas(locations,0); //Carga de informacion, velociad , tiempo , etc
                                        if(objBoton==='cargarPolylines'){
                                            setPolylineas2(map,locations); //dibujo de rutas 
                                            $("#ver_segmento").prop('disabled',false); // habilitamos boton de ver segmento                                            
                                                                                   
                                        }                                                                           
                                        //volvemos a llamar al metodo una y otra vez en caso de un evento en directo:
                                        if(objBoton==='eventoLive'){
                                            setPolylineas2(map,locations); //dibujo de rutas   
                                            interval_timeout = window.setTimeout(function(){
                                            //var fech2=new Date();
                                            //alert("tiempo transc ajax "+(fech2-fech)); //Comprobamos que el tiempo entre llamada y llamada es de 3 segundos
                                                 $.getJSON("Funciones_Coordenadas/script_web_getcoordenadas.php",{email:email_selected},getCoordenadas); 
                                            },7000);                                           
                                        }if(objBoton==='cargarMarcad'){
                                            setMarcadores(map, locations);
                                            interval_timeout = window.setTimeout(function(){                                          
                                                 $.getJSON("Funciones_Coordenadas/script_web_getcoordenadas.php",{email:email_selected},getCoordenadas); 
                                            },7000);                                          
                                        }
                                    }else{
                                        alert(mensaje); 
                                    }    
                            }         
                              
                       });   
                 }     
                  
                 //En caso de carga inmediata de mapa: (ver manual api )
                 google.maps.event.addDomListener(window, 'load', initialize);                  
                 //Iniciamos el evento principal al cargar el naveador con 'load', Luego inciamos el resto de eventos
                 //addEvento(window,'load', iniciarEventos, false);
                 $(document).ready(iniciarEventos);                
                 
                 //En esta funcion iniciamos los eventos que queramos:
                 function iniciarEventos(){
                     //Carga de mapa en caso de querer cargarlo con un boton , es decir, que la carga sea cuando uno quiera:
                     //$("#botonCargarMapa").click(loadScript);                                     
                    //Carga de select con Contactos y sus respectivas sesiones:
                    cargar_select_contactos();                    
                 }
                           
      