
document.addEventListener("DOMContentLoaded",()=>{   
    fetch('Funciones_Web/variables_sesion_admin.php?admin').
        then(response=>{                
            if (response.ok){                
                return response.json();
            }else{
                alert('sin respuesta');
            }           
        }).
        then(user=>{
        //Si esta iniciada la sesion , ponemos el contenedor 3. 
            if(user.sesion_iniciada){
                container2.hidden=true;                        
                container3.hidden=false;                        
                usuSession.innerHTML = user.nombre;
            }                    
        }
        ).catch(error=>{
            alert(`Error en el servidor ${error}`);
        });        
});       



//ACCESO USUARIO REGISTRADO:
btnAcceder.addEventListener("click",async(e)=>{        
    try {            
        let response = await fetch('Funciones_Web/comprobar_acceso.php',{
            method:'POST',                
            body: new FormData(formRegistro2)
        }            
        );
        let result = await response.json();

        //Sea cual sea el resultado, cerramos el formulario:                
        fomrAccesoUsu.hidden=true;

        //Error en alguno de los campos o bien usuaro no registrado o no ha sido completado email o pass
        if(result.true==1 || result.error){                   
            subscription_confirm.style.display="block";
            subscription_confirm.className="modal show fade";
            usu_alta_result.innerHTML= result.mensaje;        
            //Reintentamos la entrada y volvemos a sacar el formulario, cerrando el modal de informacion:
            btnReintentar.addEventListener("click",()=>{

                subscription_confirm.style.display="none";
                subscription_confirm.className="modal hide fade";
                fomrAccesoUsu.hidden=false;
                formRegistro2.reset(); //reseteamos el formulario 
            });

            cerrarBotonRes.addEventListener("click",()=>{                      
                closeOneModal("subscription_confirm");            
            });                 

        }else if(result.true==0 && !result.error){
        //En este caso el usuario ha ingresado correctamente , pasamos los datos 
            let usuario_id = result.usuario_id;                
            location.href="perfil_mapa.html"; 
        }
        
        
    } catch (error) {
        // catches errors both in fetch and response.json        
        alert(`error en servidor , ${error.message}`);
    }

});


//ALTA NUEVO USUARIO:
btnEnviar.addEventListener("click",()=>{

    const  handleErrors =(response)=>{
        if (!response.ok) {
            alert("Error en servidor "+ response.statusText);
            throw Error(response.statusText);
        }            
        return response.json()
    } 
    
    
    fetch('Funciones_Web/registro.php',{
        method: 'POST',
        body: new FormData(formRegistro)
    })
    .then(handleErrors)            
    .then(result=>{                
            
        //Sea cual sea el resultado, cerramos el formulario y mostramos el modal
        fomrNuevoUsu.hidden=true;
        subscription_confirm.style.display="block";
        subscription_confirm.className="modal show fade";

        //Error en algunos de los campos
        if(result.true==1  || result.error){     
           
            usu_alta_result.innerHTML= result.mensaje;
            btnReintentar.addEventListener("click",()=>{
                subscription_confirm.style.display="none";
                subscription_confirm.className="modal hide fade";                    
                fomrNuevoUsu.hidden=false;
                formRegistro.reset();
            });

            cerrarBotonRes.addEventListener("click",()=>{                     
                closeOneModal("subscription_confirm");            
            }); 


            //Datos correctos. Ingreso de usuario
        }else if (result.true==0 && !result.error){                
            usu_alta_result.innerHTML = result.mensaje;
            btnReintentar.hidden=true;
            cerrarBotonRes.addEventListener("click",()=>{            
                closeOneModal("subscription_confirm");            
            });                     
        }
        
    }).catch(error=>{
            alert(`error en servidor ${error}`)
            });
    

});



function closeOneModal(modalId) {    //Cerrar ventana modal usando vanilla javascript (lo normal con bootstrap es usar jQuery) 
    //https://stackoverflow.com/questions/46577690/hide-bootstrap-modal-using-pure-javascript-on-click/52570205

    // get modal
    const modal = document.getElementById(modalId);

    // change state like in hidden modal
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
    modal.setAttribute('style', 'display: none');
    
    // get modal backdrop
    const modalBackdrops = document.getElementsByClassName('modal-backdrop');

    // remove opened modal backdrop
    document.body.removeChild(modalBackdrops[0]);        
}

       