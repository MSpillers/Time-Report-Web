<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Pay Information</title>
    <link rel="stylesheet" href="style.css">
    <?php


      $conn_string = "";
      $dbconn = pg_connect($conn_string);

      $user_id_col = "user_id";

      $user_id =0;
      $payperiod_start = "";

      if(isset($_POST["user_id"])){
        $user_id = intval($_POST["user_id"]);
      }
      if(isset($_POST["payperiod_start"])){
        $payperiod_start = strval($_POST["payperiod_start"]);
      }


      $payrecords = NULL;

      if(($user_id != 0) && ($payperiod_start == "")){
        $payrecords = pg_query($dbconn, "SELECT * FROM submitted_pay_information WHERE {$user_id_col}={$user_id};");
      }
      else if(($user_id == 0) && ($payperiod_start !=""))
      {
        $payrecords = pg_query($dbconn, "SELECT * FROM submitted_pay_information WHERE payperiod_start='{$payperiod_start}';");
      }
      else if(($user_id != 0) && ($payperiod_start !=""))
      {
        $payrecords = pg_query($dbconn, "SELECT * FROM submitted_pay_information WHERE {$user_id_col}={$user_id} AND payperiod_start='{$payperiod_start}';");
      }
      else{
        $payrecords = pg_query($dbconn, "SELECT * FROM submitted_pay_information WHERE id=null");
      }



      pg_close($dbconn);

    ?> 
  </head>
  <body>
    <h1> Query Pay Information</h1>
    <a href="/admin/home/adminhomepage.html"><button type ="button">Return to Administration Homepage</button></a>
    <br>
    <form action="" method="post" class="form-example">
      <div>
        <label for="user_id">Enter the user_id:</label>
        <input type="text" name="user_id" id="user_id" >
      </div>
      <div>
        <label for="payperiod_start">Enter the payperiod start date (yyyy-dd-mm): </label>
        <input type="text" name="payperiod_start" id="payperiod_start" >
      </div>
      <div>
        <input type="submit" value="Submit">
      </div>
    </form>
    <br>
    <table>
      <tr> 
        <th>User ID</th>
        <th>First and Last</th>
        <th>Secduled Hours</th>
        <th>Receive Comp Time</th>
        <th>User ID</th>
        <th>Pay Period Start</th>
        <th>Pay Period End</th>
        <th>AHW</th>
        <th>CTU</th>
        <th>HOL</th>
        <th>MED</th>
        <th>PER</th>
        <th>JURY</th>
        <th>MIL</th>
        <th>LEAVE</th>
        <th>ACT-ACP</th>
        <th>AD-CL-LP</th>
        <th>RTO</th>
        <th>DOC-PAY</th>
        <th>RTO-PAY</th>
        <th>OVERTIME</th>
        <th>COMP-TR</th>
        <th>HOW-HOL</th>
        <th>HOT-HOL</th>
        <th>SST-PAY</th>
        <th>Record ID</th>
      </tr>
   
    <?php
    while ($row = pg_fetch_row($payrecords)) {
    ?>
    <tr>
      <td><?php print "{$row[0]}"; ?></td>
      <td><?php print "{$row[1]}"; ?></td>
      <td><?php print "{$row[2]}"; ?></td>
      <td><?php print "{$row[3]}"; ?></td>
      <td><?php print "{$row[4]}"; ?></td>
      <td><?php print "{$row[5]}"; ?></td>
      <td><?php print "{$row[6]}"; ?></td>
      <td><?php print "{$row[7]}"; ?></td>
      <td><?php print "{$row[8]}"; ?></td>
      <td><?php print "{$row[9]}"; ?></td>
      <td><?php print "{$row[10]}"; ?></td>
      <td><?php print "{$row[11]}"; ?></td>
      <td><?php print "{$row[12]}"; ?></td>
      <td><?php print "{$row[13]}"; ?></td>
      <td><?php print "{$row[14]}"; ?></td>
      <td><?php print "{$row[15]}"; ?></td>
      <td><?php print "{$row[16]}"; ?></td>
      <td><?php print "{$row[17]}"; ?></td>
      <td><?php print "{$row[18]}"; ?></td>
      <td><?php print "{$row[19]}"; ?></td>
      <td><?php print "{$row[20]}"; ?></td>
      <td><?php print "{$row[21]}"; ?></td>
      <td><?php print "{$row[22]}"; ?></td>
      <td><?php print "{$row[23]}"; ?></td>
      <td><?php print "{$row[24]}"; ?></td>
      <td><?php print "{$row[25]}"; ?></td>
    </tr>
    <?php
      }
    ?>
    </table>
  </body>
</html>