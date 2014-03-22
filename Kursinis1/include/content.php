<?

function getDataForUser(){
    global $database, $session;
    if($session->isAdmin()){
        $locs = $database->getLastLocations();
    }else{
        $locs = $database->getLastLocation($session->user_id);
    }
    $result = "<table><tr><th>Latitude</th><th>Longtitude</th><th>Laikas</th></tr>";
    
    foreach($locs as $location){
        $result .= "<tr><td>$location[lat]</td><td>$location[lng]</td><td>$location[time]</td></tr>";
    }
    $result .= "</table>";
    echo $result;
}

function printUserInfo(){
    global $database, $session;
    $info = $database->getUserInfo($session->user_id);
    $type = $session->isAdmin() ? "Administatorius" : "Vartotojas";
    echo "$type   $info[first_name] $info[last_name] $info[id]<br>";
}
?>
