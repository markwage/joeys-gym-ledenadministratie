<?php
//------------------------------------------------------------------------
// Controleren of de user admin-rechten heeft. Zo niet een error-scherm displayen
//------------------------------------------------------------------------
function check_admin() {
    if (!isset($_SESSION['admin']) || (!$_SESSION['admin'])) {
        header("location: noadmin.php");
    }
}

//------------------------------------------------------------------------
// Write logrecord to file
//------------------------------------------------------------------------
function writeLogRecord($phpProg, $logRecord) {
    if (isset($_SESSION['username'])) $username = $_SESSION['username'];
    else $username = "";
    $fileName = "C:\\wamp64\\www\\mirage-urenregistratie-systeem\\logs\\systemlogMUS.log";
    $datumlog = date('Ymd H:i:s');
    file_put_contents($fileName, PHP_EOL.$datumlog.";".$phpProg.";".$username.";".$logRecord, FILE_APPEND);
}

?>