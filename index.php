<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kalkulator Sinta</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans text-gray-800">

<div class="max-w-3xl mx-auto p-6">
    <h1 class="text-3xl font-bold text-center text-blue-700 mb-6">Kalkulator Sinta</h1>

    <!-- Kalkulator Aritmatika -->
    <div class="bg-white shadow-md rounded-lg p-5 mb-8">
        <h2 class="text-xl font-semibold mb-4">Kalkulator Sinta</h2>
        <form method="post" class="flex flex-col md:flex-row gap-3 items-center">
            <input type="number" step="any" name="a1" placeholder="Angka 1" required class="p-2 border rounded w-full md:w-1/4">
            <select name="operator" class="p-2 border rounded w-full md:w-1/4">
                <option value="+">+</option>
                <option value="-">−</option>
                <option value="*">×</option>
                <option value="/">÷</option>
            </select>
            <input type="number" step="any" name="a2" placeholder="Angka 2" required class="p-2 border rounded w-full md:w-1/4">
            <input type="submit" name="hitung_aritmatika" value="Hitung" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full md:w-auto">
        </form>

        <?php
        if (isset($_POST['hitung_aritmatika'])) {
            $a1 = $_POST['a1'];
            $a2 = $_POST['a2'];
            $op = $_POST['operator'];
            $hasil = 0;

            switch ($op) {
                case '+': $hasil = $a1 + $a2; break;
                case '-': $hasil = $a1 - $a2; break;
                case '*': $hasil = $a1 * $a2; break;
                case '/': $hasil = $a2 != 0 ? $a1 / $a2 : 0; break;
            }

            echo "<div class='mt-4 bg-green-100 border-l-4 border-green-500 p-4 rounded'><strong>Hasil:</strong> {$a1} {$op} {$a2} = <strong>{$hasil}</strong></div>";

            // Simpan ke database
            $stmt = $conn->prepare("INSERT INTO kalkulator (tipe, input1, input2, operator, hasil) VALUES ('aritmatika', ?, ?, ?, ?)");
            $stmt->bind_param("ddsd", $a1, $a2, $op, $hasil);
            $stmt->execute();
            $stmt->close();
        }
        ?>
    </div>

    <!-- Kalkulator Suhu -->
    <div class="bg-white shadow-md rounded-lg p-5 mb-8">
        <h2 class="text-xl font-semibold mb-4">Kalkulator Suhu</h2>
        <form method="post" class="flex flex-col md:flex-row gap-3 items-center">
            <input type="number" step="any" name="suhu_input" placeholder="Nilai suhu" required class="p-2 border rounded w-full md:w-1/3">
            <select name="satuan_dari" class="p-2 border rounded w-full md:w-1/3">
                <option value="C">Celcius</option>
                <option value="F">Fahrenheit</option>
                <option value="K">Kelvin</option>
                <option value="R">Reamur</option>
            </select>
            <select name="satuan_ke" class="p-2 border rounded w-full md:w-1/3">
                <option value="C">Celcius</option>
                <option value="F">Fahrenheit</option>
                <option value="K">Kelvin</option>
                <option value="R">Reamur</option>
            </select>
            <input type="submit" name="konversi_suhu" value="Konversi" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 w-full md:w-auto mt-3 md:mt-0">
        </form>

        <?php
        function convert_temp($value, $from, $to) {
            // semua yg diinput di angka awal akan di terjemahkan dahulu ke celcius
            switch ($from) {
                case 'F': $c = ($value - 32) * 5/9; break;
                case 'K': $c = $value - 273.15; break;
                case 'R': $c = $value * 5/4; break;
                default: $c = $value;
            }

            switch ($to) {
                // setelah itu dari celcius baru ke suhu yang dituju
                case 'F': return ($c * 9/5) + 32;
                case 'K': return $c + 273.15;
                case 'R': return $c * 4/5;
                default: return $c;
            }
        }

        if (isset($_POST['konversi_suhu'])) {
            $input = $_POST['suhu_input'];
            $dari = $_POST['satuan_dari'];
            $ke = $_POST['satuan_ke'];

            $hasil_suhu = round(convert_temp($input, $dari, $ke), 2);

            echo "<div class='mt-4 bg-blue-100 border-l-4 border-blue-500 p-4 rounded'><strong>Konversi:</strong> {$input}°{$dari} = <strong>{$hasil_suhu}°{$ke}</strong></div>";

            // Simpan ke database
            $stmt = $conn->prepare("INSERT INTO kalkulator (tipe, input1, satuan_asal, satuan_tujuan, hasil_suhu) VALUES ('suhu', ?, ?, ?, ?)");
            $stmt->bind_param("dssd", $input, $dari, $ke, $hasil_suhu);
            $stmt->execute();
            $stmt->close();
        }
        ?>
    </div>

    <!-- Riwayat -->
    <div class="bg-white shadow-md rounded-lg p-5">
        <h2 class="text-xl font-semibold mb-4">Riwayat Perhitungan Terakhir</h2>
        <div class="overflow-x-auto">
            <table class="table-auto w-full text-sm border">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-2 py-1 border">Waktu</th>
                        <th class="px-2 py-1 border">Tipe</th>
                        <th class="px-2 py-1 border">Input</th>
                        <th class="px-2 py-1 border">Operasi</th>
                        <th class="px-2 py-1 border">Hasil</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM kalkulator ORDER BY waktu DESC LIMIT 10");
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr class='hover:bg-gray-50'>";
                        echo "<td class='border px-2 py-1'>{$row['waktu']}</td>";
                        echo "<td class='border px-2 py-1'>{$row['tipe']}</td>";

                        if ($row['tipe'] == 'aritmatika') {
                            echo "<td class='border px-2 py-1'>{$row['input1']} {$row['operator']} {$row['input2']}</td>";
                            echo "<td class='border px-2 py-1'>{$row['operator']}</td>";
                            echo "<td class='border px-2 py-1'>{$row['hasil']}</td>";
                        } else {
                            echo "<td class='border px-2 py-1'>{$row['input1']}°{$row['satuan_asal']}</td>";
                            echo "<td class='border px-2 py-1'>{$row['satuan_asal']} → {$row['satuan_tujuan']}</td>";
                            echo "<td class='border px-2 py-1'>{$row['hasil_suhu']}°{$row['satuan_tujuan']}</td>";
                        }

                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
