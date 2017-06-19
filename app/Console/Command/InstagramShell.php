<?php
class InstagramShell extends AppShell {
	public function main() {
		$start_time = microtime(true);
		passthru(ROOT."/app/Console/cake GetAccountInfo");
		passthru(ROOT."/app/Console/cake GetAccountInfoLogin");
		passthru(ROOT."/app/Console/cake GetMedia");
		passthru(ROOT."/app/Console/cake CalculateReaction");
		passthru(ROOT."/app/Console/cake FollowRanking");
		$end_time = microtime(true);
		echo "Time to complete this program: " . (($end_time - $start_time) / 60) . " minutes" . PHP_EOL;
	}
}