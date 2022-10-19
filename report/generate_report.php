<html>
    <head>
        <title> Generated Report </title> 
    <head> 
    <body>
        <?php 
            pre_r();
        ?>
    <body>
<html>

<?php 
function pre_r(){
    $user_id = $_POST["ID"];
    $name = $_POST["NAME"];
    $department = $_POST["DEPARTMENT"];
    $sch_hrs = $_POST["SCH-HRS"];
    $rcomp_time = $_POST["RCT"];
    $act_hours = $_POST["ACT-HOURS"];
    $comp_time = $_POST["COMP-TIME"];
    $holiday = $_POST["HOL"];
    $jury_duty = $_POST["JD"];
    $mil_duty = $_POST["ML"];
    $leave_wo_pay = $_POST["LWP"];
    $act_hours_acp = $_POST["ACT-HOURS-ACP"];
    $ad_close_leave_period = $_POST["AD-CLP"];
    $time_report = array($user_id,$name,$department,$sch_hrs,$rcomp_time,$act_hours,$comp_time,$holiday,$jury_duty,$mil_duty,$leave_wo_pay,$act_hours,$act_hours_acp,$ad_close_leave_period);
    echo '<pre>';
    print_r($time_report);
    echo '</pre>';
}
?>