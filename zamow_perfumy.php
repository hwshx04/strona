<?php
include 'db.php';  // Połączenie do bazy danych

// Pobranie dostępnych perfum damskich i męskich
function fetchPerfumy($rodzaj) {
    global $conn;
    $sql = "SELECT id, nazwa FROM perfumy WHERE rodzaj = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $rodzaj);
    $stmt->execute();
    $result = $stmt->get_result();
    $perfumy = [];
    while ($row = $result->fetch_assoc()) {
        $perfumy[] = $row;
    }
    $stmt->close();
    return $perfumy;
}

$perfumy_damskie = fetchPerfumy('damskie');
$perfumy_meskie = fetchPerfumy('męskie');

// Inicjalizacja sesji do przechowywania koszyka
session_start();

if (!isset($_SESSION['koszyk'])) {
    $_SESSION['koszyk'] = [];
}

// Flaga do wyświetlania komunikatu
$zamowienie_zlozone = false;

// Obsługa dodawania perfum do koszyka
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_cart'])) {
    $perfumy_id = $_POST['perfumy_id'];
    $pojemnosc = $_POST['pojemnosc'];
    $ilosc = $_POST['ilosc'];

    // Dodajemy perfumy do koszyka (sesji)
    $_SESSION['koszyk'][] = [
        'perfumy_id' => $perfumy_id,
        'pojemnosc' => $pojemnosc,
        'ilosc' => $ilosc
    ];
}

// Obsługa wysyłania zamówienia
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_order'])) {
    $imie_nazwisko = $_POST['imie_nazwisko'];
    
    // Przygotowanie i wykonanie zapytań do bazy danych
    if (!empty($_SESSION['koszyk'])) {
        $stmt = $conn->prepare("INSERT INTO zamowienia (imie_nazwisko, perfumy_id, pojemnosc, ilosc) VALUES (?, ?, ?, ?)");
        foreach ($_SESSION['koszyk'] as $item) {
            $stmt->bind_param("siss", $imie_nazwisko, $item['perfumy_id'], $item['pojemnosc'], $item['ilosc']);
            $stmt->execute();
        }
        $stmt->close();
        // Opróżniamy koszyk po złożeniu zamówienia
        $_SESSION['koszyk'] = [];
        $zamowienie_zlozone = true;  // Flaga do wyświetlenia komunikatu
    }
}

// Przekonwertuj ID perfum na nazwę (dla wyświetlenia w koszyku)
function getPerfumName($perfumy_id, $rodzaj) {
    global $perfumy_damskie, $perfumy_meskie;
    $all_perfumy = array_merge($perfumy_damskie, $perfumy_meskie);
    foreach ($all_perfumy as $perfum) {
        if ($perfum['id'] == $perfumy_id) {
            return $perfum['nazwa'];
        }
    }
    return 'Nieznane perfumy';
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zamów Perfumy</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        select, input[type="text"], input[type="number"], input[type="submit"], button {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px;
        }
        select {
            padding: 10px;
        }
        .koszyk {
            margin-top: 20px;
        }
        .koszyk table {
            width: 100%;
            border-collapse: collapse;
        }
        .koszyk th, .koszyk td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .koszyk th {
            background-color: #f2f2f2;
        }
        .zamowienie-zlozone {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.5);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }
        .zamowienie-zlozone p {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>

<?php if ($zamowienie_zlozone): ?>
    <div class="zamowienie-zlozone" id="zamowienie-zlozone" onclick="hideMessage()">
        <p>Dziękujemy! Twoje zamówienie zostało złożone!</p>
    </div>
<?php endif; ?>

    <h1>Zamów Perfumy</h1>

    <!-- Formularz dodawania perfum do koszyka -->
    <form method="post" action="">
        <input type="hidden" name="add_to_cart" value="1">
        <label for="rodzaj">Wybierz rodzaj perfum:</label>
        <select id="rodzaj" name="rodzaj" onchange="filterPerfumy()">
            <option value="damskie">Damskie</option>
            <option value="męskie">Męskie</option>
        </select>

        <div class="perfumy-item">
            <label>Wybierz perfumy:</label>
            <select name="perfumy_id" class="perfumy_select" required>
                <!-- Opcje perfum zostaną dodane dynamicznie przez JavaScript -->
            </select>

            <label>Wybierz pojemność (ml):</label>
            <select name="pojemnosc" required>
                <option value="30">30 ml</option>
                <option value="50">50 ml</option>
                <option value="100">100 ml</option>
            </select>

            <label>Wybierz ilość:</label>
            <input type="number" name="ilosc" min="1" value="1" required>
        </div>

        <button type="submit">Dodaj do koszyka</button>
    </form>

    <!-- Koszyk -->
    <div class="koszyk">
        <h2>Twój koszyk</h2>
        <table>
            <thead>
                <tr>
                    <th>Nazwa</th>
                    <th>Pojemność (ml)</th>
                    <th>Ilość</th>
                    <th>Usuń</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($_SESSION['koszyk'])): ?>
                    <?php foreach ($_SESSION['koszyk'] as $index => $item): ?>
                        <tr>
                            <td><?php echo getPerfumName($item['perfumy_id'], $_POST['rodzaj']); ?></td>
                            <td><?php echo $item['pojemnosc']; ?> ml</td>
                            <td><?php echo $item['ilosc']; ?></td>
                            <td><a href="?remove=<?php echo $index; ?>">Usuń</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Koszyk jest pusty.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Formularz wysyłania zamówienia -->
    <?php if (!empty($_SESSION['koszyk'])): ?>
    <form method="post" action="">
        <input type="hidden" name="submit_order" value="1">
        <label for="imie_nazwisko">Imię i Nazwisko:</label>
        <input type="text" id="imie_nazwisko" name="imie_nazwisko" required>

        <input type="submit" value="Złóż zamówienie">
    </form>
    <?php endif; ?>

    <script>
        var perfumyDamskie = <?php echo json_encode($perfumy_damskie); ?>;
        var perfumyMeskie = <?php echo json_encode($perfumy_meskie); ?>;

        function filterPerfumy() {
            var rodzaj = document.getElementById("rodzaj").value;
            var perfumySelect = document.querySelector('.perfumy_select');
            var perfumy = rodzaj === 'damskie' ? perfumyDamskie : perfumyMeskie;

            perfumySelect.innerHTML = "";
            perfumy.forEach(function(perfum) {
                var option = document.createElement("option");
                option.value = perfum.id;
                option.textContent = perfum.nazwa;
                perfumySelect.appendChild(option);
            });
        }

        // Ukryj komunikat po kliknięciu lub po 10 sekundach
        function hideMessage() {
            var messageDiv = document.getElementById('zamowienie-zlozone');
            if (messageDiv) {
                messageDiv.style.display = 'none';
            }
        }

        // Automatyczne ukrycie po 10 sekundach
        setTimeout(hideMessage, 10000);

        // Initialize the perfumy list
        filterPerfumy();
    </script>
</body>
</html>
