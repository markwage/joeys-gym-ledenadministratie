<?php

//------------------------------------------------------------------------
// functie om een connectie met de database te maken
//------------------------------------------------------------------------
function makedbconnection() {
    if (!isset($dbconn)) {
        include ("./db.php");
    }
    if (mysqli_connect_errno()) {
        die("Kan de connectie met de database niet maken");
    }
    $dbselect = mysqli_select_db($dbconn, $dbname);
    if (!$dbselect) {
        die("Kan de database niet openen : " . mysqli_error());
    }
}

//------------------------------------------------------------------------
// functie om een error-message te displayen met standaard
// header en kleuren
//------------------------------------------------------------------------
function errormessage($error_header, $error_message) {
	echo '<div id="message">';
	echo "<h2>".$error_header."</h2>";
	echo $error_message;
	echo "</div>";
}

//------------------------------------------------------------------------
// Controleren of cookies aanwezig zijn. Zo niet dan wordt het login-script
// uitgevoerd.
//------------------------------------------------------------------------
function check_cookies() {
	if(isset($_COOKIE['ID_leden'])) {
		// Indien aanwezig word je naar de volgende page ge-redirect
		include ("./db.php");
	    $dbconn = mysqli_connect($dbhost, $dbuser, $dbpassw, $dbname);
		$username = $_COOKIE['ID_leden'];
		$pass = $_COOKIE['Key_leden'];
		$check = mysqli_query($dbconn, "SELECT * FROM users WHERE username = '$username'") or die(mysqli_error($dbconn));
		while ($info = mysqli_fetch_array($check)) {
			if ($pass != $info['password']) {
				header("location: login.php");
			}
		}
	}
	else {
		header("location: login.php");
	}
}

//------------------------------------------------------------------------
// Controleren of de user admin-rechten heeft. Zo niet een error-scherm displayen
//------------------------------------------------------------------------
function check_admin() {
	if (!isset($_SESSION['admin']) || (!$_SESSION['admin'])) {
		header("location: noadmin.php");
	}
}

//------------------------------------------------------------------------
// Cursor op een bepaald veld in het formulier zetten
//------------------------------------------------------------------------
function setfocus($formnaam, $veldnaam) {
	echo '<script type="text/javascript">';
	echo 'document.'.$formnaam.'.'.$veldnaam.'.focus()';
	echo '</script>';
}

//------------------------------------------------------------------------
// Displayen van de diverse gegevens van de user
//------------------------------------------------------------------------
function displayUserGegevens() {
	global $username, $user_id, $voornaam, $tussenvoegsel, $achternaam, $emailadres, $datum_laatste_mutatie, $weekNumber;
	echo "<p><table>";
	$username = $_SESSION['username'];
	include ("./db.php");
	$dbconn = mysqli_connect($dbhost, $dbuser, $dbpassw, $dbname);
	$sql_user = mysqli_query($dbconn, "SELECT * FROM users WHERE username = '$username'");
	while($row_user = mysqli_fetch_array($sql_user)) {
		$user_id       = $row_user['ID'];
		$username      = $row_user['username'];
		$voornaam      = $row_user['voornaam'];
		$tussenvoegsel = $row_user['tussenvoegsel'];
		$achternaam    = $row_user['achternaam'];
		$emailadres    = $row_user['emailadres'];
		echo '<tr><td align="right"><strong>Medewerker: </strong></td><td>'.$voornaam.' '.$tussenvoegsel.' '.$achternaam.'</td></tr>';
		echo '<tr><td align="right"><strong>Emailadres: </strong></td><td>'.$emailadres.'</td></tr>';
		//echo "</tr></table></p>";
	}
	echo "</table></p>";
}

//------------------------------------------------------------------------
// Converteren datum (JJJJ-MM-DD) naar een weeknummer
//------------------------------------------------------------------------
function cnv_dateToWeek($datum) {
    $dat_jaar  = substr($datum, 0, 4); // jaren     (Y)
    $dat_maand = substr($datum, 5, 2); // maanden   (m)
    $dat_dag   = substr($datum, 8, 2); // dagen     (d)
    $buildDatum = mktime(0, 0, 0, $dat_maand, $dat_dag, $dat_jaar);
	$weekNumber = date('W', $buildDatum); 
	return $weekNumber;
}

