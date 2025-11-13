<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('success' => false, 'message' => 'Invalid request method'));
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? intval($input['id']) : 0;

if ($id <= 0) {
    echo json_encode(array('success' => false, 'message' => 'Invalid ID'));
    exit;
}

try {
    $conn = getDBConnection();
    
    // Prepare update query
    $sql = "UPDATE jadwal_diklat SET 
            Nama_diklat = ?, 
            deskripsi = ?, 
            tujuan = ?, 
            JP = ?, 
            panitia = ?, 
            jml_peserta = ?, 
            waktu_awal = ?, 
            waktu_akhir = ?, 
            kategori_diklat = ? 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    
    $Nama_diklat = isset($input['Nama_diklat']) ? $input['Nama_diklat'] : '';
    $deskripsi = isset($input['deskripsi']) ? $input['deskripsi'] : '';
    $tujuan = isset($input['tujuan']) ? $input['tujuan'] : '';
    $JP = isset($input['JP']) ? $input['JP'] : '';
    $panitia = isset($input['panitia']) ? $input['panitia'] : '';
    $jml_peserta = isset($input['jml_peserta']) ? intval($input['jml_peserta']) : 0;
    $waktu_awal = isset($input['waktu_awal']) && !empty($input['waktu_awal']) ? date('Y-m-d H:i:s', strtotime($input['waktu_awal'])) : null;
    $waktu_akhir = isset($input['waktu_akhir']) && !empty($input['waktu_akhir']) ? date('Y-m-d H:i:s', strtotime($input['waktu_akhir'])) : null;
    $kategori_diklat = isset($input['kategori_diklat']) ? intval($input['kategori_diklat']) : 1;
    
    $stmt->bind_param("sssssisiii", 
        $Nama_diklat, 
        $deskripsi, 
        $tujuan, 
        $JP, 
        $panitia, 
        $jml_peserta, 
        $waktu_awal, 
        $waktu_akhir, 
        $kategori_diklat, 
        $id
    );
    
    if ($stmt->execute()) {
        echo json_encode(array('success' => true, 'message' => 'Data berhasil diupdate'));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Gagal mengupdate data: ' . $stmt->error));
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => $e->getMessage()));
}
?>

