<?

/**
 * Just a little page footer, tells how many registered members
 * there are, how many users currently logged in and viewing site,
 * and how many guests viewing site. 
 */
if (isset($database)) {
    $log_out = "";
    if(isset($session) && $session->logged_in){
        $log_out = "<a href=\"process.php\">Atsijungti</a> &nbsp;&nbsp;";
    }
    echo ""
    . "<table width=100% "
    . "style=\"padding:1px;background-color:#DCDCDC;border:1px dashed grey;\">\n"
    . "<tr align=\"center\"><td>\n"
    . $log_out
    . "<b>Registruotų vartotojų kiekis: </b> " . $database->getNumMembers() . ".&nbsp"
    . "</td></tr>\n"
    . "</table>\n"
    ;
}
?>