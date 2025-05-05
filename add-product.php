<?php
header('Content-Type: application/json');

// Kiểm tra phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Yêu cầu không hợp lệ"]);
    exit;
}


// Lấy dữ liệu từ body
$data = json_decode(file_get_contents('php://input'), true);
$name = trim($data['name'] ?? '');
$category = trim($data['category'] ?? '');
$price = floatval($data['price'] ?? 0);
$image = trim($data['image'] ?? '');
$password = $data['password'] ?? '';


// Kiểm tra dữ liệu
if (empty($name) || empty($category) || $price <= 0 || empty($image)) {
    echo json_encode(["error" => "Vui lòng điền đầy đủ thông tin"]);
    exit;
}

// Đọc products.json
$jsonData = file_get_contents('../products.json');
$products = json_decode($jsonData, true);

if ($products === null) {
    echo json_encode(["error" => "Không thể đọc dữ liệu sản phẩm"]);
    exit;
}

// Tạo ID mới
$newId = max(array_column($products, 'id')) + 1;

// Thêm sản phẩm mới
$products[] = [
    "id" => $newId,
    "name" => $name,
    "category" => $category,
    "price" => $price,
    "image" => $image
];

// Lưu lại products.json
if (file_put_contents('../products.json', json_encode($products, JSON_PRETTY_PRINT))) {
    echo json_encode(["message" => "Thêm sản phẩm thành công", "product" => end($products)]);
} else {
    echo json_encode(["error" => "Không thể lưu sản phẩm"]);
}
?>