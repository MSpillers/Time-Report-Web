
function default_feilds(){
    let inputs = document.getElementsByTagName('input');
            for(let i = 0; i < inputs.length;i++){
                if(inputs[i].type.toLowerCase() == 'number'){
                    inputs[i].value = 0;
                }
            }
}


default_feilds();