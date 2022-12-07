<html>
    <head>
        <title> Generated Report </title> 
    <head> 
    <body>
        <?php
            require '';

            use PhpOffice\PhpSpreadsheet\Spreadsheet;
            use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
            
            //retrieve session info
            session_start();

            //payperiod([payperiod_day_array("Sunday","Monday",etc.),payperiod_dates_array("6-5-2022","6-15-2022",etc.)])
            $payperiod = $_SESSION['payperiod'];
            $weeks = $_SESSION['weeks'];
            

            //print "<br> Payperiod-Array";
            // print_array($payperiod);
            //print "<br> Weeks-Array";
            // print_array($weeks);

            //Payperiod conversion
            $conversion = $_SESSION['conversion'];
            //$conversion = 7.879;
            $sub_totals = get_totals_for_sub_cat();
    

            //Get subcat array
            $sub_cat_array = get_sub_cat_array();

            //Get user info array
            $user_info_array = get_user_info();


            //Calculate the Rto array
            $rto_conversions_array = calculate_rto_array($sub_cat_array,$conversion);
            // print "<br>RTO-Array";
            // print_r($rto_conversions_array);

            //Calculate the Doc pay Array
            $doc_pay_conversions_array = calculate_doc_conv_array($sub_cat_array,$conversion);
            // print "<br>DOC-Array";
            // print_r($doc_pay_conversions_array);

            //Calculate HowHoliday array
            $how_holiday_pay_array = calculate_how_holiday($sub_cat_array,$weeks);
            // print "<br>How-Holiday-Array";
            // print_r($how_holiday_pay_array);

            //Calculate HotHoliday array
            $hot_holiday_pay_array = calculate_hot_holiday($sub_cat_array, $how_holiday_pay_array, $weeks);
            // print "<br> Hot-Holiday-Array";
            // print_r($hot_holiday_pay_array);

            //Calculate the Overtime array
            $overtime_pay_array = calculate_overtime_array($sub_cat_array,$hot_holiday_pay_array,$weeks);
            // print " <br>Overtime-Array";
            // print_r($overtime_pay_array);

            //Calculate the Hours transferred to Comp Time
            $comp_time_tr_array = calculate_comp_time_tr($overtime_pay_array);
            // print "<br>Comp-Time-TR-Arry";
            // print_r($comp_time_tr_array);

            // //Calculate SST Pay for each week
            $sst_pay_array = calculate_sst_payroll_weekly_array($sub_cat_array,$weeks,$user_info_array,$overtime_pay_array,$how_holiday_pay_array,$hot_holiday_pay_array);
            // print "<br> SST Payroll Array";
            // print_r($sst_pay_array);

            //Debug 
            // $week_size_array = get_size_of_sub_week_arrays($weeks);
            // $weekly_totals = get_weekly_totals_per_day($week_size_array, $sub_cat_array, 1, 9);
            // print "<br> Weekly Act totals for day 4: {$weekly_totals} ";
            
            //Form totals for each week
            $form_totals = get_weekly_totals_for_form($sub_cat_array,$weeks,$rto_conversions_array,$doc_pay_conversions_array,$overtime_pay_array,$comp_time_tr_array,$how_holiday_pay_array,$hot_holiday_pay_array,$sst_pay_array);
            // print "<br> Form Totals Array by Week";
            // print_array($form_totals);

            //Form totals aggregate 
            $form_totals_aggr = get_form_totals_for_db($sub_cat_array,$doc_pay_conversions_array,$rto_conversions_array,$how_holiday_pay_array,$hot_holiday_pay_array,$overtime_pay_array,$comp_time_tr_array,$sst_pay_array,$weeks);
            // print "<br> Form Totals Aggregate";
            // print_array($form_totals_aggr);

            // //Export User Data to CSV and send to database
            // print "<br> Exporting to Database"; 
            $exit_code = export_to_database($user_info_array,$form_totals_aggr);


            if($exit_code == 1){
                print "<br> An error occurred when submitting payroll information, Please Alert an System Administrator.";
            }
            else{
                print "<br>  Payroll Information successfully submitted, You may close this window or return to the homepage.";
            }

            $export_result = export_csv($user_info_array, $conversion, $sub_cat_array, $form_totals_aggr,$payperiod,$weeks);
            
        ?>

        <a href="/home/landing.html"><button type="button">Back to Homepage</button></a> 
        
        
        
    <body>
<html>

<?php 


function get_db_connection(){
     //Connect to the database
     $conn_string = "";
     $dbconn = pg_connect($conn_string);

    return $dbconn;
}

function export_to_database($user_info_array,$form_totals_aggr){
    $dbconn = get_db_connection();
    $user_id = "user_id";
    $user_name = "user_name";
    //print "<br> column one {$user_id} col 2 {$user_name}";

    $activate = 't';
    $deactivate = 'f';
    $rcv_comp_time = $user_info_array[4];

    if($rcv_comp_time == "Yes")
    {
        $rcv_comp_time = $activate;
    }
    else if($rcv_comp_time == "No")
    {
        $rcv_comp_time = $deactivate;
    }
    else{
        $rcv_comp_time = $deactivate;
    }

    $results = pg_query($dbconn, "INSERT INTO submitted_pay_information({$user_id},{$user_name},user_department,user_sch_hours,rcv_comp_time,payperiod_start,payperiod_end,
    act_hrs_wrk,comp_time_used,holiday_hrs,med_hrs,per_hrs,jury_hrs,mil_hrs,leave_hrs,act_hrs_acp_hrs,ad_cl_lp_hrs,rto_hrs,doc_pay_conv,rto_pay_conv,overtime_hrs,comp_time_hrs,
    how_holiday_hrs,hot_holiday_hrs,sst_pay) 
    VALUES 
    ('{$user_info_array[0]}','{$user_info_array[1]}','{$user_info_array[2]}','{$user_info_array[3]}','{$rcv_comp_time}','{$user_info_array[5]}','{$user_info_array[6]}',{$form_totals_aggr[0]},
    {$form_totals_aggr[1]},{$form_totals_aggr[2]},{$form_totals_aggr[3]},{$form_totals_aggr[4]},{$form_totals_aggr[5]},{$form_totals_aggr[6]},{$form_totals_aggr[7]},
    {$form_totals_aggr[8]},{$form_totals_aggr[9]},{$form_totals_aggr[10]},{$form_totals_aggr[11]},{$form_totals_aggr[12]},{$form_totals_aggr[13]},
    {$form_totals_aggr[14]},{$form_totals_aggr[15]},{$form_totals_aggr[16]},{$form_totals_aggr[17]});");

    if(!$results){
        print "<br> An error occurred.";
        return 1;
    }

    pg_close($dbconn);
    return 0;

}

