<?php
class TopController extends AppController {
	public function index () {
		if(!$this->Session->check('username')){
			$this->redirect( array('controller' => 'register','action' => 'login' ));
		}
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		
		$collection = $db->selectCollection(date('Y-m'));
		
		$currentDate = (new DateTime())->modify('-1 day')->format('Y-m-d');

		if (isset( $this->params['url']['private'] )) {
			$status = $this->params['url']['private'] === 'true' ? true : false;
			$data = $collection->find(array('time' => $currentDate, 'is_private' => $status))->sort(array('followers' => -1));
		}else {
			$data = $collection->find(array('time' => $currentDate))->sort(array('followers' => -1));
		}
		
		$this->set(compact('data'));
	}
}