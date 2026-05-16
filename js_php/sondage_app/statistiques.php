<?php


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projet_Sondage";

$conn = mysqli_connect($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {




    $sql = "SELECT date_lancement FROM datel ";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $dateLancement = $row["date_lancement"];


        $dateActuelle = date("Y-m-d");
        if ($dateActuelle < $dateLancement) {

            echo "Sondage non encore lancé";
        } else {

            echo "Sondage lancé, vous pouvez participer !";
        }
    } else {

        echo "Erreur : Thème non trouvé";
    }

}
$conn->close();

?>