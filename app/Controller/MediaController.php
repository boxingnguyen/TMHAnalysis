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

		$m = new MongoClient();
		$db = $m->instagram;
		$collections = $db->media;

		$page = isset($_POST['page']) ? $_POST['page'] : 1;
		$limit = 20;
		$start= ($page*$limit)-$limit;

		$query = array('user.id' => $id);
		$cursor = $collections->find($query,array())->sort(array('created_time'=>-1))->skip($start)->limit($limit);

		$data= array();
		foreach ($cursor as $value){
			$value['likes']['count'] = number_format($value['likes']['count']);
			$value['comments']['count'] = number_format($value['comments']['count']);
			$data[]=$value;
		}
		return json_encode($data);
	}

	public function postComment(){
		$this->layout = false;
		$this->autoRender = false;

		$idMedia = $_POST['id'];
		$username = $this->Session->read('username');
		$text = $_POST['text'];
		$access_token = $this->_token;
		$this->_instagram->setAccessToken($access_token);
		$selectt = $this->_instagram->addMediaComment($idMedia,$text);
		if($selectt->meta->code == 400){
			$error = 400;
			return $error;
		}
		else return $username;

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
			$token = $this->_token;
			$this->_instagram->setAccessToken($token);
// 			print_r($token);
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
					array('$set' => array('user_has_liked' => 'true', 'likes.count' => $numLikes +1))
				);
			 	return json_encode("Success! The image was liked ");
			} else if($unlike->meta->code === 200){
				$collection->update(
					array('id' => $id),
					array('$set' => array('user_has_liked' => 'false', 'likes.count' => $numLikes -1))
				);
				echo json_encode("Success! The image was unliked");
			}
			else {
			  echo json_encode("Something's wrong");
			}
	}
}
