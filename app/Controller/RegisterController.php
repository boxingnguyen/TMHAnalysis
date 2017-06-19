<?php
App::uses('Controller', 'Controller');
class RegisterController extends AppController {
	public function login() {
		if($this->Session->check('username')){
			$this->redirect(array('controller' => 'top', 'action' => 'index'));
		}
		$scope = array('basic','follower_list','public_content','likes','comments');
		$url = $this->_instagram->getLoginUrl($scope);
		$this->set('instagrams', $url);
	}
	public function logout() {
		$this->layout= false;
		$this->autoRender= false;

		if($this->Session->check('username') && $this->Session->check('id')){
			$usename = $this->Session->read('username');
			$id = $this->Session->read('id');

			$m = new MongoClient;
			$db = $m->instagram_account_info;
			$dbFollow = $m->instagram;

			$collections = $db->account_username;
			$testUsername = $collections->find(array('username' => $usename));

			if($testUsername->count() == 0) {
				//if username not collections account_username => delete username in caculalor
				if (date('d') == '01') {
					$month = (new DateTime())->modify('-1 month')->format('m');
					$day = cal_days_in_month(CAL_GREGORIAN,$month,date('Y'));
					$currentTime = date('Y')."-".$month."-".$day;
				} else {
					$currentTime = (new DateTime())->modify('-1 day')->format('Y-m-d');
				}

				$currentDate = (new DateTime())->modify('-1 day')->format('Y-m-d');
				$time = date('Y-m', strtotime($currentDate));
				$dbAccount = $m->instagram_account_info;

				//delete caculateDate
				$collectionCaculate = $dbAccount->selectCollection($time);
				$collectionCaculate->remove(array('username' => $usename));

				$colLogin = $db->account_login;
				$colLogin->remove(array('id' => $id), array('justOne' => true));

				$colFollow = $dbFollow->follow;
				$colFollow->remove(array($id => array('$exists' => 1)), array('justOne' => true));
			}
			$this->Session->delete('username');
			$this->Session->delete('id');
			return true;
		}
			$this->Session->delete('username');
			$this->Session->delete('id');
	}

	public function register(){
		$this->layout= false;
		$this->autoRender= false;
		if(isset($_POST['username'])){
			$username = $_POST['username'];
			$m = new MongoClient();
			$db = $m->instagram_account_info;
			$collection = $db->account_username;
			$exist = $collection->find(array('username'=>$username))->count();
			if(!$exist == 0){
				return json_encode("The account had added before!");
			}
			else{
				$data = $this->cURLInstagram('https://www.instagram.com/' . $username . '/?__a=1');
				if(isset($data)){
					$id = $data->user->id;
					// save to mongo db
					$collection->insert(array('username'=>$username,'id'=>$id));
					return true;
				}
				else{
					// alert username doesn't exist
					return json_encode("The account doesn't exist, please fill again!");
				}
			}
		}else{
			return false;
		}
	}