function toColumn($array){
    $colArray = array_chunk($array, 1);
    return $colArray;
}
function export_csv($user_data_array,$conversion,$sub_cat_array,$form_totals_aggr,$payperiod,$weeks){
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();


    //Get the dates 
    $payperiod_col = toColumn($payperiod[0]);
    $payperiod_col2 = toColumn($payperiod[1]);
    

    //Get the subcategories 
    $act_hrs_col = toColumn($sub_cat_array[1]);
    $comp_time_col = toColumn($sub_cat_array[2]);
    $holiday_col = toColumn($sub_cat_array[3]);
    $med_col = toColumn($sub_cat_array[4]);
    $per_leave = toColumn($sub_cat_array[5]);
    $jur_col = toColumn($sub_cat_array[6]);
    $mil_col = toColumn($sub_cat_array[7]);
    $lwp_col = toColumn($sub_cat_array[8]);
    $act_wrk_ad_col = toColumn($sub_cat_array[9]);
    $admin_lp_cl_col = toColumn($sub_cat_array[10]);
    $rto_col = toColumn($sub_cat_array[0]);

    //Get the totals
    $doc_col = $form_totals_aggr[11];
    $rto_conv_col = $form_totals_aggr[12];
    $overtime_col = $form_totals_aggr[13];
    $comp_tr_col = $form_totals_aggr[14];
    $how_hol_col = $form_totals_aggr[15];
    $hot_hol_col = $form_totals_aggr[16];
    $sst_col = $form_totals_aggr[17];

    // Get the column headers
    $headers = array(
        "Pay Period Days",
        "Payperiod Dates",
        "Actual Hours Worked",
        "Compensatory Time Used",
        "Holiday",
        "Medical",
        "Personal Leave",
        "Actual Worked Hours During Admin Close Period",
        "Administrative Closing/Leave Period",
        "Jury Duty",
        "Military Leave",
        "Leave Without Pay",
        "DOC-Payroll Conversion",
        "RTO",
        "RTO-Payroll Conversion",
        "Overtime Hours",
        "Hours Transferred to Comp. Time",
        "HOW-Holiday Pay or Admin/Leave Closing",
        "HOT-Holiday Pay",
        "SST-Payroll"
    );

    //Write values to sheet
    $sheet->fromArray($headers, null, 'A1');
    $sheet->fromArray($payperiod_col,null,'A2');
    $sheet->fromArray($payperiod_col2, null, 'B2');
    $sheet->fromArray($act_hrs_col, null, 'C2');
    $sheet->fromArray($comp_time_col,null,'D2');
    $sheet->fromArray($holiday_col, null, 'E2');
    $sheet->fromArray($med_col, null, 'F2');
    $sheet->fromArray($per_leave,null,'G2');
    $sheet->fromArray($act_wrk_ad_col, null, 'H2');
    $sheet->fromArray($admin_lp_cl_col, null, 'I2');
    $sheet->fromArray($jur_col,null,'J2');
    $sheet->fromArray($mil_col, null, 'K2');
    $sheet->fromArray($lwp_col, null, 'L2');
    $sheet->setCellValue('M2', $doc_col);
    $sheet->fromArray($rto_col, null, 'N2');
    $sheet->setCellValue('O2',$rto_conv_col);
    $sheet->setCellValue('P2',$overtime_col);
    $sheet->setCellValue('Q2',$comp_tr_col);
    $sheet->setCellValue('R2',$how_hol_col);
    $sheet->setCellValue('S2',$hot_hol_col);
    $sheet->setCellValue('T2',$sst_col);




    $writer = new Xlsx($spreadsheet);
    $writer->save('report.xlsx');

    return 0;
}

function convert_rcv_comp_time_bool(){
    $user_rcv_comp_time = $_POST["RCV-COMP-TIME"];
    if($user_rcv_comp_time == "Yes"){
        $user_rcv_comp_time = true;
    }
    elseif($user_rcv_comp_time == "No")
    {
        $user_rcv_comp_time = false;
    }
    else{
        $user_rcv_comp_time = false;
    }
    return $user_rcv_comp_time;
}

function get_user_info(){
    $user_id = $_POST["ID"];
    $user_sch_hrs = $_POST["SCH-HRS"];
    $user_name = $_POST["NAME"];
    $user_department = $_POST["DEPARTMENT"];
    $user_sch_hrs = $_POST["SCH-HRS"];
    $user_rcv_comp_time = $_POST["RCV-COMP-TIME"];
    $payperiod_str = $_SESSION['payperiod_str'];
    $payperiod_end = $_SESSION['payperiod_end'];
    $user_info_array = array($user_id,$user_name,$user_department,$user_sch_hrs,$user_rcv_comp_time,$payperiod_str,$payperiod_end);
    return $user_info_array;
}
function get_sub_cat_array(){
    $rto_array = array($_POST["RTO-1"],$_POST["RTO-2"],$_POST["RTO-3"],$_POST["RTO-4"],$_POST["RTO-5"],$_POST["RTO-6"],$_POST["RTO-7"],$_POST["RTO-8"],$_POST["RTO-9"],$_POST["RTO-10"],$_POST["RTO-11"],$_POST["RTO-12"],$_POST["RTO-13"],$_POST["RTO-14"],$_POST["RTO-15"]);
    $act_hours_wrk_array = array($_POST["ACT-HOURS-1"],$_POST["ACT-HOURS-2"],$_POST["ACT-HOURS-3"],$_POST["ACT-HOURS-4"],$_POST["ACT-HOURS-5"],$_POST["ACT-HOURS-6"],$_POST["ACT-HOURS-7"],$_POST["ACT-HOURS-8"],$_POST["ACT-HOURS-9"],$_POST["ACT-HOURS-10"],$_POST["ACT-HOURS-11"],$_POST["ACT-HOURS-12"],$_POST["ACT-HOURS-13"],$_POST["ACT-HOURS-14"],$_POST["ACT-HOURS-15"]);
    $comp_time_used = array($_POST["COMP-TIME-1"],$_POST["COMP-TIME-2"],$_POST["COMP-TIME-3"],$_POST["COMP-TIME-4"],$_POST["COMP-TIME-5"],$_POST["COMP-TIME-6"],$_POST["COMP-TIME-7"],$_POST["COMP-TIME-8"],$_POST["COMP-TIME-9"],$_POST["COMP-TIME-10"],$_POST["COMP-TIME-11"],$_POST["COMP-TIME-12"],$_POST["COMP-TIME-13"],$_POST["COMP-TIME-14"],$_POST["COMP-TIME-15"]);
    $holiday = array($_POST["HOL-1"],$_POST["HOL-2"],$_POST["HOL-3"],$_POST["HOL-4"],$_POST["HOL-5"],$_POST["HOL-6"],$_POST["HOL-7"],$_POST["HOL-8"],$_POST["HOL-9"],$_POST["HOL-10"],$_POST["HOL-11"],$_POST["HOL-12"],$_POST["HOL-13"],$_POST["HOL-14"],$_POST["HOL-15"]);
    $med_leave = array($_POST["MED-LEAVE-1"],$_POST["MED-LEAVE-2"],$_POST["MED-LEAVE-3"],$_POST["MED-LEAVE-4"],$_POST["MED-LEAVE-5"],$_POST["MED-LEAVE-6"],$_POST["MED-LEAVE-7"],$_POST["MED-LEAVE-8"],$_POST["MED-LEAVE-9"],$_POST["MED-LEAVE-10"],$_POST["MED-LEAVE-11"],$_POST["MED-LEAVE-12"],$_POST["MED-LEAVE-13"],$_POST["MED-LEAVE-14"],$_POST["MED-LEAVE-15"]);
    $per_leave = array($_POST["PER-LEAVE-1"],$_POST["PER-LEAVE-2"],$_POST["PER-LEAVE-3"],$_POST["PER-LEAVE-4"],$_POST["PER-LEAVE-5"],$_POST["PER-LEAVE-6"],$_POST["PER-LEAVE-7"],$_POST["PER-LEAVE-8"],$_POST["PER-LEAVE-9"],$_POST["PER-LEAVE-10"],$_POST["PER-LEAVE-11"],$_POST["PER-LEAVE-12"],$_POST["PER-LEAVE-13"],$_POST["PER-LEAVE-14"],$_POST["PER-LEAVE-15"]);
    $jury_duty = array($_POST["JD-1"],$_POST["JD-2"],$_POST["JD-3"],$_POST["JD-4"],$_POST["JD-5"],$_POST["JD-6"],$_POST["JD-7"],$_POST["JD-8"],$_POST["JD-9"],$_POST["JD-10"] ,$_POST["JD-11"],$_POST["JD-12"],$_POST["JD-13"],$_POST["JD-14"],$_POST["JD-15"]);
    $mil_duty = array($_POST["ML-1"],$_POST["ML-2"],$_POST["ML-3"],$_POST["ML-4"],$_POST["ML-5"],$_POST["ML-6"],$_POST["ML-7"],$_POST["ML-8"],$_POST["ML-9"],$_POST["ML-10"],$_POST["ML-11"],$_POST["ML-12"],$_POST["ML-13"],$_POST["ML-14"],$_POST["ML-15"]);
    $leave_wo_pay  = array($_POST["LWP-1"],$_POST["LWP-2"],$_POST["LWP-3"],$_POST["LWP-4"],$_POST["LWP-5"],$_POST["LWP-6"],$_POST["LWP-7"],$_POST["LWP-8"],$_POST["LWP-9"],$_POST["LWP-10"],$_POST["LWP-11"],$_POST["LWP-12"],$_POST["LWP-13"],$_POST["LWP-14"],$_POST["LWP-15"]);
    $act_hours_acp = array($_POST["ACT-HOURS-ACP-1"],$_POST["ACT-HOURS-ACP-2"],$_POST["ACT-HOURS-ACP-3"],$_POST["ACT-HOURS-ACP-4"],$_POST["ACT-HOURS-ACP-5"],$_POST["ACT-HOURS-ACP-6"],$_POST["ACT-HOURS-ACP-7"],$_POST["ACT-HOURS-ACP-8"],$_POST["ACT-HOURS-ACP-9"],$_POST["ACT-HOURS-ACP-10"],$_POST["ACT-HOURS-ACP-11"],$_POST["ACT-HOURS-ACP-12"],$_POST["ACT-HOURS-ACP-13"],$_POST["ACT-HOURS-ACP-14"],$_POST["ACT-HOURS-ACP-15"]);
    $ad_close_leave_period = array($_POST["AD-CLP-1"],$_POST["AD-CLP-2"],$_POST["AD-CLP-3"],$_POST["AD-CLP-4"],$_POST["AD-CLP-5"],$_POST["AD-CLP-6"],$_POST["AD-CLP-7"],$_POST["AD-CLP-8"],$_POST["AD-CLP-9"],$_POST["AD-CLP-10"],$_POST["AD-CLP-11"],$_POST["AD-CLP-12"],$_POST["AD-CLP-13"],$_POST["AD-CLP-14"],$_POST["AD-CLP-15"]);
    $sub_cat_array = array($rto_array,$act_hours_wrk_array,$comp_time_used,$holiday,$med_leave,$per_leave,$jury_duty,$mil_duty,$leave_wo_pay,$act_hours_acp,$ad_close_leave_period);
    return $sub_cat_array;
}

