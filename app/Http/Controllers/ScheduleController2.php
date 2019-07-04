<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Validator;
use Illuminate\Http\Request;

class ScheduleController2 extends Controller
{
    public function sessionSchedule(Request $request){
        //check validation of input
        $validator = Validator::make($request->all(), [
            'starting_date'=>'required|date_format:Y-m-d',
            'days_per_week'=>'required|array',
            'days_per_week.*'=>'integer|min:0|max:6',
            'sessions'=>'required|integer|min:1'
        ]);
        //validation fail
        if($validator->fails()) {
            return response()->json([
                'code' => '400',
                'status' => 'Error',
                'data'=>$validator->errors()
            ]);
        }
        //validation success
        //get values from request
        $starting_date = $request->starting_date;
        $days_per_week = $request->days_per_week;
        $sessions = $request->sessions;
        //sort days per week
        sort($days_per_week);

        //remove duplicated days
        $days_per_week = array_unique($days_per_week);


        //number of days to finish book
        $total_days = 30 * $sessions;

        //count of days per week
        $num_of_days_per_week = count($days_per_week);

        //apperance of each days
        $apperance_of_day = (int)($total_days / $num_of_days_per_week);

        //remaining dates out of apperance
        $remaining_days_of_apperance = $total_days - ($num_of_days_per_week * $apperance_of_day);

        //get day name using date
        $day_name = date('D', strtotime($starting_date));

        // get day index
        $starting_date_index = $this->getDayIndex($day_name);

        //check date in days per week
        //example => [choose sat,mon,tue  and start_date for friday]
        if(!in_array($starting_date_index, $days_per_week)){
            return response()->json([
                'code' => '400',
                'status' => 'Error',
                'data'=>['Start date is not equal to one of the selected days date']
            ]);
        }

        //array of all times he will read including start date
        $sessions_schedule = array();

        //calculate apperance of days date
        for($i=0; $i<$num_of_days_per_week; $i++){
            if($starting_date_index != $days_per_week[$i]){
                for($j=0; $j<$apperance_of_day; $j++){
                        if($j==0){
                            if($starting_date_index > $days_per_week[$i]){
                                $added_value = 7 - abs(($starting_date_index - $days_per_week[$i]));
                            }else{
                                $added_value = $days_per_week[$i] - $starting_date_index;
                            }
                            $added_date = date('Y-m-d', strtotime($starting_date. ' + '.$added_value.' days'));
                            $sessions_schedule [] = $added_date;
                        }
                     else{
                         $added_date = date('Y-m-d', strtotime($added_date. ' + 7 days'));
                         $sessions_schedule [] = $added_date;
                     }
                }
            }else{
                for($j=0; $j<$apperance_of_day; $j++){
                    if($j == 0 ){
                        $sessions_schedule [] = $starting_date;
                        $added_date = $starting_date;
                    }else{
                        $added_date = date('Y-m-d', strtotime($added_date. ' + 7 days'));
                        $sessions_schedule [] = $added_date;
                    }
                }
            }
        }

        //sorting dates
        sort($sessions_schedule);

        //get last date appear
        $last_date = end($sessions_schedule);

        //get last Date name
        $last_day_name = date('D', strtotime($last_date));

        // get last day index as days
        $last_date_index = $this->getDayIndex($last_day_name);

        //get key of last date index as index of days_per_week array
        $key = array_search($last_date_index, $days_per_week);

        for($i=0; $i<$remaining_days_of_apperance; $i++){

            $next_date = $days_per_week[(($key + 1) % $num_of_days_per_week)];


            if($next_date > $last_date_index){
                $added_value = abs($next_date - $last_date_index);
            }else{
                $added_value = 7 - abs($next_date - $last_date_index);
            }
            $added_date = date('Y-m-d', strtotime($last_date. ' + '.$added_value.' days'));
            $sessions_schedule [] = $added_date;

            //update values to new last data
            $last_date = $added_date;

            $last_day_name = date('D', strtotime($last_date));

            $last_date_index = $this->getDayIndex($last_day_name);

            $key = array_search($last_date_index, $days_per_week);

        }

        // returning result
        return response()->json([
            'code' => '200',
            'status' => 'Success',
            'data'=>$sessions_schedule
        ]);
    }
    protected function getDayIndex($day){
        $days = array(
            'Sat'=>0,
            'Sun'=>1,
            'Mon'=>2,
            'Tue'=>3,
            'Wed'=>4,
            'Thu'=>5,
            'Fri'=>6,
        );
        return $days[$day];
    }
}
