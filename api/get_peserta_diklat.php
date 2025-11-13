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
    
    // Coba beberapa variasi query untuk menemukan kolom foreign key yang benar
    $queries = array(
        "SELECT no_urut, nama_peserta, NIP, peringkat, kelompok, unit_kerja, mentor, image, file, no_sertifikat FROM peserta_diklat WHERE id_diklat = " . intval($id_diklat) . " ORDER BY no_urut ASC",
        "SELECT no_urut, nama_peserta, NIP, peringkat, kelompok, unit_kerja, mentor, image, file, no_sertifikat FROM peserta_diklat WHERE id_jadwal_diklat = " . intval($id_diklat) . " ORDER BY no_urut ASC",
        "SELECT no_urut, nama_peserta, NIP, peringkat, kelompok, unit_kerja, mentor, image, file, no_sertifikat FROM peserta_diklat WHERE jadwal_diklat_id = " . intval($id_diklat) . " ORDER BY no_urut ASC",
        "SELECT no_urut, nama_peserta, NIP, peringkat, kelompok, unit_kerja, mentor, image, file, no_sertifikat FROM peserta_diklat WHERE id = " . intval($id_diklat) . " ORDER BY no_urut ASC"
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
    
    if ($result !== false && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $data[] = array(
                'no' => $no++,
                'no_urut' => isset($row['no_urut']) ? $row['no_urut'] : ($no - 1),
                'nama_peserta' => isset($row['nama_peserta']) ? $row['nama_peserta'] : '-',
                'NIP' => isset($row['NIP']) ? $row['NIP'] : '-',
                'peringkat' => isset($row['peringkat']) ? $row['peringkat'] : '-',
                'kelompok' => isset($row['kelompok']) ? $row['kelompok'] : '-',
                'unit_kerja' => isset($row['unit_kerja']) ? $row['unit_kerja'] : '-',
                'mentor' => isset($row['mentor']) ? $row['mentor'] : '-',
                'image' => isset($row['image']) ? $row['image'] : '-',
                'file' => isset($row['file']) ? $row['file'] : '-',
                'no_sertifikat' => isset($row['no_sertifikat']) ? $row['no_sertifikat'] : '-'
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

