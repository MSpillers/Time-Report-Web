<html>
   <head>
   </head>
   <body>
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
            echo "Enter Week Split While Loop <br>";
            while($i < 15){
                if($pay_period[0][$i] == "Saturday" && $first_day==true){
                    
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
                elseif($pay_period[0][$i] == "Saturday" && $first_day==false){
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
                    while($pay_period[0][$i] != "Saturday"){
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
            print_r($weeks);

            return $weeks;
        }

        $weeks = pay_period_weeks($payperiod);

        $_SESSION['payperiod'] = $payperiod;
        $_SESSION['weeks'] = $weeks;

        echo "Loaded <br>";
    
    ?>
    </body> 
</html>