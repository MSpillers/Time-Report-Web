<?php 
    //Debugging get_weekly_totals
    $week_size_array = get_size_of_sub_week_arrays($weeks);
    $totals_1 = get_weekly_totals($week_size_array,$sub_cat_array,1,0);
    $totals_2 = get_weekly_totals($week_size_array,$sub_cat_array,1,1);
    $totals_3 = get_weekly_totals($week_size_array,$sub_cat_array,1,2);
    $totals_4 = get_weekly_totals($week_size_array,$sub_cat_array,1,3);
    print "<br> Totals Returned: {$totals_1} {$totals_2} {$totals_3} {$totals_4}";



    function get_weekly_totals($week_size_array,$sub_cat_array,$sub_cat,$week_num)
{

    //Week One Totals
    if($week_num== 0){
        $week_one_totals = 0;
        for($x=0;$x <$week_size_array[0];$x++){
            $act_val = intval($sub_cat_array[$sub_cat][$x]);
            $week_one_totals = $week_one_totals + $act_val;
            print "<br> Adding Act Hrs Worked Value {$act_val} for day {$x} to Week One Totals";
        }
        print "<br> Week-One-Totals: {$week_one_totals} ";
        return $week_one_totals;
    }
    //Week Two Totals
    if($week_num == 1){
        $week_two_totals = 0;
        for($x=$week_size_array[0];$x <$week_size_array[0]+ $week_size_array[1];$x++){
            $act_val = intval($sub_cat_array[$sub_cat][$x]);
            $week_two_totals = $week_two_totals + intval($sub_cat_array[$sub_cat][$x]);
            print "<br> Adding Act Hrs Worked Value {$act_val} for day {$x} to Week Two Totals";
        }
        print "<br> Week-One-Totals: {$week_two_totals} ";
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
                print "<br> Adding Act Hrs Worked Value {$act_val} for day {$x} to Week Three Totals";
            }
            print "<br> Week-Three-Totals: {$week_three_totals} ";
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
                print "<br> Adding Act Hrs Worked Value {$act_val} for day {$x} to Week Three Totals";
            }
            print "<br> Week-Three-Totals: {$week_four_totals} ";
            return $week_four_totals;
        }
    }
    
    //Return totals array
    //$weekly_totals_sub_cat = array($week_one_totals,$week_two_totals,$week_three_totals,$week_four_totals);
    //eturn $weekly_totals_sub_cat;



    // //debugging get_week_from_day
            // $week_size_array = get_size_of_sub_week_arrays($weeks);
            // $week_num = get_week($week_size_array,0);
            // print "<br> Day 0 is in week {$week_num}";
            // $week_num = get_week($week_size_array,1);
            // print "<br> Day 1 is in week {$week_num}";
            // $week_num = get_week($week_size_array,2);
            // print "<br> Day 2 is in week {$week_num}";
            // $week_num = get_week($week_size_array,3);
            // print "<br> Day 3 is in week {$week_num}";
            // $week_num = get_week($week_size_array,4);
            // print "<br> Day 4 is in week {$week_num}";
            // $week_num = get_week($week_size_array,5);
            // print "<br> Day 5 is in week {$week_num}";
            // $week_num = get_week($week_size_array,6);
            // print "<br> Day 6 is in week {$week_num}";
            // $week_num = get_week($week_size_array,7);
            // print "<br> Day 7 is in week {$week_num}";
            // $week_num = get_week($week_size_array,8);
            // print "<br> Day 8 is in week {$week_num}";
            // $week_num = get_week($week_size_array,9);
            // print "<br> Day 9 is in week {$week_num}";
            // $week_num = get_week($week_size_array,10);
            // print "<br> Day 10 is in week {$week_num}";
            // $week_num = get_week($week_size_array,11);
            // print "<br> Day 11 is in week {$week_num}";
            // $week_num = get_week($week_size_array,12);
            // print "<br> Day 12 is in week {$week_num}";
            // $week_num = get_week($week_size_array,13);
            // print "<br> Day 13 is in week {$week_num}";
            // $week_num = get_week($week_size_array,14);
            // print "<br> Day 14 is in week {$week_num}";
        



            //debugging weekly totals per day
            // $week_size_array = get_size_of_sub_week_arrays($weeks);
            // $day_totals = get_weekly_totals_per_day($week_size_array,$sub_cat_array,1,0);
            // print "<br>ACT Worked Hours Totals for day 1: {$day_totals}";
            // $day_totals = get_weekly_totals_per_day($week_size_array,$sub_cat_array,1,1);
            // print "<br>ACT Worked Hours Totals for day 2: {$day_totals}";
            // $day_totals = get_weekly_totals_per_day($week_size_array,$sub_cat_array,1,2);
            // print "<br>ACT Worked Hours Totals for day 3: {$day_totals}";
            // $day_totals = get_weekly_totals_per_day($week_size_array,$sub_cat_array,1,3);
            // print "<br>ACT Worked Hours Totals for day 4: {$day_totals}";
            // $day_totals = get_weekly_totals_per_day($week_size_array,$sub_cat_array,1,4);
            // print "<br>ACT Worked Hours Totals for day 5: {$day_totals}";
            // $day_totals = get_weekly_totals_per_day($week_size_array,$sub_cat_array,1,5);
            // print "<br>ACT Worked Hours Totals for day 6: {$day_totals}";
            // $day_totals = get_weekly_totals_per_day($week_size_array,$sub_cat_array,1,6);
            // print "<br>ACT Worked Hours Totals for day 7: {$day_totals}";
}
?>