	public function detail() {
		if (isset($_GET['code'])) {
			$m = new MongoClient;
			$db = $m->instagram_account_info;
			$collections = $db->account_login;
			$collectionsUsername = $db->account_username;
			$date = date("dmY");

			$code = $_GET['code'];
			$data = $this->_instagram->getOAuthToken($code);
			$id = $data->user->id;
			$username = $data->user->username;

			//write username into session
			$this->Session->destroy();
			if($this->Session->check('username')){
				$this->Session->delete('username');
			}
			if($this->Session->check('id')){
				$this->Session->delete('id');
			}
			if($this->Session->check('access_token')){
				$this->Session->delete('access_token');
			}
			$this->Session->write('username', $username);
			$this->Session->write('id', $id);

			$setId = $collectionsUsername->find(array('id' => $id))->count();
			$setIdLogin = $collections->find(array('id' => $id))->count();
			if ($setId > 0) {
				$collections->remove(array('id' => $id));
				$collectionsUsername->remove(array('id' => $id));
				$collectionsUsername->insert(array(
						'id' => $id,
						'username' => $username,
						'access_token' => $data->access_token
				));
			} else {
				if($setIdLogin > 0) {
					$collections->remove(array('id' => $id));
				}
				$collections->insert(array(
						'access_token' => $data->access_token,
						'id' => $id,
						'username' => $username
				));
			}
			$this->Session->write('access_token', $data->access_token);
			// get account info
			$acc_info = $this->__getAccountInfo($username);

			// save account info into db
			$this->__saveAccountIntoDb($acc_info->user);
			// get media
			$media = $this->__getMedia($id, $data->access_token);
			$this->__saveMediaIntoDb($media, $username);
			$totalAccountInfo = $this->__totalAccountInfo($username);
			$totalMediaTop = $this->__totalMedia($username);
			if ($totalMediaTop['id']) {
				$mediaTop = array('id' => $totalMediaTop['id'], 'likesTop' => $totalMediaTop['likes'], 'commentsTop' => $totalMediaTop['comments'], 'media_get' => $totalMediaTop['media_get']);
			} else {
				$mediaTop = array('id' => $id, 'likesTop' => $totalMediaTop['likes'], 'commentsTop' => $totalMediaTop['comments'], 'media_get' => $totalMediaTop['media_get']);
			}
			$date = (new DateTime())->format('Y-m-d 00:00:00');
			$date = (string)strtotime($date);
			$totalMediaAnalytic = $this->__totalMedia($username, $date);
			$mediaAnalytic = array('likesAnalytic' => $totalMediaAnalytic['likes'], 'commentsAnalytic' => $totalMediaAnalytic['comments']);
			$this->__calculateReaction($username,$totalAccountInfo, $mediaTop, $mediaAnalytic);
// 			get follow list and save db
			$this->getFollow($id, $username);
			// after get data successful, redirect to Top page
			$this->redirect(array('controller' => 'top', 'action' => 'index'));
		} else {
			$this->redirect( array('controller' => 'register','action' => 'login' ));
		}
	}

	public function getDataRegister(){
		$this->layout = false;
		$this->autoRender = false;

		if(isset($_POST['username'])){
			$username = $_POST['username'];
			$acc = array();

			// get account info
			$acc_info = $this->__getAccountInfo($username);
			$acc['fullname'] = $acc_info->user->full_name;
			$acc['follower'] = number_format($acc_info->user->followed_by->count);
			$acc['totalMedia'] = $acc_info->user->media->count;
			$acc['id'] = $acc_info->user->id;
			$acc['is_private'] = $acc_info->user->is_private;
			$acc['username'] = $username;

			// save account info into db
			$this->__saveAccountIntoDb($acc_info->user);

			return json_encode($acc);
		}else{
			return false;
		}
	}

	public function getMediaRegister(){
		$this->layout = false;
		$this->autoRender = false;

		if(isset($_POST['infor'])){
			$infor = $_POST['infor'];
			$username = $infor['username'];
			$id = $infor['id'];
			$isPrivate = $infor['is_private'];

			$acc = array();
			$accessToken = $this->_token;

			if($isPrivate == 'false' || $isPrivate == false){
				// get media
				$media = $this->__getMedia($id, $accessToken);
				//save media
				$this->__saveMediaIntoDb($media, $username);
			}else {
				$media = array();
			}
			$acc['mediaGet'] = count($media);


			$totalAccountInfo = $this->__totalAccountInfo($username);
			$totalMediaTop = $this->__totalMedia($username);
			if ($totalMediaTop['id']) {
				$mediaTop = array('id' => $totalMediaTop['id'], 'likesTop' => $totalMediaTop['likes'], 'commentsTop' => $totalMediaTop['comments'], 'media_get' => $totalMediaTop['media_get']);
			} else {
				$mediaTop = array('id' => $id, 'likesTop' => $totalMediaTop['likes'], 'commentsTop' => $totalMediaTop['comments'], 'media_get' => $totalMediaTop['media_get']);
			}
			$date = (new DateTime())->format('Y-m-d 00:00:00');
			$date = (string)strtotime($date);
			$totalMediaAnalytic = $this->__totalMedia($username, $date);
			$mediaAnalytic = array('likesAnalytic' => $totalMediaAnalytic['likes'], 'commentsAnalytic' => $totalMediaAnalytic['comments']);
			//save analytic
			$this->__calculateReaction($username,$totalAccountInfo, $mediaTop, $mediaAnalytic);

			$acc['totalLike'] = number_format($totalMediaAnalytic['likes']);
			$acc['totalComment'] = number_format($totalMediaAnalytic['comments']);

			return json_encode($acc);
		}else{
			return false;
		}
	}