//function to calculate RTO by day
function calculate_rto_array($sub_cat_array,$conversion)
{
    //array to hold the calculated 
    $rto_pay_conversion_array = array();
    //Loop over the submitted RTO values and append them to the array
    $cal_rto = 0;

    // Set loop to find rto values.
    for($x=0; $x < sizeof($sub_cat_array[0]); $x++){
        if(intval($sub_cat_array[0][$x]) > 0) {
            $temp = intval($sub_cat_array[0][$x])/8;
            $cal_rto = ($temp*$conversion);
            $rto_pay_conversion_array[$x] = $cal_rto;
        }
        else{
            $rto_pay_conversion_array[$x] = 0;
        }
    }

    return $rto_pay_conversion_array;
  
}


function calculate_doc_conv_array($sub_cat_array,$conversion){
    //array to hold the calculated doc conversions
    $doc_pay_array = array();
    $doc = 0;

    for($x=0;$x < sizeof($sub_cat_array[8]);$x++){
        if(intval($sub_cat_array[8][$x]) > 0){
            $temp = intval($sub_cat_array[8][$x])/8;
            $doc = round($temp * $conversion,2);
            $doc_pay_array[$x] = $doc;
        }
        else{
            $doc_pay_array[$x] = 0;
        }
    }

    return $doc_pay_array;
    
}
//Returns array of week sizes
function get_size_of_sub_week_arrays($weeks){
    //pass in $weeks and value for week desired .Function should return an array with the submitted totals per week.

    //get the amount of days in each sub week.
    $wk_one_days = sizeof($weeks[0]);
    $wk_two_days = sizeof($weeks[1]);
    $wk_three_days = sizeof($weeks[2]);
    $wk_four_days = sizeof($weeks[3]);
  
    //return an array of week sizes
    $week_size_array = array($wk_one_days,$wk_two_days,$wk_three_days,$wk_four_days);
    //print "<br> Week-Size-Array";
    //print_r($week_size_array);
    return $week_size_array;
}
//get the week from the day
function get_week($week_size_array,$day){
    //day less than the number of days in week 1
    if($day < $week_size_array[0] && $day != $week_size_array[0]){
        return 0;
    }
    if($day >= $week_size_array[0] && $day < $week_size_array[0] + $week_size_array[1]){
        return 1;
    }
    if($day >= $week_size_array[0] + $week_size_array[1] && $day < $week_size_array[0] +$week_size_array[1] + $week_size_array[2]){
        return 2;
    }
    if($day >= $week_size_array[0] +$week_size_array[1] + $week_size_array[2]){
        return 3;
    }
}

//gets the weekly totals for a specific category
function get_weekly_totals($week_size_array,$sub_cat_array,$sub_cat,$week_num)
{
    //Week One Totals
    if($week_num== 0){
        $week_one_totals = 0;
        for($x=0;$x <$week_size_array[0];$x++){
            $act_val = intval($sub_cat_array[$sub_cat][$x]);
            $week_one_totals = $week_one_totals + $act_val;
            //print "<br> Adding Hrs Worked Value {$act_val} for day {$x} to Week One Totals";
        }
        //print "<br> Week-One-Totals: {$week_one_totals} ";
        return $week_one_totals;
    }
    //Week Two Totals
    if($week_num == 1){
        $week_two_totals = 0;
        for($x=$week_size_array[0];$x <$week_size_array[0]+ $week_size_array[1];$x++){
            $act_val = intval($sub_cat_array[$sub_cat][$x]);
            $week_two_totals = $week_two_totals + intval($sub_cat_array[$sub_cat][$x]);
            //print "<br> Adding Hrs Worked Value {$act_val} for day {$x} to Week Two Totals";
        }
        //print "<br> Week-One-Totals: {$week_two_totals} ";
        return $week_two_totals;
    }
    if($week_num == 2){
        $week_three_totals = 0;
        //Check if days accounted for has already reached 15
        if($x=$week_size_array[0]+$week_size_array[1] == 15){
            return $week_three_totals;
        }
        else{
            //Loop over the subcat array for Week Three Totals
            for($x=$week_size_array[0]+$week_size_array[1];$x <$week_size_array[0]+ $week_size_array[1] + $week_size_array[2];$x++){
                $act_val = intval($sub_cat_array[$sub_cat][$x]);
                $week_three_totals = $week_three_totals + intval($sub_cat_array[$sub_cat][$x]);
                //print "<br> Adding Hrs Value {$act_val} for day {$x} to Week Three Totals";
            }
            //print "<br> Week-Three-Totals: {$week_three_totals} ";
            return $week_three_totals;
        }
    }
    if($week_num == 3){
        $week_four_totals = 0;
        
        //Check if the days accounted for has already reached 15
        if($week_size_array[0]+$week_size_array[1]+$week_size_array[2] == 15){
            $week_four_totals = 0;
            return $week_four_totals;
            
        }
        else{
            //Loop over the subcat array to get Week Four Totals
            for($x=$week_size_array[0]+$week_size_array[1]+$week_size_array[2];$x <$week_size_array[0]+ $week_size_array[1] + $week_size_array[2]+$week_size_array[3];$x++){
                $act_val = intval($sub_cat_array[$sub_cat][$x]);
                $week_four_totals = $week_four_totals + intval($sub_cat_array[$sub_cat][$x]);
                //print "<br> Adding Act Hrs Worked Value {$act_val} for day {$x} to Week Four Totals";
            }
            //print "<br> Week-Three-Totals: {$week_four_totals} ";
            return $week_four_totals;
        }
    }
}

