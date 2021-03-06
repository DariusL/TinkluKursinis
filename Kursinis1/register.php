﻿<?
include("include/session.php");
if ($session->logged_in) {
    header("Location: index.php");
} else {
?>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=9; text/html; charset=utf-8" />
    <title>Registracija</title>
    <link href="include/styles.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <table class="center">
        <tr>
            <td>
                <table style="border-width: 2px; border-style: dotted;">
                    <tr>
                        <td>Atgal į <a href="index.php">Pradžia</a>
                        </td>
                    </tr>
                </table>
                <?
    /**
     * The user has submitted the registration form and the
     * results have been processed.
     */ if (isset($_SESSION['regsuccess'])) {
            /* Registracija sėkminga */
            if ($_SESSION['regsuccess']) {
                echo "<p>Ačiū, <b>" . $_SESSION['reguname'] . "</b>, Jūsų duomenys buvo sėkmingai įvesti į duomenų bazę, galite "
                . "<a href=\"index.php\">prisijungti</a>.</p><br>";
            }
            /* Registracija nesėkminga */ else {
                echo "<p>Atsiprašome, bet vartotojo <b>" . $_SESSION['reguname'] . "</b>, "
                . " registracija nebuvo sėkmingai baigta.<br>Bandykite vėliau.</p>";
            }
            unset($_SESSION['regsuccess']);
            unset($_SESSION['reguname']);
        }
    /**
     * The user has not filled out the registration form yet.
     * Below is the page with the sign-up form, the names
     * of the input fields are important and should not
     * be changed.
     */ else {
                ?>
                <div align="center">
                    <?
        if ($form->num_errors > 0) {
            echo "<font size=\"3\" color=\"#ff0000\">Klaidų: " . $form->num_errors . "</font>";
        }
                    ?>
                    <table>
                        <tr>
                            <td>
                                <form action="process.php" method="POST" class="login">
                                    <center style="font-size: 18pt;"><b>Registracija</b></label></center>
                                    <p style="text-align: left;">
                                        Vartotojo numeris:
                                                   
                                        <input class ="s1" name="id" type="text" size="15"
                                                           value="<? echo $form->value("id"); ?>"/><br><? echo $form->error("id"); ?>
                                    </p>
                                    <p style="text-align: left;">
                                        Slaptažodis:
                                                   
                                        <input class ="s1" name="pass" type="password" size="15"
                                                           value="<? echo $form->value("pass"); ?>"/><br><? echo $form->error("pass"); ?>
                                    </p>
                                    <p style="text-align: left;">
                                        Vardas:
                                                   
                                        <input class ="s1" name="first_name" type="text" size="15"
                                                           value="<? echo $form->value("first_name"); ?>"/><br><? echo $form->error("first_name"); ?>
                                    </p>
                                    <p style="text-align: left;">
                                        Pavardė:
                                                   
                                        <input class ="s1" name="last_name" type="text" size="15"
                                                           value="<? echo $form->value("last_name"); ?>"/><br><? echo $form->error("last_name"); ?>
                                    </p>
                                    <p style="text-align: left;">
                                        <input type="hidden" name="subjoin" value="1">
                                        <input type="submit" value="Registruotis">
                                    </p>
                                </form>
                            </td>
                        </tr>
                    </table>
                </div>
                <?
    }
    echo "<tr><td>";
    include("include/footer.php");
    echo "</td></tr>";
                ?>
            </td>
        </tr>
    </table>
</body>
</html>
<?
}
?>