//------------------------------------------------------------------------
// Write logrecord to file 
//------------------------------------------------------------------------
function writeLogRecord($phpProg, $logRecord) {
    if (isset($_SESSION['username'])) $username = $_SESSION['username'];
    else $username = "";
    $fileName = "C:\\wamp64\\www\\joeys-gym-ledenadministratie\\logs\\systemlog.log";
    $datumlog = date('Ymd H:i:s');
    file_put_contents($fileName, PHP_EOL.$datumlog.";".$phpProg.";".$username.";".$logRecord, FILE_APPEND);
}

//------------------------------------------------------------------------
// Vullen van de frm_variabelen voor invullen van soort uren-scherm
//------------------------------------------------------------------------
function form_soorturen_fill($aktie) {
    if ($aktie == "save" || $aktie == "toevoegen") {
        global $frm_code, $frm_omschrijving, $formerror;
        $formerror = 0;
        $frm_ID            = $_POST['ID'];
        $frm_code          = $_POST['code'];
        $frm_omschrijving  = $_POST['omschrijving'];
    }
}

//------------------------------------------------------------------------
// Stel de ingevulde gegevensin het scherm veilig zodat de velden gevuld worden
// met de al ingevulde waarden bij het optreden van een error
//------------------------------------------------------------------------
function form_leden_fill($btn_aktie) {
    if ($btn_aktie == "save" || $btn_aktie == "toevoegen") {
        global $frm_lidnr, $frm_voornaam, $frm_tussenvoegsel, $frm_achternaam, $frm_adres, $frm_postcode,
        $frm_woonplaats, $frm_telefoonnummer, $frm_emailadres, $frm_abonnementID, $frm_inschrijfdatum, 
        $frm_uitschrijfdatum, $frm_geencontributie, $frm_sleutel, $formerror;
        $formerror = 0;
        $frm_lidnr           = $_POST['lidID'];
        $frm_voornaam        = $_POST['voornaam'];
        $frm_tussenvoegsel   = $_POST['tussenvoegsel'];
        $frm_achternaam      = $_POST['achternaam'];
        $frm_adres           = $_POST['adres'];
        $frm_postcode        = $_POST['postcode'];
        $frm_woonplaats      = $_POST['woonplaats'];
        $frm_telefoonnummer  = $_POST['telefoonnummer'];
        $frm_emailadres      = $_POST['emailadres'];
        $frm_abonnementID    = $_POST['abonnement'];
        $frm_inschrijfdatum  = $_POST['inschrijfdatum'];
        $frm_uitschrijfdatum = $_POST['uitschrijfdatum'];
        //$frm_geencontributie = $_POST['geencontributie'];
        if (isset($_POST['geencontributie'])) $frm_geencontributie = $_POST['geencontributie'];
        else $frm_geencontributie = "";
        if (isset($_POST['sleutel'])) $frm_sleutel = $_POST['sleutel'];
        else $frm_sleutel = "";
    }
}

//------------------------------------------------------------------------
// Vullen van de frm_variabelen voor invullen van abonnementen
//------------------------------------------------------------------------
function form_abonnement_fill($aktie) {
    if ($aktie == "save" || $aktie == "toevoegen") {
        global $frm_ID, $frm_soortomschrijving, $frm_bedrag, $formerror;
        $formerror = 0;
        $frm_ID              = $_POST['ID'];
        $frm_soortabonnement = $_POST['soortabonnement'];
        $frm_bedrag          = $_POST['bedrag'];
    }
}

//------------------------------------------------------------------------
// Vullen van de frm_variabelen voor invullen van abonnementen
//------------------------------------------------------------------------
function form_betaalperiode_fill($aktie) {
    if ($aktie == "save" || $aktie == "toevoegen") {
        global $frm_ID, $frm_betaalperiode, $formerror;
        $formerror = 0;
        $frm_ID              = $_POST['ID'];
        $frm_betaalperiode = $_POST['betaalperiode'];
    }
}

