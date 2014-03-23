<?
include("include/session.php");
include("include/content.php");
?>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=9; text/html; charset=utf-8" />
    <title>Demo projektas</title>
    <link href="include/styles.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <table class="center">
        <tr>
            <td>
                <div style="text-align: center; color: green">
                    <h1>Įmonės personalo judėjimo stebėjimo IS</h1>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <?
                //Jei vartotojas prisijungęs
                if ($session->logged_in) {
                    printUserInfo();
                    showContent();
                    
                    if(!$session->isAdmin()){
                        echo "<a href=\"include/update.php\">Atnaujinti buvimo vietą</a> &nbsp;&nbsp;";
                    }
                    
                    //Jei vartotojas neprisijungęs, rodoma prisijungimo forma
                    //Jei atsiranda klaidų, rodomi pranešimai.
                } else {
                    echo "<div align=\"center\">";
                    if ($form->num_errors > 0) {
                        echo "<font size=\"3\" color=\"#ff0000\">Klaidų: " . $form->num_errors . "</font>";
                    }
                    echo "<table class=\"center\"><tr><td>";
                    include("include/loginForm.php");
                    echo "</td></tr></table></div><br></td></tr>";
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
