<br>
<div id="header">
		<ul id="nav">

		<li><a href="/index.php">Home</a></li>

        <?php
        if(!$cookieValid){
                //Display this menu if the user isn't logged in
	?>
			<li><a href="/register.php">Register</a></li>
	<?php
	} else if($cookieValid){
	?>
			<li><a href="/accountdetails.php">My Account</a>
				<ul>
					<li><a href="/accountdetails.php">Account Details</a></li>
					<li><a href="/accountworkers.php">My Workers</a></li>
					<li><a href="/accounttransactions.php">Transaction History</a></li>
				</ul>
			</li>
	<?php
	//If this user is an admin show the adminPanel link
        	if($isAdmin){
	?>
			<li><a href="/adminPanel.php">Admin Panel</a></li>
	<?php
        	}
        }
	?>
	<?php if($cookieValid){ ?>
		<li><a href="/statsAuth.php">Stats</a>
			<ul>
				<li><a href="/statsAuth.php">Pool Stats</a></li>
				<li><a href="/blocksAuth.php">Block Stats</a></li>
			</ul>
		</li>
	<?php } else { ?>
		<li><a href="/stats.php">Stats</a></li>
	<?php } ?>
		<li><a href="/gettingstarted.php">Getting Started</a></li>

		<li><a href="/support.php">Support</a></li>

		<li><a href="/about.php">About</a>

		<li><a href="/news.php">News</a>
		<!--
			<ul>
				<li><a href="#">About Bitcoin</a></li>
				<li><a href="#">About Mainframe</a></li>
				<li><a href="#">API Details</a></li>
			</ul>
		-->
		</li>

		<?php if($cookieValid){ ?>
			<li><a href="/logout.php">Logout</a></li>
		<?php } ?>
	</ul>
</div>
