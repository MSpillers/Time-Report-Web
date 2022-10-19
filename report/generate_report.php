<html>
    <head>
        <title> Generated Report </title> 
    <head> 
    <body>
        <?php 
            $array = get_array();
            print_array($array);
            $totals = calculate_total($array);
            print "<p> Total Hours Submitted:" .$totals. "<\p>"; 
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
    $jury_duty = $_POST["JD"];
    $mil_duty = $_POST["ML"];
    $leave_wo_pay = $_POST["LWP"];
    $act_hours_acp = $_POST["ACT-HOURS-ACP"];
    $ad_close_leave_period = $_POST["AD-CLP"];
    $array = array($user_id,$name,$department,$sch_hrs,$rcomp_time,$act_hours,$comp_time_used,$holiday,$jury_duty,$mil_duty,$leave_wo_pay,$act_hours_acp,$ad_close_leave_period);
    return $array;
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
    $jury_duty = $array[8];
    $mil_duty = $array[9];
    $leave_wo_pay = $array[10];
    $ad_close_leave_period = $array[12];

    $totals = $act_hours + $comp_time_used + $holiday + $jury_duty + $mil_duty + $leave_wo_pay + $ad_close_leave_period;

    return $totals;
}

function calculate_overtime($array){
    $totals = calculate_total($array);
    $sch_hrs = $array[3];
    $overtime = 0;

    if( $totals > $sch_hrs){
        $overtime = $totals - $sch_hrs;
    }
    return $overtime;
}

function calculate_tr_comp_record(){
    $overtime;
}



?>