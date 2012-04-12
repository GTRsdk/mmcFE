<?php
$includeDirectory = "/var/www/pool/www/includes/";

//Include hashing functions
include($includeDirectory."requiredFunctions.php");
connectToDb();

//Open a bitcoind connection
$bitcoinController = new BitcoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);

// score vars
$f = .00001;

//Get site percentage
$sitePercent = 0;
$sitePercentQ = mysql_query("SELECT value FROM settings WHERE setting='sitepercent'");
if ($sitePercentR = mysql_fetch_object($sitePercentQ)) $sitePercent = $sitePercentR->value;

$overallReward = 0;

// command line args
if (isset($argv["1"])) { $lastNshares = $argv["1"]; } else { print("usage: $argv[0] <last N shares> <blocknumber>\n"); die(); }
if (isset($argv["2"])) { $block = $argv["2"]; } else { print("usage: $argv[0] <last N shares> <blocknumber>\n"); die(); }

if (isset($block)) {

	// LastNshares - determine block number lower boundary where N shares is met
	$l_bound = 0;
	$total = 0;
	if(!isset($lastNshares)) { $lastNshares = 1000000; }

	$sql = mysql_query("SELECT blockNumber, count FROM ( ".
				"SELECT blockNumber, count FROM `shares_uncounted` WHERE blockNumber <= " .$block. " ".
				"UNION SELECT blockNumber, count FROM `shares_counted` WHERE blockNumber <= " .$block. " AND blockNumber > ".($block - 1000)." ".
			   ")a ORDER BY blockNumber DESC");

	while ($result = mysql_fetch_object($sql)) {

		// increment $total with each row returned
		$total = $total + $result->count;

		// if $lastNshares criteria is met, and $l_bound is not our whole count, set everything below $l_bound as counted = 1
		if ($total >= $lastNshares) {
			$l_bound = $result->blockNumber;

			if ($l_bound < $block) {
				// mysql_query("UPDATE shares_uncounted SET counted = 1 WHERE blockNumber < ".$l_bound);
			}
			break;
		}
	}

	$totalRoundSharesQ = mysql_query("SELECT sum(id) as id FROM ( ".
					  "SELECT sum(count) as id FROM shares_uncounted WHERE blockNumber <= ".$block." AND blockNumber >= ".$l_bound." ".
					  "UNION SELECT sum(count) as id FROM shares_counted WHERE blockNumber <= " .$block. " AND blockNumber >= ".$l_bound."".
					 " )a");

	if ($totalRoundSharesR = mysql_fetch_object($totalRoundSharesQ)) {
		$totalRoundShares = $totalRoundSharesR->id;

		$userListCountQ = mysql_query("SELECT userId, sum(id) as id FROM ( ".
						  "SELECT DISTINCT userId, sum(count) as id FROM shares_uncounted WHERE blockNumber <= ".$block." AND blockNumber >= ".$l_bound." GROUP BY userId ".
						  "UNION DISTINCT SELECT userId, sum(count) as id FROM shares_counted WHERE blockNumber <= " .$block. " AND blockNumber >= ".$l_bound." GROUP BY userId ".
						 " )a GROUP BY userId");

		while ($userListCountR = mysql_fetch_object($userListCountQ)) {
			$userInfoR = mysql_fetch_object(mysql_query("SELECT DISTINCT username, donate_percent FROM webUsers WHERE id = '" .$userListCountR->userId. "'"));

			$username = $userInfoR->username;
			$uncountedShares = $userListCountR->id;
			$shareRatio = $uncountedShares/$totalRoundShares;
			$ownerId = $userListCountR->userId;
			$donatePercent = $userInfoR->donate_percent;

			//Take out site percent unless user is of early adopter account type
                        $account_type = account_type($ownerId);
                        if ($account_type == 0) {
				// is normal account
				$predonateAmount = (1-$f)*(50*$shareRatio);
				$predonateAmount = rtrim(sprintf("%f",$predonateAmount ),"0");
				$totalReward = $predonateAmount - ($predonateAmount * ($sitePercent/100));
			} else {
				// is early adopter round 1 0% lifetime fees
				$predonateAmount = 0.9999*(50*$shareRatio);
				$predonateAmount = rtrim(sprintf("%f",$predonateAmount ),"0");
				$totalReward = $predonateAmount;
			}

			if ($predonateAmount > 0.00000001)	{

				//Take out donation
				$totalReward = $totalReward - ($totalReward * ($donatePercent/100));

				//Round Down to 8 digits
				$totalReward = $totalReward * 100000000;
				$totalReward = floor($totalReward);
				$totalReward = $totalReward/100000000;

				//Get total site reward
				$donateAmount = round(($predonateAmount - $totalReward), 8);

				$overallReward += $totalReward;

				//Update account balance & site ledger
				echo $username.":".$ownerId." Tot_rew: ".$totalReward." Act_type: ".account_type($ownerId)." Dnt_amt: " .$donateAmount. " blk: " .$block. " shares: " .$uncountedShares. "\n";
			}
		}
		// find pool reward
		if (isset($B)) {
		 $poolReward = $B -$overallReward;
		}
	}
}

echo "overall: ".$overallReward."\n";
echo "pool: ".(50-$overallReward)."\n";

