<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Validator;
use Illuminate\Http\Request;

class ScheduleController extends Controller
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
        //get day name using date
		$day_name = date('D', strtotime($starting_date));

		//get day index
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
		//get index of day in days per week
		$key = array_search($starting_date_index, $days_per_week);
		//difference between days
		$diff = array();
		$num_of_days_per_week = count($days_per_week);
		for ($i=0; $i<$num_of_days_per_week; $i++) {
			if($i!=($num_of_days_per_week - 1)){
				$diff[] = $days_per_week[$i+1] -  $days_per_week[$i];
			}else{
				$diff[] = 7 - ($days_per_week[$i] -  $days_per_week[0]);
			}
		}

		//days that read in it
		$readed_days = 1;
		//array of all times he will read including start date
		$sessions_schedule = array();
		$sessions_schedule[] = $starting_date;
		//calculate dates of first week & first day of next week
		if($starting_date_index != 6){
			for ($i=$key; $i<$num_of_days_per_week; $i++) {
					$starting_date = date('Y-m-d', strtotime($starting_date. ' + '.$diff[$i].' days'));
					$sessions_schedule[] = $starting_date;
					$readed_days ++;
			}
		}
		//calculate number of days remaining to read
		$remaining_days_to_read = $total_days - $readed_days;
		//calculate dates of remaining days
		while($remaining_days_to_read > 0){
			if($remaining_days_to_read < $num_of_days_per_week){
				for ($i=0; $i<$remaining_days_to_read; $i++) {
					$starting_date = date('Y-m-d', strtotime($starting_date. ' + '.$diff[$i].' days'));
					$sessions_schedule[] = $starting_date;
				}
                	$remaining_days_to_read = 0;
			}else{
				for ($j=0; $j<$num_of_days_per_week; $j++) {
					$starting_date = date('Y-m-d', strtotime($starting_date. ' + '.$diff[$j].' days'));
					$sessions_schedule[] = $starting_date;
					$remaining_days_to_read--;
				}
			}
		}
		//returning result
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
