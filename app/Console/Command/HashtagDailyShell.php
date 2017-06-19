<?php
class HashtagDailyShell extends AppShell {
	
	public function main() {
		$m = new MongoClient();
		$db = $m->hashtag;
		$media_daily = $db->media_daily;
		
		$date = date('d-m-Y');
		$this->__checkMediaDaily($date, $media_daily);
		
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
				$media = $this->getMediaHashtag($tag, null);
				if (isset($media->tag->media->count)) {
					$data = array(
							'total_media' => $media->tag->media->count,
							'tag' => $tag,
							'date' => new MongoDate(strtotime($date))
					);
					$insert = $media_daily->insert($data);
					if (!$insert) {
						echo "Insert total_media of #" . $tag . " failed!" . PHP_EOL;
					} else {
						echo "Media daily of #" . $tag . " completed!" . PHP_EOL;
					}
				} else {
					echo $tag . " do not have count field" . PHP_EOL;
					print_r($media);
				}
				exit;
			}
			foreach ($pids as $pid) {
				pcntl_waitpid($pid, $status);
				unset($pids[$pid]);
			}
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
	
	private function __checkMediaDaily($date, $collection) {
		$count = $collection->count(array('date' => $date));
		if ($count > 0) {
			$collection->remove(array('date' => $date));
		}
	}
}