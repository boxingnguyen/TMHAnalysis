<?php
class HashtagMonthlyShell extends AppShell {
	public function main() {
		$m = new MongoClient();
		$db = $m->hashtag;
		$c_media = $db->media;
		$c_media->drop();

		$date = date('MY');
		
		$previous_month = intval(date('m')) - 1; 
		
		$hashtag = $this->__getHashtag();
		foreach ($hashtag as $tag) {
			// create 2 processes here
			$pid = pcntl_fork();
			if ($pid == -1) {
				die('could not fork');
			} else if ($pid) {
				// we are the parent
				// collect process id to know when children complete
				$pids[] = $pid;
			} else {
				// we are childs
				echo "Get hashtag of " . $tag . '...' . PHP_EOL;
				$max_id = null;
				// write data into json file
				$count = 0;
				$tmp = array ();
				$myfile = fopen(APP . "Vendor/Hashtag/" . $date . "." . $tag . ".media_hashtag.json", "w+") or die("Unable to open file!");
				do {
					$media = $this->getMediaHashtag($tag, $max_id);
					
					if (!isset($media->tag->media->nodes) || empty($media->tag->media->nodes)) {
						echo "Last media of " . $tag . ": " . PHP_EOL;
						print_r(end($tmp));
						break;
					}
					$data = $media->tag->media->nodes;
					$tmp = $data;
					foreach ($data as $value) {
						$count ++;
						// do not get media of October
						if (isset($value->date) && intval(date('m', $value->date)) > $previous_month) {
							continue;
						} else if (isset($value->date) && intval(date('m', $value->date)) < $previous_month) {
							echo $tag . " get full media" . PHP_EOL;
							// do not get media of month before September
							break 2;
						} else {
							$value->date = date('d-m-Y', $value->date);
							$value->tag_name = $tag;
							fwrite($myfile, json_encode($value) . "\n");
						}
					}
					// get next page of data
					$max_id = isset($media->tag->media->page_info->end_cursor) ? $media->tag->media->page_info->end_cursor : null;
				} while ($media->tag->media->page_info->has_next_page == 1);
				
				echo "Total media of " . $tag . ": " . $count . PHP_EOL;
				
				echo "Get HASHTAG of " . $tag . " completed!" . PHP_EOL;
				
				$this->__saveIntoDb($tag, $c_media, $date);
				
				echo "Save data of #" . $tag . " into DB completed!" . PHP_EOL;
				
				// Jump out of loop in this child. Parent will continue.
				exit;
			}
		}
		foreach ($pids as $pid) {
			pcntl_waitpid($pid, $status);
			unset($pids[$pid]);
		}
	}
	
	private function __getHashtag() {
		$m = new MongoClient();
		$db = $m->hashtag;
		$c = $db->tags;
		$hashtag = $c->find(array(), array('tag' => true, '_id' => false));
		$tags = array();
		foreach ($hashtag as $value) {
			$tags[] = str_replace("#", "", $value['tag']);
		}
		return $tags;
	}
	
	private function __saveIntoDb($tag, $collection, $date) {
		$filename = APP . "Vendor/Hashtag/" . $date . "." . $tag . ".media_hashtag.json";
		$all_lines = file($filename);
		if (empty($all_lines)) {
			echo "#" . $tag . " has no media or something wrong!" . PHP_EOL;
			return;
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
				$data[] =  json_decode($value);
			}
			$collection->batchInsert($data, array('timeout' => -1));
			unset($data);
		}
	}
}