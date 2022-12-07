<?php 

print "Processing Login";
 //start_session
 session_start();
 // Get submitted into
 $email = $_POST["email"];
 $pass = $_POST["password"];



 //-> To External Helper Class
 //Connect to the database
 $conn_string = "";
 $dbconn = pg_connect($conn_string);


 $is_admin = pg_query($dbconn,"SELECT * FROM hrm_admins WHERE email ='{$email}' AND pass = '{$pass}'");
 if(pg_num_rows($is_admin) == 0){
   print "<br> Entered Credentials are Wrong.Please Try Again";
   header("Location: /admin/login/login.html");
}
else{
   header("Location: /admin/home/adminhomepage.html");
}


?>
<a href="/admin/login/login.html"> Back to Login</a>
