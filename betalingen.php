<?php
session_start();

include ("config.php");
include ("db.php");
include ("function.php");
if (isset($_GET['aktie'])) {
	$aktie = $_GET['aktie'];
}
else {
	$aktie = "";
}

check_admin();     // Controleren of gebruiker admin-rechten heeft
check_cookies();   // Controleren of cookie aanwezig is. Zo niet, login-scherm displayen

include ("header.php");

?>
<div id="main">		
	<h1>Betalingen</h1>
			
<?php 

//------------------------------------------------------------------------------------------------------
//
//       *******************   SUBMITTED   *******************
//
// From here this code runs if the form has been submitted
//------------------------------------------------------------------------------------------------------

//------------------------------------------------------------------------------------------------------
// BUTTON Cancel
// Wanneer het geen admin betreft wordt de hoofdpagina getoond. Indien wel adminrechten dan wordt de
// lijst met alle leden getoond
//------------------------------------------------------------------------------------------------------
if (isset($_POST['cancel'])) {
    if (!isset($_SESSION['admin']) || (!$_SESSION['admin'])) {
        header("location: betalingen.php?aktie=disp");
    }
}

//------------------------------------------------------------------------------------------------------
// BUTTON Delete
//------------------------------------------------------------------------------------------------------
if (isset($_POST['delete'])) {
	$delID = $_POST['ID'];
	$sql_select = "SELECT * FROM leden where abonnementID='".$delID."'";
	if($sql_result = mysqli_query($dbconn, $sql_select)) {
	    if(mysqli_num_rows($sql_result) > 0) {
	        //ERROR DAT ER NOG UREN GEKOPPELD ZIJN AAN DEZE SOORTUUR!!!!
	        echo '<p class="errmsg"> ERROR: Er zijn nog leden gekoppeld aan dit abonnement</p>';
	        $focus     = 'ID';
	        $formerror = 1;
	    } else {
	        $sql_delsoortuur = mysqli_query($dbconn, "DELETE FROM abonnement WHERE ID = '$delID'");
	        header("location: abonnementen.php?aktie=disp");
	    }
	}
}

//------------------------------------------------------------------------------------------------------
// BUTTON Save
//------------------------------------------------------------------------------------------------------
if (isset($_POST['save']) || (isset($_POST['submit']))) {
    form_abonnement_fill('save');
    writelogrecord("abonnementen","BTNSAVE Op save gedrukt om gewijzigd record op te slaan");
    //$formerror = 0;
	if ((!$_POST['soortabonnement'] || $_POST['soortabonnement'] == "") && (!$formerror)) {
	    writelogrecord("abonnementen","CHECK1A Het soort abonnement is niet ingevuld");
		echo '<p class="errmsg"> ERROR: Soort is een verplicht veld</p>';
		$focus     = 'soortabonnement';
		$formerror = 1;
	}
	if ((!$_POST['bedrag'] || $_POST['bedrag'] == "") && (!$formerror)) {
	    writelogrecord("abonnement","CHECK1B Het veld bedrag is niet ingevuld");
		echo '<p class="errmsg"> ERROR: Bedrag is een verplicht veld</p>';
		$focus     = 'bedrag';
		$formerror = 1;
	}

	// Update record indien er geen errors zijn
	if (!$formerror) { 
	    if (isset($_POST['save'])) { 
		    $update = "UPDATE abonnement SET 
            soortabonnement = '".$_POST['soortabonnement']."',
		    bedrag = '".$_POST['bedrag']."' WHERE ID = '".$_POST['ID']."'";
		    $check_update = mysqli_query($dbconn, $update) or die ("Error in query: $update. ".mysqli_error($dbconn));
		    if ($check_update) { 
		        writelogrecord("abonnement","UPDATE abonnement ".$_POST['ID']." is succesvol ge-update");
			    echo '<p class="infmsg">Abonnement <b>'.$_POST['ID'].'</b> is gewijzigd</p>.';
			    $frm_soortabonnement = "";
			    $frm_bedrag  = "";
		    }
		    else {
			    echo '<p class="errmsg">Er is een fout opgetreden bij het updaten van abonnement. Probeer het nogmaals.<br />
			    Indien het probleem zich blijft voordoen neem dan contact op met de webmaster</p>';
		    }
	    }
	    if (isset($_POST['submit'])) {
	        $insert = "INSERT INTO abonnement (soortabonnement, bedrag)
			VALUES ('".$_POST['soortabonnement']."',
					'".$_POST['bedrag']."')";
	        writeLogRecord("abonnement","UPDQUERY INSERT-query: ".$insert);
	        $check_insert = mysqli_query($dbconn, $insert);
	    }
		header("location: abonnementen.php?aktie=disp"); 
	}
}