//Get the totals for a week for a specific day and specific category
function get_weekly_totals_per_day($week_size_array,$sub_cat_array,$sub_cat,$day,$day_offset){
    $week_num = get_week($week_size_array,$day);
    //print "<br> Day {$day} is in WeekNum {$week_num}";
    //Day in Week One Totals
    if($week_num == 0){
        $day_totals= 0;
        //$week_one_offset = intval($sub_cat_array[$sub_cat][$week_size_array[0]-$week_size_array[0]]);
        
        for($x=0;$x <$day + $day_offset;$x++){
            $act_val = intval($sub_cat_array[$sub_cat][$x]);
            $day_totals = $day_totals + $act_val;
            print "<br> Adding Hrs Worked Value {$act_val} for day {$x} to Day Totals";
        }
        print "<br> Day-Totals: {$day_totals} ";
        return $day_totals;//+ $week_one_offset;
    }
    // Day in Week Two Totals
    if($week_num == 1){
        $day_totals = 0;
        //$week_two_offset = intval($sub_cat_array[$sub_cat][$week_size_array[0]]);
        $day_five_offset = intval($sub_cat_array[$sub_cat][4]);
        for($x=$week_size_array[0];$x <$day + $day_offset;$x++){ 
            $act_val = intval($sub_cat_array[$sub_cat][$x]);
            $day_totals = $day_totals + intval($sub_cat_array[$sub_cat][$x]);
            //print "<br> Adding Hrs Worked Value {$act_val} for day {$x} to Day Totals";
        }
        //print "<br> Day-Totals: {$day_totals} ";
        return $day_totals;//+ $week_two_offset;
    }
    //Day in Week Three Totals
    if($week_num == 2){
        $day_totals = 0;
        $week_three_offset = intval($sub_cat_array[$sub_cat][$week_size_array[0]+$week_size_array[1]]);
        //Check if days accounted for has already reached 15
        if($x=$week_size_array[0]+$week_size_array[1] == 15){
            return $day_totals;
        }
        else{
            //Loop over the subcat array for Week Three Totals
            for($x=$week_size_array[0]+$week_size_array[1];$x <$day+$day_offset;$x++){
                $act_val = intval($sub_cat_array[$sub_cat][$x]);
                $day_totals = $day_totals + intval($sub_cat_array[$sub_cat][$x]);
                //print "<br> Adding Hrs Worked Value {$act_val} for day {$x} to Week Three Totals";
            }
            //print "<br> Week-Three-Totals: {$day_totals} ";
            return $day_totals;
        }
    }
    //Day in Week Four Totals
    if($week_num == 3){
        $day_totals = 0;
        
        //Check if the days accounted for has already reached 15
        if($week_size_array[0]+$week_size_array[1]+$week_size_array[2] == 15){
            $day_totals = 0;
            return $day_totals;
            
        }
        else{
            //Loop over the subcat array to get Week Four Totals
            for($x=$week_size_array[0]+$week_size_array[1]+$week_size_array[2];$x <$day+$day_offset;$x++){
                $act_val = intval($sub_cat_array[$sub_cat][$x]);
                $day_totals = $day_totals + intval($sub_cat_array[$sub_cat][$x]);
                //print "<br> Adding Act Hrs Worked Value {$act_val} for day {$x} to Week Three Totals";
            }
            //print "<br> Week-Three-Totals: {$day_totals} ";
            return $day_totals;
        }
    }

}


function calculate_how_holiday($sub_cat_array,$weeks)
{
    //print "<br> In cal how holiday";
    //array to hold the calulated how holiday values 
    $how_holiday_pay_array = array();
    $how_holiday = 0;
    

    for($x=0;$x <sizeof($sub_cat_array[3]);$x++){
        //check for the holiday flag, or just that holiday hours were submitted
        if($sub_cat_array[1][$x] > 0 && $sub_cat_array[3][$x] > 0){
            //print "<br> Act Hours Worked for day {$x}:{$sub_cat_array[1][$x]}";
            //print "<br> Holiday Hours for Day {$x}:{$sub_cat_array[3][$x]}";

            //Check for which week the day being calulated is in
            $week_size_array = get_size_of_sub_week_arrays($weeks);
            //print "<br> Week Size Array: ";
            //print_r($week_size_array);
            $week_num = get_week($week_size_array,$x);
            //print "<br> Week Num for day {$x}: {$week_num}";

            //Get totals for Act_Hours_Worked up until that day but not including that day;day offset is 0
            $act_hrs_totals = get_weekly_totals_per_day($week_size_array,$sub_cat_array,1,$x,0);
            //print "<br> Act_hrs_wrked totals for week {$week_num} until day {$x}: {$act_hrs_totals}";


            //Check if 40 hours minus the sum of the actual hours worked until that day is greater than and less that eight
            if((40 - $act_hrs_totals) > 0 && (40 - $act_hrs_totals) < 8)
            {
                $how_holiday_pay_array[$x] = 40 - $act_hrs_totals;
            }
            else if($sub_cat_array[1][$x] < $sub_cat_array[3][$x]){
                $how_holiday_pay_array[$x] = $sub_cat_array[1][$x];
            }
            else if($sub_cat_array[1][$x] > $sub_cat_array[3][$x]){
                $how_holiday_pay_array[$x] = $sub_cat_array[3][$x];
            }
            else if($sub_cat_array[1][$x] == $sub_cat_array[3][$x]){
                $how_holiday_pay_array[$x] = $sub_cat_array[1][$x];
            }
            else{
                $how_holiday_pay_array[$x] = 0;
            }
            //Check if the hours worked for the week is less than 40 hours
            //if(40 - $act_hrs_totals > 0 && 40 - $act_hrs_totals < 8){
                //$how_holiday = 40 - $act_hrs_totals;
                //$how_holiday_pay_array = $how_holiday;
            //}else{
                //$how_holiday_pay_array[$x] = 0;
            //}
        }
        else{
            $how_holiday_pay_array[$x] = 0;
        }

    }

    return $how_holiday_pay_array;
}

function calculate_hot_holiday($sub_cat_array,$how_holiday_pay_array,$weeks){
    //print "<br> In calculate HOT-Holiday";
    //Array to store the calculated hot holiday
    $hot_holiday_array = array();

    //Get the size of the weeks
    $week_size_array = get_size_of_sub_week_arrays($weeks);

    for ($x = 0; $x < sizeof($sub_cat_array[3]);$x++){
        //Check if holiday flag is set (Checking if there are holiday days)
        if($sub_cat_array[3][$x] > 0){
            //print "<br> Holiday Flag Set";
            //Get the totals of the  actual hours worked up until that day
            $act_hours_totals = get_weekly_totals_per_day($week_size_array, $sub_cat_array,1,$x,1);
            //print "<br> ACT Hours worked totals for day {$x}: {$act_hours_totals}";

            //Get the act hours worked for that day
            $act_hours_for_day = $sub_cat_array[1][$x];
            //print "<br> ACT Hours worked for day {$x}: {$act_hours_for_day}";
            //Get the holiday hours worked for that day
            $holiday_hours_for_day = $sub_cat_array[3][$x];
            //print "<br> Holiday Hours for day {$x}: {$holiday_hours_for_day}";

            //Check if the totals are greater than 40 and that the holiday hours for that specific day is greater than the actual hours worked for that day
            //if so, subtract the act hours worked for that day by the how holiday pay for that day
            if(($act_hours_totals > 40) && ($act_hours_for_day < $holiday_hours_for_day)){
                //print "<br> Act totals over 40 and act for day is less than holiday hours";
                $hot_holiday_array[$x] = $act_hours_for_day - $how_holiday_pay_array[$x];
            }
            //Else, Check if the act hours totals are greater than 10 and that the act hours are greater than or equal to the holiday hours for that day
            //if so, subtract the holiday hours for that day by the actual hours totals
            else if(($act_hours_totals>0) && ($act_hours_for_day >= $holiday_hours_for_day)){
                //print "<br> Act totals over 0 and act for day is greater than  or equal to holiday hours";
                $hot_holiday_array[$x] = $holiday_hours_for_day - $how_holiday_pay_array[$x];
            }
            else{
                //print "<br> something broke";
                $hot_holiday_array[$x] = 0;
            }

        }
        else{
            //print "<br> Holiday Flag not set";
            $hot_holiday_array[$x] = 0;
        }
    
    }
    return $hot_holiday_array;
    
}

