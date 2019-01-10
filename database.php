<?php
// laver en global variable der er false
$GLOBALS['debug'] = false;

//tjekker om vi er i debug mode før den sender debug beskeden
if($GLOBALS['debug']){
    echo "<br>DEBUG: database.php included";
}
    //en funktion der er lavet til at producere alt der har med databasen at gøre
    function getConnectionAndCreateAll(){
        //her bliver Connection oprettet
        $connect = new mysqli("localhost", "root","");
        /*Nedenstående udkommenteret - kan bruges til debugging!!
        if ($connect->connect_error) {
            die("<br>Connection failed: " . $conn->connect_error);
        } 
        echo "Connected successfully";*/
        createDatabase($connect);           //Laver Database
        createUserTable($connect);          //laver En tabel for brugere
        createAktieTable($connect);         //laver en tabel for aktier
        createTransaktionsTable($connect);  //laver en tabel for transaktioner
        insertAktierHardcoded($connect);    //Indsætter nogen aktier designet på forhånd.
        return $connect;
    }

    //Funktionen der laver databasen
    function createDatabase($connection){
        $sql = "CREATE DATABASE myDB";
        $dbCreated = $connection->query($sql);

        //Igen kun for debug mode
        if($GLOBALS['debug']){
            if ($dbCreated) {
                echo "<br>DEBUG:Database created successfully";
            } else {
                echo "<br>DEBUG:Error creating database: " . $connection->error;
            }
        }
    }

    //Denne funktion kan indsætte en user designet fra siden
    function createUser($connection, $navn, $password){
        $secretnavn = string password_hash ( string $navn , int PASSWORD_DEFAULT );
        $secretpassword = string password_hash ( string $password , int PASSWORD_DEFAULT );//krypterer vores password inden man sætter det ind i databasen        
        $sql =  "INSERT INTO myDB.USERS (navn, password, formue) VALUES ('".$secretnavn."','".$secretpassword."',1000)";
        $userCreated = $connection->query($sql);

        //debug beskeder
        if($GLOBALS['debug']){
            if ($userCreated) {
                echo "<br>DEBUG:New record created successfully";
            } else {
                echo "<br>DEBUG: Error: " . $sql . "<br>" . $connection->error;
            } 
        }
        return $userCreated;
    }

    //Laver en tabel der er designet på forhånd
    //Den indeholder Autoincrementing ID, navn, password og formue
    function createUserTable($connection){
        $sql = "CREATE TABLE myDB.USERS (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, navn VARCHAR(30) NOT NULL, password VARCHAR(30) NOT NULL, formue DECIMAL(8,2))";
        $tbCreated = $connection->query($sql);

        //Debug beskeder
        if($GLOBALS['debug']){
            if ($tbCreated) {
                echo "<br>DEBUG:Table users created successfully";
            } else {
                echo "<br>DEBUG:Error creating users: " . $connection->error;
            }
        }
    }

    //Laver Aktie tabllen
    //Den  indeholder Autoincrementing ID, navn og pris.
    function createAktieTable($connection){
        $sql = "CREATE TABLE myDB.AKTIER (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, navn VARCHAR(30) NOT NULL, pris DECIMAL(8,2) NOT NULL)";
        $tbCreated = $connection->query($sql);

        //debug bekseder
        if($GLOBALS['debug']){
            if ($tbCreated) {
                echo "<br>DEBUG:able aktier created successfully";
            } else {
                echo "<br>DEBUG:Error creating aktier: " . $connection->error;
            }
        }
    }

    //laver transaktions tabellen
    //Den indeholder Autoincrementing ID, Foereign key(user id), Foereign key(aktie_id), antal og omkostninger.
    function createTransaktionsTable($connection){
        $sql = "CREATE TABLE myDB.TRANSAKTIONER (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT(6) UNSIGNED, aktie_id INT(6) UNSIGNED, antal INT(6), omkostning DECIMAL(8,2) NOT NULL, FOREIGN KEY (user_id) REFERENCES myDB.USERS(id), FOREIGN KEY (aktie_id) REFERENCES myDB.AKTIER(id))";
        $tbCreated = $connection->query($sql);

        //debug beskeder
        if($GLOBALS['debug']){
            if ($tbCreated) {
                echo "<br>DEBUG:Table transaktioner created successfully";
            } else {
                echo "<br>DEBUG:Error creating transaktioner: " . $connection->error;
            }
        }
    }

    //Laver nogen aktier designet på forhåndt
    //Den laver 1,2 og 3 der har navnene Aktie- 123, 1234, 12345 og priserne 123,1234 og 12345.
    function insertAktierHardcoded($connection){
        $sql ="INSERT INTO myDB.AKTIER (`id`, `navn`, `pris`) VALUES (1, 'Aktie123', '10.00'), (2, 'Aktie1234', '20.00'), (3, 'Aktie12345', '30.00'), (4, 'Aktie123456', '40.00');";
        $tbCreated = $connection->query($sql);

        //debug beskeder
        if($GLOBALS['debug']){
            if ($tbCreated) {
                echo "<br>DEBUG:Aktier inserted successfully";
            } else {
                echo "<br>DEBUG:Error insert in aktier: " . $connection->error;
            }
        } 
    }

    //Denne funktion tjekker om brugeren hvis navn den får som input
    function doesUserNameExists($connection, $navn){
        $secretnavn = string password_hash ( string $navn , int PASSWORD_DEFAULT );
        $sql = "SELECT * FROM myDB.USERS WHERE navn='".$secretnavn."' LIMIT 1";
        $result = $connection->query($sql);
        $row = $result->fetch_assoc();

        //debug beskeder
        if($GLOBALS['debug']){
            echo "<br>DEBUG: Check if  user  with PASSWORD exists" . $sql . " status(error):" . $connection->error;
        }
        return $row!=null;
    }

    //tjekker om både password og navn eksistere sammen.
    function doesUserNameAndPasswordExists($connection, $navn, $password){
        $secretnavn = string password_hash ( string $navn , int PASSWORD_DEFAULT );
        $secretpassword = string password_hash ( string $password , int PASSWORD_DEFAULT );//krypterer vores password inden man sætter det ind i databasen
        $sql = "SELECT USERS.id FROM myDB.USERS WHERE password='".$secretpassword."' AND navn='".$secretnavn."' LIMIT 1";
        $result = $connection->query($sql);
        $row = $result->fetch_assoc();
        if($GLOBALS['debug']){
            echo "<br>DEBUG: USER ID ".$row['id'];
            echo "<br>DEBUG: Check if  user  with NAME,PASSWORD exists sql:" . $sql . " " . $connection->error;
        }
        return $row != null ? $row['id']: null;
    }

    //Denne funktion finderr ud af hvor mange transaktioner man har gjort og trækker deres omkostning fra startkapitalet 1000
    function getFormue($connection, $navn, $password){
        $transaktions   = getUserAktieOversigt($connection, $_SESSION['user_id']);
        $formue = 1000;
        foreach($transaktions as $row){
            $formue = $formue - $row['omkostning'];
        }

        return $formue;
    }


    //Denne funktion står for at vise ens aktier
    function getUserAktieOversigt($connection, $userId){
        $alle_transaktioner          = array();
        $unik_transaktioer           = array();
        //Denne kode finder alle transaktioner og aktier og sørger for at de kune opstår en gang hver.
        //stykket inde i FROM(...) finder en del information og gemmer den under nogle fælles navne det udenom sørger bare for at organisere det.
        $sql = "SELECT trans_id,aktie_id,aktie_navn,aktie_pris,SUM(antal) AS 'antal', SUM(omkostning) AS 'omkostning' FROM(
                    SELECT 0 AS 'trans_id', id as 'aktie_id',aktier.navn AS 'aktie_navn' ,pris as 'aktie_pris',0 AS 'antal', 0 AS 'omkostning' 
                    FROM myDB.AKTIER

                    UNION

                    SELECT transaktioner.id AS 'trans_id', transaktioner.aktie_id, aktier.navn AS 'aktie_navn', aktier.pris as 'aktie_pris', transaktioner.antal,transaktioner.omkostning
                    FROM myDB.TRANSAKTIONER, myDB.AKTIER
                    WHERE
                    transaktioner.aktie_id = aktier.id AND transaktioner.user_id =".$userId.") t GROUP BY aktie_id";

        $result = $connection->query($sql);
        
        //Denne gemmer aktierne og transaktionerne og indsætter dem i et array.
        $i = 0;
        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
                    $alle_transaktioner[$i] = $row;
                    $i++;
            }
        }
        
        //Debug besked
        if($GLOBALS['debug']){            
                foreach($alle_transaktioner as $tranRow){
                    echo "<br>DEBUG: User transactions overview , row id:".$tranRow['trans_id'];                    
                }
            

        }
        
        return $alle_transaktioner;
        
    }

    //Denne er lavet til at lave en transaktion der fratager penge og aktie antal.
    function sellAktie($connection, $userid, $aktieId, $pris,$antal){
        //Denne tager prisen og trækker fra
        $omkostning = -$pris*$antal;
        //Denne tager antal og søger for at vi fjerner.
        $salgAntal      = -$antal;
        //står for transaktioner
        $transaktions   = getUserAktieOversigt($connection, $_SESSION['user_id']);
        //indsætter vores transaktion
        foreach($transaktions as $row){
            if($row['antal']>=$antal&&$row['aktie_id']==$aktieId){
                $sql ="INSERT INTO myDB.TRANSAKTIONER (id,`user_id`, `aktie_id`, `antal`, `omkostning`) VALUES (NULL,".$userid.", ".$aktieId.",".$salgAntal.",".$omkostning.");";
                $tbCreated = $connection->query($sql);
            }
        }
        //debug besked
        if($GLOBALS['debug']){
            echo "<br>DEBUG: SELL sql ".$sql;
            if ($tbCreated) {
                echo "<br>DEBUG:sell aktie successfully";
            } else {
                echo "<br>DEBUG:Error sell: " . $connection->error;
            }
        } 
    }

    //en funktion der køber aktien. Den indsætter bare en ny transaktion
    function kobAktie($connection, $userid, $aktieId, $pris, $antal){
        $omkostning = $pris*$antal;
        $Money = getFormue($connection,$_SESSION['navn'],$_SESSION['password']);
        if($omkostning<=$Money){
            $sql ="INSERT INTO myDB.TRANSAKTIONER (`user_id`, `aktie_id`, `antal`, `omkostning`) VALUES (".$userid.", ".$aktieId.", ".$antal.", ".$omkostning.");";
            $tbCreated = $connection->query($sql);
        }

        //debug beskeder
        if($GLOBALS['debug']){
            echo "<br>DEBUG: køb sql ".$sql;
            if ($tbCreated) {
                echo "<br>DEBUG:dummy transaktion inserted successfully";
            } else {
                echo "<br>DEBUG:Error insert in transaktioner: " . $connection->error;
            }
        } 
    }


?>