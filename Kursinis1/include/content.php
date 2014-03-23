<?

function showContent(){
    global $session;
    if($session->isAdmin())
        getDataForAdmin();
    else
        getDataForUser();
}

function getDataForUser(){
    global $database, $session;
    $location = $database->getLastLocation($session->user_id);
    $result = "<table><tr><th>Latitude</th><th>Longtitude</th><th>Laikas</th></tr>";
    
    $result .= "<tr><td>$location[lat]</td><td>$location[lng]</td><td>$location[time]</td></tr>";
    
    $result .= "</table>";
    echo $result;
}

function getDataForAdmin(){
    global $database, $session;
    $locs = $database->getLastLocations();
    $result = "<table><tr><th>Vardas</th><th>Pavardë</th><th>Numeris</th><th>Latitude</th><th>Longtitude</th><th>Laikas</th></tr>";
        
    foreach($locs as $location){
        $result .= "<tr><td>$location[first_name]</td><td>$location[last_name]</td><td>$location[id]</td><td>$location[lat]</td><td>$location[lng]</td><td>$location[time]</td></tr>";
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
