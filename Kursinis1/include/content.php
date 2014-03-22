<?

function getDataFor(){
    global $database, $session;
    if($session->isAdmin()){
        $data = $database->query("SELECT first_name, last_name FROM users WHERE type != 2");
    }else{
        $data = $database->query("SELECT first_name, last_name FROM users WHERE id = '$session->user_id'");
    }
    $result = "<table><tr><th>Vardas</th><th>Pavardë</th></tr>";
    while($row = mysql_fetch_array($data, MYSQL_NUM)){
        $result .= "<tr><td>$row[0]</td><td>$row[1]</td></tr>";
    }
    $result .= "</table>";
    echo $result;
}

getDataFor();
?>
