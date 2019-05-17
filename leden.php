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

// 1 - Controleren of gebruiker admin-rechten heeft
// 2 - Controleren of cookie aanwezig is. Anders login-scherm displayen
check_admin();
check_cookies();

include ("header.php");

?>
<div id="main">		
	<h1>Aktieve leden</h1>
			
<?php 
//------------------------------------------------------------------------------------------------------
// From here this code runs if the form has been submitted
//------------------------------------------------------------------------------------------------------

//------------------------------------------------------------------------------------------------------
// BUTTON Cancel
// Wanneer het geen admin betreft wordt de hoofdpagina getoond. Indien wel adminrechten dan wordt de
// lijst met alle users getoond
//------------------------------------------------------------------------------------------------------
if (isset($_POST['cancel'])) {
    if (!isset($_SESSION['admin']) || (!$_SESSION['admin'])) {
        header("location: index.php");
    } else {
        header("location: leden.php?aktie=dispAktief");
    }
}

//------------------------------------------------------------------------------------------------------
// BUTTON Delete
//------------------------------------------------------------------------------------------------------
if (isset($_POST['delete'])) {
	$lidID = $_POST['lidID'];
	$sql_dellid = mysqli_query($dbconn, "DELETE FROM leden WHERE ID = $lidID");
	writeLogRecord("leden","Lid ".$lidID." is succesvol verwijderd.");
	header("location: leden.php?aktie=dispAktief");
}

//------------------------------------------------------------------------------------------------------
// BUTTON Save (wijzigen bestaande lid) of submit (toevoegen nieuw lid)
//------------------------------------------------------------------------------------------------------
if ( (isset($_POST['save'])) || (isset($_POST['submit'])) ) {
    form_leden_fill('save');
	if ((!$_POST['voornaam'] || $_POST['voornaam'] == "") && (!$formerror)) {
		echo '<p class="errmsg"> ERROR: Voornaam is een verplicht veld</p>';
		$focus     = 'voornaam';
		$formerror = 1;
	}
	if (!$_POST['achternaam'] && (!$formerror)) {
		echo '<p class="errmsg"> ERROR: Achternaam is een verplicht veld</p>';
		$focus     = 'achternaam';
		$formerror = 1;
	}
	if ((!isset($_POST['geencontributie'])) || $_POST['geencontributie'] == "") $frm_geencontributie = 0;
	else $frm_geencontributie = 1;
	
	if (!$formerror) {
	    if (isset($_POST['save'])) { 
	        $update = "UPDATE leden SET 
	        voornaam = '".$_POST['voornaam']."',
		    tussenvoegsel = '".$_POST['tussenvoegsel']."',
		    achternaam = '".$_POST['achternaam']."',
            adres = '".$_POST['adres']."',
            postcode = '".$_POST['postcode']."',
            woonplaats = '".$_POST['woonplaats']."',
            telefoonnummer = '".$_POST['telefoonnummer']."',
            emailadres = '".$_POST['emailadres']."',
            abonnementID = ".$_POST['abonnement'].",";
	        if((!isset($_POST['inschrijfdatum'])) || $_POST['inschrijfdatum'] == "") $update .= "inschrijfdatum = NULL,";
	        else $update .= "inschrijfdatum = '".$_POST['inschrijfdatum']."',";
	        writeLogRecord("leden","Waarde van uitschrijfdatum : *".$_POST['uitschrijfdatum']."*");
	        if((!isset($_POST['uitschrijfdatum'])) || $_POST['uitschrijfdatum'] == "") $update .= "uitschrijfdatum = NULL,";
	        else $update .= "uitschrijfdatum = '".$_POST['uitschrijfdatum']."',";
            $update .= "geencontributie = '".$frm_geencontributie."' WHERE ID = ".$_POST['lidID'];
	        writeLogRecord("leden","UPDQUERY UPDATE-query: ".$update);
	        $check_upd_lid = mysqli_query($dbconn, $update);
	    }
	    if (isset($_POST['submit'])) {
	        if ((!isset($_POST['geencontributie'])) || $_POST['geencontributie'] == "") $frm_geencontributie = 0;
	        else $frm_geencontributie = 1;
	        writeLogRecord("leden","Waarde van geencontributie: ".$frm_geencontributie);
	        $insert = "INSERT INTO leden (voornaam, tussenvoegsel, achternaam, adres, postcode, woonplaats, telefoonnummer, emailadres,
            abonnementID, inschrijfdatum, uitschrijfdatum, geencontributie)
			VALUES ('".$_POST['voornaam']."',
					'".$_POST['tussenvoegsel']."',
					'".$_POST['achternaam']."',
					'".$_POST['adres']."',
                    '".$_POST['postcode']."',
                    '".$_POST['woonplaats']."',
                    '".$_POST['telefoonnummer']."',
                    '".$_POST['emailadres']."',";
                    //".$_POST['abonnement'].",";
	        if((!isset($_POST['abonnement'])) || $_POST['abonnement'] == "Maak je keuze") $insert .= "NULL,";
	        else $insert .= "'".$_POST['abonnement']."',";
	        if((!isset($_POST['inschrijfdatum'])) || $_POST['inschrijfdatum'] == "") $insert .= "NULL,";
	        else $insert .= "'".$_POST['inschrijfdatum']."',";
	        if((!isset($_POST['uitschrijfdatum'])) || $_POST['uitschrijfdatum'] == "") $insert .= "NULL,";
	        else $insert .= "'".$_POST['uitschrijfdatum']."',";
                    $insert .= "'".$frm_geencontributie."')";
	        writeLogRecord("leden","UPDQUERY INSERT-query: ".$insert);
	        $check_insert_lid = mysqli_query($dbconn, $insert);
	    }
	}
	header("location: leden.php?aktie=dispAktief");
}