	private function __getAccountInfo($username) {
		$data = $this->cURLInstagram('https://www.instagram.com/' . $username . '/?__a=1');
		return $data;
	}

	private function __getMedia($id, $access_token) {
		$this->_instagram->setAccessToken($access_token);
		$max_id = null;
		$data = array();
		do {
			$media = $this->_instagram->getUserMedia($id, 10, $max_id);
			if(isset($media->data)){
				foreach ($media->data as $val) {
					$data[] = $val;
				}
			}
			if (isset($media->pagination) && !empty($media->pagination->next_max_id)) {
				$max_id = $media->pagination->next_max_id;
			} else {
				$max_id = null;
				break;
			}
		} while ($max_id != null);
		return $data;
	}

	private function __saveAccountIntoDb($acc_info) {
		$m = new MongoClient;
		$db = $m->instagram_account_info;
		$collection = $db->account_info;
		// insert new data
		$data = $collection->find(array('username' => $acc_info->username))->count();
		if($data > 0) {
			$collection->remove(
				array('username' => $acc_info->username)
			);
		}
		$collection->insert($acc_info, array('timeout' => -1));
	}

	private function __saveMediaIntoDb($media, $username) {
		$m = new MongoClient;
		$db = $m->instagram;
		$collection = $db->media;
		if(isset($media) && count($media) > 0) {
			$collection->remove(array("user.username" => $username));
			$collection->batchInsert($media, array('timeout' => -1));
		}
	}

	private function __totalAccountInfo($username) {
		//get data to account_info
		$m = new MongoClient;
		$dbAccount = $m->instagram_account_info;
		$collectionInfo = $dbAccount->account_info;
		$conditionInfo = array(
				array('$match' => array('username' => $username)),
				array(
						'$group' => array(
								'_id' => '$id',
								'username' => array('$first' => '$username'),
								'fullname' => array('$first' => '$full_name'),
								'followers' => array('$first' => '$followed_by.count'),
								'media_count' => array('$first' => '$media.count'),
								'is_private' => array('$first' => '$is_private')
						)
				)
		);
		$dataInfo = $collectionInfo->aggregate($conditionInfo);
		$result['username'] = isset($dataInfo['result'][0]['username']) ? $dataInfo['result'][0]['username'] : '';
		$result['fullname'] = isset($dataInfo['result'][0]['fullname']) ? $dataInfo['result'][0]['fullname'] : '';
		$result['media_count'] = isset($dataInfo['result'][0]['media_count']) ? $dataInfo['result'][0]['media_count'] : 0;
		$result['followers'] = isset($dataInfo['result'][0]['followers']) ? $dataInfo['result'][0]['followers'] : 0;
		return $result;
	}

	private function __totalMedia($username, $date = null) {
		$m = new MongoClient;
		$db = $m->instagram;
		$collection = $db->media;
		//get data to media
		if($date == null) {
			// data in top page
			$condition = array(
					array('$match' => array('user.username' => $username)),
					array(
							'$group' => array(
									'_id' => '$user.id',
									'total_likes' => array('$sum' => '$likes.count'),
									'total_comments' => array('$sum' => '$comments.count'),
									'media_get' => array('$sum' => 1)
							)
					)
			);
		} else {
			// data in analysis pages
			$condition = array(
					array('$match' => array('user.username' => $username, 'created_time' => array('$lt' => $date))),
					array(
							'$group' => array(
									'_id' => '$user.id',
									'total_likes' => array('$sum' => '$likes.count'),
									'total_comments' => array('$sum' => '$comments.count'),
							)
					)
			);
		}
		$data = $collection->aggregate($condition, array('maxTimeMS' => 3*60*1000));
		$result['id'] = isset($data['result'][0]['_id']) ? $data['result'][0]['_id'] : 0;
		$result['likes'] = isset($data['result'][0]['total_likes']) ? $data['result'][0]['total_likes'] : 0;
		$result['comments'] = isset($data['result'][0]['total_comments']) ? $data['result'][0]['total_comments'] : 0;
		$result['media_get'] = isset($data['result'][0]['media_get']) ? $data['result'][0]['media_get'] : 0;
		return $result;
	}