//------------------------------------------------------------------------------------------------------
//
//       *******************   START   *******************
//
// Dit wordt uitgevoerd wanneer de user op Onderhoud soort uren heeft geklikt
// Er wordt een lijst met de uren getoond
//------------------------------------------------------------------------------------------------------
if ($aktie == 'open') {
	$sql_abonnement = mysqli_query($dbconn, "SELECT * FROM abonnement ORDER BY soortabonnement");
	echo "<center><table>";
	echo "<tr><th>ID</th><th>Soort abonnement</th><th>Bedrag</th><th colspan=\"3\" align=\"center\">Akties</th></tr>";
	$rowcolor = 'row-a';
	while($row_abonnement = mysqli_fetch_array($sql_abonnement)) {
		$id              = $row_abonnement['ID'];
		$soortabonnement = $row_abonnement['soortabonnement'];
		$bedrag          = $row_abonnement['bedrag'];
		echo '<tr class="'.$rowcolor.'">
			<td><b>'.$id.'</b></td><td>'.$soortabonnement.'</td><td style="text-align:right;">'.$bedrag.'</td>

			<td class="button"><a href="abonnementen.php?aktie=edit&edtID='.$id.'"><img src="./img/buttons/icons8-edit-48.png" alt="wijzigen abonnement" title="wijzigen abonnement" /></a></td>
			<td class="button"><a href="abonnementen.php?aktie=delete&edtID='.$id.'"><img src="./img/buttons/icons8-trash-can-48.png" alt="delete abonnement" title="delete abonnement" /></a></td>
			<td class="button"><a href="abonnementen.php?aktie=toevoegen"><img src="./img/buttons/icons8-plus-48.png" alt="toevoegen abonnement" title="toevoegen abonnement" /></a></td>
			</tr>';
		if ($rowcolor == 'row-a') $rowcolor = 'row-b';
		else $rowcolor = 'row-a';
	}
	echo "</table></center>";
}

//------------------------------------------------------------------------------------------------------
// Wordt uitgevoerd wanneer men op de button klikt om te wijzigen of te deleten
//------------------------------------------------------------------------------------------------------
if ($aktie == 'edit' || $aktie == 'delete' || $aktie == 'toevoegen') {
    if($aktie != 'toevoegen') {
	    $edtID = $_GET['edtID'];
	    $focus = "ID";
	    $sql_abonnement = mysqli_query($dbconn, "SELECT * FROM abonnement WHERE ID = '$edtID'");
	    while($row_abonnement = mysqli_fetch_array($sql_abonnement)) {
	        global $frm_ID, $frm_soortabonnement, $frm_bedrag, $formerror;
	        $formerror = 0;
		    $frm_ID              = $row_abonnement['ID'];
		    $frm_soortabonnement = $row_abonnement['soortabonnement'];
		    $frm_bedrag          = $row_abonnement['bedrag'];
	    }
	}
    ?>
	<form name="abonnement" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
 		<p>
		<table>
	    	<tr>
				<td><b>ID</b></td>
				<td><input style="text-align:right;" type="text" readonly name="ID" size="4" maxlength="8" value="<?php if (isset($frm_ID)) { echo $frm_ID; } ?>"></td>
			</tr>
			<tr>
				<td><b>Soort abonnement</b></td>
				<td><input type="text" name="soortabonnement" size="35" maxlength="50" value="<?php if (isset($frm_soortabonnement)) { echo $frm_soortabonnement; } ?>" required></td>
			</tr>
			<tr>
				<td><b>Bedrag</b></td>
				<td><input style="text-align:right;" type="text" name="bedrag" size="3" maxlength="7" value="<?php if (isset($frm_bedrag)) { echo $frm_bedrag; } ?>" required></td>
			</tr>
		</table>
		<br />
		<?php if ($aktie == 'toevoegen') echo '<input class="button" type="submit" name="submit" value="submit">'; ?>
		<?php if ($aktie == 'edit') echo '<input class="button" type="submit" name="save" value="save">'; ?>
		<?php if ($aktie == 'delete') echo '<input class="button" type="submit" name="delete" value="delete" onClick="return confirmDelSoortuur()">'; ?>
		<input class="button" type="submit" name="cancel" value="cancel" formnovalidate>
		</p>
	</form>
	<br />		
	<?php 
    if (!isset($focus)) {
    	$focus='code';
    }
    setfocus('soorturen', $focus);
}
	
include ("footer.php");
?>		