function get_weekly_totals_special_array($calculated_array,$weeks){
    //get the size of the weeks
    $week_size_array = get_size_of_sub_week_arrays($weeks);

    //Variables to store the totals for each week
    $week_one_totals = 0;
    $week_two_totals = 0;
    $week_three_totals = 0;
    $week_four_totals = 0; 

    //get the week one totals
    for($x=0;$x < $week_size_array[0];$x++)
    {
        $week_one_totals = $week_one_totals + $calculated_array[$x];
    }
    //get the week two totals
    for($x=$week_size_array[0]; $x < $week_size_array[0] + $week_size_array[1];$x++){
        $week_two_totals = $week_two_totals + $calculated_array[$x];
    }
    //Check if the sizes of the weeks has already reached 15
    if(($week_size_array[0] + $week_size_array[1]) < 14 ){
        //get the week three totals
        for($x=$week_size_array[0] + $week_size_array[1];$x < $week_size_array[0] + $week_size_array[1] + $week_size_array[2];$x++)
        {
            $week_three_totals = $week_three_totals + $calculated_array[$x];
        }
    }
    //Check if the sizes of the weeks has already reached 15
    if(($week_size_array[0] + $week_size_array[1] + $week_size_array[2]) < 14 ){
        //get the week four totals
        for($x=$week_size_array[0] + $week_size_array[1] + $week_size_array[2];$x < $week_size_array[0] + $week_size_array[1] + $week_size_array[2] + $week_size_array[3];$x++)
        {
            $week_four_totals = $week_four_totals + $calculated_array[$x];
        }
    }

    $calculated_array_totals = array($week_one_totals, $week_two_totals, $week_three_totals, $week_four_totals);
    return $calculated_array_totals;
}

function calculate_overtime_array($sub_cat_array,$hot_holiday_pay_array,$weeks){
    $overtime_array = array();

    //get week size array
    $week_size_array = get_size_of_sub_week_arrays($weeks);

    //get weekly totals of act hours worked for each week
    $week_one_act_hrs = get_weekly_totals($week_size_array,$sub_cat_array,1,0);
    //print "<br> Week One Hours (Overtime): {$week_one_act_hrs}";
    $week_two_act_hrs = get_weekly_totals($week_size_array,$sub_cat_array,1,1);
    //print "<br> Week Two Hours (Overtime): {$week_two_act_hrs}";
    $week_three_act_hrs = get_weekly_totals($week_size_array,$sub_cat_array,1,2);
    //print "<br> Week Three Hours (Overtime): {$week_three_act_hrs}";
    $week_four_act_hrs = get_weekly_totals($week_size_array,$sub_cat_array,1,3);
    //print "<br> Week Four Hours (Overtime): {$week_four_act_hrs}";

    //Get weekly totals of hot_holiday_pay
    $week_one_hot_holiday= 0;
    $week_two_hot_holiday= 0;
    $week_three_hot_holiday= 0;
    $week_four_hot_holiday= 0;

    //variables to hold weekly overtime hours
    $week_one_overtime = 0;
    $week_two_overtime = 0;
    $week_three_overtime = 0;
    $week_four_overtime = 0;

    //get the weekly totals for the hot-holiday pay
    $hot_holiday_pay_weekly_totals = get_weekly_totals_special_array($hot_holiday_pay_array, $weeks);

    //calculate overtime per week
    if($week_one_act_hrs > 40)
    {
        $week_one_overtime = ($week_one_act_hrs - 40) - $hot_holiday_pay_weekly_totals[0];
    }
    if($week_two_act_hrs > 40)
    {
        $week_two_overtime = ($week_two_act_hrs- 40) - $hot_holiday_pay_weekly_totals[1];
    }
    if($week_three_act_hrs > 40)
    {
        $week_three_overtime = ($week_three_act_hrs- 40) - $hot_holiday_pay_weekly_totals[2];
    }
    if($week_four_act_hrs > 40)
    {
        $week_four_overtime = ($week_four_act_hrs- 40) - $hot_holiday_pay_weekly_totals[3];
    }

    $overtime_array = array($week_one_overtime,$week_two_overtime,$week_three_overtime,$week_four_overtime);
    return $overtime_array;
}
function floor2nearest($number, $decimal) {
    return floor($number / $decimal) * $decimal;
}


function calculate_comp_time_tr($overtime_array){
    $comp_time_tr = array();

    //Calculate comp times
    for($x=0;$x < sizeof($overtime_array);$x++){
        if($overtime_array[$x] > 0){
            $comp_time_tr_temp = ($overtime_array[$x] * 1.50);
            $comp_time_tr_temp = floor2nearest($comp_time_tr_temp,.25);
            $comp_time_tr[$x] = $comp_time_tr_temp;
        }
        else {
            $comp_time_tr[$x] = 0;
        }
    }
    
    return $comp_time_tr;
}



function get_how_holiday_by_week($how_holiday_pay_array,$week_size_array,$week_num){
    //print "<br> Day {$day} is in WeekNum {$week_num}";
    //Day in Week One Totals
    if($week_num == 0){
        $day_totals= 0;
        //$day_one_offset = intval($how_holiday_pay_array[0]);
        for($x=0;$x < $week_size_array[0];$x++){
            $act_val = intval($how_holiday_pay_array[$x]);
            $day_totals = $day_totals + $act_val;
            //print "<br> Adding Act Hrs Worked Value {$act_val} for day {$x} to Day Totals";
        }
        //print "<br> Day-Totals: {$day_totals} ";
        return $day_totals ;//+ $day_one_offset;
    }
    // Day in Week Two Totals
    if($week_num == 1){
        $day_totals = 0;
        //$day_five_offset = intval($how_holiday_pay_array[4]);
        for($x=$week_size_array[0];$x <$week_size_array[0] + $week_size_array[1] ;$x++){ 
            $act_val = intval($how_holiday_pay_array[$x]);
            $day_totals = $day_totals + intval($how_holiday_pay_array[$x]);
            //print "<br> Adding Act Hrs Worked Value {$act_val} for day {$x} to Day Totals";
        }
        //print "<br> Day-Totals: {$day_totals} ";
        return $day_totals;// + $day_five_offset;
    }
    //Day in Week Three Totals
    if($week_num == 2){
        $day_totals = 0;
        //Check if days accounted for has already reached 15
        if($x=$week_size_array[0]+$week_size_array[1] == 15){
            return $day_totals;
        }
        else{
            //Loop over the subcat array for Week Three Totals
            for($x=$week_size_array[0]+$week_size_array[1];$x <$week_size_array[0] + $week_size_array[1] + $week_size_array[3];$x++){
                $act_val = intval($how_holiday_pay_array[$x]);
                $day_totals = $day_totals + intval($how_holiday_pay_array[$x]);
                //print "<br> Adding Act Hrs Worked Value {$act_val} for day {$x} to Week Three Totals";
            }
            //print "<br> Week-Three-Totals: {$day_totals} ";
            return $day_totals;
        }
    }
    //Day in Week Four Totals
    if($week_num == 3){
        $day_totals = 0;
        
        //Check if the days accounted for has already reached 15
        if($week_size_array[0]+$week_size_array[1]+$week_size_array[2] == 15){
            $day_totals = 0;
            return $day_totals;
            
        }
        else{
            //Loop over the subcat array to get Week Four Totals
            for($x=$week_size_array[0]+$week_size_array[1]+$week_size_array[2];$x <$week_size_array[0] + $week_size_array[1] + $week_size_array[3] + $week_size_array[4];$x++){
                $act_val = intval($how_holiday_pay_array[$x]);
                $day_totals = $day_totals + intval($how_holiday_pay_array[$x]);
                //print "<br> Adding Act Hrs Worked Value {$act_val} for day {$x} to Week Three Totals";
            }
            //print "<br> Week-Three-Totals: {$day_totals} ";
            return $day_totals;
        }
    }
}

