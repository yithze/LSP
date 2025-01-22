<?php
session_start();

// Proses menyimpan data ke file JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil soal CAPTCHA dari form yang sudah di-submit
    $userCaptcha = intval($_POST['captcha']);
    
    // Validasi CAPTCHA dengan strict comparison
    if (isset($_POST['captcha']) && $userCaptcha === $_SESSION['captcha']) {
        $dataFile = 'data.json';

        // Data dari form
        $data = [
            'nama' => $_POST['nama'],
            'tanggal' => $_POST['tanggal'],
            'kelas' => $_POST['kelas'],
            'jurusan' => $_POST['jurusan'],
            'total' => $_POST['total'],
            'keterangan' => $_POST['keterangan'],
        ];

        // Membaca file JSON jika sudah ada, jika tidak, buat array kosong
        $existingData = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];

        // Menambahkan data baru ke array
        $existingData[] = $data;

        // Menyimpan kembali ke file JSON
        file_put_contents($dataFile, json_encode($existingData, JSON_PRETTY_PRINT));
        $_SESSION['successMessage'] = "Data berhasil disimpan!";
        
        // Reset CAPTCHA session setelah berhasil
        unset($_SESSION['captcha']);
        
        // Redirect ke halaman yang sama menggunakan GET untuk menghindari resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['errorMessage'] = "CAPTCHA salah! Silakan coba lagi.";
        unset($_SESSION['captcha']);
    }
}

// Membaca data dari file JSON
$dataTable = file_exists('data.json') ? json_decode(file_get_contents('data.json'), true) : [];

// Generate CAPTCHA baru (soal)
$operators = ['+', '-', '*'];  // Menghapus operator '/' untuk menghindari hasil desimal
$number1 = rand(1, 5);
$number2 = rand(1, 5);
$operator = $operators[array_rand($operators)];

// Hitung hasil operasi untuk validasi CAPTCHA
switch ($operator) {
    case '+':
        $_SESSION['captcha'] = $number1 + $number2;
        break;
    case '-':
        // Pastikan hasil tidak negatif
        if ($number1 < $number2) {
            list($number1, $number2) = array($number2, $number1);
        }
        $_SESSION['captcha'] = $number1 - $number2;
        break;
    case '*':
        $_SESSION['captcha'] = $number1 * $number2;
        break;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Kuitansi dengan CAPTCHA</title>
    <link href="./style.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <!-- Form Container -->
        <div class="form-container">
            <h1>Form Kuitansi</h1>
            <?php if (isset($_SESSION['successMessage'])) : ?>
                <div class="message success" id="successMessage"><?= htmlspecialchars($_SESSION['successMessage']) ?></div>
                <?php unset($_SESSION['successMessage']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['errorMessage'])) : ?>
                <div class="message error" id="errorMessage"><?= htmlspecialchars($_SESSION['errorMessage']) ?></div>
                <?php unset($_SESSION['errorMessage']); ?>
            <?php endif; ?>
            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="tanggal">Tanggal Bayar</label>
                    <input type="date" id="tanggal" name="tanggal" value="<?= isset($_POST['tanggal']) ? htmlspecialchars($_POST['tanggal']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="kelas">Kelas</label>
                    <select id="kelas" name="kelas" required>
                        <option value="">Pilih Kelas</option>
                        <option value="X" <?= isset($_POST['kelas']) && $_POST['kelas'] == 'X' ? 'selected' : '' ?>>X</option>
                        <option value="XI" <?= isset($_POST['kelas']) && $_POST['kelas'] == 'XI' ? 'selected' : '' ?>>XI</option>
                        <option value="XII" <?= isset($_POST['kelas']) && $_POST['kelas'] == 'XII' ? 'selected' : '' ?>>XII</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="jurusan">Jurusan</label>
                    <select id="jurusan" name="jurusan" required>
                        <option value="">Pilih Jurusan</option>
                        <option value="Multimedia" <?= isset($_POST['jurusan']) && $_POST['jurusan'] == 'Multimedia' ? 'selected' : '' ?>>Multimedia</option>
                        <option value="Rekayasa Perangkat Lunak" <?= isset($_POST['jurusan']) && $_POST['jurusan'] == 'Rekayasa Perangkat Lunak' ? 'selected' : '' ?>>Rekayasa Perangkat Lunak</option>
                        <option value="Otomatisasi Tata Kelola Perkantoran" <?= isset($_POST['jurusan']) && $_POST['jurusan'] == 'Otomatisasi Tata Kelola Perkantoran' ? 'selected' : '' ?>>Otomatisasi Tata Kelola Perkantoran</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="total">Total Bayar (Rp)</label>
                    <input type="number" id="total" name="total" value="<?= isset($_POST['total']) ? htmlspecialchars($_POST['total']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <input type="text" id="keterangan" name="keterangan" value="<?= isset($_POST['keterangan']) ? htmlspecialchars($_POST['keterangan']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <div class="captcha">
                        <p class="captcha-q" for="captcha">Captcha: <?= $number1 ?> <?= $operator ?> <?= $number2 ?>?</p>
                        <p class="captcha-refresh">Reload</p>
                        <!-- <label class="captcha-q" for="captcha">Berapa hasil dari <?= $number1 ?> <?= $operator ?> <?= $number2 ?>?</label> -->
                    </div>
                    <input placeholder="?..." type="number" id="captcha" name="captcha" required>
                </div>
                <div class="form-group">
                    <button type="submit">Submit</button>
                </div>
            </form>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <h1>Data Kuitansi</h1>
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Tanggal</th>
                        <th>Kelas</th>
                        <th>Jurusan</th>
                        <th>Total</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($dataTable)) : ?>
                        <?php foreach ($dataTable as $row) : ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td><?= htmlspecialchars($row['tanggal']) ?></td>
                                <td><?= htmlspecialchars($row['kelas']) ?></td>
                                <td><?= htmlspecialchars($row['jurusan']) ?></td>
                                <td>Rp <?= number_format(htmlspecialchars($row['total']), 0, ',', '.') ?></td>
                                <td><?= htmlspecialchars($row['keterangan']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Belum ada data.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Menghapus pesan setelah 5 detik
        setTimeout(function() {
            var successMessage = document.getElementById('successMessage');
            var errorMessage = document.getElementById('errorMessage');
            if (successMessage) {
                successMessage.style.display = 'none';
            }
            if (errorMessage) {
                errorMessage.style.display = 'none';
            }
        }, 5000);
    </script>
</body>
</html>

