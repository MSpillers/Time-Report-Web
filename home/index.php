<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>MSU Time Report Web - Server Test Site</title>
        <link rel="stylesheet" href="index.css">
        <?php

            //start_session
            session_start();

            //-> To External Helper Class
            //Connect to the database
            $conn_string = "host=localhost port=5432 dbname=timereportweb user=postgres password=Anth3im182";
            $dbconn = pg_connect($conn_string);

            //Get the all pay periods
            $periods = pg_query($dbconn,"SELECT * FROM pay_period");
            if(!$periods){
                echo "An error occured when loading the pay period information.\n";
            }

            //Test loop that fetches all functions from the datbase.
            /*while($row = pg_fetch_row($periods)){
                echo "Pay Period Number : $row[0] Payperiod Start Date: $row[1] Payperiod End Date: $row[2]";
                echo "<br />\n";
            }
            */

            //->To external helper class 
            //Get active pay period
            $active_pay_period = pg_query($dbconn,"SELECT * FROM pay_period WHERE active=true");
            if(!$active_pay_period){
                echo "An error occured during fetching the active pay period or none of the pay periods are active.\n";
            }

            $payperiod_num = 0;
            $payperiod_str = null;
            $payperiod_end = null;

            while($row = pg_fetch_row($active_pay_period)){
                $payperiod_num = $row[0];
                $payperiod_str = $row[1];
                $payperiod_end = $row[2];
            }


            //-> To external helper class 
            function parse_date($payperiod_str,$payperiod_end){
                $payperiod_str_array = explode("-",$payperiod_str);
                $payperiod_end_array  = explode("-",$payperiod_end);

                //Test print
                //echo " PPSTARTYR: $payperiod_str_array[0] PPSTRMN: $payperiod_str_array[1] PPSTRDY: $payperiod_str_array[2]";
                //echo " PPENDYR: $payperiod_end_array[0] PPENDMN: $payperiod_end_array[1] PPENDDY: $payperiod_end_array[2]";

                $payperiod_array = array($payperiod_str_array,$payperiod_end_array);

                return $payperiod_array;
            }

            $payperiod_array = parse_date($payperiod_str,$payperiod_end);

    
            function translate_month_key($monthkey){

                //January
                if($monthkey == 1){
                    return 1;
                }
                //February
                elseif($monthkey == 2){
                    return 4;
                }
                //March 
                elseif($monthkey == 3){
                    return 4;
                } 
                //April
                elseif($monthkey == 4){
                    return 0;
                }
                //May
                elseif($monthkey == 5){
                    return 2;
                }
                //June
                elseif($monthkey == 6){
                    return 5;
                }
                //July
                elseif($monthkey == 7){
                    return 0;
                }
                //August
                elseif($monthkey == 8){
                    return 3;
                }
                //September
                elseif($monthkey == 9){
                    return 6;
                }
                //October
                elseif($monthkey == 10){
                    return 1;
                }
                //November
                elseif($monthkey == 11){
                    return 4;
                }
                else{
                    return 6;
                }
            }

            function translate_day_key($day_key){
                if($day_key == 1){
                    return "Sunday";
                }
                elseif($day_key == 2){
                    return "Monday";
                }
                elseif($day_key == 3){
                    return "Tuesday";
                }
                elseif($day_key == 4){
                    return "Wednesday";
                }
                elseif($day_key == 5){
                    return "Thursday";
                }
                elseif($day_key == 6){
                    return "Friday";
                }
                else{
                    return "Saturday";
                }

            }

            //Farmer's Almanaxc Caluclation for Finding Day from Date
            function find_day_from_date($int_pp_st_yr,$int_pp_st_mn,$int_pp_st_dy){
                $sub_factor = 0;

                if($int_pp_st_yr>= 2000 && $int_pp_st_yr <= 2099){

                    $sub_factor = 1;

                    //Take the last two digits of the year
                    $last_two_yr_digits  = $int_pp_st_yr % 2000;

                    //echo "Last two digits of date: $last_two_yr_digits <br>";

                    //Add to a quarter of those two digits(discard any remainder)
                    $day_key  = $last_two_yr_digits + (floor($last_two_yr_digits *.25));

                    //echo "Quarter of those two digits added: $day_key <br>";

                    //Add to days in the date and the month key
                    //echo "Days in date: $int_pp_st_dy <br> ";
                    $day_key = $day_key + $int_pp_st_dy + translate_month_key($int_pp_st_mn);

                    //echo "Added days in the date and month key: $day_key <br>";

                    //Subtract 1 before dividing 

                    $day_key = $day_key  - $sub_factor;

                    //Divide the sum by Seven,remainder is day of the Week
                    $day_key = $day_key % 7;

                    //echo "Divided by seven, get remainder: $day_key";

                    $day_string = translate_day_key($day_key);

                    //echo "The day calculated: $day_string <br> ";

                    $day_code_array = array($day_key,$day_string);


                    return $day_code_array;
                }
                

            }


            //-> To external helper class
            function update_day_code($day_code_array){
                $day_key = 0;
                //If the day is Friday,reset to 0
                //echo "First Day in to translation day key: $day_code_array[0] <br>";
                if($day_code_array[0] == 6){
                    //echo "Day is: $day_code_array[1] <br>";
                    $day_key = 0;
                    $day_code_array[1] = translate_day_key($day_key);
                    $day_code_array[0] = $day_key;
                    //echo "Day is updated to: $day_code_array[1] <br>";
                    return $day_code_array;
                }
                else{
                    //echo "Day is: $day_code_array[1] <br>";
                    $day_key = $day_code_array[0] + 1;
                    $day_code_array[1] = translate_day_key($day_key);
                    $day_code_array[0] = $day_key;
                    //echo "Day is updated to: $day_code_array[1] <br>";
                    return $day_code_array;
                }
            }

            //-> To external helper class
            function get_pay_period_dates($payperiod_array){
                //Pay Period Start Information
                $int_pp_st_yr = intval($payperiod_array[0][0]);
                $int_pp_st_mn = intval($payperiod_array[0][1]);
                $int_pp_st_dy = intval($payperiod_array[0][2]);

                $payperiod_dates_array = array();

                for($i = 0; $i < 15; $i++){
                    if($i == 0){
                        $str_date = "".strval($int_pp_st_mn). "-" .strval($int_pp_st_dy)."-".strval($int_pp_st_yr);
                        $payperiod_dates_array[$i] = $str_date;
                        
                    }
                    else{
                        $int_pp_st_dy = $int_pp_st_dy +1;
                        $str_date = "".strval($int_pp_st_mn)."-".strval($int_pp_st_dy)."-".strval($int_pp_st_yr);
                        
                        $payperiod_dates_array[$i] = $str_date;
                    }
                }
                
                //Testing Print Loop
                //for($i = 0; $i < sizeof($payperiod_dates_array); $i++){
                    //echo "PayPeriod Date $i: $payperiod_dates_array[$i] <br>";
                    
                //}

                return $payperiod_dates_array;
            }

            //->To external helper class
            function map_interval_to_days($payperiod_array){

                //Pay Period Start Information
                $int_pp_st_yr = intval($payperiod_array[0][0]);
                $int_pp_st_mn = intval($payperiod_array[0][1]);
                $int_pp_st_dy = intval($payperiod_array[0][2]);

                //Pay Period End Information
                $int_pp_end_yr = intval($payperiod_array[1][0]);
                $int_pp_end_mn = intval($payperiod_array[1][1]);
                $int_pp_end_dy  = intval($payperiod_array[1][2]);

                //Get the day of the week that the Pay Period Begins On.
                $day_code_array = find_day_from_date($int_pp_st_yr,$int_pp_st_mn,$int_pp_st_dy);

                //fill array with the pay period days of the week
                $payperiod_day_array  = array();
                //fill array with the pay period dates of the week
                $payperiod_dates_array = get_pay_period_dates($payperiod_array);

                //Loop until all slots are filled (15 days for each pay period)
                for($i = 0; $i < 15;$i++){
                    //Add day of week to array
                    $payperiod_day_array[$i] = $day_code_array[1];
                    //Update day code array
                    $day_code_array = update_day_code($day_code_array);

                }

                //Test print for payperiod day array
                //for($i=0;$i < sizeof($payperiod_day_array);$i++){
                    //echo "Pay Period Day $i: $payperiod_day_array[$i] <br>";
                //}

                $payperiod = array($payperiod_day_array,$payperiod_dates_array);

                return $payperiod;

            }

            $payperiod = map_interval_to_days($payperiod_array);

            function pay_period_weeks($pay_period){
                //function divides the payperiod into weeks for the purpose of calulations , specfically for Overtime,HOW-Holiday Pay, HOT-HOLIDAY Pay
    
                //first day flag variable
                $first_day = true;
                //week loopers
                //Tracks overall days
                $i = 0;
                //Tracks Weeks
                $j = 0;
                //Tracks days in sub weeks
                $k = 0;
    
                // const weeks array with 4 sub arrays
                $week_one = array(0);
                $week_two = array(0);
                $week_three = array(0);
                $week_four = array(0);
                $weeks = array($week_one,$week_two,$week_three,$week_four);
    
                //Split the pay period dates into weeks
                //echo "Enter Week Split While Loop <br>";
                while($i < 15){
                    if($pay_period[0][$i] == "Sunday" && $first_day==true){
                        
                        //Test Prints
                        //echo " First Day is Saturday, Adding to week_one sub array <br>";
                        //echo "i: $i <br>";
                        //echo "j: $j <br>";
                        //echo "k: $k <br>";
                        
                        $weeks[$j][$k] = $pay_period[1][$i];
                        $first_day = false; 
                        $k++;
                        $i++;                
                    }
                    elseif($pay_period[0][$i] == "Sunday" && $first_day==false){
                        //echo " Next Day is Saturday, beginning new week <br>";
                        $k=0;
                        $j++;
                        //echo "i: $i <br>";
                        //echo "j: $j <br>";
                        //echo "k: $k <br>";
                        $weeks[$j][$k] = $pay_period[1][$i];
                        //echo " Date addeed to new week at element $k: ".$pay_period[1][$i]."<br>";
                        $k++;
                        $i++;
                    }
                    else{
                        while($pay_period[0][$i] != "Sunday"){
                            //echo " Day is not Saturday, Adding day to current week: $j <br>";
                            //echo "i: $i <br>";
                            //echo "j: $j <br>";
                            //echo "k: $k <br>";
                            $first_day = false;
                            
                            $weeks[$j][$k] = $pay_period[1][$i];
                            $i++;
                            $k++;
                            
                            if($i > 14){
                                //echo "Finished <br>";
                                break;
                            }
                        }
                    }
                }
    
                
                //Test print for weeks array 
                //for($i=0;$i < sizeof($weeks);$i++){
                    //echo "Week $i Dates: ";
                    //for($j=0;$j < sizeof($weeks[$i]);$j++){
                        //echo "".$weeks[$i][$j]."<br>";
                    //}
                //}
                
    
                return $weeks;
            }

            $weeks = pay_period_weeks($payperiod);

            $_SESSION['payperiod'] = $payperiod;
            $_SESSION['weeks'] = $weeks;

            
            
        ?> 
    </head>
    <body>
        <h1>MSU Time Report Web - Server Test Site</h1>
        <form action="/report/generate_report.php" method="POST">
            <div id="form-top">
                <ul>
                    <li>
                        <label for="SRT-DATE">Pay Period Start Date: <?php echo"$payperiod_str";?></label>
                        <div id="SRT-PY-DATE">

                        </div>
                        <label for="END-DATE">Pay Period End Date: <?php echo "$payperiod_end";?></label>
                        <div id="END-PY-DATE"></div>
                    </li>
                    <li>
                        <label for="PNUM">Pay Period Number: <?php echo "$payperiod_num"; ?></label>
                    </li>
                    <li>
                        <label for="ID">MSU ID:</label>
                        <input type="number" id="ID" name="ID" min="1">
                    </li>
                    <li>
                        <label for="NAME">Name:</label>
                        <input type="text" id="NAME" name="NAME">
                    </li>
                    <li>
                        <label for="DEPARTMENT" >Department:</label>
                        <input type="text" id="DEPARTMENT" name="DEPARTMENT">
                    </li>
                    <li>
                        <label for="SCH-HRS">Employee Scheduled Hours Per Week:</label>
                        <input type="number" id="SCH-HRS" name="SCH-HRS" min="0">
                    </li>
                    <li>
                        <label for="RCV-COMP-TIME">Recieve Compensatory Time</label>
                    </li>
                </ul>
            </div>
            <div class="form-body" >
                <section class="Weeks">
                    <div class="Day">
                        <ul>
                            <li><label for="Day-ONE"><?php echo "".$payperiod[0][0]." ".$payperiod[1][0];?></label></li>
                            <li>
                                <label for="ACT-HOURS-1">Actual Hours Worked:</label>
                                <input type="number" id="ACT-HOURS-1" name="ACT-HOURS-1" min="0">
                            </li>
                            <li>
                                <label for="COMP-TIME-1">Compensatory Time Used:</label>
                                <input type="number" id="COMP-TIME-1" name="COMP-TIME-1" min="0">
                            </li>
                            <li>
                                <label for="HOL-1">Holiday:</label>
                                <input type="number" id="HOL-1" name="HOL-1" min="0">
                            </li>
                            <li>
                                <label for="MED-LEAVE-1">Medical Leave:</label>
                                <input type="number" id="MED-LEAVE-1" name="MED-LEAVE-1" min="0">
                            </li>
                            <li>
                                <label for="PER-LEAVE-1">Personal Leave:</label>
                                <input type="number" id="PER-LEAVE-1" name="PER-LEAVE-1" min="0">
                            </li>
                            <li>
                                <label for="JD-1">Jury Duty:</label>
                                <input type="number" id="JD-1" name="JD-1" min="0">
                            </li>
                            <li>
                                <label for="ML-1">Military Leave:</label>
                                <input type="number" id="ML-1" name="ML-1" min="0">
                            </li>
                            <li>
                                <label for="LWP-1">Leave without Pay:</label>
                                <input type="number" id="LWP-1" name="LWP-1" min="0">
                            </li>
                            <li>
                                <label for="RTO-1">RTO:</label>
                                <input type="number" id="RTO-1" name="RTO-1" min="0">
                            </li>
                            <li>
                                <label for="ACT-HOURS-ACP-1">Actual Wrk Hrs During Admin Close Period:</label>
                                <input type="number" id="ACT-HOURS-ACP-1" name="ACT-HOURS-ACP-1" min="0">
                            </li>
                            <li>
                                <label for="AD-CLP-1">Adminstrative Closing/Leave Period:</label>
                                <input type="number" id="AD-CLP-1" name="AD-CLP-1" min="0">
                            </li>
                        </ul>
                    </div>
                    <div class="Day">
                        <ul>
                            <li><label for="Day-TWO"><?php echo "".$payperiod[0][1]." ".$payperiod[1][1];?></label></li>
                            <li>
                                <label for="ACT-HOURS-2">Actual Hours Worked:</label>
                                <input type="number" id="ACT-HOURS-2" name="ACT-HOURS-2" min="0">
                            </li>
                            <li>
                                <label for="COMP-TIME-2">Compensatory Time Used:</label>
                                <input type="number" id="COMP-TIME-2" name="COMP-TIME-2" min="0">
                            </li>
                            <li>
                                <label for="HOL-2">Holiday:</label>
                                <input type="number" id="HOL-2" name="HOL-2" min="0">
                            </li>
                            <li>
                                <label for="MED-LEAVE-2">Medical Leave:</label>
                                <input type="number" id="MED-LEAVE-2" name="MED-LEAVE-2" min="0">
                            </li>
                            <li>
                                <label for="PER-LEAVE-2">Personal Leave:</label>
                                <input type="number" id="PER-LEAVE-2" name="PER-LEAVE-2" min="0">
                            </li>
                            <li>
                                <label for="JD-2">Jury Duty:</label>
                                <input type="number" id="JD-2" name="JD-2" min="0">
                            </li>
                            <li>
                                <label for="ML-2">Military Leave:</label>
                                <input type="number" id="ML-2" name="ML-2" min="0">
                            </li>
                            <li>
                                <label for="LWP-2">Leave without Pay:</label>
                                <input type="number" id="LWP-2" name="LWP-2" min="0">
                            </li>
                            <li>
                                <label for="RTO-2">RTO:</label>
                                <input type="number" id="RTO-2" name="RTO-2" min="0">
                            </li>
                            <li>
                                <label for="ACT-HOURS-ACP-2">Actual Wrk Hrs During Admin Close Period:</label>
                                <input type="number" id="ACT-HOURS-ACP-2" name="ACT-HOURS-ACP-2" min="0">
                            </li>
                            <li>
                                <label for="AD-CLP-2">Adminstrative Closing/Leave Period:</label>
                                <input type="number" id="AD-CLP-2" name="AD-CLP-2" min="0">
                            </li>
                        </ul>
                    </div>
                    <div class="Day">
                        <ul>
                            <li><label for="Day-THREE"><?php echo "".$payperiod[0][2]." ".$payperiod[1][2];?></label></li>
                            <li>
                                <label for="ACT-HOURS-3">Actual Hours Worked:</label>
                                <input type="number" id="ACT-HOURS-3" name="ACT-HOURS-3" min="0">
                            </li>
                            <li>
                                <label for="COMP-TIME-3">Compensatory Time Used:</label>
                                <input type="number" id="COMP-TIME-3" name="COMP-TIME-3" min="0">
                            </li>
                            <li>
                                <label for="HOL-3">Holiday:</label>
                                <input type="number" id="HOL-3" name="HOL-3" min="0">
                            </li>
                            <li>
                                <label for="MED-LEAVE-3">Medical Leave:</label>
                                <input type="number" id="MED-LEAVE-3" name="MED-LEAVE-3" min="0">
                            </li>
                            <li>
                                <label for="PER-LEAVE-3">Personal Leave:</label>
                                <input type="number" id="PER-LEAVE-3" name="PER-LEAVE-3" min="0">
                            </li>
                            <li>
                                <label for="JD-3">Jury Duty:</label>
                                <input type="number" id="JD-3" name="JD-3" min="0">
                            </li>
                            <li>
                                <label for="ML-3">Military Leave:</label>
                                <input type="number" id="ML-3" name="ML-3" min="0">
                            </li>
                            <li>
                                <label for="LWP-3">Leave without Pay:</label>
                                <input type="number" id="LWP-3" name="LWP-3" min="0">
                            </li>
                            <li>
                                <label for="RTO-3">RTO:</label>
                                <input type="number" id="RTO-3" name="RTO-3" min="0">
                            </li>
                            <li>
                                <label for="ACT-HOURS-ACP-3">Actual Wrk Hrs During Admin Close Period:</label>
                                <input type="number" id="ACT-HOURS-ACP-3" name="ACT-HOURS-ACP-3" min="0">
                            </li>
                            <li>
                                <label for="AD-CLP-3">Adminstrative Closing/Leave Period:</label>
                                <input type="number" id="AD-CLP-3" name="AD-CLP-3" min="0">
                            </li>
                        </ul>
                    </div>
                    <div class="Day">
                        <ul>
                            <li><label for="Day-FOUR"><?php echo "".$payperiod[0][3]." ".$payperiod[1][3];?></label></li>
                            <li>
                                <label for="ACT-HOURS-4">Actual Hours Worked:</label>
                                <input type="number" id="ACT-HOURS-4" name="ACT-HOURS-4" min="0">
                            </li>
                            <li>
                                <label for="COMP-TIME-4">Compensatory Time Used:</label>
                                <input type="number" id="COMP-TIME-4" name="COMP-TIME-4" min="0">
                            </li>
                            <li>
                                <label for="HOL-4">Holiday:</label>
                                <input type="number" id="HOL-4" name="HOL-4" min="0">
                            </li>
                            <li>
                                <label for="MED-LEAVE-4">Medical Leave:</label>
                                <input type="number" id="MED-LEAVE-4" name="MED-LEAVE-4" min="0">
                            </li>
                            <li>
                                <label for="PER-LEAVE-4">Personal Leave:</label>
                                <input type="number" id="PER-LEAVE-4" name="PER-LEAVE-4" min="0">
                            </li>
                            <li>
                                <label for="JD-4">Jury Duty:</label>
                                <input type="number" id="JD-4" name="JD-4" min="0">
                            </li>
                            <li>
                                <label for="ML-4">Military Leave:</label>
                                <input type="number" id="ML-4" name="ML-4" min="0">
                            </li>
                            <li>
                                <label for="LWP-4">Leave without Pay:</label>
                                <input type="number" id="LWP-4" name="LWP-4" min="0">
                            </li>
                            <li>
                                <label for="RTO-4">RTO:</label>
                                <input type="number" id="RTO-4" name="RTO-4" min="0">
                            </li>
                            <li>
                                <label for="ACT-HOURS-ACP-4">Actual Wrk Hrs During Admin Close Period:</label>
                                <input type="number" id="ACT-HOURS-ACP-4" name="ACT-HOURS-ACP-4" min="0">
                            </li>
                            <li>
                                <label for="AD-CLP-4">Adminstrative Closing/Leave Period:</label>
                                <input type="number" id="AD-CLP-4" name="AD-CLP-4" min="0">
                            </li>
                        </ul>
                    </div>
                    <div class="Day">
                        <ul>
                            <li><label for="Day-FIVE"><?php echo "".$payperiod[0][4]." ".$payperiod[1][4];?></label></li>
                            <li>
                                <label for="ACT-HOURS-5">Actual Hours Worked:</label>
                                <input type="number" id="ACT-HOURS-5" name="ACT-HOURS-5" min="0">
                            </li>
                            <li>
                                <label for="COMP-TIME-5">Compensatory Time Used:</label>
                                <input type="number" id="COMP-TIME-5" name="COMP-TIME-5" min="0">
                            </li>
                            <li>
                                <label for="HOL-5">Holiday:</label>
                                <input type="number" id="HOL-5" name="HOL-5" min="0">
                            </li>
                            <li>
                                <label for="MED-LEAVE-5">Medical Leave:</label>
                                <input type="number" id="MED-LEAVE-5" name="MED-LEAVE-5" min="0">
                            </li>
                            <li>
                                <label for="PER-LEAVE-5">Personal Leave:</label>
                                <input type="number" id="PER-LEAVE-5" name="PER-LEAVE-5" min="0">
                            </li>
                            <li>
                                <label for="JD-5">Jury Duty:</label>
                                <input type="number" id="JD-5" name="JD-5" min="0">
                            </li>
                            <li>
                                <label for="ML-5">Military Leave:</label>
                                <input type="number" id="ML-5" name="ML-5" min="0">
                            </li>
                            <li>
                                <label for="LWP-5">Leave without Pay:</label>
                                <input type="number" id="LWP-5" name="LWP-5" min="0">
                            </li>
                            <li>
                                <label for="RTO-5">RTO:</label>
                                <input type="number" id="RTO-5" name="RTO-5" min="0">
                            </li>
                            <li>
                                <label for="ACT-HOURS-ACP-5">Actual Wrk Hrs During Admin Close Period:</label>
                                <input type="number" id="ACT-HOURS-ACP-5" name="ACT-HOURS-ACP-5" min="0">
                            </li>
                            <li>
                                <label for="AD-CLP-5">Adminstrative Closing/Leave Period:</label>
                                <input type="number" id="AD-CLP-5" name="AD-CLP-5" min="0">
                            </li>
                        </ul>
                    </div>
                    <div class="Day">
                        <ul>
                            <li><label for="Day-SIX"><?php echo "".$payperiod[0][5]." ".$payperiod[1][5];?></label></li>
                            <li>
                                <label for="ACT-HOURS-6">Actual Hours Worked:</label>
                                <input type="number" id="ACT-HOURS-6" name="ACT-HOURS-6" min="0">
                            </li>
                            <li>
                                <label for="COMP-TIME-6">Compensatory Time Used:</label>
                                <input type="number" id="COMP-TIME-6" name="COMP-TIME-6" min="0">
                            </li>
                            <li>
                                <label for="HOL-6">Holiday:</label>
                                <input type="number" id="HOL-6" name="HOL-6" min="0">
                            </li>
                            <li>
                                <label for="MED-LEAVE-6">Medical Leave:</label>
                                <input type="number" id="MED-LEAVE-6" name="MED-LEAVE-6" min="0">
                            </li>
                            <li>
                                <label for="PER-LEAVE-6">Personal Leave:</label>
                                <input type="number" id="PER-LEAVE-6" name="PER-LEAVE-6" min="0">
                            </li>
                            <li>
                                <label for="JD-6">Jury Duty:</label>
                                <input type="number" id="JD-6" name="JD-6" min="0">
                            </li>
                            <li>
                                <label for="ML-6">Military Leave:</label>
                                <input type="number" id="ML-6" name="ML-6" min="0">
                            </li>
                            <li>
                                <label for="LWP-6">Leave without Pay:</label>
                                <input type="number" id="LWP-6" name="LWP-6" min="0">
                            </li>
                            <li>
                                <label for="RTO-6">RTO:</label>
                                <input type="number" id="RTO-6" name="RTO-6" min="0">
                            </li>
                            <li>
                                <label for="ACT-HOURS-ACP-6">Actual Wrk Hrs During Admin Close Period:</label>
                                <input type="number" id="ACT-HOURS-ACP-6" name="ACT-HOURS-ACP-6" min="0">
                            </li>
                            <li>
                                <label for="AD-CLP-6">Adminstrative Closing/Leave Period:</label>
                                <input type="number" id="AD-CLP-6" name="AD-CLP-6" min="0">
                            </li>
                        </ul>
                    </div>
                    <div class="Day">
                        <ul>
                            <li><label for="Day-SEVEN"><?php echo "".$payperiod[0][6]." ".$payperiod[1][6];?></label></li>
                            <li>
                                <label for="ACT-HOURS-7">Actual Hours Worked:</label>
                                <input type="number" id="ACT-HOURS-7" name="ACT-HOURS-7" min="0">
                            </li>
                            <li>
                                <label for="COMP-TIME-7">Compensatory Time Used:</label>
                                <input type="number" id="COMP-TIME-7" name="COMP-TIME-7" min="0">
                            </li>
                            <li>
                                <label for="HOL-7">Holiday:</label>
                                <input type="number" id="HOL-7" name="HOL-7" min="0">
                            </li>
                            <li>
                                <label for="MED-LEAVE-7">Medical Leave:</label>
                                <input type="number" id="MED-LEAVE-7" name="MED-LEAVE-7" min="0">
                            </li>
                            <li>
                                <label for="PER-LEAVE-7">Personal Leave:</label>
                                <input type="number" id="PER-LEAVE-7" name="PER-LEAVE-7" min="0">
                            </li>
                            <li>
                                <label for="JD-7">Jury Duty:</label>
                                <input type="number" id="JD-7" name="JD-7" min="0">
                            </li>
                            <li>
                                <label for="ML-7">Military Leave:</label>
                                <input type="number" id="ML-7" name="ML-7" min="0">
                            </li>
                            <li>
                                <label for="LWP-7">Leave without Pay:</label>
                                <input type="number" id="LWP-7" name="LWP-7" min="0">
                            </li>
                            <li>
                                <label for="RTO-7">RTO:</label>
                                <input type="number" id="RTO-7" name="RTO-7" min="0">
                            </li>
                            <li>
                                <label for="ACT-HOURS-ACP-7">Actual Wrk Hrs During Admin Close Period:</label>
                                <input type="number" id="ACT-HOURS-ACP-7" name="ACT-HOURS-ACP-7" min="0">
                            </li>
                            <li>
                                <label for="AD-CLP-7">Adminstrative Closing/Leave Period:</label>
                                <input type="number" id="AD-CLP-7" name="AD-CLP-7" min="0">
                            </li>
                        </ul>
                    </div>
               
        
            
                
                    <div class="Day">
                        <ul>
                            <li><label for="Day-Eight"><?php echo "".$payperiod[0][7]." ".$payperiod[1][7];?></label></li>
                            <li>
                                <label for="ACT-HOURS-8">Actual Hours Worked:</label>
                                <input type="number" id="ACT-HOURS-8" name="ACT-HOURS-8" min="0">
                            </li>
                            <li>
                                <label for="COMP-TIME-8">Compensatory Time Used:</label>
                                <input type="number" id="COMP-TIME-8" name="COMP-TIME-8" min="0">
                            </li>
                            <li>
                                <label for="HOL-8">Holiday:</label>
                                <input type="number" id="HOL-8" name="HOL-8" min="0">
                            </li>
                            <li>
                                <label for="MED-LEAVE-8">Medical Leave:</label>
                                <input type="number" id="MED-LEAVE-8" name="MED-LEAVE-8" min="0">
                            </li>
                            <li>
                                <label for="PER-LEAVE-8">Personal Leave:</label>
                                <input type="number" id="PER-LEAVE-8" name="PER-LEAVE-8" min="0">
                            </li>
                            <li>
                                <label for="JD-8">Jury Duty:</label>
                                <input type="number" id="JD-8" name="JD-8" min="0">
                            </li>
                            <li>
                                <label for="ML-8">Military Leave:</label>
                                <input type="number" id="ML-8" name="ML-8" min="0">
                            </li>
                            <li>
                                <label for="LWP-8">Leave without Pay:</label>
                                <input type="number" id="LWP-8" name="LWP-8" min="0">
                            </li>
                            <li>
                                <label for="RTO-8">RTO:</label>
                                <input type="number" id="RTO-8" name="RTO-8" min="0">
                            </li>
                            <li>
                                <label for="ACT-HOURS-ACP-8">Actual Wrk Hrs During Admin Close Period:</label>
                                <input type="number" id="ACT-HOURS-ACP-8" name="ACT-HOURS-ACP-8" min="0">
                            </li>
                            <li>
                                <label for="AD-CLP-8">Adminstrative Closing/Leave Period:</label>
                                <input type="number" id="AD-CLP-8" name="AD-CLP-8" min="0">
                            </li>
                        </ul>
                    </div>
                    <div class="Day">
                        <ul>
                            <li><label for="Day-Nine"><?php echo "".$payperiod[0][8]." ".$payperiod[1][8];?></label></li>
                            <li>
                                <label for="ACT-HOURS-9">Actual Hours Worked:</label>
                                <input type="number" id="ACT-HOURS-9" name="ACT-HOURS-9" min="0">
                            </li>
                            <li>
                                <label for="COMP-TIME-9">Compensatory Time Used:</label>
                                <input type="number" id="COMP-TIME-9" name="COMP-TIME-9" min="0">
                            </li>
                            <li>
                                <label for="HOL-9">Holiday:</label>
                                <input type="number" id="HOL-9" name="HOL-9" min="0">
                            </li>
                            <li>
                                <label for="MED-LEAVE-9">Medical Leave:</label>
                                <input type="number" id="MED-LEAVE-9" name="MED-LEAVE-9" min="0">
                            </li>
                            <li>
                                <label for="PER-LEAVE-9">Personal Leave:</label>
                                <input type="number" id="PER-LEAVE-9" name="PER-LEAVE-9" min="0">
                            </li>
                            <li>
                                <label for="JD-9">Jury Duty:</label>
                                <input type="number" id="JD-9" name="JD-9" min="0">
                            </li>
                            <li>
                                <label for="ML-9">Military Leave:</label>
                                <input type="number" id="ML-9" name="ML-9" min="0">
                            </li>
                            <li>
                                <label for="LWP-9">Leave without Pay:</label>
                                <input type="number" id="LWP-9" name="LWP-9" min="0">
                            </li>
                            <li>
                                <label for="RTO-9">RTO:</label>
                                <input type="number" id="RTO-9" name="RTO-9" min="0">
                            </li>
                            <li>
                                <label for="ACT-HOURS-ACP-9">Actual Wrk Hrs During Admin Close Period:</label>
                                <input type="number" id="ACT-HOURS-ACP-9" name="ACT-HOURS-ACP-9" min="0">
                            </li>
                            <li>
                                <label for="AD-CLP-9">Adminstrative Closing/Leave Period:</label>
                                <input type="number" id="AD-CLP-9" name="AD-CLP-9" min="0">
                            </li>
                        </ul>
                    </div>
                    <div class="Day">
                        <ul>
                            <li><label for="Day-TEN"><?php echo "".$payperiod[0][9]." ".$payperiod[1][9];?></label></li>
                            <li>
                                <label for="ACT-HOURS-10">Actual Hours Worked:</label>
                                <input type="number" id="ACT-HOURS-10" name="ACT-HOURS-10" min="0">
                            </li>
                            <li>
                                <label for="COMP-TIME-10">Compensatory Time Used:</label>
                                <input type="number" id="COMP-TIME-10" name="COMP-TIME-10" min="0">
                            </li>
                            <li>
                                <label for="HOL-10">Holiday:</label>
                                <input type="number" id="HOL-10" name="HOL-10" min="0">
                            </li>
                            <li>
                                <label for="MED-LEAVE-10">Medical Leave:</label>
                                <input type="number" id="MED-LEAVE-10" name="MED-LEAVE-10" min="0">
                            </li>
                            <li>
                                <label for="PER-LEAVE-10">Personal Leave:</label>
                                <input type="number" id="PER-LEAVE-10" name="PER-LEAVE-10" min="0">
                            </li>
                            <li>
                                <label for="JD-10">Jury Duty:</label>
                                <input type="number" id="JD-10" name="JD-10" min="0">  
                            </li>
                            <li>
                                <label for="ML-10">Military Leave:</label>
                                <input type="number" id="ML-10" name="ML-10" min="0">
                            </li>
                            <li>
                                <label for="LWP-10">Leave without Pay:</label>
                                <input type="number" id="LWP-10" name="LWP-10" min="0">
                            </li>
                            <li>
                                <label for="RTO-10">RTO:</label>
                                <input type="number" id="RTO-10" name="RTO-10" min="0">
                            </li>
                            <li>
                                <label for="ACT-HOURS-ACP-10">Actual Wrk Hrs During Admin Close Period:</label>
                                <input type="number" id="ACT-HOURS-ACP-10" name="ACT-HOURS-ACP-10" min="0">
                            </li>
                            <li>
                                <label for="AD-CLP-10">Adminstrative Closing/Leave Period:</label>
                                <input type="number" id="AD-CLP-10" name="AD-CLP-10" min="0">
                            </li>
                        </ul>
                    </div>
                    <div class="Day">
                        <ul>
                            <li><label for="Day-ELEVEN"><?php echo "".$payperiod[0][10]." ".$payperiod[1][10];?></label></li>
                            <li>
                                <label for="ACT-HOURS-11">Actual Hours Worked:</label>
                                <input type="number" id="ACT-HOURS-11" name="ACT-HOURS-11" min="0">
                            </li>
                            <li>
                                <label for="COMP-TIME-11">Compensatory Time Used:</label>
                                <input type="number" id="COMP-TIME-11" name="COMP-TIME-11" min="0">
                            </li>
                            <li>
                                <label for="HOL-11">Holiday:</label>
                                <input type="number" id="HOL-11" name="HOL-11" min="0">
                            </li>
                            <li>
                                <label for="MED-LEAVE-11">Medical Leave:</label>
                                <input type="number" id="MED-LEAVE-11" name="MED-LEAVE-11" min="0">
                            </li>
                            <li>
                                <label for="PER-LEAVE-11">Personal Leave:</label>
                                <input type="number" id="PER-LEAVE-11" name="PER-LEAVE-11" min="0">
                            </li>
                            <li>
                                <label for="JD-11">Jury Duty:</label>
                                <input type="number" id="JD-11" name="JD-11" min="0">
                            </li>
                            <li>
                                <label for="ML-11">Military Leave:</label>
                                <input type="number" id="ML-11" name="ML-11" min="0">
                            </li>
                            <li>
                                <label for="LWP-11">Leave without Pay:</label>
                                <input type="number" id="LWP-11" name="LWP-11" min="0">
                            </li>
                            <li>
                                <label for="RTO-11">RTO:</label>
                                <input type="number" id="RTO-11" name="RTO-11" min="0">
                            </li>
                            <li>
                                <label for="ACT-HOURS-ACP-11">Actual Wrk Hrs During Admin Close Period:</label>
                                <input type="number" id="ACT-HOURS-ACP-11" name="ACT-HOURS-ACP-11" min="0">
                            </li>
                            <li>
                                <label for="AD-CLP-11">Adminstrative Closing/Leave Period:</label>
                                <input type="number" id="AD-CLP-11" name="AD-CLP-11" min="0">
                            </li>
                        </ul>
                    </div>
                    <div class="Day">
                        <ul>
                            <li><label for="Day-Twelve"><?php echo "".$payperiod[0][11]." ".$payperiod[1][11];?></label></li>
                            <li>
                                <label for="ACT-HOURS-12">Actual Hours Worked:</label>
                                <input type="number" id="ACT-HOURS-12" name="ACT-HOURS-12" min="0">
                            </li>
                            <li>
                                <label for="COMP-TIME-12">Compensatory Time Used:</label>
                                <input type="number" id="COMP-TIME-12" name="COMP-TIME-12" min="0">
                            </li>
                            <li>
                                <label for="HOL-12">Holiday:</label>
                                <input type="number" id="HOL-12" name="HOL-12" min="0">
                            </li>
                            <li>
                                <label for="MED-LEAVE-12">Medical Leave:</label>
                                <input type="number" id="MED-LEAVE-12" name="MED-LEAVE-12" min="0">
                            </li>
                            <li>
                                <label for="PER-LEAVE-12">Personal Leave:</label>
                                <input type="number" id="PER-LEAVE-12" name="PER-LEAVE-12" min="0">
                            </li>
                            <li>
                                <label for="JD-12">Jury Duty:</label>
                                <input type="number" id="JD-12" name="JD-12" min="0">
                            </li>
                            <li>
                                <label for="ML-12">Military Leave:</label>
                                <input type="number" id="ML-12" name="ML-12" min="0">
                            </li>
                            <li>
                                <label for="LWP-12">Leave without Pay:</label>
                                <input type="number" id="LWP-12" name="LWP-12" min="0">
                            </li>
                            <li>
                                <label for="RTO-12">RTO:</label>
                                <input type="number" id="RTO-12" name="RTO-12" min="0">
                            </li>
                            <li>
                                <label for="ACT-HOURS-ACP-12">Actual Wrk Hrs During Admin Close Period:</label>
                                <input type="number" id="ACT-HOURS-ACP-12" name="ACT-HOURS-ACP-12" min="0">
                            </li>
                            <li>
                                <label for="AD-CLP-12">Adminstrative Closing/Leave Period:</label>
                                <input type="number" id="AD-CLP-12" name="AD-CLP-12" min="0">
                            </li>
                        </ul>
                    </div>
                    <div class="Day">
                        <ul>
                            <li><label for="Day-Thirteen"><?php echo "".$payperiod[0][12]." ".$payperiod[1][12];?></label></li>
                            <li>
                                <label for="ACT-HOURS-13">Actual Hours Worked:</label>
                                <input type="number" id="ACT-HOURS-13" name="ACT-HOURS-13" min="0">
                            </li>
                            <li>
                                <label for="COMP-TIME-13">Compensatory Time Used:</label>
                                <input type="number" id="COMP-TIME-13" name="COMP-TIME-13" min="0">
                            </li>
                            <li>
                                <label for="HOL-13">Holiday:</label>
                                <input type="number" id="HOL-13" name="HOL-13" min="0">
                            </li>
                            <li>
                                <label for="MED-LEAVE-13">Medical Leave:</label>
                                <input type="number" id="MED-LEAVE-13" name="MED-LEAVE-13" min="0">
                            </li>
                            <li>
                                <label for="PER-LEAVE-13">Personal Leave:</label>
                                <input type="number" id="PER-LEAVE-13" name="PER-LEAVE-13" min="0">
                            </li>
                            <li>
                                <label for="JD-13">Jury Duty:</label>
                                <input type="number" id="JD-13" name="JD-13" min="0">
                            </li>
                            <li>
                                <label for="ML-13">Military Leave:</label>
                                <input type="number" id="ML-13" name="ML-13" min="0">
                            </li>
                            <li>
                                <label for="LWP-13">Leave without Pay:</label>
                                <input type="number" id="LWP-13" name="LWP-13" min="0">
                            </li>
                            <li>
                                <label for="RTO-13">RTO:</label>
                                <input type="number" id="RTO-13" name="RTO-13" min="0">
                            </li>
                            <li>
                                <label for="ACT-HOURS-ACP-13">Actual Wrk Hrs During Admin Close Period:</label>
                                <input type="number" id="ACT-HOURS-ACP-13" name="ACT-HOURS-ACP-13" min="0">
                            </li>
                            <li>
                                <label for="AD-CLP-13">Adminstrative Closing/Leave Period:</label>
                                <input type="number" id="AD-CLP-13" name="AD-CLP-13" min="0">
                            </li>
                        </ul>
                    </div>
                    <div class="Day">
                        <ul>
                            <li><label for="Day-Fourteen"><?php echo "".$payperiod[0][13]." ".$payperiod[1][13];?></label></li>
                            <li>
                                <label for="ACT-HOURS-14">Actual Hours Worked:</label>
                                <input type="number" id="ACT-HOURS-14" name="ACT-HOURS-14" min="0">
                            </li>
                            <li>
                                <label for="COMP-TIME-14">Compensatory Time Used:</label>
                                <input type="number" id="COMP-TIME-14" name="COMP-TIME-14" min="0">
                            </li>
                            <li>
                                <label for="HOL-14">Holiday:</label>
                                <input type="number" id="HOL-14" name="HOL-14" min="0">
                            </li>
                            <li>
                                <label for="MED-LEAVE-14">Medical Leave:</label>
                                <input type="number" id="MED-LEAVE-14" name="MED-LEAVE-14" min="0">
                            </li>
                            <li>
                                <label for="PER-LEAVE-14">Personal Leave:</label>
                                <input type="number" id="PER-LEAVE-14" name="PER-LEAVE-14" min="0">
                            </li>
                            <li>
                                <label for="JD-14">Jury Duty:</label>
                                <input type="number" id="JD-14" name="JD-14" min="0">
                            </li>
                            <li>
                                <label for="ML-14">Military Leave:</label>
                                <input type="number" id="ML-14" name="ML-14" min="0">
                            </li>
                            <li>
                                <label for="LWP-14">Leave without Pay:</label>
                                <input type="number" id="LWP-14" name="LWP-14" min="0">
                            </li>
                            <li>
                                <label for="RTO-14">RTO:</label>
                                <input type="number" id="RTO-14" name="RTO-14" min="0">
                            </li>
                            <li>
                                <label for="ACT-HOURS-ACP-14">Actual Wrk Hrs During Admin Close Period:</label>
                                <input type="number" id="ACT-HOURS-ACP-14" name="ACT-HOURS-ACP-14" min="0">
                            </li>
                            <li>
                                <label for="AD-CLP-14">Adminstrative Closing/Leave Period:</label>
                                <input type="number" id="AD-CLP-14" name="AD-CLP-14" min="0">
                            </li>
                        </ul>
                    </div>
                    <div class="Day">
                        <ul>
                            <li><label for="Day-Fifteen"><?php echo "".$payperiod[0][14]." ".$payperiod[1][14];?></label></li>
                            <li>
                                <label for="ACT-HOURS-15">Actual Hours Worked:</label>
                                <input type="number" id="ACT-HOURS-15" name="ACT-HOURS-15" min="0">
                            </li>
                            <li>
                                <label for="COMP-TIME-15">Compensatory Time Used:</label>
                                <input type="number" id="COMP-TIME-15" name="COMP-TIME-15" min="0">
                            </li>
                            <li>
                                <label for="HOL-15">Holiday:</label>
                                <input type="number" id="HOL-15" name="HOL-15" min="0">
                            </li>
                            <li>
                                <label for="MED-LEAVE-15">Medical Leave:</label>
                                <input type="number" id="MED-LEAVE-15" name="MED-LEAVE-15" min="0">
                            </li>
                            <li>
                                <label for="PER-LEAVE-15">Personal Leave:</label>
                                <input type="number" id="PER-LEAVE-15" name="PER-LEAVE-15" min="0">
                            </li>
                            <li>
                                <label for="JD-15">Jury Duty:</label>
                                <input type="number" id="JD-15" name="JD-15" min="0">
                            </li>
                            <li>
                                <label for="ML-15">Military Leave:</label>
                                <input type="number" id="ML-15" name="ML-15" min="0">
                            </li>
                            <li>
                                <label for="LWP-15">Leave without Pay:</label>
                                <input type="number" id="LWP-15" name="LWP-15" min="0">
                            </li>
                            <li>
                                <label for="RTO-15">RTO:</label>
                                <input type="number" id="RTO-15" name="RTO-15" min="0">
                            </li>
                            <li>
                                <label for="ACT-HOURS-ACP-15">Actual Wrk Hrs During Admin Close Period:</label>
                                <input type="number" id="ACT-HOURS-ACP-15" name="ACT-HOURS-ACP-15" min="0">
                            </li>
                            <li>
                                <label for="AD-CLP-15">Adminstrative Closing/Leave Period:</label>
                                <input type="number" id="AD-CLP-15" name="AD-CLP-15" min="0">
                            </li>
                        </ul>
                    </div>
                </section>
            </div>
            <a href="/report/gen_report.html">
                <input type="submit" id="sumbit" value="submit">
            </a>
        </form>
        <script src= index.js defer></script>
       
    </body>
</html>