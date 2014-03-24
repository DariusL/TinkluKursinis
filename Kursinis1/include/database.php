<?

include("constants.php");

class MySQLDB {

    var $connection;         //The MySQL database connection
    var $num_members;        //Number of signed-up users
    var $debug  = true;

    /* Note: call getNumMembers() to access $num_members! */

    /* Class constructor */

    function MySQLDB() {
        /* Make connection to database */
        $this->connection = mysql_connect(DB_SERVER, DB_USER, DB_PASS)
                or die(mysql_error() . '<br><h1>Faile include/constants.php suveskite savo MySQLDB duomenis.</h1>');
        mysql_select_db(DB_NAME, $this->connection) or
                die(mysql_error() . '<br><h1>Faile include/constants.php suveskite savo MySQLDB duomenis.</h1>');

        /**
         * Only query database to find out number of members
         * when getNumMembers() is called for the first time,
         * until then, default value set.
         */
        $this->num_members = -1;
    }

    /**
     * confirmUserPass - Checks whether or not the given
     * username is in the database, if so it checks if the
     * given password is the same password in the database
     * for that user. If the user doesn't exist or if the
     * passwords don't match up, it returns an error code
     * (1 or 2). On success it returns 0.
     */
    function confirmUserPass($uid, $password) {
        /* Add slashes if necessary (for query) */
        if (!get_magic_quotes_gpc()) {
            $uid = addslashes($uid);
        }

        /* Verify that user is in database */
        $q = "SELECT ".CLMN_USERS_PASS." FROM ".TBL_USERS." WHERE ".CLMN_USERS_ID." = '$uid'";
        $result = mysql_query($q, $this->connection);
        if (!$result || (mysql_numrows($result) < 1)) {
            return 1; //Indicates username failure
        }

        /* Retrieve password from result, strip slashes */
        $dbarray = mysql_fetch_array($result);
        $dbarray[CLMN_USERS_PASS] = stripslashes($dbarray[CLMN_USERS_PASS]);
        $password = stripslashes($password);

        /* Validate that password is correct */
        if ($password == $dbarray[CLMN_USERS_PASS]) {
            return 0; //Success! Username and password confirmed
        } else {
            return 2; //Indicates password failure
        }
    }
    
    function confirmSessionID($user_id, $session_id) {
        /* Add slashes if necessary (for query) */
        if (!get_magic_quotes_gpc()) {
            $user_id = addslashes($user_id);
        }

        /* Verify that user is in database */
        $q = "SELECT ".CLMN_USERS_S_ID." FROM ".TBL_USERS." WHERE ".CLMN_USERS_ID." = '$user_id'";
        $result = mysql_query($q, $this->connection);
        if (!$result || (mysql_numrows($result) < 1)) {
            return 1; //Indicates username failure
        }

        /* Retrieve userid from result, strip slashes */
        $dbarray = mysql_fetch_array($result);
        $dbarray[CLMN_USERS_S_ID] = stripslashes($dbarray[CLMN_USERS_S_ID]);
        $session_id = stripslashes($session_id);

        /* Validate that userid is correct */
        if ($session_id == $dbarray[CLMN_USERS_S_ID]) {
            return 0; //Success! Username and userid confirmed
        } else {
            return 2; //Indicates userid invalid
        }
    }


    /**
     * usernameTaken - Returns true if the username has
     * been taken by another user, false otherwise.
     */
    function idTaken($id) {
        if (!get_magic_quotes_gpc()) {
            $id = addslashes($id);
        }
        $q = "SELECT ".CLMN_USERS_ID." FROM ".TBL_USERS." WHERE ".CLMN_USERS_ID." = '$id'";
        $result = mysql_query($q, $this->connection);
        return (mysql_numrows($result) > 0);
    }
    
    function randomByte(){
        return sprintf("%02X", mt_rand(0, 255));
    }
    
    function getRandomColor(){
        return $this->randomByte().$this->randomByte().$this->randomByte()."AA";
    }

