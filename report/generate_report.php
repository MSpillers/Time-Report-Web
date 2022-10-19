<html>
    <head>
        <title> Generated Report </title> 
    <head> 
    <body>
        <?php
            $conversion = 7.879;
            $array = get_array();
            print_array($array);
            $totals = calculate_total($array);
            $overtime = calculate_overtime($array);
            $comp_time_tr = calculate_tr_comp_record($array);
            $sst_payroll = calculate_sst_payroll($array);
            $doc = calculate_doc_conv($array,$conversion);

            print "Total Hours Submitted: "; 
            print_r($totals);
            print "<br>Overtime: "; 
            print_r($overtime);
            print "<br>Hours Transferred to Comp. Record: "; 
            print_r($comp_time_tr);
            print "<br>SST-Payroll: ";
            print_r($sst_payroll);
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
function get_array(){
    $user_id = $_POST["ID"];
    $name = $_POST["NAME"];
    $department = $_POST["DEPARTMENT"];
    $sch_hrs = $_POST["SCH-HRS"];
    $rcomp_time = $_POST["RCT"];
    $act_hours = $_POST["ACT-HOURS"];
    $comp_time_used = $_POST["COMP-TIME"];
    $holiday = $_POST["HOL"];
    $med_leave = $_POST["MED-LEAVE"];
    $per_leave = $_POST["PER-LEAVE"];
    $jury_duty = $_POST["JD"];
    $mil_duty = $_POST["ML"];
    $leave_wo_pay = $_POST["LWP"];
    $act_hours_acp = $_POST["ACT-HOURS-ACP"];
    $ad_close_leave_period = $_POST["AD-CLP"];
    $array = array($user_id,$name,$department,$sch_hrs,$rcomp_time,$act_hours,$comp_time_used,$holiday,$med_leave,$per_leave,$jury_duty,$mil_duty,$leave_wo_pay,$act_hours_acp,$ad_close_leave_period);
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
    $ad_close_leave_period = $array[13];

    $totals = $act_hours + $comp_time_used + $holiday + $med_leave + $per_leave + $jury_duty + $mil_duty + $leave_wo_pay + $ad_close_leave_period;

    return $totals;
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
    $totals = calculate_total($array);
    $overtime = calculate_overtime($array);
    $sst_payroll = 0;
    if(($overtime == 0) and (($totals - $sch_hrs) > 0)){
        $sst_payroll = $totals - $sch_hrs - $lwp;
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


?>