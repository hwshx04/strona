<?php
include 'db.php';  // Połączenie do bazy danych

// Pobranie danych z formularza
$imie_nazwisko = $_POST['imie_nazwisko'];
$perfumy_ids = $_POST['perfumy_id']; // Tablica ID perfum
$pojemnosci = $_POST['pojemnosc'];   // Tablica pojemności
$ilosci = $_POST['ilosc'];           // Tablica ilości

// Przygotowanie i wykonanie zapytań do bazy danych
$stmt = $conn->prepare("INSERT INTO zamowienia (imie_nazwisko, perfumy_id, pojemnosc, ilosc) VALUES (?, ?, ?, ?)");
foreach ($perfumy_ids as $index => $id) {
    $stmt->bind_param("siii", $imie_nazwisko, $id, $pojemnosci[$index], $ilosci[$index]);
    $stmt->execute();
}
$stmt->close();

echo "<p>Twoje zamówienie zostało złożone!</p>";
$conn->close();
?>