//------------------------------------------------------------------------------------------------------
// START 
//------------------------------------------------------------------------------------------------------
if (($aktie == 'dispAktief') || ($aktie == 'dispInaktief') || ($aktie == 'dispGeenContr')) {
    if ($aktie == 'dispAktief') {
        $sql_select = "SELECT * FROM leden WHERE geenContributie = '0' AND uitschrijfdatum IS NULL ORDER BY achternaam;";
    } elseif ($aktie == 'dispInaktief') {
        $sql_select = "SELECT * FROM leden WHERE geenContributie = '0' AND uitschrijfdatum IS NOT NULL ORDER BY achternaam;";
    } elseif ($aktie == 'dispGeenContr') {
        $sql_select = "SELECT * FROM leden WHERE geenContributie = '1' ORDER BY achternaam;";
    }
    writelogrecord("index","Query: ".$sql_select);
    if($sql_result = mysqli_query($dbconn, $sql_select)) {
        //writelogrecord("index","Totaal aantal rijen uit de select-query: ".mysqli_num_rows($sql_result));
        if(mysqli_num_rows($sql_result) > 0) {
            echo "<center><table>";
            echo "<tr>";
            echo "<th colspan='9' style='text-align:center;'>Overzicht leden</th>";
            echo "</tr>";
            echo "<tr>";
            echo "<th>lidnr</th>";
            echo "<th>naam</th>";
            echo "<th>adres</th>";
            echo "<th>postcode</th>";
            echo "<th>woonplaats</th>";
            echo "<th>telefoon</th>";
            echo "<th colspan='3' style='text-align:center;'>Akties</th>";
            echo "</tr>";
            $rowcolor = 'row-a';
            while($row_selectleden = mysqli_fetch_array($sql_result)) {
                $lidID         = $row_selectleden['ID'];
                $voornaam      = $row_selectleden['voornaam'];
                $tussenvoegsel = $row_selectleden['tussenvoegsel'];
                $achternaam    = $row_selectleden['achternaam'];
                $adres         = $row_selectleden['adres'];
                $postcode      = $row_selectleden['postcode'];
                $woonplaats    = $row_selectleden['woonplaats'];
                $telefoon      = $row_selectleden['telefoonnummer'];
                echo '<tr class="'.$rowcolor.'">';
                echo '<td style="text-align:right;">'.$lidID.'</td>';
                echo '<td>'.$achternaam.', '.$voornaam.' '.$tussenvoegsel.'</td>';
                echo '<td>'.$adres.'</td>';
                echo '<td>'.$postcode.'</td>';
                echo '<td>'.$woonplaats.'</td>';
                echo '<td>'.$telefoon.'</td>';
                echo '<td><a href="leden.php?aktie=edit&lidID='.$lidID.'"><img src="./img/buttons/icons8-edit-48.png" alt="wijzigen gegevens lid" title="wijzigen gegevens lid" /></a></td>';
                echo '<td><a href="leden.php?aktie=delete&lidID='.$lidID.'"><img src="./img/buttons/icons8-delete-48.png" alt="verwijderen lid" title="verwijderen lid" /></a></td>';
                echo '<td><a href="leden.php?aktie=toevoegen"><img src="./img/buttons/icons8-plus-48.png" alt="toevoegen nieuwe user" title="toevoegen nieuwe user" /></a></td>';
                echo '</tr>';
                if ($rowcolor == 'row-a') $rowcolor = 'row-b';
                else $rowcolor = 'row-a';
            }
            echo "</table></center>";
        } else {
            echo "Er zijn geen records gevonden";
        }
    } else {
        echo "ERROR: Could not be able to execute $sql_select. ". mysqli_error($dbconn);
    }
}

