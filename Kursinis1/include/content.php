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
    $id = $session->user_id;
    $history = $database->getUserHistory($id);
    $image = "https://maps.googleapis.com/maps/api/staticmap?path=color:0xff0000ff|weight:5";
    foreach($history as $point){
        $image .= "|$point[lat], $point[lng]";
    }
    $image .= "&size=512x512&sensor=false";
    echo "<img src=\"$image\" alt=\"Kelias\">";
    $location = $database->getLastLocation($id);
    $result = "<table><tr><th>Latitude</th><th>Longtitude</th><th>Laikas</th></tr>";
    
    $result .= "<tr><td>$location[lat]</td><td>$location[lng]</td><td>$location[time]</td></tr>";
    
    $result .= "</table>";
    echo $result;
}

function getDataForAdmin(){
    global $database, $session;
    $histories = $database->getAllHistories();
    $image = "https://maps.googleapis.com/maps/api/staticmap";
    $delim = "?";
    foreach($histories as $history){
        
    }
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