//------------------------------------------------------------------------
// Controleer de ingevulde uren per Soortuur
//------------------------------------------------------------------------
function checkIngevuldeUrenPerSoort($ix1) {
    global $urenarray, $frm_soortuur, $frm_urendag1, $frm_urendag2, $frm_urendag3, $frm_urendag4, $frm_urendag5, $frm_urendag6, $frm_urendag7;
    $frm_soortuur = $_POST["soortuur"][$ix1];
    if(!isset($_POST["dag1"][$ix1]) || $_POST["dag1"][$ix1] == '') $frm_urendag1=0;
    else $frm_urendag1 = $_POST["dag1"][$ix1];
    if(!isset($_POST["dag2"][$ix1]) || $_POST["dag2"][$ix1] == '') $frm_urendag2=0;
    else $frm_urendag2 = $_POST["dag2"][$ix1];
    if(!isset($_POST["dag3"][$ix1]) || $_POST["dag3"][$ix1] == '') $frm_urendag3=0;
    else $frm_urendag3 = $_POST["dag3"][$ix1];
    if(!isset($_POST["dag4"][$ix1]) || $_POST["dag4"][$ix1] == '') $frm_urendag4=0;
    else $frm_urendag4 = $_POST["dag4"][$ix1];
    if(!isset($_POST["dag5"][$ix1]) || $_POST["dag5"][$ix1] == '') $frm_urendag5=0;
    else $frm_urendag5 = $_POST["dag5"][$ix1];
    if(!isset($_POST["dag6"][$ix1]) || $_POST["dag6"][$ix1] == '') $frm_urendag6=0;
    else $frm_urendag6 = $_POST["dag6"][$ix1];
    if(!isset($_POST["dag7"][$ix1]) || $_POST["dag7"][$ix1] == '') $frm_urendag7=0;
    else $frm_urendag7 = $_POST["dag7"][$ix1];
    $urenarray[0] = $frm_urendag1;
    $urenarray[1] = $frm_urendag2;
    $urenarray[2] = $frm_urendag3;
    $urenarray[3] = $frm_urendag4;
    $urenarray[4] = $frm_urendag5;
    $urenarray[5] = $frm_urendag6;
    $urenarray[6] = $frm_urendag7;
}

//-------------------------------------------------------------------------
// Geef weeknummer en jaar door aan de functie
// Deze geeft de dagnaam (mon - sun) en de datum in dd-mm 
// Dit wordt gebruikt in de headers om de uren in te vullen
//-------------------------------------------------------------------------
function getWeekdays($weeknr){
    global $weekDatum, $weekDagNaam, $week, $year, $inputweeknr;
    //writelogrecord("function","getWeekdays weeknr: ".$weeknr);
    $inputweeknr = $weeknr;
    $week = substr($inputweeknr, 4, 2);
    $year = substr($inputweeknr, 0, 4);
    $weekDatum[0] = date("d-m", strtotime($year.'W'.str_pad($week, 2, 0, STR_PAD_LEFT)));
    $weekDatum[1] = date("d-m", strtotime($year.'W'.str_pad($week, 2, 0, STR_PAD_LEFT).' +1 days'));
    $weekDatum[2] = date("d-m", strtotime($year.'W'.str_pad($week, 2, 0, STR_PAD_LEFT).' +2 days'));
    $weekDatum[3] = date("d-m", strtotime($year.'W'.str_pad($week, 2, 0, STR_PAD_LEFT).' +3 days'));
    $weekDatum[4] = date("d-m", strtotime($year.'W'.str_pad($week, 2, 0, STR_PAD_LEFT).' +4 days'));
    $weekDatum[5] = date("d-m", strtotime($year.'W'.str_pad($week, 2, 0, STR_PAD_LEFT).' +5 days'));
    $weekDatum[6] = date("d-m", strtotime($year.'W'.str_pad($week, 2, 0, STR_PAD_LEFT).' +6 days'));
    $weekDagNaam[0] = "Maa";
    $weekDagNaam[1] = "Din";
    $weekDagNaam[2] = "Woe";
    $weekDagNaam[3] = "Don";
    $weekDagNaam[4] = "Vrij";
    $weekDagNaam[5] = "Zat";
    $weekDagNaam[6] = "Zon";
    
}

?>



