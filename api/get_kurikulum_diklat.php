<?php
// Start output buffering to prevent any unwanted output
ob_start();

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$id_diklat = isset($_GET['id_diklat']) ? intval($_GET['id_diklat']) : 0;

if ($id_diklat <= 0) {
    ob_clean();
    echo json_encode(array('error' => true, 'message' => 'Invalid ID Diklat', 'data' => array()));
    ob_end_flush();
    exit;
}

try {
    $conn = getDBConnection();
    
    // Query berdasarkan kolom yang ada di database
    // Kolom: no_peserta, kegiatan_peserta, nama_peserta, nip_peserta, kualifikasi, mata_diklat, metode_pembelajaran, jp, modul1-7, tgl, tgl1, ket, waktu, kelompok, agenda, sudah_ttd, file_sertifikat_pengajar
    $queries = array(
        "SELECT no_peserta, mata_diklat, nama_peserta, nip_peserta, kualifikasi, agenda, kelompok, tgl, tgl1, waktu, jp FROM kurikulum1 WHERE kegiatan_peserta = " . intval($id_diklat) . " ORDER BY no_peserta ASC",
        "SELECT no_peserta, mata_diklat, nama_peserta, nip_peserta, kualifikasi, agenda, kelompok, tgl, tgl1, waktu, jp FROM kurikulum1 WHERE id_diklat = " . intval($id_diklat) . " ORDER BY no_peserta ASC",
        "SELECT no_peserta, mata_diklat, nama_peserta, nip_peserta, kualifikasi, agenda, kelompok, tgl, tgl1, waktu, jp FROM kurikulum1 WHERE id_jadwal_diklat = " . intval($id_diklat) . " ORDER BY no_peserta ASC"
    );
    
    $result = false;
    $error_message = '';
    
    foreach ($queries as $query) {
        $result = $conn->query($query);
        if ($result !== false) {
            break;
        } else {
            $error_message = $conn->error;
        }
    }
    
    $data = array();
    $no = 1;
    
    // Array nama bulan dalam bahasa Indonesia
    $bulan = array(
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    );
    
    if ($result !== false && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Format Widyaiswara/Narasumber
            $widyaiswara = '';
            if (isset($row['nama_peserta']) && $row['nama_peserta']) {
                $widyaiswara = $row['nama_peserta'];
                if (isset($row['nip_peserta']) && $row['nip_peserta']) {
                    $widyaiswara .= ' / ' . $row['nip_peserta'];
                }
                if (isset($row['kualifikasi']) && $row['kualifikasi']) {
                    $widyaiswara .= ' / ' . $row['kualifikasi'];
                }
            } else {
                $widyaiswara = '-';
            }
            
            // Format Tgl Kegiatan
            $tgl_kegiatan = '-';
            if (isset($row['tgl']) && $row['tgl'] && $row['tgl'] != '0000-00-00') {
                $timestamp = strtotime($row['tgl']);
                if ($timestamp !== false) {
                    $hari = date('j', $timestamp);
                    $bulan_nama = $bulan[(int)date('n', $timestamp)];
                    $tahun = date('Y', $timestamp);
                    $tgl_kegiatan = $hari . ' ' . $bulan_nama . ' ' . $tahun;
                    
                    // Tambahkan tgl1 jika ada
                    if (isset($row['tgl1']) && $row['tgl1'] && $row['tgl1'] != '0000-00-00') {
                        $timestamp1 = strtotime($row['tgl1']);
                        if ($timestamp1 !== false) {
                            $hari1 = date('j', $timestamp1);
                            $bulan_nama1 = $bulan[(int)date('n', $timestamp1)];
                            $tahun1 = date('Y', $timestamp1);
                            $tgl_kegiatan = $hari . ' ' . $bulan_nama . ' ' . $tahun . ' - ' . $hari1 . ' ' . $bulan_nama1 . ' ' . $tahun1;
                        }
                    }
                    
                    // Tambahkan waktu jika ada
                    if (isset($row['waktu']) && $row['waktu']) {
                        $tgl_kegiatan .= ' (' . $row['waktu'] . ')';
                    }
                }
            }
            
            $data[] = array(
                'no' => $no++,
                'id' => isset($row['no_peserta']) ? intval($row['no_peserta']) : 0,
                'mata_diklat' => isset($row['mata_diklat']) ? $row['mata_diklat'] : '-',
                'widyaiswara' => $widyaiswara,
                'agenda' => isset($row['agenda']) ? $row['agenda'] : '-',
                'kelompok' => isset($row['kelompok']) ? $row['kelompok'] : '-',
                'tgl_kegiatan' => $tgl_kegiatan,
                'jp' => isset($row['jp']) ? $row['jp'] : '-'
            );
        }
    }
    
    $conn->close();
    
    ob_clean();
    echo json_encode(array('success' => true, 'data' => $data));
    ob_end_flush();
    
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(array(
        'error' => true,
        'message' => $e->getMessage(),
        'data' => array()
    ));
    ob_end_flush();
}
?>

