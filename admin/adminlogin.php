<?php 

print "Processing Login";
 //start_session
 session_start();
 // Get submitted into
 $email = $_POST["email"];
 $pass = $_POST["password"];



 //-> To External Helper Class
 //Connect to the database
 $conn_string = "host=localhost port=5432 dbname=timereportweb user=postgres password=Anth3im182";
 $dbconn = pg_connect($conn_string);


 $is_admin = pg_query($dbconn,"SELECT * FROM hrm_admins WHERE email ='{$email}' AND pass = '{$pass}'");
 if(!$is_admin){
    print "<br> Entered Credentials are Wrong.Please Try Again";
 }
 else{
    print "<br> Login Successful";
 }


?>
<a href="HRMAdmins.html"> Back to Login</a>
