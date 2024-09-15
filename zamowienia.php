<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formularz zamówienia perfum</title>
</head>
<body>
    <h1>Formularz zamówienia perfum</h1>

    <form action="zloz_zamowienie.php" method="POST">
        <label for="perfumy">Wybierz perfumy:</label>
        <select name="perfumy" id="perfumy" required>
            <?php
            // Pobierz wszystkie perfumy z bazy danych
            $sql = "SELECT * FROM perfumy";
            $result = $conn->query($sql);

            // Wyświetl każdą opcję perfum
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['nazwa'] . " (" . $row['rodzaj'] . ")</option>";
                }
            }
            ?>
        </select><br><br>

        <label for="pojemnosc">Wybierz pojemność:</label>
        <select name="pojemnosc" id="pojemnosc" required>
            <option value="30ml">30ml - 20zł</option>
            <option value="50ml">50ml - 30zł</option>
            <option value="100ml">100ml - 45zł</option>
        </select><br><br>

        <label for="ilosc">Ilość:</label>
        <input type="number" id="ilosc" name="ilosc" min="1" value="1" required><br><br>

        <input type="submit" value="Złóż zamówienie">
    </form>

</body>
</html>
