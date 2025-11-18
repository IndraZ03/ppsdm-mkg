<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

try {
    $conn = getDBConnection();
    //
    // Get filter kategori from query parameter
    $kategori_filter = isset($_GET['kategori']) ? intval($_GET['kategori']) : 1; // Default: Pelatihan Teknis (1)
    
    // Build query with filter
    // Apply filter based on kategori_diklat
    // 1 = Pelatihan Teknis, 2 = Pelatihan Kepemimpinan, 3 = Pelatihan Dasar CPNS, 4 = Pelatihan Fungsional
    $sql = "SELECT id, Nama_diklat, JP, waktu_awal, waktu_akhir, panitia, kategori_diklat FROM jadwal_diklat WHERE kategori_diklat = " . intval($kategori_filter) . " ORDER BY id DESC";
    
    $result = $conn->query($sql);
    
    $data = array();
    $no = 1;
    
    // Array nama bulan dalam bahasa Indonesia
    $bulan = array(
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    );
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $waktu_awal = '-';
            $waktu_akhir = '-';
            
            if (!empty($row['waktu_awal']) && $row['waktu_awal'] != '0000-00-00 00:00:00') {
                $timestamp_awal = strtotime($row['waktu_awal']);
                if ($timestamp_awal !== false) {
                    $hari_awal = date('j', $timestamp_awal);
                    $bulan_awal = $bulan[(int)date('n', $timestamp_awal)];
                    $tahun_awal = date('Y', $timestamp_awal);
                    $waktu_awal = $hari_awal . ' ' . $bulan_awal . ' ' . $tahun_awal;
                }
            }
            
            if (!empty($row['waktu_akhir']) && $row['waktu_akhir'] != '0000-00-00 00:00:00') {
                $timestamp_akhir = strtotime($row['waktu_akhir']);
                if ($timestamp_akhir !== false) {
                    $hari_akhir = date('j', $timestamp_akhir);
                    $bulan_akhir = $bulan[(int)date('n', $timestamp_akhir)];
                    $tahun_akhir = date('Y', $timestamp_akhir);
                    $waktu_akhir = $hari_akhir . ' ' . $bulan_akhir . ' ' . $tahun_akhir;
                }
            }
            
            $waktu = $waktu_awal . ' - ' . $waktu_akhir;
            
            $data[] = array(
                'no' => $no++,
                'id' => intval($row['id']),
                'kegiatan' => $row['Nama_diklat'] ?? '-',
                'jp' => $row['JP'] ?? '-',
                'waktu' => $waktu,
                'panitia' => $row['panitia'] ?? '-'
            );
        }
    }
    
    $conn->close();
    echo json_encode(array('data' => $data));
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        'error' => true,
        'message' => $e->getMessage(),
        'data' => array()
    ));
}
?>

