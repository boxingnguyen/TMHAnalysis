<?php
class HashtagController extends AppController {
	public function index () {
		$m = new MongoClient();
		$db = $m->hashtag;
		$c = $db->media_daily;
		$count_hashtag = $db->tags->find()->count();
		$date = date('d-m-Y');
		$data = $c->find()->sort(array('_id'=>-1))->limit($count_hashtag);
		$this->set('data', $data);
	}
	public function register() {
		$this->layout= false;
		$this->autoRender= false;
		if ($this->request->is('post')) {
			$tag = $this->request->data['hashtag'];

			// connect to mongo
			$db = $this->m->hashtag;
			$c = $db->tags;

			if ($c->count(array('tag' => $tag)) > 0) {
				return json_encode('This tag has been registered before!');
			} else {
				$insert = $c->insert(array('tag' => $tag));
				if ($insert) {
					return true;
				} else {
					return false;
				}
			}
		}
	}

	public function getDataRegister(){
		$this->layout = false;
		$this->autoRender = false;
		if(isset($_POST['hashtag'])){
			$tag = $_POST['hashtag'];
			$tag = substr($tag, 1); //remove # of first character of hashtag
			$acc = array();
			//get total media of hashtag and save to db
			$infor = $this->getInformationTag($tag);
			$acc['totalMedia'] = '';
			if(is_array($infor)){
				$acc['totalMedia'] = number_format($infor['total_media']);
			}else {
				$acc['error'] = $infor;
			}
			$acc['name'] = $tag;
			return json_encode($acc);
		}else{
			return false;
		}
	}

	public function getMediaRegister() {
		$this->layout = false;
		$this->autoRender = false;
		if (isset($_POST['hashtag'])) {
			$tag = $_POST['hashtag'];
			$tag = substr($tag, 1);
			$media = array();

			$mediaJson = $this->getMediaTag($tag);
			if (!$mediaJson) {
				return json_encode('Getting media of hashtag #'.$tag.' is not full');
			}

			$m = new MongoClient();
			$db = $m->hashtag;
			$c_media = $db->media;
			$date = date('MY');
			$save = $this->saveIntoDb($tag, $c_media, $date);
			if (!$save) {
				return json_encode('Error when save media of hashtag #'.$tag);
			}

			return true;

		}else {
			return false;
		}
	}

	public function getInformationTag($tag) {
		$m = new MongoClient();
		$db = $m->hashtag;
		$media_daily = $db->media_daily;
		$date = date('d-m-Y');
		$media = $this->getMediaHashtag($tag, null);
		if (isset($media->tag->media->count)) {
			$data = array(
					'total_media' => $media->tag->media->count,
					'tag' => $tag,
					'date' => new MongoDate(strtotime($date))
			);
			$insert = $media_daily->insert($data);
// 			$insert = true;
			if (!$insert) {
				return "Insert total_media of #" . $tag . " failed!";
			} else {
				return $data;
			}
		} else {
			return json_encode($media);
		}
	}

	public function getMediaHashtag($tag, $max_id) {
		if ($max_id != null) {
			$media = $this->cURLInstagram('https://www.instagram.com/explore/tags/'.$tag.'/?__a=1&max_id=' . $max_id);
		} else {
			$media = $this->cURLInstagram('https://www.instagram.com/explore/tags/'.$tag.'/?__a=1');
		}
		return $media;
	}

	public function getMediaTag($tag) {
		$m = new MongoClient();
		$db = $m->hashtag;
		$c_media = $db->media;

		$date = date('MY');
		$previous_month = intval(date('m')) - 1;

		$max_id = null;
		// write data into json file
		$count = 0;
		$tmp = array ();
		$myfile = fopen(APP . "Vendor/Hashtag/" . $date . "." . $tag . ".media_hashtag.json", "w+") or die("Unable to open file!");
		do {
			$media = $this->getMediaHashtag($tag, $max_id);

			if (!isset($media->tag->media->nodes) || empty($media->tag->media->nodes)) {
				return false;
			}
			$data = $media->tag->media->nodes;
			$tmp = $data;
			foreach ($data as $value) {
				$count ++;
				// do not get media of October
				if (isset($value->date) && intval(date('m', $value->date)) > $previous_month) {
					continue;
				} else if (isset($value->date) && intval(date('m', $value->date)) < $previous_month) {
					// do not get media of month before September
					return false;
				} else {
					$value->date = date('d-m-Y', $value->date);
					$value->tag_name = $tag;
					fwrite($myfile, json_encode($value) . "\n");
				}
			}
			// get next page of data
			$max_id = isset($media->tag->media->page_info->end_cursor) ? $media->tag->media->page_info->end_cursor : null;
		} while ($media->tag->media->page_info->has_next_page == 1);
		return true;
	}

