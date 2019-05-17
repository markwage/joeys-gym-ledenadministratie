<?php

if (isset($_COOKIE['ID_leden'])) {
	echo '<div class="sidebox">';
	echo '<h1>Welkom</h1>';
	echo "<p>Welkom <b>".$_SESSION['voornaam']."</b>.<br /> Je bent nu ingelogd op de ledenadministratie van JOEYS GYM.<br /></p>";			
	echo '</div>';
	
	// Menu voor updaten/onderhouden van de gewerkte uren
	echo '<div class="sidebox">';	
	echo '<h1 class="clear">Ledenadministratie</h1>';
	echo '<ul class="sidemenu">';
	echo '<li><a href="leden.php?aktie=dispAktief">Overzicht aktieve leden</a></li>';
	echo '<li><a href="leden.php?aktie=dispInaktief">Overzicht uitgeschreven leden</a></li>';
	echo '<li><a href="leden.php?aktie=dispGeenContr">Leden zonder contributie</a></li>';
	echo '<li><a href="edit_users.php?aktie=editprof&edtuser='.$_SESSION["username"].'">Mijn profiel</a></li>';
	echo '</ul>';	
	echo '</div>';	
	
	// menu alleen voor gebruiker met adminrechten
	//if ($_SESSION['admin']) {
		echo '<div class="sidebox">';
		echo '<h1>Betalingen</h1>';
		echo '<ul class="sidemenu">';
		echo '<li><a href="betalingen.php?aktie=open">Openstaande betalingen</a></li>';
		echo '<li><a href="betaalperiode.php">Nieuwe betaalperiode</a></li>';
		echo '<li><a href="abonnementen.php">Abonnementen</a></li>';
		echo '</ul>';	
		echo '</div>';
	//}
	
	echo '<div class="sidebox">';
	echo '<h1>Rapportage</h1>';
	echo '<ul class="sidemenu">';
	echo '<li><a href="rappAktieveleden.php">Print aktieve leden</a></li>';
	echo '<li><a href="rappAdressen.php">Print adresgegevens</a></li>';
	echo '<li><a href="rappPerabonnement.php">Leden per abonnement</a></li>';
	echo '</ul>';
	echo '</div>';
}		

?>