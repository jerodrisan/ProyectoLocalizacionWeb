


            document.addEventListener("visibilitychange",function(){
                if(document.visibilityState=='visible'){
                    d = new Date(); 
                    inter = setInterval(intervalo1,2000);
                }else if(document.visibilityState=='hidden'){
                    console.log("oculto ");
                    clearInterval(inter);

                }               
            },false);

            function intervalo1 (){                
                    ++k;
                    dif = new Date()-d;
                    console.log("tiempo transcurrido: "+ k +'sg'+ ' y en milisegundos la dif es :'+ dif+" .. y d :"+d);
                    if (k>20 ){
                        clearInterval(inter);
                    }
            }

            var inter;
            var k =0;
            var d ;
            var dif;
            
            function load_carrusel2(){
                d = new Date();             
                inter = setInterval(intervalo1,2000);
            }





            // Carrusel inventado a traves de intervalos de tiempo
            function load_carrusel(p){        
                console.log("cargando carrusel "+p); 

                if(p=="cargado"){
                   // clearInterval(si);
                }

                var time_carrusel = 4000;   //tiempo que dura cada transicion de imagen 
                var time_opacity= 100;     //tiempo que dura cada transicion de opacidad
                var num_imagenes = 5;       //numero de imagenes. 

                var divis = (time_carrusel/time_opacity);
                var arraydiv =[];

                arraydiv[0]=0;                
                for (i=1; i<divis ; ++i){                                            
                    arraydiv[i] = (1/divis)*(i);                    
                   // console.log("arrays "+arraydiv[i] );
                }    
                
                var new_arraydiv = arraydiv.reverse();
                //console.log("array longitudo total : "+ new_arraydiv.length);  

                 for (i=0; i< new_arraydiv.length ; ++i){                    
                    //console.log("arraysdivi "+ new_arraydiv[i] );
                }   

                var d = new Date();
                var i = 1, j=0;

                var si = setInterval(function(){
                    
                    var dif = new Date()-d;
                    console.log("d : "+dif+"   i: "+i);

                   document.getElementById("id-imagen").src = "img"+i+".jpg";                 
                   document.getElementById("id-imagen").style.opacity= 1;                  

                    if(dif>=(num_imagenes*time_carrusel)-1000){
                        i=0;
                        d=new Date();
                        clearInterval(si);
                    }
                    
                    var d2 = new Date();
                    var newinterv = setInterval(function(){

                        ++j;
                        var dif2 = new Date -d2;
                        console.log("diferencia2 : "+dif2 + " j: "+j +" opacity :"+ new_arraydiv[j-1] );
                        document.getElementById("id-imagen").style.opacity= new_arraydiv[j];

                        if(dif2>=time_carrusel - time_opacity ){
                            j=0; 
                            d2=new Date();                            
                            clearInterval(newinterv);
                            document.getElementById("id-imagen").src = "img"+i+".jpg";       
                        }
                    },time_opacity);
                    ++i;                                                      
                
                },time_carrusel);               
               
            }



            const deviceEvents = await Backend.getDeviceEventsByNotifications({
                category: 'acknowledgement',
                notifications: notificationIDs,
                epp: LIMIT_EPP,
                page: 1,
              }).then(async responseDeviceEvents => {
                if(responseDeviceEvents.success && responseDeviceEvents.total > LIMIT_EPP){
                  // do extra request
                  const maxPages = Math.ceil(responseDeviceEvents.total / LIMIT_EPP);
                  const devicesExtra = [];
              
                  for(let i = 2; i <= maxPages; i ++) {
                    devicesExtra.push(Backend.getDeviceEventsByNotifications({
                      category: 'acknowledgement',
                      notifications: notificationIDs,
                      epp: LIMIT_EPP,
                      page: i,
                    }))
                  }
              
                  const result = await Promise.all(devicesExtra);
                  debugger;
              
                }
                return getDataFromResponse(responseDeviceEvents, 'events');
              });