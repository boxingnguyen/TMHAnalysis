<?php
class GetAccountInfoLoginShell extends AppShell {
	public $m;
	public $db;
	public $countReSend = 0;
	
	public function initialize() {
		parent::initialize();
		$this->m = new MongoClient;
		$this->db = $this->m->instagram_account_info;
	} 
	
	public function main() { 
		$time_start = microtime(true);
		$colUser = $this->db->account_username;
		$dbUser = $colUser->find(array(), array('username' => true));
		
		$all_account = array();
		if($dbUser->count() > 0) {
			foreach ($dbUser as $accUser) {
				if ($accUser['username'] == null) {
					$this->db->account_login->remove(array('username' => null));
					continue;
				}
				$all_account[] = $accUser['username'];
			}
		}
		
		$acc_login = $this->db->account_login->find(array(), array('username' => true));
		$collections = $this->db->account_info_login;
		$collections->drop();
		if($acc_login->count() > 0) {
			foreach ($acc_login as $accLogin) {
				if ($accLogin['username'] == null) {
					$this->db->account_login->remove(array('username' => null));
					continue;
				}
				array_push($all_account,$accLogin['username'] );
			}
		}
		$data = $this->__getAccountInfo($all_account);
		if(!empty($data)) {
			$collections->batchInsert($data);
		}	
		$time_end = microtime(true);
		echo "Time to get all account: " . ($time_end - $time_start) . " seconds" . PHP_EOL;
	}

/**
 * Get data of instagram's account
 * @param string $username
 * @return object account's data
 */
	private function __getAccountInfo($username) {
		if(!empty($username)) {
			foreach ($username as $val) {
				$data[] = $this->cURLInstagram('https://www.instagram.com/' . $val . '/?__a=1')->user;
				echo PHP_EOL."Account ".$val.PHP_EOL;
			}
			return $data;
		} else {
			return false;			
		}
		
	}
}