<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Pay Periods</title>
    <link rel="stylesheet" href="style.css">
    <?php


      $conn_string = "";
      $dbconn = pg_connect($conn_string);


      $payperiod_start = "";
      $active = "";
      $pp_num = 0;
      if(isset($_POST["active"])){
        $active = $_POST["active"];
      }
      if(isset($_POST["payperiod_start"])){
        $payperiod_start = strval($_POST["payperiod_start"]);
      }
      if(isset($_POST["pp_num"])){
        $pp_num = intval($_POST["pp_num"]);
      }


    

      if(($pp_num != 0)){
        $payperiods = pg_query($dbconn, "SELECT * FROM pay_period WHERE period_key={$pp_num};");
      }
      else if($payperiod_start !="")
      {
        $payperiods = pg_query($dbconn, "SELECT * FROM pay_period WHERE period_start='{$payperiod_start}';");
      }
      else if($active != "")
      {
        $activate = 't';
        $deactivate = 'f';
        pg_query($dbconn, "UPDATE pay_period 
        SET active='{$deactivate}'
        WHERE active='{$activate}';");

        pg_query($dbconn, "UPDATE pay_period 
        SET active='{$activate}'
        WHERE period_start='{$active}';");

        $payperiods = pg_query($dbconn, "SELECT * FROM pay_period WHERE period_start='{$active}';");
      }
      else{
        $payperiods = pg_query($dbconn, "SELECT * FROM pay_period WHERE period_key=null");
      }



      pg_close($dbconn);

    ?> 
  </head>
  <body>
    <h1> Query Pay Information</h1>
    <a href="/admin/home/adminhomepage.html"><button type ="button">Return to Administration Homepage</button></a>
    <br>
    <form action="" method="post">
      <h2>Edit Payperiod Information</h2>
      <div>
        <label for="active">Set the active payperiod(yyyy-dd-mm): </label>
        <input type="text" name="active" id="active" >  
      </div>
      <h2> Query the Payperiod Information</h2>
      <div>
        <label for="pp_num">Enter the payperiod number:</label>
        <input type="text" name="pp_num" id="pp_num" >
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
        <th>Period Number</th>
        <th>Period Start</th>
        <th>Period End</th>
        <th>Active</th>
        <th>Conversion Factor</th>
      </tr>
   
    <?php
    while ($row = pg_fetch_row($payperiods)) {
    ?>
    <tr>
      <td><?php print "{$row[0]}"; ?></td>
      <td><?php print "{$row[1]}"; ?></td>
      <td><?php print "{$row[2]}"; ?></td>
      <td><?php print "{$row[3]}"; ?></td>
      <td><?php print "{$row[4]}"; ?></td>
    <?php
      }
    ?>
    </table>
  </body>
</html>