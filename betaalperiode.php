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
	<h1>Betaalperioden</h1>
			
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
        header("location: index.php");
    } else {
        header("location: betaalperiode.php?aktie=disp");
    }
}

//------------------------------------------------------------------------------------------------------
// BUTTON Delete
//------------------------------------------------------------------------------------------------------
if (isset($_POST['delete'])) {
	$delID = $_POST['ID'];
	$sql_select = "SELECT * FROM betalingen where betaalperiodeID='".$delID."'";
	if($sql_result = mysqli_query($dbconn, $sql_select)) {
	    if(mysqli_num_rows($sql_result) > 0) {
	        echo '<p class="errmsg"> ERROR: Er zijn nog betalingen gekoppeld aan deze periode</p>';
	        $focus     = 'ID';
	        $formerror = 1;
	    } else {
	        $sql_delbetaalperiode = mysqli_query($dbconn, "DELETE FROM betaalperiode WHERE ID = '$delID'");
	        header("location: betaalperiode.php?aktie=disp");
	    }
	}
}

//------------------------------------------------------------------------------------------------------
// BUTTON Save
//------------------------------------------------------------------------------------------------------
if (isset($_POST['save']) || (isset($_POST['submit']))) {
    form_betaalperiode_fill('save');
    writelogrecord("betaalperiode","BTNSAVE Op save gedrukt om gewijzigd record op te slaan");
    //$formerror = 0;
	if ((!$_POST['betaalperiode'] || $_POST['betaalperiode'] == "") && (!$formerror)) {
	    writelogrecord("betaalperiode","CHECK1A Betaalperiode is niet ingevuld");
	    echo("<script> alert('Password is too long')</script>");
		//echo '<p class="errmsg"> ERROR: Betaalperiode is een verplicht veld</p>';
		$focus     = 'betaalperiode';
		$formerror = 1;
	}

	// Update record indien er geen errors zijn
	if (!$formerror) { 
	    if (isset($_POST['save'])) { 
		    $update = "UPDATE betaalperiode SET 
            betaalperiode = '".$_POST['betaalperiode']." 'WHERE ID = '".$_POST['ID']."'";
		    $check_update = mysqli_query($dbconn, $update) or die ("Error in query: $update. ".mysqli_error($dbconn));
		    if ($check_update) { 
		        writelogrecord("betaalperiode","UPDATE betaalperiode ".$_POST['ID']." is succesvol ge-update");
			    echo '<p class="infmsg">Betaalperiode <b>'.$_POST['ID'].'</b> is gewijzigd</p>.';
			    $frm_betaalperiode = "";
		    }
		    else {
			    echo '<p class="errmsg">Er is een fout opgetreden bij het updaten van betaalperiode. Probeer het nogmaals.<br />
			    Indien het probleem zich blijft voordoen neem dan contact op met de webmaster</p>';
		    }
	    }
	    if (isset($_POST['submit'])) {
	        $insert = "INSERT INTO betaalperiode (betaalperiode)
			VALUES ('".$_POST['betaalperiode']."')";
	        writeLogRecord("betaalperiode","UPDQUERY INSERT-query: ".$insert);
	        $check_insert = mysqli_query($dbconn, $insert);
	        $sql_select = "SELECT ID FROM betaalperiode WHERE betaalperiode = ".$_POST['betaalperiode'];
	        if($sql_result = mysqli_query($dbconn, $sql_select)) {
	            while($row_selectbetaalperiode = mysqli_fetch_array($sql_result)) {
	                $betaalperiodeID    = $row_selectbetaalperiode['ID'];
	                writeLogRecord("betaalperiode","Insert betaling voor betaalperiodeID: ".$betaalperiodeID);
	            }
	        }
	        // Voeg hieronder records toe aan betalingen indien lid abonnement.ID 1 of 2 heeft en
	        // uitschrijfdatum is leeg en
	        // geencontributie is 0
	        $sql_select = "SELECT L.ID as LID, L.abonnementID as LabonnementID, A.bedrag as Abedrag, A.ID as AabonnementID FROM leden L 
                JOIN abonnement A ON A.ID = L.abonnementID
                WHERE (L.abonnementID=1 OR L.abonnementID=2) AND L.geencontributie=0 AND L.uitschrijfdatum IS NULL";
	        if($sql_result = mysqli_query($dbconn, $sql_select)) {
	            while($row_selectleden = mysqli_fetch_array($sql_result)) {
	                $lidID    = $row_selectleden['LID'];
	                $contributiebedrag = $row_selectleden['Abedrag'];
	                writeLogRecord("betaalperiode","Lidnr die toegevoegd zou worden aan betalingen: ".$lidID.", betaalperiodeID ".$betaalperiodeID." contributiebedrag ".$contributiebedrag);
	                // Insert record in betalingen
	                $insert = "INSERT INTO betalingen (ledenID, betaalperiodeID, contributiebedrag) 
                    VALUES ('".$lidID."',
                            '".$betaalperiodeID."',
                            '".$contributiebedrag."')";
	                writeLogRecord("betaalperiode","INSERT-query betalingen: ".$insert);
	                $check_insert_betalingen = mysqli_query($dbconn, $insert);
	            }
	        }
	    }
		header("location: betaalperiode.php?aktie=disp"); 
	}
}

