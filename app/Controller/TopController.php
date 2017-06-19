<?php
App::import('Vendor','Package',array('file'=>'vendor/autoload.php'));
class TopController extends AppController {
	public $instagram;
	const DEBUG = false;
	public function beforeFilter(){
		parent::beforeFilter();
	}
	public function index () {
		if(!$this->Session->check('username')){
			$this->redirect( array('controller' => 'register','action' => 'login' ));
		}
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		
		$currentDate = (new DateTime())->modify('-1 day')->format('Y-m-d');
		$time = date('Y-m', strtotime($currentDate));
		$collection = $db->selectCollection($time);
		$collection->remove(array('username' => " "));
		if (isset( $this->params['url']['private'] )) {
			$status = $this->params['url']['private'] === 'true' ? true : false;
			$data = $collection->find(array('time' => new MongoDate(strtotime($currentDate)) , 'is_private' => $status))->sort(array('followers' => -1));
		}else {
			$data = $collection->find(array('time' => new MongoDate(strtotime($currentDate)) ))->sort(array('followers' => -1));
		}
		
		$this->set(compact('data'));
	}
	public function story(){
		$this->layout=false;
		$this->autoRender=false;
		$instagram = new \InstagramAPI\Instagram("tmh_techlab","!!tmhtechlab20150123",self::DEBUG);
		$this->_Instagram->login();
		$data = $this->_Instagram->getReelsTrayFeed();
		//$data = $this->_Instagram->aaa();
		print_r($data);die;
	}
}
