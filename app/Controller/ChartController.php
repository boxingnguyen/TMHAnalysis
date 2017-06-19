<?php
class ChartController extends AppController {
	const FIRSTDAY = "01";
	const SECONDDAY = "02";
	public function follower() {
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		
		// if the day is 1th of month: $m = $mcurrent - 1;
		if (date('d') == self::FIRSTDAY) {
			$month = (new DateTime())->modify('-1 month')->format('Y-m');
			$collection = $db->selectCollection($month);
		} else {
			$collection = $db->selectCollection(date('Y-m'));
		}
		
		$id = $this->request->query['id'];
		$data = $collection->find( array('id' => $id))->sort(array('time' => 1));
		$arr = array();
		if(isset($data) && ($data->count() > 0)) {
			foreach ($data as $val) {
				$follow = $val['followers'];
				$timedb = date('Y-m-d',$val['time']->sec);
				$arr[$timedb] = $follow;
			}
			$this->set('data', $arr);
		}
	}
	public function readLikeAndComment($id) {
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		
		// if the day is 1th of month the data still be of previous month.
		$newDate = (new DateTime())->modify('-1 month');
		$month = $newDate->format('Y-m');
		$like = 0; $comment = 0;
		if (date('d') == self::FIRSTDAY) {
			$collection = $db->selectCollection($month);
		} else {
			$collection = $db->selectCollection(date('Y-m'));
		}
		
		$data = $collection->find(array('id' => $id))->sort(array('time'=>1));
		
		// if the day is the 2th: get the data of 1th of this month and compare to the last day of previous month 
		if (date('d') == self::SECONDDAY) {
			$d=cal_days_in_month(CAL_GREGORIAN,$newDate->format('m'),$newDate->format('Y'));
			$time = $month.'-'.$d;//2016-09-30
			$lastCollection = $db->selectCollection($month);
			$lastdata = $lastCollection->find(array('id' => $id,'time' => new MongoDate(strtotime($time)) ));
			if(isset($lastdata) && $lastdata->count() > 0) {
				foreach ($lastdata as $val) {
					$like = $val['likesAnalytic'];
					$comment = $val['commentsAnalytic'];
				}
			}
		}
		
		$dt = array();
		$arr = array();
		$likes = array(); $comments = array();
		foreach($data as $item) {
			$dt[] = $item;
		}
		if(isset($dt) && !empty($dt)) {
			$secTime = date('Y-m-d',$dt[0]['time']->sec);
			if($like > 0 || $comment > 0) {
				$arr[$secTime] = array('comment' => ($dt[0]['commentsAnalytic'] - $comment), 'like' => ($dt[0]['likesAnalytic'] - $like) );
			} else {
				$arr[$secTime] = 0;
			}
			for ($i = 0; $i < count($dt) - 1; $i++) {
				for ($j = $i + 1; $j < count($dt); $j++) {
					if($dt[$i]['time']->sec !=  $dt[$j]['time']->sec) {
						$arr[date('Y-m-d', $dt[$j]['time']->sec)] = array('comment' => ($dt[$j]['commentsAnalytic'] - $dt[$i]['commentsAnalytic']), 'like' => ($dt[$j]['likesAnalytic'] - $dt[$i]['likesAnalytic']));
					}
					break;
				}
			}
			return $arr;
		}
		return false;
	}
	public function like() {
		$id = $this->params->query['id'];
		$data = $this->readLikeAndComment($id);
		$arr = array();
		if(isset($data) && !empty($data)) {
			foreach ($data as $key => $val) {
				if(is_array($val)) {
					$arr[$key] = $val['like'];
				} else {
					$arr[$key] = $val;
				}	
			}
		}
		if(isset($arr) && !empty($arr)) {
			$this->set('dataLikes', $arr);
		}
	}
	public function comment() {
		$id = $this->params->query['id'];
		$data = $this->readLikeAndComment($id);
		if(isset($data) && !empty($data)) {
			foreach ($data as $key => $val) {
				if(is_array($val)) {
					$arr[$key] = $val['comment'];
				} else {
					$arr[$key] = $val;
				}	
			}
		}
		if(isset($arr) && !empty($arr)) {
			$this->set('dataComments', $arr);
		}
	}
}





