//------------------------------------------------------------------------------------------------------
//
//       *******************   START   *******************
//
// Dit wordt uitgevoerd wanneer de user op Onderhoud soort uren heeft geklikt
// Er wordt een lijst met de uren getoond
//------------------------------------------------------------------------------------------------------
if ($aktie == 'disp') {
	$sql_betaalperiode = mysqli_query($dbconn, "SELECT * FROM betaalperiode ORDER BY betaalperiode desc");
	echo "<center><table>";
	echo "<tr><th>ID</th><th>betaalperiode</th><th colspan=\"3\" align=\"center\">Akties</th></tr>";
	$rowcolor = 'row-a';
	while($row_betaalperiode = mysqli_fetch_array($sql_betaalperiode)) {
		$id            = $row_betaalperiode['ID'];
		$betaalperiode = $row_betaalperiode['betaalperiode'];
		echo '<tr class="'.$rowcolor.'">
			<td><b>'.$id.'</b></td><td>'.$betaalperiode.'</td></td>

			<td class="button"><a href="betaalperiode.php?aktie=edit&edtID='.$id.'"><img src="./img/buttons/icons8-edit-48.png" alt="wijzigen betaalperiode" title="wijzigen betaalperiode" /></a></td>
			<td class="button"><a href="betaalperiode.php?aktie=delete&edtID='.$id.'"><img src="./img/buttons/icons8-trash-can-48.png" alt="delete betaalperiode" title="delete betaalperiode" /></a></td>
			<td class="button"><a href="betaalperiode.php?aktie=toevoegen"><img src="./img/buttons/icons8-plus-48.png" alt="toevoegen betaalperiode" title="toevoegen betaalperiode" /></a></td>
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
	    $sql_betaalperiode = mysqli_query($dbconn, "SELECT * FROM betaalperiode WHERE ID = '$edtID'");
	    while($row_betaalperiode = mysqli_fetch_array($sql_betaalperiode)) {
	        global $frm_ID, $frm_betaalperiode, $formerror;
	        $formerror         = 0;
	        $frm_ID            = $row_betaalperiode['ID'];
	        $frm_betaalperiode = $row_betaalperiode['betaalperiode'];
	    }
	}
    ?>
	<form name="betaalperiode" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
 		<p>
		<table>
	    	<tr>
				<td><b>ID</b></td>
				<td><input style="text-align:right;" type="text" readonly name="ID" size="4" maxlength="8" value="<?php if (isset($frm_ID)) { echo $frm_ID; } ?>"></td>
			</tr>
			<tr>
				<td><b>Betaalperiode</b></td>
				<td><input type="text" name="betaalperiode" size="4" maxlength="6" value="<?php if (isset($frm_betaalperiode)) { echo $frm_betaalperiode; } ?>"></td>
			</tr>
		</table>
		<br />
		<?php if ($aktie == 'toevoegen') echo '<input class="button" type="submit" name="submit" value="submit">'; ?>
		<?php if ($aktie == 'edit') echo '<input class="button" type="submit" name="save" value="save">'; ?>
		<?php if ($aktie == 'delete') echo '<input class="button" type="submit" name="delete" value="delete" >'; ?>
		<input class="button" type="submit" name="cancel" value="cancel" formnovalidate>
		</p>
	</form>
	<br />		
	<?php 
    if (!isset($focus)) {
    	$focus='betaalperiode';
    }
    setfocus('betaalperiode', $focus);
}
	
include ("footer.php");
?>		