	public function saveIntoDb($tag, $collection, $date) {
		$filename = APP . "Vendor/Hashtag/" . $date . "." . $tag . ".media_hashtag.json";
		$all_lines = file($filename);
		if (empty($all_lines)) {
			return false;
		}
		$part = (int)(count($all_lines) / 1000) + 1;
		$start = 0;
		if ($part == 1) {
			$count_get = count($all_lines) % 1000;
		} else {
			$count_get = 1000;
		}
		$data = array();
		for ($i = 0; $i < $part; $i++) {
			$my[$i] = array_slice($all_lines, $start, $count_get );
			if ($i < $part - 1) {
				$start = $start + 1000;
			} else {
				$start = $start + 1000;
				$count_get = count($all_lines) % 1000;
			}
		}

		for ($i = 0; $i < $part ; $i++) {
			foreach ($my[$i] as $value) {
				$data[] = json_decode($value);
			}
			$collection->batchInsert($data, array('timeout' => -1));
			unset($data);
		}
		return true;
	}

	public function detail() {
	}

	public function media() {
		$tag = $_GET['hashtag'];
		$m = new MongoClient();
		$db = $m->hashtag;
		$c = $db->media_daily;
		$statistic = $c->find(array('tag' =>$tag))->sort(array('_id'=>-1))->limit(10);
		$sort_date = array();
		foreach ($statistic as $val) {
			$sort_date[]=$val;
		}
		$data = array();
		for($i=count($sort_date)-1;$i>=0;$i--){
			if($i==count($sort_date)-1) {
				if(isset($sort_date[$i]['date']->sec)){
					$data[]= array("date"=>date("d-m-Y",$sort_date[$i]['date']->sec),"total_media"=>0);
				}
				else{
					$data[]= array("date"=>$sort_date[$i]['date'],"total_media"=>0);
				}
			} else {
					$total = $sort_date[$i]['total_media'] - $tam;
					if(isset($sort_date[$i]['date']->sec)){
						$data[]= array("date"=>date("d-m-Y",$sort_date[$i]['date']->sec),"total_media"=>$total);
					}
					else{
						$data[]= array("date"=>$sort_date[$i]['date'],"total_media"=>$total);
					}
			}
			$tam = $sort_date[$i]['total_media'];
		}
		$this->set('data', $data);

	}

	public function more() {
		$this->layout = false;
		$this->autoRender = false;

		if (isset($_POST['tag'])){
			$tag = $_POST['tag'];
		}else {
			return false;
		}

		$db = $this->m->hashtag;
		$c = $db->media;

		$page = isset($_POST['page']) ? $_POST['page'] : 1;
		$limit = 20;
		$start= ($page*$limit)-$limit;

		if ($_POST['sort'] === 'like') {
			$sort = 'likes.count';
		}elseif ($_POST['sort'] === 'comment') {
			$sort = 'comments.count';
		}else {
			$sort = 'likes.count';
		}

		$query = array('tag_name' => $tag);
		$cursor = $c->find($query,array())->sort(array($sort=>-1))->skip($start)->limit($limit);

		$data= array();
		foreach ($cursor as $value){
			$value['likes']['count'] = number_format($value['likes']['count']);
			$value['comments']['count'] = number_format($value['comments']['count']);
			$data[]=$value;
		}

		return json_encode($data);
	}
	public function total(){
		$this->layout = false;
		$this->autoRender = false;

		if (isset($_POST['tag'])){
			$tag = $_POST['tag'];
		}else {
			return false;
		}

		$db = $this->m->hashtag;
		$c = $db->media;

		$query = array('tag_name' => $tag);
		$total = $c->find($query,array())->count();
		return $total;
	}
}
