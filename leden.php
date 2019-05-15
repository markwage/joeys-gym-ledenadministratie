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
// BUTTON Save
//------------------------------------------------------------------------------------------------------
if (isset($_POST['save'])) {
    form_leden_fill('save');
	//$formerror = 0;
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
	if (!$_POST['email'] && (!$formerror)) {
		echo '<p class="errmsg"> ERROR: Email is een verplicht veld</p>';
		$focus     = 'email';
		$formerror = 1;
	}
	if ($_SESSION['admin'] && (!$formerror)) {
		if (!isset($_POST['admin'])) {
			$_POST['admin'] = 0;
		}
		else {
			$_POST['admin'] = 1;
		}
		if (!isset($_POST['indienst'])) {
			$_POST['indienst'] = 0;
		}
		else {
			$_POST['indienst'] = 1;
		}
	}
	
	// here we encrypt the password and add slashes if needed
	if (!$formerror) {
	    writelogrecord("edit_users", "CREATEQRY1 - Beginnen met het aanmaken van de UPDATE query om user ".$_POST['username']."te updaten");
	    $update = "UPDATE users SET ";
	    if (!$_POST['pass'] == "") {
	        $_POST['pass'] = md5($_POST['pass']);
	        writelogrecord("edit_users", "PASS_MD5 - Wachtwoord is middels md5 encrypted");
	        if (!get_magic_quotes_gpc()) {
	            $_POST['pass'] = addslashes($_POST['pass']);
	            $_POST['username'] = addslashes($_POST['username']);
	        }
	        $update .= "password='".$_POST['pass']."',";
	    }
	    
	    $update .= "admin='".$_POST['admin']."',
		voornaam='".$_POST['voornaam']."',
		tussenvoegsel='".$_POST['tussenvoegsel']."',
		achternaam='".$_POST['achternaam']."',
		emailadres='".$_POST['email']."',
		indienst='".$_POST['indienst']."' WHERE username = '".$_POST['username']."'";
	    writeLogRecord("edit_users","UPDQUERY De UPDATE-query wordt nu uitgevoerd op de database voor user".$frm_username);
	    $check_upd_user = mysqli_query($dbconn, $update);
		if ($check_upd_user) { 
			echo '<p class="infmsg">User <b>'.$_POST['username'].'</b> is gewijzigd</p>.';
			$frm_username      = "";
			$frm_pass          = "";
			$frm_pass2         = "";
			$frm_voornaam      = "";
			$frm_tussenvoegsel = "";
			$frm_achternaam    = "";
			$frm_email         = "";
		}
		else {
			echo '<p class="errmsg">Er is een fout opgetreden bij het toevoegen van de user. Probeer het nogmaals.<br />
			Indien het probleem zich blijft voordoen neem dan contact op met de webmaster</p>';
		}
		header("location: leden.php?aktie=dispAktief"); 
	}
}

//------------------------------------------------------------------------------------------------------
// START Dit wordt uitgevoerd wanneer de user op Usermanagement heeft geklikt
// Er wordt een lijst met de users getoond
//------------------------------------------------------------------------------------------------------
if ($aktie == 'dispAktief') {
    //$sql_select = "SELECT * FROM leden ORDER BY achternaam";
    $sql_select = "SELECT * FROM leden WHERE geenContributie = '0' AND uitschrijfdatum IS NULL ORDER BY achternaam;";
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
	    $lidID = $_GET['lidID'];
	    $focus = "voornaam";
	    $sql_dspleden = mysqli_query($dbconn, "SELECT * FROM leden WHERE ID = '$lidID'");
	    while($row_dspleden = mysqli_fetch_array($sql_dspleden)) {
	        // >>> uitvoeren fill_leden_form om onderstaande variabele te vullen??????
	        $frm_lidnr          = $row_dspleden['ID'];
		    $frm_voornaam       = $row_dspleden['voornaam'];
		    $frm_tussenvoegsel  = $row_dspleden['tussenvoegsel'];
		    $frm_achternaam     = $row_dspleden['achternaam'];
		    $frm_adres          = $row_dspleden['adres'];
		    $frm_postcode       = $row_dspleden['postcode'];
		    $frm_woonplaats     = $row_dspleden['woonplaats'];
		    $frm_telefoonnummer = $row_dspleden['telefoonnummer'];
		    $frm_emailadres     = $row_dspleden['emailadres'];
		    //if ($row_dspleden['admin'] == 1) $frm_admin = "checked";
		    //else $frm_admin = "";
		    //if ($row_dspuser['indienst'] == 1) $frm_indienst = "checked";
		    //else $frm_indienst = "";
	    }
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
		<hr>
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
			<!--  
			<tr>
				<td>In dienst</td>
				<td><input type="checkbox" <?php if (!$_SESSION['admin']) { echo "checked disabled "; } ?>name="indienst" <?php { echo $frm_indienst; } ?>></td>
			</tr>
			-->
		</table>
		<br />
		<?php if ($aktie == 'edit' || $aktie == 'editprof') echo '<input class="button" type="submit" name="save" value="save">'; ?>
		<?php if ($aktie == 'delete') echo '<input class="button" type="submit" name="delete" value="delete" onClick="return confirmDelLid()">'; ?>
		<?php if ($aktie == 'toevoegen') echo '<input class="button" type="submit" name="save" value="save">'; ?>
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

