
<?php

    if($brugerEksisterer){
        $_SESSION['navn']       = $navn;
        $_SESSION['password']   = $password; //vi behøver ikke at beskytte password her da vi hasher det senere
        $_SESSION['connection'] = $connection;
        $_SESSION['user_id']    = doesUserNameAndPasswordExists($connection,$navn,$password);
        //Viser brugerens data - aktier osv.
        include "brugerSubSide.php";
    }

?>