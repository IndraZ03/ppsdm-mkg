<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

try {
    $conn = getDBConnection();
    
    // Get filter kategori from query parameter
    $kategori_filter = isset($_GET['kategori']) ? intval($_GET['kategori']) : 0; // 0 = all
    
    // Build query with filter
    if ($kategori_filter > 0) {
        $sql = "SELECT id, Nama_diklat, JP, waktu_awal, waktu_akhir, panitia, kategori_diklat FROM jadwal_diklat WHERE kategori_diklat = " . intval($kategori_filter) . " ORDER BY id DESC";
    } else {
        $sql = "SELECT id, Nama_diklat, JP, waktu_awal, waktu_akhir, panitia, kategori_diklat FROM jadwal_diklat ORDER BY id DESC";
    }
    
    $result = $conn->query($sql);
    
    $data = array();
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $data[] = array(
                'id' => intval($row['id']),
                'Nama_diklat' => $row['Nama_diklat'] ?? '-',
                'JP' => $row['JP'] ?? '-',
                'waktu_awal' => $row['waktu_awal'] ?? '-',
                'waktu_akhir' => $row['waktu_akhir'] ?? '-',
                'panitia' => $row['panitia'] ?? '-',
                'kategori_diklat' => $row['kategori_diklat'] ?? 1
            );
        }
    }
    
    $conn->close();
    echo json_encode(array('success' => true, 'data' => $data));
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage(),
        'data' => array()
    ));
}
?>

