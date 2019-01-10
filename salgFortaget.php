<?php
    session_start();
    include "database.php";
    $connect = getConnectionAndCreateAll(); //genopretter forbindelse her... af en eller anden grund mistede jeg den...
    //Ved hjælp af Get har vi fået information om købet man har tængt sig at lave.

    $antal = $_POST['antal'];
    if($antal>0){//tjekker om der sælges 1 eller flere aktier for at være sikker på at der ikke købes i salg
    	sellAktie($connect, $_SESSION['user_id'], $_POST['aktie_id'], $_POST['pris'], $antal);

    	header("location: http://localhost/ajrp_aktiespil/ajrp_aktiespil/brugerSide.php");
    }
    echo "Du kan ikke sælge negative antal!";
    echo "<a href='brugerSide.php'><button>Gå tilbage til brugerside</button></a>";
?>