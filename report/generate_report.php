<html>
    <head>
        <title> Generated Report </title> 
    <head> 
    <body>
        <?php
            
            //retrieve session info
            session_start();
            $payperiod = $_SESSION['payperiod'];
            print_array($payperiod);

            $conversion = 7.879;
            $vars = comp_var();
            $array = get_array($vars);
            $rto_array = to_array();
            print_array($array);
            $totals = calculate_total($array);
            $rto = calculate_rto($rto_array,$conversion);
            //$overtime = calculate_overtime($array);
            $comp_time_tr = calculate_tr_comp_record($array);
            $how_holiday = calculate_how_holiday($array);
            $sst_payroll = calculate_sst_payroll($array);
            $doc = calculate_doc_conv($array,$conversion);

            print "Total Hours Submitted: "; 
            print_r($totals);
            print "<br>RT0 Payroll-Conversion: ";
            print_r($rto);
            //print "<br>Overtime: "; 
            //print_r($overtime);
            //print "<br>Hours Transferred to Comp. Record: "; 
            //print_r($comp_time_tr);
            //print "<br>HOW-Holiday Pay: ";
            //print_r($how_holiday);
            //print "<br>SST-Payroll: ";
            //print_r($sst_payroll);
            print "<br>DOC-Payroll Conversion: ";
            print_r($doc);
            //print "<p> Total Hours Submitted:" .$totals. "<\p>"; 
            //print "<p> Overtime:" .$overtime. "<\p>";
            //print "<p> Hours Transferred to Comp. Record:" .$comp_time_tr. "<\p>";
            //print "<p> SST-Payroll:" .$sst_payroll. "<\p>";
            //print "<p> DOC-Payroll Conversion:" .$doc. "<\p>";
        ?>
    <body>
<html>

<?php 


function to_array(){
    $rto_array = array($_POST["RTO-1"],$_POST["RTO-2"],$_POST["RTO-3"],$_POST["RTO-4"],$_POST["RTO-5"],$_POST["RTO-6"],$_POST["RTO-7"],$_POST["RTO-8"],$_POST["RTO-9"],$_POST["RTO-10"],$_POST["RTO-11"],$_POST["RTO-12"],$_POST["RTO-13"],$_POST["RTO-14"],$_POST["RTO-15"]);
    return $rto_array;
}
function comp_var(){
    $rto = $_POST["RTO-1"] + $_POST["RTO-2"] + $_POST["RTO-3"] + $_POST["RTO-4"] + $_POST["RTO-5"] + $_POST["RTO-6"] + $_POST["RTO-7"] + $_POST["RTO-8"] + $_POST["RTO-9"] + $_POST["RTO-10"] + $_POST["RTO-11"] + $_POST["RTO-12"] + $_POST["RTO-13"] + $_POST["RTO-14"] + $_POST["RTO-15"];
    $act_hours = $_POST["ACT-HOURS-1"] + $_POST["ACT-HOURS-2"] + $_POST["ACT-HOURS-3"] + $_POST["ACT-HOURS-4"] + $_POST["ACT-HOURS-5"] + $_POST["ACT-HOURS-6"] + $_POST["ACT-HOURS-7"] + $_POST["ACT-HOURS-8"] + $_POST["ACT-HOURS-9"] + $_POST["ACT-HOURS-10"] + $_POST["ACT-HOURS-11"] + $_POST["ACT-HOURS-12"] + $_POST["ACT-HOURS-13"] + $_POST["ACT-HOURS-14"] + $_POST["ACT-HOURS-15"];
    echo " comp_var act_hours: $act_hours";
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
function get_array($array){
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
    $array = array($user_id,$name,$department,$sch_hrs,$rcomp_time,$act_hours,$comp_time_used,$holiday,$med_leave,$per_leave,$jury_duty,$mil_duty,$leave_wo_pay,$rto,$act_hours_acp,$ad_close_leave_period);
    return $array;
}

function floor2nearest($number, $decimal) {
    return floor($number / $decimal) * $decimal;
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

function get_overtime_wk($array){
    

}

function calculate_overtime($array){


    $act_hr_totals = $array[5];
    $sch_hrs = $array[3];
    $overtime = 0;

    if( $act_hr_totals> $sch_hrs){
        $overtime = $act_hr_totals - $sch_hrs;
    }
    return $overtime;
}

function calculate_tr_comp_record($array){
    $overtime = calculate_overtime($array);
    if($overtime > 0){
        $comp_time_tr = ($overtime * 1.50);
        $comp_time_tr = floor2nearest($comp_time_tr,.25);
    }
    else {
        $comp_time_tr = 0;
    }
    return $comp_time_tr;
}

function calculate_sst_payroll($array){
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
    $overtime = calculate_overtime($array);
    $how_holiday_pay = calculate_how_holiday($array);
    $sst_payroll = 0;
    if(($act_hours_wrk-($sch_hrs-($holiday+$ad_close_leave_period+$comp_time_used+$med_leave+$per_leave+$jury_duty+$mil_duty))-($overtime+$how_holiday_pay)) > 0){
        $sst_payroll = ($act_hours_wrk-($sch_hrs-($holiday+$ad_close_leave_period+$comp_time_used+$med_leave+$per_leave+$jury_duty+$mil_duty))-($overtime+$how_holiday_pay));
    }

    return $sst_payroll;

}

function calculate_doc_conv($array,$conversion){
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

function calculate_rto($array,$conversion){
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
    
function calculate_how_holiday($array){
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