//------------------------------------------------------------------------------------------------------
// Wordt uitgevoerd wanneer men op de button klikt om te wijzigen of te deleten of om het eigen
// profiel aan te passen
//------------------------------------------------------------------------------------------------------
if ($aktie == 'edit' || $aktie == 'delete' || $aktie == 'toevoegen') {
    if($aktie != 'toevoegen') {
        $frm_abonnementIDfilled = '';
	    $lidID = $_GET['lidID'];
	    $focus = "voornaam";
	    $sql_dspleden = mysqli_query($dbconn, "SELECT * FROM leden WHERE ID = '$lidID'");
	    while($row_dspleden = mysqli_fetch_array($sql_dspleden)) {
	        // >>> uitvoeren fill_leden_form om onderstaande variabele te vullen??????
	        $frm_lidnr                = $row_dspleden['ID'];
		    $frm_voornaam             = $row_dspleden['voornaam'];
		    $frm_tussenvoegsel        = $row_dspleden['tussenvoegsel'];
		    $frm_achternaam           = $row_dspleden['achternaam'];
		    $frm_adres                = $row_dspleden['adres'];
		    $frm_postcode             = $row_dspleden['postcode'];
		    $frm_woonplaats           = $row_dspleden['woonplaats'];
		    $frm_telefoonnummer       = $row_dspleden['telefoonnummer'];
		    $frm_emailadres           = $row_dspleden['emailadres'];
		    $frm_abonnementIDfilled   = $row_dspleden['abonnementID'];
		    $frm_inschrijfdatum       = $row_dspleden['inschrijfdatum'];
		    $frm_uitschrijfdatum      = $row_dspleden['uitschrijfdatum'];
		    $frm_geencontributieValue = $row_dspleden['geencontributie'];
	    }
	    if($frm_geencontributieValue == 1) $frm_geencontributie = "checked";
	    else $frm_geencontributie = "";
    } else {
        $frm_abonnementIDfilled = '';
        $frm_geencontributie = "";
    }
    ?>
	<form name="leden" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
 		<p>
		<table>
			<tr>
				<td><b>Lidnr</b></td>
				<td><input  style="text-align:right;" readonly type="text" name="lidID" size="2" value="<?php if (isset($frm_lidnr)) { echo $frm_lidnr; } ?>"></td>
			</tr>
			<tr>
				<td><b>Voornaam</b></td>
				<td><input type="text" name="voornaam" size="10" maxlength="20" value="<?php if (isset($frm_voornaam)) { echo $frm_voornaam; } ?>"></td>
				<td><b>Tussenv.</b></td>
				<td><input type="text" name="tussenvoegsel" size="4" maxlength="8" value="<?php if (isset($frm_tussenvoegsel)) { echo $frm_tussenvoegsel; } ?>"></td>
				<td><b>Achternaam</b></td>
				<td><input type="text" name="achternaam" size="26" maxlength="35" value="<?php if (isset($frm_achternaam)) { echo $frm_achternaam; } ?>"></td>
			</tr>
		</table>
		
		<table>
			<tr>
				<td><b>Adres</b></td><td><input type="text" name="adres" size="35" maxlength="35" value="<?php if (isset($frm_adres)) { echo $frm_adres; } ?>"></td>
			</tr>
			<tr>
				<td><b>Postcode</b></td><td><input type="text" name="postcode" size="5" maxlength="7" value="<?php if (isset($frm_postcode)) { echo $frm_postcode; } ?>"></td>
			</tr>
			<tr>
				<td><b>Woonplaats</b></td><td><input type="text" name="woonplaats" size="35" maxlength="40" value="<?php if (isset($frm_woonplaats)) { echo $frm_woonplaats; } ?>"></td>
			</tr>
			<tr>
				<td><b>Telefoonnummer</b></td><td><input type="text" name="telefoonnummer" size="11" maxlength="11" value="<?php if (isset($frm_telefoonnummer)) { echo $frm_telefoonnummer; } ?>"></td>
			</tr>
			<tr>
				<td><b>Email</b></td>
				<td><input type="text" name="emailadres" size="40" maxlength="60" value="<?php if (isset($frm_emailadres)) { echo $frm_emailadres; } ?>"></td>
			</tr>
			<tr>
				<td><b>Soort abonnement</b></td>
				<td><select name=abonnement>
					<?php
				    if ($aktie == 'toevoegen') echo '<option>Maak je keuze</option>';
				    if (($aktie == 'edit' || $aktie == 'delete') && ($frm_abonnementIDfilled == "")) echo '<option>Abonnement onbekend</option>';
				    $sql_abonnement = mysqli_query($dbconn, "SELECT * FROM abonnement ORDER BY soortAbonnement");
				    while($row_abonnement = mysqli_fetch_array($sql_abonnement)) {
				        $frm_abonnementID    = $row_abonnement['ID'];
				        $frm_soortAbonnement = $row_abonnement['soortAbonnement'];
				        if ($frm_abonnementIDfilled == $frm_abonnementID) $optionSelected = 'selected';
				        else $optionSelected = '';
				        echo '<option '.$optionSelected.' value="'.$frm_abonnementID.'">'.$frm_soortAbonnement.'</option>';
				    }
				    ?>
				</select></td>
			</tr>
			<tr>
				<td><b>Inschrijfdatum</b></td>
				<td><input type="date" name="inschrijfdatum" value="<?php if (isset($frm_inschrijfdatum)) { echo $frm_inschrijfdatum; } ?>"></td>
			</tr>
			<tr>
				<td><b>Uitschrijfdatum</b></td>
				<td><input type="date" name="uitschrijfdatum" placeholder=" " value="<?php if (isset($frm_uitschrijfdatum)) { echo $frm_uitschrijfdatum; } ?>"></td>
			</tr>
			<tr>
				<td><b>Betaalt geen contributie</b></td>
				<td><input type="checkbox" name="geencontributie" <?php { echo $frm_geencontributie; } ?>></td>
			</tr>
		</table>
		<br />
		<?php if ($aktie == 'edit' || $aktie == 'editprof') echo '<input class="button" type="submit" name="save" value="save">'; ?>
		<?php if ($aktie == 'delete') echo '<input class="button" type="submit" name="delete" value="delete" onClick="return confirmDelLid()">'; ?>
		<?php if ($aktie == 'toevoegen') echo '<input class="button" type="submit" name="submit" value="submit">'; ?>
		<input class="button" type="submit" name="cancel" value="cancel">
		<!--  <input class="button" type="submit" name="save" value="save"> -->
		</p>
	</form>
	<br />		
	<?php 
    if (!isset($focus)) {
    	$focus='voornaam';
    }
    setfocus('leden', $focus);
}
	
include ("footer.php");
?>		

