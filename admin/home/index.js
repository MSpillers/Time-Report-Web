function set_fields_required(){
    let inputs = document.getElementsByTagName('input');
    for(let i = 0; i < inputs.length;i++){
        inputs[i].required = true;
    }
}

set_fields_required();