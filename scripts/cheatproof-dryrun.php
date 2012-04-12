<?php
$includeDirectory = "/var/www/pool/www/includes/";

//Include hashing functions
include($includeDirectory."requiredFunctions.php");
connectToDb();

//try {
//Open a bitcoind connection
$bitcoinController = new BitcoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);

//Get difficulty
$difficulty = $bitcoinController->query("getdifficulty");

$overallReward = 0;
$poolEstimate = 0;

//Get site percentage
$sitePercent = 0;
$sitePercentQ = mysql_query("SELECT value FROM settings WHERE setting='sitepercent'");
if ($sitePercentR = mysql_fetch_object($sitePercentQ)) $sitePercent = $sitePercentR->value;

//Setup score variables
$c = .00001;
$f=1;
$f = $sitePercent / 100;
$p = 1.0/$difficulty;
$r = log(1.0-$p+$p/$c);
$B = 50;
$los = log(1/(exp($r)-1));


	// Set here the <number of confirms> minus one before a user will be paid out. For 120 confirms, set to 119
	$num_confirms = '-1';
	$overallReward = 0;
	if (!isset($txAmount)) { $txAmount = 0; }

	$blocksQ = mysql_query("SELECT DISTINCT s.blockNumber FROM shares_history s, networkBlocks n ".
				 "WHERE s.blockNumber = n.blocknumber AND s.counted = 0 AND n.confirms > ".$num_confirms." ORDER BY s.blockNumber DESC LIMIT 1");

	while ($blocks = mysql_fetch_object($blocksQ)) {
		$block = $blocks->blockNumber;

		$totalscoreQ = mysql_query("SELECT (sum(exp(s1.score-s2.score))+exp(".$los."-s2.score)) AS score FROM shares_history s1, shares_history s2 ".
					   "WHERE s2.id = s1.id - 1 AND s1.counted = 0 AND s1.our_result != 'N' AND s1.blockNumber <= ".$block);
		$totalscoreR = mysql_fetch_object($totalscoreQ);
		$totalscore = $totalscoreR->score;

		$userListCountQ = mysql_query("SELECT DISTINCT s1.username, count(s1.id) AS id, sum(exp(s1.score-s2.score)) AS score FROM shares_history s1, shares_history s2 ".
					      "WHERE s2.id = s1.id -1 AND s1.counted = 0 AND s1.blockNumber <= ".$block." AND s1.our_result != 'N' GROUP BY username");
		while ($userListCountR = mysql_fetch_object($userListCountQ)) {
			$username = $userListCountR->username;
			$uncountedShares = $userListCountR->id;
			$score = $userListCountR->score;

			//get owner userId and donation percent
			$ownerIdQ = mysql_query("SELECT p.associatedUserId, u.donate_percent FROM pool_worker p, webUsers u ".
						"WHERE u.id = p.associatedUserId AND p.username = '".$username."' LIMIT 0,1");
			$ownerIdObj = mysql_fetch_object($ownerIdQ);
			if ($ownerIdObj) {
			 $ownerId = $ownerIdObj->associatedUserId;
			 $donatePercent = $ownerIdObj->donate_percent;
			}

			//Take out site percent unless user is of early adopter account type
                        $account_type = account_type($ownerId);
echo $account_type . " ; ";
                        if ($account_type == 0) {
				// is normal account
				$predonateAmount = (1-$f)*$B*$score/$totalscore;
				$predonateAmount = rtrim(sprintf("%f",$predonateAmount ),"0");
			} else {
				// is early adopter round 1 0% lifetime fees
				$predonateAmount = 0.9999*$B*$score/$totalscore;
				$predonateAmount = rtrim(sprintf("%f",$predonateAmount ),"0");
			}

			if ($predonateAmount > 0.00000001) {
				//Take out donation
				$totalReward = $predonateAmount - ($predonateAmount * ($donatePercent/100));

				//Round Down to 8 digits
				$totalReward = $totalReward * 100000000;
				$totalReward = floor($totalReward);
				$totalReward = $totalReward/100000000;

				//Get total site reward
				$donateAmount = $predonateAmount - $totalReward;
				$overallReward += $totalReward;

			echo $username.": ".$totalReward." : ".$uncountedShares."\n";

				// Update user balance
				 //$updateOk = mysql_query("UPDATE accountBalance SET balance = balance + ".$totalReward." WHERE userId = ".$ownerId);
				 //if (!$updateOk) {
				 //	mysql_query("INSERT INTO accountBalance (userId, balance) VALUES (".$ownerId.",'".$totalReward."')");
				 //}
			}
			//mysql_query("UPDATE shares_history SET counted = '1' WHERE username='".$username."' AND blockNumber <= ".$block);
		}

		// Update Site Reward
		if ($overallReward) {
			$poolReward = round(($B + $txAmount - $overallReward), 8);
			if ($poolReward <= 0) { $poolReward = 0; }
			//mysql_query("UPDATE settings SET value = value +".$poolReward." WHERE setting='sitebalance'");
		}
	}



echo "\noverallReward: ".$overallReward."\n";
echo "pool reward: ".$poolReward."\n";
echo "pool fee: ".$sitePercent."%\n";
if (isset($totalRoundShares)) { echo "roundshares: ".$totalRoundShares."\n"; }


?>
