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
$blocksQ = mysql_query("SELECT DISTINCT s.blockNumber FROM shares_uncounted s, networkBlocks n WHERE s.blockNumber = n.blocknumber AND s.counted=0 ORDER BY s.blockNumber DESC LIMIT 1");

while ($blocks = mysql_fetch_object($blocksQ)) {
	$block = $blocks->blockNumber;

	$totalRoundSharesQ = mysql_query("SELECT sum(count) as id FROM shares_uncounted WHERE blockNumber <= ".$block." AND counted = 0");
	if ($totalRoundSharesR = mysql_fetch_object($totalRoundSharesQ)) {
		$totalRoundShares = $totalRoundSharesR->id;

		$userListCountQ = mysql_query("SELECT DISTINCT userId, sum(count) as id FROM shares_uncounted WHERE blockNumber <= ".$block." AND counted = 0 GROUP BY userId");

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

				//Update account balance & ledger
				echo $username.":".$ownerId." Tot_rew: ".$totalReward." Act_type: ".account_type($ownerId)." Dnt_amt: " .$donateAmount. " blk: " .$block. " shares: " .$uncountedShares. "\n";
			}

		}
		// update site wallet with our reward from this block
		if (isset($B)) {
		 $poolReward = $B -$overallReward;
		}
	}
}

echo "overall: ".$overallReward."\n";
echo "pool: ".(50-$overallReward)."\n";


?>
