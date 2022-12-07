<?php 
function get_db_connection(){
    //Connect to the database
    $conn_string = "";
    $dbconn = pg_connect($conn_string);

   return $dbconn;
}

?>