function calculate_sst_payroll_weekly_array($sub_cat_array,$weeks,$user_info_array,$overtime_pay_array,$how_holiday_pay_array,$hot_holiday_pay_array){
    //get the week size array
    $week_size_array = get_size_of_sub_week_arrays($weeks);
    $sch_hrs = $user_info_array[3];

    //Array to hold calculated SST values
    $sst_values_array = array(); 
    //for loop to calculate the SST for each week
    for($x=0;$x < sizeof($week_size_array);$x++){
        //Get the necessary calculation values
        $act_hours = get_weekly_totals($week_size_array,$sub_cat_array,1,$x);
        //print "<br> ACT Hours for week {$x}: {$act_hours}";
        $comp_time_used_hrs = get_weekly_totals($week_size_array,$sub_cat_array,2,$x);
        //print "<br> Comp Hours for week {$x}: {$comp_time_used_hrs}";
        $holiday_hrs = get_weekly_totals($week_size_array,$sub_cat_array,3,$x);
        //print "<br> Holiday Hours for week {$x}: {$holiday_hrs}";
        $med_hrs = get_weekly_totals($week_size_array,$sub_cat_array,4,$x);
        //print "<br> Medical Hours for week {$x}: {$med_hrs}";
        $per_hrs = get_weekly_totals($week_size_array,$sub_cat_array,5,$x);
        //print "<br> Personal Hours for week {$x}: {$per_hrs}";
        $jury_hrs = get_weekly_totals($week_size_array,$sub_cat_array,6,$x);
        //print "<br> Jury Hours for week {$x}: {$jury_hrs}";
        $mil_hrs = get_weekly_totals($week_size_array,$sub_cat_array,7,$x);
        //print "<br> Military Hours for week {$x}: {$mil_hrs}";
        $ad_close_leave_period_hrs = get_weekly_totals($week_size_array,$sub_cat_array,10,$x);
        //print "<br> Ad Close Hours for week {$x}: {$ad_close_leave_period_hrs}";
        
        //get overtime,how_holiday,hot_holiday
        $overtime_hrs = $overtime_pay_array[$x];
        $how_holiday_hrs = get_how_holiday_by_week($how_holiday_pay_array,$week_size_array,$x);
        //print "<br> How Holiday hours for week {$x}: {$ad_close_leave_period_hrs}";
        $hot_holiday_pay_weekly_totals = get_weekly_totals_special_array($hot_holiday_pay_array,$weeks);
        

        //Subtract above values,overtime hrs,how_holiday, and hot_holiday_hrs from the sceduled hours
        $SST = ($act_hours -(intval($sch_hrs)-($holiday_hrs+$ad_close_leave_period_hrs+$comp_time_used_hrs+$med_hrs+$per_hrs+$jury_hrs+$mil_hrs)) - ($overtime_hrs+$how_holiday_hrs+$hot_holiday_pay_weekly_totals[$x]));
        if($SST > 0){
            $sst_values_array[$x] = $SST;
        }
        else{
            $sst_values_array[$x] = 0;
        }
    }
    return $sst_values_array;
}

function get_weekly_totals_for_form($sub_cat_array,$weeks,$doc_pay_conversions_array,$rto_conversions_array,$overtime_pay_array,$comp_time_tr_array,$how_holiday_pay_array,$hot_holiday_pay_array,$sst_pay_array){
    //Get the Week Size
    $weeks_size_array = get_size_of_sub_week_arrays($weeks);
    //array to hold all submitted information and calculated information
    $week_one_totals = array();
    $week_two_totals = array();
    $week_three_totals = array();
    $week_four_totals = array();
    $form_totals = array($week_one_totals,$week_two_totals,$week_three_totals,$week_four_totals);

    //Loop, gathering the totals for each week
    for($x=0; $x < sizeof($form_totals);$x++){
        //Tweleve user submitted fields
        $act_hrs_wrk = get_weekly_totals($weeks_size_array,$sub_cat_array,1,$x);
        $comp_time_used = get_weekly_totals($weeks_size_array,$sub_cat_array,2,$x);
        $holiday_hrs = get_weekly_totals($weeks_size_array,$sub_cat_array,3,$x);
        $med_hrs = get_weekly_totals($weeks_size_array,$sub_cat_array,4,$x);
        $per_hrs = get_weekly_totals($weeks_size_array,$sub_cat_array,5,$x);
        $jury_hrs = get_weekly_totals($weeks_size_array,$sub_cat_array,6,$x);
        $mil_hrs = get_weekly_totals($weeks_size_array,$sub_cat_array,7,$x);
        $leave_hrs = get_weekly_totals($weeks_size_array,$sub_cat_array,8,$x);
        $act_hrs_acp_hrs = get_weekly_totals($weeks_size_array,$sub_cat_array,9,$x);
        $admin_cl_lp_hrs = get_weekly_totals($weeks_size_array,$sub_cat_array,10,$x);
        $rto_hrs = get_weekly_totals($weeks_size_array,$sub_cat_array,0,$x);

        //Calculated feilds - Doc Pay, Rto Coversions, Overtime, Comptime Transferred,How Holiday,SST
        $doc_payroll_conversion = get_weekly_totals_special_array($doc_pay_conversions_array,$weeks);
        $rto_payroll_conversion = get_weekly_totals_special_array($rto_conversions_array,$weeks);
        $overtime_hrs = $overtime_pay_array[$x];
        $comp_time_tr = $comp_time_tr_array[$x];
        $how_holiday_hrs = get_how_holiday_by_week($how_holiday_pay_array,$weeks_size_array,$x);
        $hot_holiday_hrs = get_weekly_totals_special_array($hot_holiday_pay_array,$weeks);
        $sst_hrs = $sst_pay_array[$x];

        //combine into the array
        $form_totals[$x] = [$act_hrs_wrk,$comp_time_used,$holiday_hrs,$med_hrs,$per_hrs,$jury_hrs,$mil_hrs,$leave_hrs,$act_hrs_acp_hrs,
        $admin_cl_lp_hrs,$rto_hrs,$doc_payroll_conversion[$x],$rto_payroll_conversion[$x],$overtime_hrs,$comp_time_tr,$how_holiday_hrs,$hot_holiday_hrs[$x],$sst_hrs];
        
    }
    return $form_totals; 
}
function get_form_totals_for_db($sub_cat_array,$doc_pay_conversions_array,$rto_conversions_array,$how_holiday_pay_array,$hot_holiday_pay_array,$overtime_pay_array,$comp_time_tr_array,$sst_pay_array,$weeks){
    
    $weeks_size_array = get_size_of_sub_week_arrays($weeks);

    $act_hrs_wrk_totals = get_weekly_totals($weeks_size_array,$sub_cat_array,1,0) + get_weekly_totals($weeks_size_array,$sub_cat_array,1,1)+get_weekly_totals($weeks_size_array,$sub_cat_array,1,2)+get_weekly_totals($weeks_size_array,$sub_cat_array,1,3);
    $comp_time_used_totals = get_weekly_totals($weeks_size_array,$sub_cat_array,2,0) + get_weekly_totals($weeks_size_array,$sub_cat_array,2,1) + get_weekly_totals($weeks_size_array,$sub_cat_array,2,2) + get_weekly_totals($weeks_size_array,$sub_cat_array,2,3); 
    $holiday_hrs_totals = get_weekly_totals($weeks_size_array,$sub_cat_array,3,0) + get_weekly_totals($weeks_size_array,$sub_cat_array,3,1) + get_weekly_totals($weeks_size_array,$sub_cat_array,3,2) + get_weekly_totals($weeks_size_array,$sub_cat_array,3,3);
    $med_hrs_totals = get_weekly_totals($weeks_size_array,$sub_cat_array,4,0) + get_weekly_totals($weeks_size_array,$sub_cat_array,4,1) + get_weekly_totals($weeks_size_array,$sub_cat_array,4,2) + get_weekly_totals($weeks_size_array,$sub_cat_array,4,3);
    $per_hrs_totals = get_weekly_totals($weeks_size_array,$sub_cat_array,5,0) + get_weekly_totals($weeks_size_array,$sub_cat_array,5,1) + get_weekly_totals($weeks_size_array,$sub_cat_array,5,2) + get_weekly_totals($weeks_size_array,$sub_cat_array,5,3);
    $jury_hrs_totals = get_weekly_totals($weeks_size_array,$sub_cat_array,6,0) + get_weekly_totals($weeks_size_array,$sub_cat_array,6,1) + get_weekly_totals($weeks_size_array,$sub_cat_array,6,2) + get_weekly_totals($weeks_size_array,$sub_cat_array,6,3);
    $mil_hrs_totals = get_weekly_totals($weeks_size_array,$sub_cat_array,7,0) + get_weekly_totals($weeks_size_array,$sub_cat_array,7,1) + get_weekly_totals($weeks_size_array,$sub_cat_array,7,2) + get_weekly_totals($weeks_size_array,$sub_cat_array,7,3);
    $leave_hrs_totals = get_weekly_totals($weeks_size_array,$sub_cat_array,8,0) + get_weekly_totals($weeks_size_array,$sub_cat_array,8,1) + get_weekly_totals($weeks_size_array,$sub_cat_array,8,2) + get_weekly_totals($weeks_size_array,$sub_cat_array,8,3);
    $act_hrs_acp_hrs_totals = get_weekly_totals($weeks_size_array,$sub_cat_array,9,0) + get_weekly_totals($weeks_size_array,$sub_cat_array,9,1) + get_weekly_totals($weeks_size_array,$sub_cat_array,9,2) + get_weekly_totals($weeks_size_array,$sub_cat_array,9,3);
    $admin_cl_lp_hrs_totals = get_weekly_totals($weeks_size_array,$sub_cat_array,10,0) + get_weekly_totals($weeks_size_array,$sub_cat_array,10,1) + get_weekly_totals($weeks_size_array,$sub_cat_array,10,2) + get_weekly_totals($weeks_size_array,$sub_cat_array,10,3);
    $rto_hrs_totals = get_weekly_totals($weeks_size_array,$sub_cat_array,0,0) + get_weekly_totals($weeks_size_array,$sub_cat_array,0,1) + get_weekly_totals($weeks_size_array,$sub_cat_array,0,2) + get_weekly_totals($weeks_size_array,$sub_cat_array,0,3);
    $doc_pay_totals = get_weekly_totals_special_array($doc_pay_conversions_array,$weeks)[0] + get_weekly_totals_special_array($doc_pay_conversions_array,$weeks)[1] + get_weekly_totals_special_array($doc_pay_conversions_array,$weeks)[2] + get_weekly_totals_special_array($doc_pay_conversions_array,$weeks)[3];
    $rto_pay_totals = get_weekly_totals_special_array($rto_conversions_array,$weeks)[0] + get_weekly_totals_special_array($rto_conversions_array,$weeks)[1] + get_weekly_totals_special_array($rto_conversions_array,$weeks)[2] + get_weekly_totals_special_array($rto_conversions_array,$weeks)[3];
    $overtime_pay_totals = $overtime_pay_array[0] + $overtime_pay_array[1] + $overtime_pay_array[2] + $overtime_pay_array[3];
    $comp_time_tr_totals = $comp_time_tr_array[0] + $comp_time_tr_array[1] + $comp_time_tr_array[2] + $comp_time_tr_array[3];
    $how_holiday_totals = get_how_holiday_by_week($how_holiday_pay_array,$weeks_size_array,0) + get_how_holiday_by_week($how_holiday_pay_array,$weeks_size_array,1) + get_how_holiday_by_week($how_holiday_pay_array,$weeks_size_array,2) + get_how_holiday_by_week($how_holiday_pay_array,$weeks_size_array,3);
    $hot_holiday_totals = get_weekly_totals_special_array($hot_holiday_pay_array,$weeks)[0] + get_weekly_totals_special_array($hot_holiday_pay_array,$weeks)[1] + get_weekly_totals_special_array($hot_holiday_pay_array,$weeks)[2] + get_weekly_totals_special_array($hot_holiday_pay_array,$weeks)[3];
    $sst_totals = $sst_pay_array[0] + $sst_pay_array[1] + $sst_pay_array[2] + $sst_pay_array[3];


    $form_totals_sum = array($act_hrs_wrk_totals,$comp_time_used_totals,$holiday_hrs_totals,$med_hrs_totals,$per_hrs_totals,$jury_hrs_totals,$mil_hrs_totals,$leave_hrs_totals,
    $act_hrs_acp_hrs_totals,$admin_cl_lp_hrs_totals,$rto_hrs_totals,$doc_pay_totals,$rto_pay_totals,$overtime_pay_totals,$comp_time_tr_totals,
    $how_holiday_totals,$hot_holiday_totals,$sst_totals);

    return $form_totals_sum;
}

















