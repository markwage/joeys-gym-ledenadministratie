<?php 

echo '<ul>';
echo '<li><a href="index.php">Home</a></li>';
if (!isset($_SESSION['admin'])) {
	echo '<li><a href="login.php">Login</a></li>';
}
// Indien ingelogd is
if (isset($_COOKIE['ID_leden'])) {
    echo '<li><a href="logout.php">Logout</a></li>';
?>
	<!-- 
	<div class="navbar">
		<a href="index.php">Home</a>
		<div class="dropdown">
			<button class="dropbtn">Leden</button>
			<div class="dropdown-content">
				<a href="#">Overzicht aktieve leden</a><br />
				<a href="#">Overzicht uitgeschreven leden</a><br />
				<a href="#">Leden zonder contributie</a><br />
				<a href="#">Leden met een sleutel</a><br />
			</div>
		</div>
		<div class="dropdown">
			<button class="dropbtn">Betalingen</button>
			<div class="dropdown-content">
				<a href="#">Openstaande betalingen</a><br />
				<a href="#">Nieuwe betaalperiode</a><br />
				<a href="#">Abonnementen</a><br />
			</div>
		</div>
		<div class="dropdown">
			<button class="dropbtn">Rapportage</button>
			<div class="dropdown-content">
				<a href="#">Print aktieve leden</a><br />
				<a href="#">Print adresgegevens</a><br />
				<a href="#">Leden per abonnement</a><br />
			</div>
		</div>
	</div>
	-->
	<?php 	
	// Indien de user admin-rechten heeft
	// if ($_SESSION['admin']) {
	// 	echo '<li><a href="add_user.php">Add user</a></li>';
	// }
}
?>
</ul>';



