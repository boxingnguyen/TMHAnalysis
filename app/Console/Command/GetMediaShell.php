 <?php
class GetMediaShell extends AppShell {
	
	public function main() {
		$start_time = microtime(true);
		$m = new MongoClient();
		$db = $m->instagram;
		$collection = $db->media;
		
		$all_account = $this->__sortAccountByMedia();
		$date = date("dmY");
		
		if (!empty($all_account)) {
			// drop old data
			$collection->drop();
			// empty file contain missing account
			file_put_contents(APP."Vendor/Data/tmp_missing_acc.json", "");
			// we get data of 34 accounts at a time
			$account_chunks = array_chunk($all_account, 34);
			foreach ($account_chunks as $account) {
				foreach ($account as $name) {
					// create 2 processes here
					$pid = pcntl_fork();
					if ($pid == -1) {
						die('could not fork');
					} else if ($pid) {
						// we are the parent
						// collect process id to know when children complete
						$pids[] = $pid;
					} else {
						// we are the child
						$max_id = null;
						$myfile = fopen(APP."Vendor/Data/".$date.".".$name.".media.json", "w+") or die("Unable to open file!");
						do {
							$data = $this->__getMedia($name, $max_id);
							// write data into json file
							if (isset($data->items) && !empty($data->items)) {
								foreach ($data->items as $val) {
									fwrite($myfile, json_encode($val)."\n");
								}
								$max_id = end($data->items)->id;
							} else {
								$this->out("Error: data is null");
								break;
							}
						}
						while (isset ($data->more_available) && ($data->more_available == true || $data->more_available == 1));
						fclose($myfile);
						
						// check if account's media is missing or not
						$checkMedia = $this->__checkMedia($name);
						if ($checkMedia) {
							// write data from json file to database
							$this->__saveIntoDb($name, $collection, $date);
							echo "Get media of " . $name . " completed!" . PHP_EOL;
						} else {
							file_put_contents(APP."Vendor/Data/tmp_missing_acc.json", $name . "\n", FILE_APPEND | LOCK_EX);
							echo "Media of " . $name . " is missing!!!!!!!" . PHP_EOL;
						}
						// Jump out of loop in this child. Parent will continue.
						exit;
					}
				}
				foreach ($pids as $pid) {
					pcntl_waitpid($pid, $status);
					unset($pids[$pid]);
				}
			}
			// re-get media if media is missing (maximum 5 times)
			$missing_account = file(APP."Vendor/Data/tmp_missing_acc.json");
			foreach ($missing_account as $name) {
				$name = trim(preg_replace('/\s\s+/', ' ', $name));
				echo "Account " . $name . " has missing mediaaaaaaaaaaa" . PHP_EOL;
				$check_count = 0;
				$checkMedia = false;
				while (!$checkMedia && $check_count < 5) {
					$checkMedia = $this->__reGetMedia($name, $date);
					$check_count ++;
				}
				if (!$checkMedia) {
					echo "Re-get media of " . $name . " failed!!!!!!!!!!" . PHP_EOL;
				} else {
					echo "Re-get media of " . $name . " successfully!" . PHP_EOL;
				}
				// write data into database
				$this->__saveIntoDb($name);
			}
			// indexing
			echo "Indexing media ..." . PHP_EOL;
			$collection->createIndex(array('user.id' => 1, 'created_time' => 1), array('timeout' => -1, 'background' => true));
			echo "Indexing media completed!" . PHP_EOL;
			echo "Total documents: " . $collection->count() . PHP_EOL;
			$end_time = microtime(true);
			echo "Time to get all media: " . ($end_time - $start_time) . " seconds" . PHP_EOL;
		}
	}
	
	private function __getMedia($username, $max_id = null) {
		if ($max_id != null) {
			$data = $this->cURLInstagram('https://www.instagram.com/' . $username . '/media/?max_id=' . $max_id);
		} else {
			$data = $this->cURLInstagram('https://www.instagram.com/' . $username . '/media/');
		}
		return $data;
	}
	
	private function __sortAccountByMedia() {
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$collection = $db->account_info;
		
		$data = $collection->find()->sort(array('media.count' => -1))->fields(array('username' => true, 'media.count' => true));
		$result = array();
		foreach ($data as $value) {
			$result[] = $value['username'];
		}
		return $result;
	}
	
	private function __checkMedia($name) {
		if (isset($name) && !empty($name)) {
			$date = date("dmY");
			$filename = APP . "Vendor/Data/" . $date . "." . $name . ".media.json";
			$fp = file($filename);
			$lines = count($fp);
			
			$m = new MongoClient();
			$db = $m->instagram_account_info;
			$collection = $db->account_info;
			$query = array('username' => $name);
			$result = $collection->find($query,array('media.count','media.nodes'));
			$total_media = 0;
			foreach ($result as $v) {
				$total_media = $v['media']['count'];
				$timeMediaFirst = $v['media']['nodes'][0]['date'];
			}

			$miss_count = $total_media - $lines;
			if ($miss_count >= 0 && $miss_count <= 10 ) {
				$this->out ('0 <= miss <= 10 : ' . $miss_count . ' ~ ' . $name);
				return true;
			} elseif ($miss_count >= -10 && $miss_count < 0) {
				$this->out ('-10 <= miss < 0 : ' . $miss_count . ' ~ ' . $name);
				// remove data is over
				for ($i = 0; $i < 10; $i++) {
					$current_line = json_decode($fp[$i]);
					if (intval($current_line->created_time) > $timeMediaFirst) {
						unset($fp[$i]);
					}
				}
				file_put_contents($filename, implode("", $fp));
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	private function __reGetMedia($name, $date) {
		$max_id = null;
		$myfile = fopen(APP . "Vendor/Data/" . $date . "." . $name . ".media.json", "w+") or die("Unable to open file!");
		do {
			$data = $this->__getMedia($name, $max_id);
			// write data into json file
			if (isset($data->items) && !empty($data->items)) {
				foreach ($data->items as $val) {
					fwrite($myfile, json_encode($val)."\n");
				}
				$max_id = end($data->items)->id;
			} else {
				$this->out("Error: data is null");
				break;
			}
		}
		while (isset ($data->more_available) && ($data->more_available == true || $data->more_available == 1));
		fclose($myfile);
		// re-check if media of this account is not missing anymore
		return $this->__checkMedia($name);
	}
	
	private function __saveIntoDb($name, $collection, $date) {
		$filename = APP . "Vendor/Data/" . $date . "." . $name . ".media.json";
		$file = fopen($filename, "r");
		$data = array();
		if ($file) {
			while (($line = fgets($file)) !== false) {
				// store media into an array
				$data[] = json_decode($line);
				// write data to mongo if media count = 1000 (to avoid batchInsert is too large, maximum 48000000 bytes ~ 2000 medias (after json_decode))
				if (count($data) == 1000) {
					$collection->batchInsert($data, array('timeout' => -1));
					unset($data);
				}
			}
			fclose($file);
		}
		// insert remaining media into mongo
		if (isset($data) && count($data) > 0) {
			$collection->batchInsert($data, array('timeout' => -1));
		}
	}
}