<?

include("database.php");
include("mailer.php");
include("form.php");

class Session {

    var $user_id;      //unique uid
    var $session_id;   //Random value generated on current login
    var $user_type;    //The level to which the user pertains
    var $time;         //Time user was last active (page loaded)
    var $logged_in;    //True if user is logged in, false otherwise
    var $userinfo = array();  //The array holding all user info
    var $url;          //The page url current being viewed
    var $referrer;     //Last recorded site page viewed

    /**
     * Note: referrer should really only be considered the actual
     * page referrer in process.php, any other time it may be
     * inaccurate.
     */
    /* Class constructor */

    function Session() {
        $this->time = time();
        $this->startSession();
    }

    /**
     * startSession - Performs all the actions necessary to 
     * initialize this session object. Tries to determine if the
     * the user has logged in already, and sets the variables 
     * accordingly. Also takes advantage of this page load to
     * update the active visitors tables.
     */
    function startSession() {
        global $database;  //The database connection
        session_start();   //Tell PHP to start the session

        /* Determine if user is logged in */
        $this->logged_in = $this->checkLogin();

        /**
         * Set guest value to users not logged in, and update
         * active guests table accordingly.
         */
        if (!$this->logged_in) {
            $this->user_id = $_SESSION[CLMN_USERS_ID] = GUEST_ID;
        }

        /* Set referrer page */
        if (isset($_SESSION['url'])) {
            $this->referrer = $_SESSION['url'];
        } else {
            $this->referrer = "/";
        }

        /* Set current url */
        $this->url = $_SESSION['url'] = $_SERVER['PHP_SELF'];
    }

    /**
     * checkLogin - Checks if the user has already previously
     * logged in, and a session with the user has already been
     * established. Also checks to see if user has been remembered.
     * If so, the database is queried to make sure of the user's 
     * authenticity. Returns true if the user has logged in.
     */
    function checkLogin() {
        global $database;  //The database connection
        /* Check if user has been remembered */
        if (isset($_COOKIE[CLMN_USERS_ID]) && isset($_COOKIE[CLMN_USERS_S_ID])) {
            $this->user_id = $_SESSION[CLMN_USERS_ID] = $_COOKIE[CLMN_USERS_ID];
            $this->session_id = $_SESSION[CLMN_USERS_S_ID] = $_COOKIE[CLMN_USERS_S_ID];
        }

        /* Username and userid have been set and not guest */
        if (isset($_SESSION[CLMN_USERS_ID]) && isset($_SESSION[CLMN_USERS_S_ID]) &&
                $_SESSION[CLMN_USERS_ID] != GUEST_ID) {
            /* Confirm that username and userid are valid */
            if ($database->confirmSessionID($_SESSION[CLMN_USERS_ID], $_SESSION[CLMN_USERS_S_ID]) != 0) {
                /* Variables are incorrect, user not logged in */
                unset($_SESSION[CLMN_USERS_ID]);
                unset($_SESSION[CLMN_USERS_S_ID]);
                return false;
            }

            /* User is logged in, set class variables */
            $this->userinfo = $database->getUserInfo($_SESSION[CLMN_USERS_ID]);
            $this->user_id = $this->userinfo[CLMN_USERS_ID];
            $this->session_id = $this->userinfo[CLMN_USERS_S_ID];
            $this->user_type= $this->userinfo[CLMN_TYPES_NAME];
            return true;
        }

        /* User not logged in */ else {
            return false;
        }
    }

