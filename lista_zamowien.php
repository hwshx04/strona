<?php
include 'db.php';  // Połączenie do bazy danych

// Zapytanie SQL do pobrania zamówień zgrupowanych według osoby, perfum i rodzaju (damskie/męskie)
$sql = "
    SELECT
        z.imie_nazwisko,
        p.nazwa AS nazwa_perfum,
        p.rodzaj AS rodzaj_perfum,  /* Pobieramy rodzaj perfum (damskie/męskie) */
        z.pojemnosc,
        SUM(z.ilosc) AS total_ilosc
    FROM
        zamowienia z
    JOIN
        perfumy p ON z.perfumy_id = p.id
    GROUP BY
        z.imie_nazwisko, p.nazwa, p.rodzaj, z.pojemnosc
    ORDER BY
        z.imie_nazwisko, p.rodzaj, p.nazwa, z.pojemnosc
";

$result = $conn->query($sql);

// Tablica do przechowywania zamówień zgrupowanych według osoby
$zamowienia = [];

// Tablice do zbierania sum zamówionych perfum
$suma_damskie = [];
$suma_meskie = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $imie_nazwisko = $row['imie_nazwisko'];
        $nazwa_perfum = $row['nazwa_perfum'];
        $rodzaj_perfum = $row['rodzaj_perfum'];  // Pobieramy rodzaj perfum (damskie/męskie)
        $pojemnosc = $row['pojemnosc'];
        $total_ilosc = $row['total_ilosc'];

        // Obliczenie ceny w zależności od pojemności
        $cena = 0;
        if ($pojemnosc == '30') {
            $cena = 20;
        } elseif ($pojemnosc == '50') {
            $cena = 30;
        } elseif ($pojemnosc == '100') {
            $cena = 45;
        }

        // Grupowanie zamówień według osoby i rodzaju perfum (damskie/męskie)
        if (!isset($zamowienia[$imie_nazwisko])) {
            $zamowienia[$imie_nazwisko] = [
                'damskie' => [],  // Perfumy damskie
                'męskie' => [],   // Perfumy męskie
                'suma_ceny' => 0  // Suma dla danej osoby
            ];
        }

        // Dodanie perfum do odpowiedniego rodzaju (damskie/męskie)
        if ($rodzaj_perfum === 'damskie') {
            $zamowienia[$imie_nazwisko]['damskie'][] = [
                'nazwa_perfum' => $nazwa_perfum,
                'pojemnosc' => $pojemnosc,
                'total_ilosc' => $total_ilosc,
                'cena' => $cena * $total_ilosc
            ];

            // Dodanie perfum damskich do sumy
            if (!isset($suma_damskie[$nazwa_perfum][$pojemnosc])) {
                $suma_damskie[$nazwa_perfum][$pojemnosc] = 0;
            }
            $suma_damskie[$nazwa_perfum][$pojemnosc] += $total_ilosc;
        } elseif ($rodzaj_perfum === 'męskie') {
            $zamowienia[$imie_nazwisko]['męskie'][] = [
                'nazwa_perfum' => $nazwa_perfum,
                'pojemnosc' => $pojemnosc,
                'total_ilosc' => $total_ilosc,
                'cena' => $cena * $total_ilosc
            ];

            // Dodanie perfum męskich do sumy
            if (!isset($suma_meskie[$nazwa_perfum][$pojemnosc])) {
                $suma_meskie[$nazwa_perfum][$pojemnosc] = 0;
            }
            $suma_meskie[$nazwa_perfum][$pojemnosc] += $total_ilosc;
        }

        // Dodanie ceny do całkowitej sumy dla danej osoby
        $zamowienia[$imie_nazwisko]['suma_ceny'] += $cena * $total_ilosc;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Lista Zamówień</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
        }
        .zamowienie {
            background-color: #fff;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
        }
        .zamowienie h2 {
            margin-top: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .suma {
            font-weight: bold;
            text-align: right;
        }
    </style>
</head>
<body>
    <h1>Lista Zamówień</h1>

    <?php if (!empty($zamowienia)) : ?>
        <?php foreach ($zamowienia as $imie_nazwisko => $dane_osoby) : ?>
            <div class="zamowienie">
                <h2><?php echo htmlspecialchars($imie_nazwisko); ?></h2>

                <!-- Perfumy damskie -->
                <?php if (!empty($dane_osoby['damskie'])): ?>
                    <h3>Perfumy Damskie</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Nazwa Perfum</th>
                                <th>Pojemność (ml)</th>
                                <th>Ilość</th>
                                <th>Cena</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dane_osoby['damskie'] as $item) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['nazwa_perfum']); ?></td>
                                    <td><?php echo htmlspecialchars($item['pojemnosc']); ?></td>
                                    <td><?php echo htmlspecialchars($item['total_ilosc']); ?></td>
                                    <td><?php echo htmlspecialchars($item['cena']); ?> zł</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <!-- Perfumy męskie -->
                <?php if (!empty($dane_osoby['męskie'])): ?>
                    <h3>Perfumy Męskie</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Nazwa Perfum</th>
                                <th>Pojemność (ml)</th>
                                <th>Ilość</th>
                                <th>Cena</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dane_osoby['męskie'] as $item) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['nazwa_perfum']); ?></td>
                                    <td><?php echo htmlspecialchars($item['pojemnosc']); ?></td>
                                    <td><?php echo htmlspecialchars($item['total_ilosc']); ?></td>
                                    <td><?php echo htmlspecialchars($item['cena']); ?> zł</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <!-- Suma za zamówienie -->
                <p class="suma">Suma za zamówienie: <?php echo htmlspecialchars($dane_osoby['suma_ceny']); ?> zł</p>
            </div>
        <?php endforeach; ?>

        <!-- Podsumowanie perfum damskich i męskich -->
        <div class="zamowienie">
            <h2>Podsumowanie Perfum Damskich</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nazwa Perfum</th>
                        <th>Pojemność (ml)</th>
                        <th>Całkowita Ilość</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suma_damskie as $nazwa => $pojemnosci): ?>
                        <?php foreach ($pojemnosci as $pojemnosc => $ilosc): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($nazwa); ?></td>
                                <td><?php echo htmlspecialchars($pojemnosc); ?></td>
                                <td><?php echo htmlspecialchars($ilosc); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2>Podsumowanie Perfum Męskich</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nazwa Perfum</th>
                        <th>Pojemność (ml)</th>
                        <th>Całkowita Ilość</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suma_meskie as $nazwa => $pojemnosci): ?>
                        <?php foreach ($pojemnosci as $pojemnosc => $ilosc): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($nazwa); ?></td>
                                <td><?php echo htmlspecialchars($pojemnosc); ?></td>
                                <td><?php echo htmlspecialchars($ilosc); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else : ?>
        <p>Brak zamówień</p>
    <?php endif; ?>
</body>
</html>