//old functions

function get_totals_for_sub_cat(){
    $rto = $_POST["RTO-1"] + $_POST["RTO-2"] + $_POST["RTO-3"] + $_POST["RTO-4"] + $_POST["RTO-5"] + $_POST["RTO-6"] + $_POST["RTO-7"] + $_POST["RTO-8"] + $_POST["RTO-9"] + $_POST["RTO-10"] + $_POST["RTO-11"] + $_POST["RTO-12"] + $_POST["RTO-13"] + $_POST["RTO-14"] + $_POST["RTO-15"];
    $act_hours = $_POST["ACT-HOURS-1"] + $_POST["ACT-HOURS-2"] + $_POST["ACT-HOURS-3"] + $_POST["ACT-HOURS-4"] + $_POST["ACT-HOURS-5"] + $_POST["ACT-HOURS-6"] + $_POST["ACT-HOURS-7"] + $_POST["ACT-HOURS-8"] + $_POST["ACT-HOURS-9"] + $_POST["ACT-HOURS-10"] + $_POST["ACT-HOURS-11"] + $_POST["ACT-HOURS-12"] + $_POST["ACT-HOURS-13"] + $_POST["ACT-HOURS-14"] + $_POST["ACT-HOURS-15"];
    $comp_time_used = $_POST["COMP-TIME-1"] + $_POST["COMP-TIME-2"] + $_POST["COMP-TIME-3"] + $_POST["COMP-TIME-4"] + $_POST["COMP-TIME-5"] + $_POST["COMP-TIME-6"] + $_POST["COMP-TIME-7"] + $_POST["COMP-TIME-8"] + $_POST["COMP-TIME-9"] + $_POST["COMP-TIME-10"] + $_POST["COMP-TIME-11"] + $_POST["COMP-TIME-12"] + $_POST["COMP-TIME-13"] + $_POST["COMP-TIME-14"] + $_POST["COMP-TIME-15"];
    $holiday = $_POST["HOL-1"] + $_POST["HOL-2"] + $_POST["HOL-3"] + $_POST["HOL-4"] + $_POST["HOL-5"] + $_POST["HOL-6"] + $_POST["HOL-7"] + $_POST["HOL-8"] + $_POST["HOL-9"] + $_POST["HOL-10"] + $_POST["HOL-11"] + $_POST["HOL-12"] + $_POST["HOL-13"] + $_POST["HOL-14"] + $_POST["HOL-15"];
    $med_leave = $_POST["MED-LEAVE-1"] + $_POST["MED-LEAVE-2"] + $_POST["MED-LEAVE-3"] + $_POST["MED-LEAVE-4"] + $_POST["MED-LEAVE-5"] + $_POST["MED-LEAVE-6"] + $_POST["MED-LEAVE-7"] + $_POST["MED-LEAVE-8"] + $_POST["MED-LEAVE-9"] + $_POST["MED-LEAVE-10"] + $_POST["MED-LEAVE-11"] + $_POST["MED-LEAVE-12"] + $_POST["MED-LEAVE-13"] + $_POST["MED-LEAVE-14"] + $_POST["MED-LEAVE-15"];
    $per_leave = $_POST["PER-LEAVE-1"] + $_POST["PER-LEAVE-2"] + $_POST["PER-LEAVE-3"] + $_POST["PER-LEAVE-4"] + $_POST["PER-LEAVE-5"] + $_POST["PER-LEAVE-6"] + $_POST["PER-LEAVE-7"] + $_POST["PER-LEAVE-8"] + $_POST["PER-LEAVE-9"] + $_POST["PER-LEAVE-10"] + $_POST["PER-LEAVE-11"] + $_POST["PER-LEAVE-12"] + $_POST["PER-LEAVE-13"] + $_POST["PER-LEAVE-14"] + $_POST["PER-LEAVE-15"];
    $jury_duty = $_POST["JD-1"] + $_POST["JD-2"] + $_POST["JD-3"] + $_POST["JD-4"] + $_POST["JD-5"] + $_POST["JD-6"] + $_POST["JD-7"] + $_POST["JD-8"] + $_POST["JD-9"] + $_POST["JD-10"] + $_POST["JD-11"] + $_POST["JD-12"] + $_POST["JD-13"] + $_POST["JD-14"] + $_POST["JD-15"];
    $mil_duty = $_POST["ML-1"] + $_POST["ML-2"] + $_POST["ML-3"] + $_POST["ML-4"] + $_POST["ML-5"] + $_POST["ML-6"] + $_POST["ML-7"] + $_POST["ML-8"] + $_POST["ML-9"] + $_POST["ML-10"] + $_POST["ML-11"] + $_POST["ML-12"] + $_POST["ML-13"] + $_POST["ML-14"] + $_POST["ML-15"];
    $leave_wo_pay  = $_POST["LWP-1"] + $_POST["LWP-2"] + $_POST["LWP-3"] + $_POST["LWP-4"] + $_POST["LWP-5"] + $_POST["LWP-6"] + $_POST["LWP-7"] + $_POST["LWP-8"] + $_POST["LWP-9"] + $_POST["LWP-10"] + $_POST["LWP-11"] + $_POST["LWP-12"] + $_POST["LWP-13"] + $_POST["LWP-14"] + $_POST["LWP-15"];
    $act_hours_acp = $_POST["ACT-HOURS-ACP-1"] + $_POST["ACT-HOURS-ACP-2"] + $_POST["ACT-HOURS-ACP-3"] + $_POST["ACT-HOURS-ACP-4"] + $_POST["ACT-HOURS-ACP-5"] + $_POST["ACT-HOURS-ACP-6"] + $_POST["ACT-HOURS-ACP-7"] + $_POST["ACT-HOURS-ACP-8"] + $_POST["ACT-HOURS-ACP-9"] + $_POST["ACT-HOURS-ACP-10"] + $_POST["ACT-HOURS-ACP-11"] + $_POST["ACT-HOURS-ACP-12"] + $_POST["ACT-HOURS-ACP-13"] + $_POST["ACT-HOURS-ACP-14"] + $_POST["ACT-HOURS-ACP-15"];
    $ad_close_leave_period = $_POST["AD-CLP-1"] + $_POST["AD-CLP-2"] + $_POST["AD-CLP-3"] + $_POST["AD-CLP-4"] + $_POST["AD-CLP-5"] + $_POST["AD-CLP-6"] + $_POST["AD-CLP-7"] + $_POST["AD-CLP-8"] + $_POST["AD-CLP-9"] + $_POST["AD-CLP-10"] + $_POST["AD-CLP-11"] + $_POST["AD-CLP-12"] + $_POST["AD-CLP-13"] + $_POST["AD-CLP-14"] + $_POST["AD-CLP-15"];
    $array = array($rto,$act_hours,$comp_time_used,$holiday,$med_leave,$per_leave,$jury_duty,$mil_duty,$leave_wo_pay,$act_hours_acp,$ad_close_leave_period);
    return $array;
}
function get_user_data_array($array){
    $user_id = $_POST["ID"];
    $name = $_POST["NAME"];
    $department = $_POST["DEPARTMENT"];
    $sch_hrs = $_POST["SCH-HRS"];
    $rcomp_time = $array[0];
    $act_hours = $array[1];
    $comp_time_used = $array[2];
    $holiday = $array[3];
    $med_leave = $array[4];
    $per_leave = $array[5];
    $jury_duty = $array[6];
    $mil_duty = $array[7];
    $leave_wo_pay = $array[8];
    $rto = $array[0];
    $act_hours_acp = $array[9];
    $ad_close_leave_period = $array[10];
    $user_data_array = array($user_id,$name,$department,$sch_hrs,$rcomp_time,$act_hours,$comp_time_used,$holiday,$med_leave,$per_leave,$jury_duty,$mil_duty,$leave_wo_pay,$rto,$act_hours_acp,$ad_close_leave_period);
    return $user_data_array;
}