	private function __calculateReaction($username, $totalAccountInfo, $mediaTop, $mediaAnalytic) {
		// if the day is 1th of month we'll get data of the last day of previous month and save to db
		if (date('d') == '01') {
			$month = (new DateTime())->modify('-1 month')->format('m');
			$day = cal_days_in_month(CAL_GREGORIAN,$month,date('Y'));
			$currentTime = date('Y')."-".$month."-".$day;
		} else {
			$currentTime = (new DateTime())->modify('-1 day')->format('Y-m-d');
		}

		$m = new MongoClient;
		$dbAccount = $m->instagram_account_info;
		$time = date('Y-m', strtotime($currentTime));
		$collectionCaculate = $dbAccount->selectCollection($time);//2016-10

		$dateCurrent = $collectionCaculate->find(array('username' => $username, 'time' => new MongoDate(strtotime($currentTime)) ));
		if($dateCurrent->count() > 0){
			$collectionCaculate->remove(array(
					'username' => $username,
					'time' => new MongoDate(strtotime($currentTime))
			));
		}
		$date['time'] = new MongoDate(strtotime($currentTime)) ;
		$result = array_merge($totalAccountInfo, $mediaTop, $mediaAnalytic, $date);
		$collectionCaculate->insert($result);


	}

	public function register_hashtag($tags) {
		$tags = 'cat';
		$max_id = null;
		do {
			$data = $this->cURLInstagram('https://www.instagram.com/explore/tags/' . $tags . '/?__a=1&');
			print_r($data); break;
		} while (true);
	}

	public function getFollow($id, $username) {
		$mLogin = new MongoClient;
		$db = $mLogin->instagram;
		$collection = $db->follow;
		if($id) {
//          check $id exist in collections usernameDate ? "not do it" : "continue to check"
			$checkName = $collection->find(array($id => array('$exists' => 1)));
			if($checkName->count() <= 0) {
				$this->__getInfoFollow($username);
			}
		} else {
			return false;
		}

	}

	private function __getInfoFollow($username) {
		$mLogin = new MongoClient;
		$dbLogin = $mLogin->instagram_account_info;
		$colLogin = $dbLogin->account_login;
		$db = $mLogin->instagram;
		$coll = $db->follow;
		$username = $this->Session->read('username');
		$data = $colLogin->find(array('username' => $username), array('access_token' => true, 'id' => true));
		if ($data->count() > 0) {
			foreach($data as $access) {
				$id = $access['id'];
				$accessToken = $access['access_token'];
			}
			$this->_instagram->setToken($accessToken);
			//get follow list
			$arr = array();
			$cursor = null;
			do {
				if($cursor == null) {
					$infoFollowsBy = $this->_instagram->getUserFollower();
				} else {
					$infoFollowsBy = $this->_instagram->getUserFollower($cursor);
				}
				if(isset($infoFollowsBy) && !empty($infoFollowsBy->data)) {

					//get total follow each account
					$dataFollow = $infoFollowsBy->data;
					foreach ($dataFollow as $valFollow) {
						$this->_instagram->setToken($accessToken);
						$follow = $this->_instagram->getUserFollow($valFollow->id);
						if(isset($follow) && !empty($follow->meta) &&  $follow->meta->code == 400) {
							$usernamePrivate = $valFollow->username;
							$url = 'https://www.instagram.com/'.$usernamePrivate.'/?__a=1';
							$getUrl = $this->cURLInstagram($url);
							$countFollowBy = $getUrl->user->followed_by->count;
							$countFollows = $getUrl->user->follows->count;
						} else {
							$countFollowBy = $follow->data->counts->followed_by;
							$countFollows = $follow->data->counts->follows;
						}
						$arr[] = array(
								'id' => $valFollow->id, 'username' => $valFollow->username,
								'full_name' => $valFollow->full_name, 'totalFollow' => $countFollowBy,
								'follows' => $countFollows
						);
					}

				} else {
					echo "<pre>";
					print_r($infoFollowsBy);
				}

				if(isset($infoFollowsBy->pagination->next_cursor) && !empty($infoFollowsBy->pagination->next_cursor)) {
					$cursor = $infoFollowsBy->pagination->next_cursor;
				}
			} while (isset($infoFollowsBy->pagination->next_cursor) && !empty($infoFollowsBy->pagination->next_cursor));
			usort($arr, function($a, $b) { return $a['totalFollow'] < $b['totalFollow'] ? 1 : -1 ; } );
			$coll->insert(array($id => $arr));
		}
	}
}

