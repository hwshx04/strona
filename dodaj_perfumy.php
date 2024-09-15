<?php
// Dołącz plik połączenia z bazą danych
include 'db.php';

// Pobierz dane z formularza i zabezpiecz je przed znakami specjalnymi
$nazwa = mysqli_real_escape_string($conn, $_POST['nazwa']);
$rodzaj = mysqli_real_escape_string($conn, $_POST['rodzaj']);

// Przygotuj zapytanie SQL do dodania danych do bazy
$sql = "INSERT INTO perfumy (nazwa, rodzaj) VALUES ('$nazwa', '$rodzaj')";

// Wykonaj zapytanie i sprawdź, czy udało się dodać dane
if ($conn->query($sql) === TRUE) {
    echo "Nowe perfumy zostały dodane!";
} else {
    echo "Błąd: " . $sql . "<br>" . $conn->error;
}

// Zamknij połączenie z bazą
$conn->close();
?>
