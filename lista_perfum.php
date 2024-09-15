<?php
include 'db.php';

// Zapytanie SQL do pobrania wszystkich perfum
$sql = "SELECT * FROM perfumy";
$result = $conn->query($sql);
$sql_damskie = "SELECT * FROM perfumy WHERE rodzaj = 'damskie'";
$sql_meskie = "SELECT * FROM perfumy WHERE rodzaj = 'męskie'";

$result_damskie = $conn->query($sql_damskie);
$result_meskie = $conn->query($sql_meskie);


echo "<center><h1>Lista perfum</h1></center>";
echo "<center><h2>30ml - 20zł <br> 50ml - 30zł <br> 100ml - 45zł</h2></center>";

///if ($result->num_rows > 0) {
///    echo "<ol>";
///    while ($row = $result->fetch_assoc()) {
///        echo "<li>" . $row["nazwa"] . " (" . $row["rodzaj"] . ") - " . "30ml 20zł | 50ml 30zł | 100ml 45zł</li>";
///    }
///    echo "</ol>";
///} else {
///    echo "Brak perfum w bazie.";
///}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista perfum</title>
    <style>
        body {
            margin: 0; /* Usuń domyślne marginesy */
            background-color: #f4f4f4; /* Opcjonalne tło */
        }
        table {
            width: auto; /* Ustaw szerokość tabeli */
            border-collapse: collapse;
            margin: 0 auto; /* Wyśrodkowanie tabeli poziomo */
            background-color: #fff; /* Opcjonalne tło tabeli */
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Opcjonalny cień */
        }
        th, td {
            padding: 10px;
            text-align: left; /* Wyśrodkowanie tekstu wewnątrz komórek */
            border: 1px solid #ddd;
            vertical-align: top; /* Wyśrodkowanie tekstu w komórkach od góry */
        }
        th {
            background-color: #f2f2f2;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <th>Damskie Perfumy</th>
            <th>Męskie Perfumy</th>
        </tr>
        <tr>
            <td>
                <ol>
                    <?php
                    if ($result_damskie->num_rows > 0) {
                        // Wyświetlanie perfum damskich
                        while ($row = $result_damskie->fetch_assoc()) {
                            echo "<li><strong>" . $row["nazwa"] . "</strong>"."</li><br>";
                        }
                    } else {
                        echo "Brak perfum damskich.";
                    }
                    ?>
                </ol>
            </td>
            <td>
                <ol>
                    <?php
                    if ($result_meskie->num_rows > 0) {
                        // Wyświetlanie perfum męskich
                        while ($row = $result_meskie->fetch_assoc()) {
                            echo "<li><strong>" . $row["nazwa"] . "</strong>"."</li><br>";
                        }
                    } else {
                        echo "Brak perfum męskich.";
                    }
                    ?>
                </ol>
            </td>
        </tr>
    </table>

</body>
</html>

<?php
?>