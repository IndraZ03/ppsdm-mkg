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
    
    $sql = "DELETE FROM jadwal_diklat WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(array('success' => true, 'message' => 'Data berhasil dihapus'));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Gagal menghapus data'));
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => $e->getMessage()));
}
?>

