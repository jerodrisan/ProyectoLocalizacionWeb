    
    document.addEventListener("DOMContentLoaded",()=>{
        
        btn_cerrarsesion.addEventListener("click",()=>{        
            location.href="Funciones_Web/logout.php";           
        });
        
        fetch("Funciones_Web/variables_sesion_admin.php?admin").
        then(response=>response.json()).
        then(user=>{             
            if(!user.sesion_iniciada){            
                location.href="index.html"; 
                
            }else{  //En caso de que este la sesion iniciada:
               
                btn_desenganchar.addEventListener('click',()=>{
                    email_admin.innerHTML =user.email;
                    si_desenganchar.addEventListener('click',  async ()=>{
                        
                        let response = await fetch(`Funciones_Login/script_web_desenganchar.php?email=${user.email}&tipo_login=islogged`);
                        let result = await response.json();
                        if(result){ //si hemos desenganchado
                               
                                closeOneModal('modal_desenganchar');
                                modal_desenganchado.classList.add("show");
                                modal_desenganchado.setAttribute('aria-hidden', 'false');
                                modal_desenganchado.setAttribute('style', 'display: block');
                                ok_desenganchado.onclick=function(){
                                   
                                    //closeOneModal("modal_desenganchado");
                                    modal_desenganchado.classList.remove('show');
                                    modal_desenganchado.setAttribute('aria-hidden', 'true');
                                    modal_desenganchado.setAttribute('style', 'display: none');
                                }
                        }
    
                    })
    
                })

            }
                         
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
    
