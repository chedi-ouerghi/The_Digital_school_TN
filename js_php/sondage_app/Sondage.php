<?php
$servername="localhost";
$username="root";
$password="";
$dbname="projet_Sondage";

$connection = mysqli_connect($servername,$username,$password,$dbname);



if(!$connection){
    die("connection failed :" . mysqli_connect_error());
}

$user=$_POST['user'];
$pass=$_POST['pass'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["q1"])) {
        
        $selectedone = $_POST["q1"];

        
        if ($selectedone == "1o") {
            $q1 = "1o" ;
        } elseif ($selectedone == "1n") {
            $q1 = "1n" ;
        } elseif ($selectedone == "1s") {
            $q1 = "1s" ;
        }
    } 
    if (isset($_POST["q2"])) {
        
        $selectedone = $_POST["q2"];

        
        if ($selectedone == "2o") {
            $q2 = "2o" ;
        } elseif ($selectedone == "2n") {
            $q2 = "2n" ;
        } elseif ($selectedone == "2s") {
            $q2 = "2s" ;
        }
    } 
    if (isset($_POST["q3"])) {
        
        $selectedone = $_POST["q3"];

        
        if ($selectedone == "3o") {
            $q3 = "3o" ;
        } elseif ($selectedone == "3n") {
            $q3 = "3n" ;
        } elseif ($selectedone == "3s") {
            $q3 = "3s" ;
        }
    } 
}        



$sql="INSERT INTO users (user,passwordi) VALUES ('$user','$pass')";
$sqli="INSERT INTO reponse (NumQ, NumS, idParticipant, Rep) VALUES (1,1,'$user','$q1')";
$sqly="INSERT INTO reponse (NumQ, NumS, idParticipant, Rep) VALUES (2,1,'$user','$q2')";
$sqlu="INSERT INTO reponse (NumQ, NumS, idParticipant, Rep) VALUES (3,1,'$user','$q3')";


if($connection->query($sql)===TRUE){
    header("Location: statistiques.html");
exit();

}
else {
    echo "error adding Product :" . $connection->error;
}





 




      
      
      $sqla = "SELECT passwordi FROM users WHERE user = '$user'";
    $result = $connection->query($sqla);
    if ($result->num_rows > 0) {
       
        $row = $result->fetch_assoc();
        $motDePasseBD = $row["passwordi"];
        if (password_verify($pass, $motDePasseBD)) {
           
            echo "Authentification réussie";
        } else {
            
            echo "Erreur d'authentification : Mot de passe incorrect";
        }
    } else {
       
        echo "Erreur d'authentification : Adresse e-mail incorrecte";
    }
    mysqli_close($connection);










?>