<?php 
session_start();

include ("./config.php");
include ("./db.php");
include ("./function.php");

// Controleren of cookie aanwezig is. Anders login-scherm displayen
check_cookies();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="Description" content="Information architecture, Web Design, Web Standards." />
<meta name="Keywords" content="your, keywords" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="Distribution" content="Global" />
<meta name="Author" content="Mark Wage" />
<meta name="Robots" content="index,follow" />

<link rel="stylesheet" href="./css/style.css" type="text/css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<title>JOEYS GYM ledenadministratie</title>
</head>
<body>
<!-- wrap starts here -->
<div id="wrap">
	<div id="header"><div id="header-content">	
		<h1 id="logo"><a href="index.html" title="">JOEYS GYM</a></h1>	
		<h2 id="slogan">JOEYS GYM ledenadministratie...</h2>		
		
		<!-- TopMenu Tabs -->
		<?php include ("./menu_top.php") ?>

	</div></div>
	
	<!-- content-wrap starts here -->
	<div id="content-wrap"><div id="content">		
	<div id="sidebar" ><?php include ("./menu_links.php") ?></div>	
	<div id="main">		
		<h1>Statistieken JOEY'S GYM</h1>
		<?php
		
		$rowcolor = 'row-a';
		echo "<center><table>";
		    echo "<tr><th colspan='2' style='text-align:center;'>Statistieken leden JOEY'S GYM</th></tr>";
		    //Totaal aantal aktieve leden
		    $sql_select = "SELECT * FROM leden WHERE geenContributie = 0 AND uitschrijfdatum IS NULL;";
		    if($sql_result = mysqli_query($dbconn, $sql_select)) {
		        $cnt_aktieveLeden = mysqli_num_rows($sql_result);
		    } else {
		        echo "ERROR: Could not be able to execute $sql_select. ". mysqli_error($dbconn);
		    }
		    //Aantal leden die geen contributie betalen
		    $sql_select = "SELECT * FROM leden WHERE geenContributie = '1';";
		    if($sql_result = mysqli_query($dbconn, $sql_select)) {
		        $cnt_geenContributie = mysqli_num_rows($sql_result);
		    } else {
		        echo "ERROR: Could not be able to execute $sql_select. ". mysqli_error($dbconn);
		    }
		    //Aantal uitgeschreven leden
		    $sql_select = "SELECT * FROM leden WHERE uitschrijfdatum IS NOT NULL";
		    if($sql_result = mysqli_query($dbconn, $sql_select)) {
		        $cnt_uitgeschreven = mysqli_num_rows($sql_result);
		    } else {
		        echo "ERROR: Could not be able to execute $sql_select. ". mysqli_error($dbconn);
		    }
		
			echo "<tr class='".$rowcolor."'>";
			    echo "<td>Aantal aktieve leden</td><td>".$cnt_aktieveLeden."</td>";
            echo "</tr>";
            if ($rowcolor == 'row-a') $rowcolor = 'row-b';
            else $rowcolor = 'row-a';
            echo '<tr class="'.$rowcolor.'">';
                echo "<td>Aantal leden die geen contributie betalen</td><td style='text-align:right;'>".$cnt_geenContributie."</td>";
            echo "</tr>";
            if ($rowcolor == 'row-a') $rowcolor = 'row-b';
            else $rowcolor = 'row-a';
            echo '<tr class="'.$rowcolor.'">';
                echo "<td>Totaal aantal uitgeschreven leden</td><td style='text-align:right;'>".$cnt_uitgeschreven."</td>";
            echo "</tr>";
        echo "</table></center>";
        
        $rowcolor = 'row-a';
        echo "<br /><center><table>";
            echo "<tr><th colspan='2' style='text-align:center;'>Aktieve leden per soort abonnement</th></tr>";
            $sql_select = "SELECT * FROM leden WHERE abonnementID = 1 AND geenContributie = 0 AND uitschrijfdatum IS NULL";
            if($sql_result = mysqli_query($dbconn, $sql_select)) {
                $cnt_onbeperktMaand = mysqli_num_rows($sql_result);
            } else {
                echo "ERROR: Could not be able to execute $sql_select. ". mysqli_error($dbconn);
            }
            $sql_select = "SELECT * FROM leden WHERE abonnementID = 2 AND geenContributie = 0 AND uitschrijfdatum IS NULL";
            if($sql_result = mysqli_query($dbconn, $sql_select)) {
                $cnt_eenmaalWeek = mysqli_num_rows($sql_result);
            } else {
                echo "ERROR: Could not be able to execute $sql_select. ". mysqli_error($dbconn);
            }
            $sql_select = "SELECT * FROM leden WHERE abonnementID = 3 AND geenContributie = 0 AND uitschrijfdatum IS NULL";
            if($sql_result = mysqli_query($dbconn, $sql_select)) {
                $cnt_strippenkaart = mysqli_num_rows($sql_result);
            } else {
                echo "ERROR: Could not be able to execute $sql_select. ". mysqli_error($dbconn);
            }
            $sql_select = "SELECT * FROM leden WHERE abonnementID = 4 AND geenContributie = 0 AND uitschrijfdatum IS NULL";
            if($sql_result = mysqli_query($dbconn, $sql_select)) {
                $cnt_personaltraining = mysqli_num_rows($sql_result);
            } else {
                echo "ERROR: Could not be able to execute $sql_select. ". mysqli_error($dbconn);
            }
            
            echo "<tr class='".$rowcolor."'>";
            echo "<td>Onbeperkt maand</td><td style='text-align:right;'>".$cnt_onbeperktMaand."</td>";
            echo "</tr>";
            if ($rowcolor == 'row-a') $rowcolor = 'row-b';
            else $rowcolor = 'row-a';
            echo '<tr class="'.$rowcolor.'">';
            echo "<td>Eenmaal per week</td><td style='text-align:right;'>".$cnt_eenmaalWeek."</td>";
            echo "</tr>";
            if ($rowcolor == 'row-a') $rowcolor = 'row-b';
            else $rowcolor = 'row-a';
            echo '<tr class="'.$rowcolor.'">';
            echo "<td>Strippenkaart</td><td style='text-align:right;'>".$cnt_strippenkaart."</td>";
            echo "</tr>";
            if ($rowcolor == 'row-a') $rowcolor = 'row-b';
            else $rowcolor = 'row-a';
            echo '<tr class="'.$rowcolor.'">';
            echo "<td>Personal Training</td><td style='text-align:right;'>".$cnt_personaltraining."</td>";
            echo "</tr>";
        echo "</table></center>";
			
include ("footer.php");
?>	