function print_array($array){
    echo "<pre>";
    print_r($array);
    echo '</pre>';
}

function calculate_total($array){
    $act_hours = $array[5];
    $comp_time_used = $array[6];
    $holiday = $array[7];
    $med_leave = $array[8];
    $per_leave = $array[9];
    $jury_duty = $array[10];
    $mil_duty = $array[11];
    $leave_wo_pay = $array[12];
    $ad_close_leave_period = $array[15];

    $totals = $act_hours + $comp_time_used + $holiday + $med_leave + $per_leave + $jury_duty + $mil_duty + $leave_wo_pay + $ad_close_leave_period;

    return $totals;
}


function calculate_overtime_old($array){


    $act_hr_totals = $array[5];
    $sch_hrs = $array[3];
    $overtime = 0;

    if( $act_hr_totals> $sch_hrs){
        $overtime = $act_hr_totals - $sch_hrs;
    }
    return $overtime;
}

function calculate_tr_comp_record_old($array){
    $overtime = calculate_overtime_old($array);
    if($overtime > 0){
        $comp_time_tr = ($overtime * 1.50);
        $comp_time_tr = floor2nearest($comp_time_tr,.25);
    }
    else {
        $comp_time_tr = 0;
    }
    return $comp_time_tr;
}

function calculate_sst_payroll_old($array){
    $sch_hrs = $array[3];
    $lwp = $array[12];
    $act_hours_wrk = $array[5];
    $holiday = $array[7];
    $ad_close_leave_period = $array[15];
    $comp_time_used = $array[6];
    $med_leave = $array[8];
    $per_leave = $array[9];
    $jury_duty = $array[10];
    $mil_duty = $array[11];
    $totals = calculate_total($array);
    $overtime = calculate_overtime_old($array);
    $how_holiday_pay = calculate_how_holiday_old($array);
    $sst_payroll = 0;
    if(($act_hours_wrk-($sch_hrs-($holiday+$ad_close_leave_period+$comp_time_used+$med_leave+$per_leave+$jury_duty+$mil_duty))-($overtime+$how_holiday_pay)) > 0){
        $sst_payroll = ($act_hours_wrk-($sch_hrs-($holiday+$ad_close_leave_period+$comp_time_used+$med_leave+$per_leave+$jury_duty+$mil_duty))-($overtime+$how_holiday_pay));
    }

    return $sst_payroll;

}

function calculate_doc_conv_old($array,$conversion){
    $lwp = $array[12];
    $doc = 0;

    if($lwp == 0){
        return $doc;
    }
    else{
        $temp = $lwp/8;
        $doc = round($temp * $conversion,2);
        return $doc;
    }
    

}

function calculate_rto_old($array,$conversion){
    $rto = $array[13];
    $cal_rto = 0;

    // Set loop to find rto values.
    for($x=0; $x < sizeof($array); $x++){
        if($array[$x] > 0) {
            $temp = $array[$x]/8;
            $cal_rto = $cal_rto + ($temp*$conversion);
        }
    }
    
    return $cal_rto;

}
    
function calculate_how_holiday_old($array){
    $act_hours = $array[5];
    $holiday = $array[7];
    $how_holiday = 0;

    if(($act_hours ) && ($holiday > 0)){
        $how_holiday = $holiday;
    }
    else{
        $how_holiday = 0;
    }

    return $how_holiday;
}


?>