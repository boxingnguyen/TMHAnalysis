<?php
class StoryController extends AppController
{
	public function beforeFilter(){
		parent::beforeFilter();
	}
	public function index(){

		$this->layout = false;
		$this->autoRender=false;

		try {
		    $this->_story->login();
		} catch (Exception $e) {
		    $e->getMessage();
		    exit();
		}
		try {
		    $result = $this->_story->getReelsTrayFeed();
		    echo "<pre>";
		    print_r($result);
		} catch (Exception $e) {
		    echo $e->getMessage();
		}
	}

}

?>
