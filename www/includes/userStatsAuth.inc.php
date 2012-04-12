<?php

function is_odd($i) {
	if (is_int($i / 2)) {
	 return 0;
    	} else {
         return 0;
    	}
}

function hashGraphs($graph = NULL, $userId = NULL, $interval = "48", $type = "area") {
 global $userInfo;

 // we need to load in a newer version of visualize for somet of what we want to do here.
 echo '<script type="text/javascript" src="js/jquery.visualize-bargraphs.js"></script>';

	if($graph == "mine") {

		echo "<br><h2>Hash Rates <font size='1'>(Last " .$interval. " Hours)</font></h2>";

		echo '<table class="stats" rel="' .$type. '" cellpadding="0" cellspacing="0" width="" style="">';
		echo '<caption style="padding:10px;">Your Avg. Hrly Hash Rate &nbsp;</caption>';

		$last24htsQ = mysql_query("SELECT timestamp, hashrate FROM userHashrates WHERE userId = ".$userId." AND timestamp > DATE_SUB(now(), INTERVAL ".$interval." HOUR) GROUP BY HOUR(timestamp) ORDER BY timestamp ASC");
		$last24hhrQ = mysql_query("SELECT timestamp, hashrate FROM userHashrates WHERE userId = ".$userId." AND timestamp > DATE_SUB(now(), INTERVAL ".$interval." HOUR) GROUP BY HOUR(timestamp) ORDER BY timestamp ASC");
		$poollast24htsQ = mysql_query("SELECT timestamp, hashrate FROM userHashrates WHERE userId = 0 AND timestamp > DATE_SUB(now(), INTERVAL ".$interval." HOUR) GROUP BY HOUR(timestamp) ORDER BY timestamp ASC");
		$pool_rows = mysql_num_rows($poollast24htsQ);
		$user_rows = mysql_num_rows($last24htsQ);
		$pad_rows = ($pool_rows - $user_rows);

		// intervals
		echo "<thead><tr>";
		$count = 0;
		while ($count < $pool_rows) {
			if (!is_odd($count)) echo "<th scope='col'>&nbsp;</th>\n\r";
			$count++;
		}
		echo "</tr></thead>";

		// hashrates
		echo "<tbody><tr><th scope='row'>" .$userInfo->username. " (Mh/s)</th>";
		$count = 0;
		while ($count < $pad_rows) {
			if (!is_odd($count)) echo "<td>0</td>\n\r";
			$count++;
		}

		$count = 0;
		while ($row = mysql_fetch_array($last24hhrQ, MYSQL_ASSOC)) {
			if (!is_odd($count)) echo "<td>" .$row["hashrate"]. "</td>\n\r";
			$count++;
		}

		echo "</tr></tbody>";

		echo "</table>";
	}

	if ($graph == "pool") {

		echo "<br><h2>Hash Rates <font size='1'>(Last " .$interval. " Hours)</font></h2>";

		echo '<table class="stats" rel="' .$type. '" cellpadding="0" cellspacing="0" width="" style="">';
		echo '<caption style="padding:10px;">Pool Avg. Hrly Hash Rate &nbsp;</caption>';

		$last24htsQ = mysql_query("SELECT timestamp, hashrate FROM userHashrates WHERE userId = 0 AND timestamp > DATE_SUB(now(), INTERVAL ".$interval." HOUR) GROUP BY HOUR(timestamp) ORDER BY timestamp ASC");
		$last24hhrQ = mysql_query("SELECT timestamp, hashrate FROM userHashrates WHERE userId = 0 AND timestamp > DATE_SUB(now(), INTERVAL ".$interval." HOUR) GROUP BY HOUR(timestamp) ORDER BY timestamp ASC");
		$poollast24htsQ = mysql_query("SELECT timestamp, hashrate FROM userHashrates WHERE userId = 0 AND timestamp > DATE_SUB(now(), INTERVAL ".$interval." HOUR) GROUP BY HOUR(timestamp) ORDER BY timestamp ASC");
		$pool_rows = mysql_num_rows($poollast24htsQ);

		// intervals
		echo "<thead><tr>";
		$count = 0;
		while ($count < $pool_rows) {
			if (!is_odd($count)) echo "<th scope='col'>&nbsp;</th>\n\r";
			$count++;
		}
		echo "</tr></thead>";

		// hashrates
		echo "<tbody><tr><th scope='row'>Pool (Gh/s)</th>";
		$count = 0;
		while ($row = mysql_fetch_array($last24hhrQ, MYSQL_ASSOC)) {
			if (!is_odd($count)) echo "<td>" .round(($row["hashrate"] / 1000), 0). "</td>\n\r";
			$count++;
		}
		echo "</tr></tbody>";

		echo "</table>";
	}

	if ($graph == "both") {

		echo "<br><h2>Hash Rates <font size='1'>(Last " .$interval. " Hours)</font></h2>";

		echo '<table class="stats" rel="' .$type. '" cellpadding="0" cellspacing="0" width="" style="">';
		echo '<caption style="padding:10px;">Your vs. Pool Avg. Hrly Hash Rate &nbsp;</caption>';

		$last24htsQ = mysql_query("SELECT timestamp, hashrate FROM userHashrates WHERE userId = ".$userId." AND timestamp > DATE_SUB(now(), INTERVAL ".$interval." HOUR) GROUP BY HOUR(timestamp) ORDER BY timestamp ASC");
		$last24hhrQ = mysql_query("SELECT timestamp, hashrate FROM userHashrates WHERE userId = ".$userId." AND timestamp > DATE_SUB(now(), INTERVAL ".$interval." HOUR) GROUP BY HOUR(timestamp) ORDER BY timestamp ASC");
		$poollast24htsQ = mysql_query("SELECT timestamp, hashrate FROM userHashrates WHERE userId = 0 AND timestamp > DATE_SUB(now(), INTERVAL ".$interval." HOUR) GROUP BY HOUR(timestamp) ORDER BY timestamp ASC");
		$poollast24hhrQ = mysql_query("SELECT timestamp, hashrate FROM userHashrates WHERE userId = 0 AND timestamp > DATE_SUB(now(), INTERVAL ".$interval." HOUR) GROUP BY HOUR(timestamp) ORDER BY timestamp ASC");
		$pool_rows = mysql_num_rows($poollast24htsQ);
		$user_rows = mysql_num_rows($last24htsQ);
		$pad_rows = ($pool_rows - $user_rows);

		// intervals
		echo "<thead><tr>";
		$count = 0;
		while ($count < $pool_rows) {
			if (!is_odd($count)) echo "<th scope='col'>&nbsp;</th>\n\r";
			$count++;
		}
		echo "</tr></thead>";

		// hashrates
		echo "<tbody><tr><th scope='row'>" .$userInfo->username. " (Gh/s)</th>";

		// User
		$count = 0;
		while ($count < $pad_rows) {
			if (!is_odd($count)) echo "<td>0</td>\n\r";
			$count++;
		}

		$count = 0;
		while ($row = mysql_fetch_array($last24hhrQ, MYSQL_ASSOC)) {
			if (!is_odd($count)) echo "<td>" .round(($row["hashrate"] / 1000), 2). "</td>\n\r";
			$count++;
		}

		echo "</tr><tr><th scope='row'>Pool (Gh/s)</th>";
		// Pool
		$count = 0;
		while ($row = mysql_fetch_array($poollast24hhrQ, MYSQL_ASSOC)) {
			if (!is_odd($count)) echo "<td>" .round(($row["hashrate"] / 1000), 2). "</td>\n\r";
			$count++;
		}

		echo "</tr></tbody>";

		echo "</table>";

	}
}

