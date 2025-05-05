<?php
header('Content-Type: application/json');

// Đọc file products.json
$jsonData = file_get_contents('../products.json');
$products = json_decode($jsonData, true);

if ($products === null) {
    echo json_encode(["error" => "Không thể đọc dữ liệu sản phẩm"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Lấy chi tiết sản phẩm
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $product = array_filter($products, function($p) use ($id) { return $p['id'] == $id; });
        if ($product) {
            echo json_encode(reset($product));
        } else {
            echo json_encode(["error" => "Sản phẩm không tồn tại"]);
        }
        exit;
    }

    // Lấy danh sách sản phẩm
    $search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';
    $categories = isset($_GET['categories']) ? explode(',', $_GET['categories']) : [];
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'price-asc';

    if (!empty($search)) {
        $products = array_filter($products, function($product) use ($search) {
            return strpos(strtolower($product['name']), $search) !== false;
        });
    }

    if (!empty($categories)) {
        $products = array_filter($products, function($product) use ($categories) {
            return in_array($product['category'], $categories);
        });
    }

    usort($products, function($a, $b) use ($sort) {
        if ($sort === 'price-desc') {
            return $b['price'] - $a['price'];
        } elseif ($sort === 'name-asc') {
            return strcmp($a['name'], $b['name']);
        } else {
            return $a['price'] - $b['price'];
        }
    });

    echo json_encode(array_values($products));
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Thêm sản phẩm
    $data = json_decode(file_get_contents('php://input'), true);
    $name = trim($data['name'] ?? '');
    $category = trim($data['category'] ?? '');
    $price = floatval($data['price'] ?? 0);
    $image = trim($data['image'] ?? '');

    if (empty($name) || empty($category) || $price <= 0 || empty($image)) {
        echo json_encode(["error" => "Dữ liệu không hợp lệ"]);
        exit;
    }

    $newProduct = [
        "id" => count($products) + 1,
        "name" => $name,
        "category" => $category,
        "price" => $price,
        "image" => $image
    ];

    $products[] = $newProduct;

    if (file_put_contents('../products.json', json_encode($products, JSON_PRETTY_PRINT))) {
        echo json_encode(["message" => "Thêm sản phẩm thành công", "product" => $newProduct]);
    } else {
        echo json_encode(["error" => "Không thể thêm sản phẩm"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Sửa sản phẩm
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? null);
    $name = trim($data['name'] ?? '');
    $category = trim($data['category'] ?? '');
    $price = floatval($data['price'] ?? 0);
    $image = trim($data['image'] ?? '');

    if (!$id || empty($name) || empty($category) || $price <= 0 || empty($image)) {
        echo json_encode(["error" => "Dữ liệu không hợp lệ"]);
        exit;
    }

    $index = array_search($id, array_column($products, 'id'));
    if ($index !== false) {
        $products[$index] = [
            "id" => $id,
            "name" => $name,
            "category" => $category,
            "price" => $price,
            "image" => $image
        ];
        if (file_put_contents('../products.json', json_encode($products, JSON_PRETTY_PRINT))) {
            echo json_encode(["message" => "Cập nhật sản phẩm thành công"]);
        } else {
            echo json_encode(["error" => "Không thể cập nhật sản phẩm"]);
        }
    } else {
        echo json_encode(["error" => "Sản phẩm không tồn tại"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Xóa sản phẩm
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? null);

    if (!$id) {
        echo json_encode(["error" => "Thiếu ID sản phẩm"]);
        exit;
    }

    $products = array_filter($products, function($product) use ($id) {
        return $product['id'] != $id;
    });
    $products = array_values($products); // Re-index the array after filtering

    if (file_put_contents('../products.json', json_encode($products, JSON_PRETTY_PRINT))) {
        echo json_encode(["message" => "Xóa sản phẩm thành công"]);
    } else {
        echo json_encode(["error" => "Không thể xóa sản phẩm"]);
    }
}
?>