    /**
     * addNewUser - Inserts the given (username, password, email)
     * info into the database. Appropriate user level is set.
     * Returns true on success, false otherwise.
     */
    function addNewUser($id, $password, $first_name, $last_name) {
        $q = "INSERT INTO users VALUES ('$id', '$first_name', '$last_name', 1, '$password', NULL, '".$this->getRandomColor()."')";
        return $this->query($q);
    }
    
    function addLocation($id, $lat, $lng){
        $time = date("Y-m-d H:i:s");
        $q = "INSERT INTO locations (user_id, time, lat, lng) VALUES('$id', '$time', '$lat', '$lng')";
        $this->query($q);
    }

    /**
     * updateUserField - Updates a field, specified by the field
     * parameter, in the user's row of the database.
     */
    function updateUserField($id, $field, $value) {
        $q = "UPDATE ".TBL_USERS." SET " . $field . " = '$value' WHERE ".CLMN_USERS_ID." = '$id'";
        return mysql_query($q, $this->connection);
    }

    /**
     * getUserInfo - Returns the result array from a mysql
     * query asking for all information stored regarding
     * the given username. If query fails, NULL is returned.
     */
    function getUserInfo($id) {
        $q = "SELECT users.id, first_name, last_name, s_id, user_types.name, users.color FROM users LEFT JOIN user_types ON users.type = user_types.id WHERE users.id = '$id'";
        $result = mysql_query($q, $this->connection);
        /* Error occurred, return given name by default */
        if (!$result || (mysql_numrows($result) < 1)) {
            return NULL;
        }
        /* Return result array */
        $dbarray = mysql_fetch_array($result);
        return $dbarray;
    }
    
    function getLastLocation($id){
        return mysql_fetch_assoc($this->query("SELECT lat, lng, time, first_name, last_name, users.id FROM locations LEFT JOIN users ON locations.user_id = users.id WHERE user_id = '$id' ORDER BY time DESC LIMIT 1"));
    }
    
    function getLastLocations(){
        $ret = [];
        $ids = $this->query("SELECT DISTINCT user_id FROM locations");
        while($row = mysql_fetch_assoc($ids)){
            array_push($ret, $this->getLastLocation($row['user_id']));
        }
        return $ret;
    }
    
    function getUserHistory($id){
        $ret = [];
        $data = $this->query("SELECT lat, lng FROM locations WHERE user_id = '$id' ORDER BY time");
        while($row = mysql_fetch_assoc($data)){
            array_push($ret, $row);
        }
        return $ret;
    }
    
    function getAllHistories(){
        $ret = [];
        $ids = $this->query("SELECT DISTINCT user_id FROM locations");
        while($row = mysql_fetch_array($ids, MYSQL_NUM)){
            $color = $this->getUserInfo($row[0])['color'];
            array_push($ret, ["path" => $this->getUserHistory($row[0]), "color" => $color]);
        }
        return $ret;
    }

    /**
     * getNumMembers - Returns the number of signed-up users
     * of the website, banned members not included. The first
     * time the function is called on page load, the database
     * is queried, on subsequent calls, the stored result
     * is returned. This is to improve efficiency, effectively
     * not querying the database when no call is made.
     */
    function getNumMembers() {
        if ($this->num_members < 0) {
            $q = "SELECT * FROM ".TBL_USERS;
            $result = mysql_query($q, $this->connection);
            $this->num_members = mysql_numrows($result);
        }
        return $this->num_members;
    }

    /**
     * query - Performs the given query on the database and
     * returns the result, which may be false, true or a
     * resource identifier.
     */
    function query($query) {
        $result = mysql_query($query, $this->connection);
        if($result != false){
            return $result;
        }else{
            if($this->debug){
                die("Query: ".$query."<br><br>"."Error: ".mysql_error($this->connection));
            }
            return false;
        }
    }

}

/* Create database connection */
$database = new MySQLDB;
?>