function financialGraphs($graph = NULL, $userId = NULL, $interval = "30", $type = "area") {
 global $userInfo;

	if($graph == "mine") {

		echo "<br><h2>Financial Data</h2>";

		echo '<table class="stats" rel="' .$type. '" cellpadding="0" cellspacing="0" width="" style="">';
		echo '<caption style="padding:10px;">Income Last ' .$interval. ' day(s) &nbsp;</caption>';

		$userEarningsQ = mysql_query("SELECT round(sum(amount), 4) as earnings, date(timestamp) as day FROM ledger where userId = ".$userId." AND transType = \"Credit\" GROUP BY DAY(timestamp) ORDER BY timestamp DESC LIMIT " .$interval);
		$userEarningsDaysQ = mysql_query("SELECT round(sum(amount), 4) as earnings, UNIX_TIMESTAMP(timestamp) as day FROM ledger where userId = ".$userId." AND transType = \"Credit\" GROUP BY DAY(timestamp) ORDER BY timestamp DESC LIMIT " .$interval);
		$user_rows = mysql_num_rows($userEarningsQ);

		// intervals
		echo "<thead><tr>";
		$count = 0;
		while ($row = mysql_fetch_array($userEarningsDaysQ, MYSQL_ASSOC)) {
			echo "<th scope='col'>" .date('m.d.y', $row["day"]). "</th>\n\r";
			$count++;
		}
		echo "</tr></thead>";

		// amounts
		echo "<tbody><tr><th scope='row'>" .$userInfo->username. " (BTC)</th>";

		$count = 0;
		while ($row = mysql_fetch_array($userEarningsQ, MYSQL_ASSOC)) {
			echo "<td>" .$row["earnings"]. "</td>\n\r";
			$count++;
		}

		echo "</tr></tbody>";

		echo "</table>";
	}

}
?>

