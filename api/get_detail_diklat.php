<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode(array('error' => true, 'message' => 'Invalid ID'));
    exit;
}

try {
    $conn = getDBConnection();
    
    $sql = "SELECT id, Nama_diklat, deskripsi, tujuan, panitia, jml_peserta, waktu_awal, waktu_akhir FROM jadwal_diklat WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Array nama bulan dalam bahasa Indonesia
        $bulan = array(
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        );
        
        // Format tanggal kegiatan
        $tanggal_kegiatan = '-';
        if (!empty($row['waktu_awal']) && $row['waktu_awal'] != '0000-00-00 00:00:00' && 
            !empty($row['waktu_akhir']) && $row['waktu_akhir'] != '0000-00-00 00:00:00') {
            
            $timestamp_awal = strtotime($row['waktu_awal']);
            $timestamp_akhir = strtotime($row['waktu_akhir']);
            
            if ($timestamp_awal !== false && $timestamp_akhir !== false) {
                $hari_awal = date('j', $timestamp_awal);
                $bulan_awal = $bulan[(int)date('n', $timestamp_awal)];
                $tahun_awal = date('Y', $timestamp_awal);
                
                $hari_akhir = date('j', $timestamp_akhir);
                $bulan_akhir = $bulan[(int)date('n', $timestamp_akhir)];
                $tahun_akhir = date('Y', $timestamp_akhir);
                
                
                if ($tahun_awal == $tahun_akhir) {
                
                    $tanggal_kegiatan = $hari_awal . ' ' . $bulan_awal . ' - ' . $hari_akhir . ' ' . $bulan_akhir . ' ' . $tahun_awal;
                } else {
                 
                    $tanggal_kegiatan = $hari_awal . ' ' . $bulan_awal . ' ' . $tahun_awal . ' - ' . $hari_akhir . ' ' . $bulan_akhir . ' ' . $tahun_akhir;
                }
            }
        }
        
        // Format deskripsi dengan <p>
        $deskripsi = $row['deskripsi'] ?? '-';
        $deskripsiFormatted = '';
        if ($deskripsi !== '-') {
            $deskripsiLines = explode("\n", $deskripsi);
            $deskripsiFormatted = '<p>' . implode('</p><p>', array_filter(array_map('trim', $deskripsiLines))) . '</p>';
        } else {
            $deskripsiFormatted = '<p>-</p>';
        }
        
        // Format panitia dengan <br>
        $panitia = $row['panitia'] ?? '-';
        $panitiaFormatted = '';
        if ($panitia !== '-') {
            $panitiaLines = explode("\n", $panitia);
            $panitiaFormatted = implode('<br>', array_filter(array_map('trim', $panitiaLines)));
        } else {
            $panitiaFormatted = '-';
        }
        
        echo json_encode(array(
            'success' => true,
            'data' => array(
                'kegiatan' => $row['Nama_diklat'] ?? '-',
                'deskripsi' => $deskripsiFormatted,
                'tujuan' => $row['tujuan'] ?? '-',
                'panitia' => $panitiaFormatted,
                'jml_peserta' => $row['jml_peserta'] ?? 0,
                'tanggal_kegiatan' => $tanggal_kegiatan
            )
        ));
    } else {
        echo json_encode(array('error' => true, 'message' => 'Data tidak ditemukan'));
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        'error' => true,
        'message' => $e->getMessage()
    ));
}
?>

