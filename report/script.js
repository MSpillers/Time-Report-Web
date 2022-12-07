function downloadReport() {
    var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200){
                    alert('Button Clicked!')
                }
        };
    xhttp.send();
}
