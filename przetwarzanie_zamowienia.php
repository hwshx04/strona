<?php
include 'db.php';  // Połączenie do bazy danych

// Pobranie danych z formularza
$imie_nazwisko = $_POST['imie_nazwisko'];
$perfumy_id = $_POST['perfumy_id'];
$pojemnosc = $_POST['pojemnosc'];

// Iteracja po perfumach i pojemnościach
for ($i = 0; $i < count($perfumy_id); $i++) {
    $id = $perfumy_id[$i];
    $ml = $pojemnosc[$i];

    // Dodaj zamówienie do bazy danych
    $sql = "INSERT INTO zamowienia (imie_nazwisko, perfumy_id, pojemnosc) VALUES ('$imie_nazwisko', '$id', '$ml')";
    if ($conn->query($sql) === TRUE) {
        echo "Zamówienie na perfumy zostało złożone!<br>";
    } else {
        echo "Błąd przy składaniu zamówienia: " . $conn->error;
    }
}

$conn->close();
?>
