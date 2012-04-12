<?php

$bitcoinController = new BitcoinClient($rpcType, $rpcUsername, $rpcPassword, $rpcHost);
$difficulty = round($bitcoinController->query("getdifficulty"));

function shares_per_block($count = "10") {
	global $difficulty, $settings;

        $wonblocksQ = mysql_query("SELECT * FROM (SELECT blockNumber FROM `winning_shares` ORDER BY blockNumber DESC LIMIT ".$count.")b ORDER BY blockNumber ASC");
        $wonsharecountQ = mysql_query("SELECT * FROM (SELECT blockNumber, shareCount FROM `winning_shares` ORDER BY blockNumber DESC LIMIT ".$count.")s ORDER BY blockNumber ASC");
	$difficultyQ = mysql_query("SELECT * FROM (SELECT blockNumber, round(difficulty) AS difficulty FROM `networkBlocks` WHERE accountAddress != '' ORDER BY blockNumber DESC LIMIT ".$count.")nb ORDER BY blockNumber ASC");

	// table head
	echo "<thead>
		<tr>
	";
        while ($row = mysql_fetch_array($wonblocksQ, MYSQL_ASSOC)) {

                 printf("<th scope='col'>%s</th>\n\r",

			$row["blockNumber"]);

        }
	//echo "<th scope='col'>Current</th>\n\r";
	echo "</tr>
	     </thead>
	";


	// table body
	echo "<tbody>
		<tr>
		<th scope='row'>Actual Shares</th>
	";
        while ($row = mysql_fetch_array($wonsharecountQ, MYSQL_ASSOC)) {

                 printf("<td>%s</td>\n\r",

			$row["shareCount"]);

        }
	//echo "<td>" .$settings->getsetting('currentroundshares'). "</td>\n\r";
	echo "</tr>
	      <tr>
	      <th scope='row'>Zero-Variance Assumption</th>
	";

	while($row = mysql_fetch_array($difficultyQ, MYSQL_ASSOC)) {
		echo "<td>" .$row["difficulty"]. "</td>\n\r";
	}
	//echo "<td>" .$difficulty. "</td>\n\r";
	echo "</tr>
	     </tbody>
	";
	mysql_free_result($wonblocksQ);
	mysql_free_result($wonsharecountQ);
	mysql_free_result($difficultyQ);
}

?>

