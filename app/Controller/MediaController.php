<?php
App::uses('AppController', 'Controller');
class MediaController extends AppController {
	public function index () {
		$this->layout = false;
		if (isset($this->params->query['id']) && !empty($this->params->query['id'])){
			$id = $this->params->query['id'];
			if($this->Session->check('User.id')){
				$this->Session->delete('User.id');
				$this->Session->write('User.id',$id);
			}else{
				$this->Session->write('User.id',$id);
			}

			$m = new MongoClient();
			$db = $m->instagram_account_info;
			$collections = $db->account_info;

			$query = array('id' => $id);
			$cursor = $collections->find($query,array());
			$acc = array();
			foreach ($cursor as $value){
				$acc = $value;
			}
			$this->set('inforAcc', $acc);
		}else {
			$this->redirect(array("controller" => "top","action" => "index"));
		}
	}

	public function more(){
		$this->layout = false;
		$this->autoRender = false;
		if($this->Session->check('User.id')){
			$id = $this->Session->read('User.id');
		}else{
			$id = '';
		}
		$access_token = $this->_token;
		$this->_instagram->setAccessToken($access_token);
		$userId = $this->Session->read('id');
		$m = new MongoClient();
		$db = $m->instagram;
		$collections = $db->media;

		$page = isset($_POST['page']) ? $_POST['page'] : 1;
		$limit = 5;
		$start= ($page*$limit)-$limit;

		$query = array('user.id' => $id);
		$cursor = $collections->find($query,array())->sort(array('created_time'=>-1))->skip($start)->limit($limit);
		$access_token = $this->Session->read('access_token');
		$this->_instagram->setAccessToken($access_token);

		$data= array();
		foreach ($cursor as $value){
			$value['likes']['count'] = number_format($value['likes']['count']);
			$value['comments']['count'] = number_format($value['comments']['count']);

			$idMedia = $value['id'];
			if($this->Session->check('checkLiked'.$idMedia)){

				$checkLiked = $this->Session->read('checkLiked'.$idMedia);
			}
			else{
				$userLiked = $this->_instagram->getMedia($idMedia);
		    	$checkLiked = $userLiked->data->user_has_liked;
	        	$this->Session->write('checkLiked'.$idMedia, $checkLiked);
			}
	        $value['current_user_has_liked'] = $checkLiked;
			$data[]=$value;
		}
		return json_encode($data);
	}
	public function showComment(){
		$this->layout = false;
		$this->autoRender = false;
		$link = $_POST['link'];
		$result = $this->cURLInstagram($link."?__a=1")->graphql->shortcode_media->edge_media_to_comment->edges;
		$data = array();
		$t=0;
		foreach ($result as $value) {
			if(count($result)<6){
				$data[]=$value;
			}
			else{
				if($t>count($result)-6){
					$data[]=$value;
					$t++;
				}
				else{
					$t++;
				}
			}
		}
		return json_encode($data);
	}
	public function postComment(){
		$this->layout = false;
		$this->autoRender = false;

		$idMedia = $_POST['id'];
		$idAccount = $this->Session->read('id');
		$username = $this->Session->read('username');
		$text = $_POST['text'];
		$access_token = $this->Session->read('access_token');
		$this->_instagram->setAccessToken($access_token);
		$selectt = $this->_instagram->addMediaComment($idMedia,$text);
		$link = $_POST['link'];
		$result = $this->cURLInstagram($link."?__a=1")->graphql->shortcode_media->edge_media_to_comment->edges;
		$data = array();
		$t=0;
		foreach ($result as $value) {
			if(count($result)<6){
				$data[]=$value;
			}
			else{
				if($t>count($result)-6){
					$data[]=$value;
					$t++;
				}
				else{
					$t++;
				}
			}
		}
		return json_encode($data);
	}
	public function total(){
		$this->layout = false;
		$this->autoRender = false;

		if($this->Session->check('User.id')){
			$id = $this->Session->read('User.id');
		}else{
			$id = '';
		}
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$collections = $db->account_info;

		$query = array('id' => $id);
		$cursor = $collections->find($query,array());
		$total = 0;
		foreach ($cursor as $value){
			$total = $value['media']['count'];
		}
		return $total;
	}
	public function postLike(){
		$this->layout = false;
		$this->autoRender = false;
		$token = $this->Session->read('access_token');
		$this->_instagram->setAccessToken($token);
		//print_r($token);
		$like_status = $_POST['like_status'];
		$id = $_POST['media_id'];
		$numLikes = $_POST['num_likes'];
		$m = new MongoClient();
		$db = $m->instagram;
		$collection = $db->media;
		if($like_status == "false"){
			$like = $this->_instagram->likeMedia($id);
		}
		else{
			$unlike = $this->_instagram->deleteLikedMedia($id);
		}
		if ($like->meta->code === 200) {
			$collection->update(
				array('id' => $id),
				array('$set' => array('likes.count' => $numLikes +1))
			);
			$this->Session->write('checkLiked'.$id, 1);
		 	echo json_encode("Success! The image was liked ");
		} else if($unlike->meta->code === 200){
			$collection->update(
				array('id' => $id),
				array('$set' => array('likes.count' => $numLikes -1))
			);
			$this->Session->write('checkLiked'.$id, 0);
			echo json_encode("Success! The image was unliked");
		}
		else {
		  echo json_encode("Something's wrong");
		}
	}
	public function testUserLike(){
		$this->layout = false;
		$this->autoRender = false;
		$access_token = $this->_token;
		$this->_instagram->setAccessToken($access_token);
		$idMedia = '1357859302744084395_4025731782';
		$userLiked = $this->_instagram->getMedia($idMedia);
		var_dump($userLiked->data->user_has_liked);
	}
}