    /**
     * login - The user has submitted his username and password
     * through the login form, this function checks the authenticity
     * of that information in the database and creates the session.
     * Effectively logging in the user if all goes well.
     */
    function login($sub_id, $sub_pass, $sub_remember) {
        global $database, $form;  //The database and form object

        /* Username error checking */
        $field = "user";  //Use field name for username
        if (!$sub_id || strlen($sub_id = trim($sub_id)) == 0) {
            $form->setError($field, "* Neįvestas vartotojo vardas");
        } else {
            /* Check if username is not numeric */
            if (!eregi("^([0-9])*$", $sub_id)) {
                $form->setError($field, "* Vartotojo vardas gali būti sudarytas
                    <br>&nbsp;&nbsp;tik iš skaičių");
            }
        }

        /* Password error checking */
        $field = "pass";  //Use field name for password
        if (!$sub_pass) {
            $form->setError($field, "* Neįvestas slaptažodis");
        }

        /* Return if form errors exist */
        if ($form->num_errors > 0) {
            return false;
        }

        /* Checks that username is in database and password is correct */
        $sub_id = stripslashes($sub_id);
        $result = $database->confirmUserPass($sub_id, md5($sub_pass));

        /* Check error codes */
        if ($result == 1) {
            $field = "user";
            $form->setError($field, "* Tokio vartotojo nėra");
        } else if ($result == 2) {
            $field = "pass";
            $form->setError($field, "* Neteisingas slaptažodis");
        }

        /* Return if form errors exist */
        if ($form->num_errors > 0) {
            return false;
        }

        /* Username and password correct, register session variables */
        $this->userinfo = $database->getUserInfo($sub_id);
        $this->user_id = $_SESSION[CLMN_USERS_ID] = $sub_id;
        $this->session_id = $_SESSION[CLMN_USERS_S_ID] = $this->generateRandID();
        $this->user_type = $this->userinfo[CLMN_TYPES_NAME];

        /* Insert userid into database and update active users table */
        $database->updateUserField($this->user_id, CLMN_USERS_S_ID, $this->session_id);

        /**
         * This is the cool part: the user has requested that we remember that
         * he's logged in, so we set two cookies. One to hold his username,
         * and one to hold his random value userid. It expires by the time
         * specified in constants.php. Now, next time he comes to our site, we will
         * log him in automatically, but only if he didn't log out before he left.
         */
        if ($sub_remember) {
            setcookie(CLMN_USERS_ID, $this->user_id, time() + COOKIE_EXPIRE, COOKIE_PATH);
            setcookie(CLMN_USERS_S_ID, $this->session_id, time() + COOKIE_EXPIRE, COOKIE_PATH);
        }

        /* Login completed successfully */
        return true;
    }

    /**
     * logout - Gets called when the user wants to be logged out of the
     * website. It deletes any cookies that were stored on the users
     * computer as a result of him wanting to be remembered, and also
     * unsets session variables and demotes his user level to guest.
     */
    function logout() {
        global $database;  //The database connection
        /**
         * Delete cookies - the time must be in the past,
         * so just negate what you added when creating the
         * cookie.
         */
        if (isset($_COOKIE[CLMN_USERS_ID]) && isset($_COOKIE[CLMN_USERS_S_ID])) {
            setcookie(CLMN_USERS_ID, "", time() - COOKIE_EXPIRE, COOKIE_PATH);
            setcookie(CLMN_USERS_S_ID, "", time() - COOKIE_EXPIRE, COOKIE_PATH);
        }

        /* Unset PHP session variables */
        unset($_SESSION[CLMN_USERS_ID]);
        unset($_SESSION[CLMN_USERS_S_ID]);

        /* Reflect fact that user has logged out */
        $this->logged_in = false;

        /* Set user level to guest */
        $this->user_id = GUEST_ID;
    }

    /**
     * register - Gets called when the user has just submitted the
     * registration form. Determines if there were any errors with
     * the entry fields, if so, it records the errors and returns
     * 1. If no errors were found, it registers the new user and
     * returns 0. Returns 2 if registration failed.
     */
    function register($sub_id, $sub_pass, $sub_first, $sub_last) {
        global $database, $form, $mailer;  //The database, form and mailer object

        /* Username error checking */
        $field = "id";  //Use field name for username
        if (!$sub_id || strlen($sub_id = trim($sub_id)) == 0) {
            $form->setError($field, "* Vartotojas neįvestas");
        } else {
            /* Spruce up username, check length */
            $sub_id = stripslashes($sub_id);
            if(!eregi("^([0-9])+$", $sub_id)){
                $form->setError($field, "* Vartotojo vardas gali būti sudarytas
                    <br>&nbsp;&nbsp;tik iš skaičių");
            }
            else if (strlen($sub_id) != 8) {
                $form->setError($field, "* Vartotojo numerį turi sudaryti 8 skaitmenys");
            }
            else if ($database->idTaken($sub_id)) {
                $form->setError($field, "* Toks numeris jau registruotas");
            }
        }

        /* Password error checking */
        $field = "pass";  //Use field name for password
        if (!$sub_pass) {
            $form->setError($field, "* Neįvestas slaptažodis");
        } else {
            /* Spruce up password and check length */
            $sub_pass = stripslashes($sub_pass);
            if (strlen($sub_pass) < 4) {
                $form->setError($field, "* Ne mažiau kaip 4 simboliai");
            }
            /* Check if password is not alphanumeric */ else if (!eregi("^([0-9a-z])+$", ($sub_pass = trim($sub_pass)))) {
                $form->setError($field, "* Slaptažodis gali būti sudarytas
                    <br>&nbsp;&nbsp;tik iš raidžių ir skaičių");
            }
            /**
             * Note: I trimmed the password only after I checked the length
             * because if you fill the password field up with spaces
             * it looks like a lot more characters than 4, so it looks
             * kind of stupid to report "password too short".
             */
        }

        $field = "first_name"; 
        if(!$sub_first || strlen($sub_first = trim($sub_first)) == 0){
            $form->setError($field, "* Neįvestas vardas");
        }else{
            $sub_first = stripslashes($sub_first);
            if(!eregi("^([a-z])+$", $sub_first)){
                $form->setError($field, "* Vardas gali būti sudarytas
                    <br>&nbsp;&nbsp;tik iš raidžių");
            }
        }
        
        $field = "last_ame"; 
        if(!$sub_last || strlen($sub_last = trim($sub_last)) == 0){
            $form->setError($field, "* Neįvestas vardas");
        }else{
            $sub_last = stripslashes($sub_last);
            if(!eregi("^([a-z])+$", $sub_last)){
                $form->setError($field, "* Pavardė gali būti sudaryta
                    <br>&nbsp;&nbsp;tik iš raidžių");
            }
        }

        /* Errors exist, have user correct them */
        if ($form->num_errors > 0) {
            return 1;  //Errors with form
        }
        /* No errors, add the new account to the */ else {
            if ($database->addNewUser($sub_id, md5($sub_pass), $sub_first, $sub_last)) {
                return 0;  //New user added succesfully
            } else {
                return 2;  //Registration attempt failed
            }
        }
    }

    /**
     * isAdmin - Returns true if currently logged in user is
     * an administrator, false otherwise.
     */
    function isAdmin() {
        return ($this->user_type == TYPE_ADMIN);
    }

    /**
     * generateRandID - Generates a string made up of randomized
     * letters (lower and upper case) and digits and returns
     * the md5 hash of it to be used as a userid.
     */
    function generateRandID() {
        return md5($this->generateRandStr(16));
    }

    /**
     * generateRandStr - Generates a string made up of randomized
     * letters (lower and upper case) and digits, the length
     * is a specified parameter.
     */
    function generateRandStr($length) {
        $randstr = "";
        for ($i = 0; $i < $length; $i++) {
            $randnum = mt_rand(0, 61);
            if ($randnum < 10) {
                $randstr .= chr($randnum + 48);
            } else if ($randnum < 36) {
                $randstr .= chr($randnum + 55);
            } else {
                $randstr .= chr($randnum + 61);
            }
        }
        return $randstr;
    }

}

/**
 * Initialize session object - This must be initialized before
 * the form object because the form uses session variables,
 * which cannot be accessed unless the session has started.
 */
$session = new Session;

/* Initialize form object */
$form = new Form;
?>