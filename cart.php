<?php
header('Content-Type: application/json');

// Lấy giỏ hàng
function getCart() {
    $cart = json_decode(file_get_contents('../cart.json'), true) ?: [];
    return $cart;
}

// Lưu giỏ hàng
function saveCart($cart) {
    return file_put_contents('../cart.json', json_encode($cart, JSON_PRETTY_PRINT));
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Lấy danh sách giỏ hàng
    echo json_encode(getCart());
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Thêm sản phẩm vào giỏ
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = $data['productId'] ?? null;
    $quantity = intval($data['quantity'] ?? 1);

    if (!$productId || $quantity <= 0) {
        echo json_encode(["error" => "Dữ liệu không hợp lệ"]);
        exit;
    }

    $cart = getCart();
    $existing = array_search($productId, array_column($cart, 'productId'));

    if ($existing !== false) {
        $cart[$existing]['quantity'] += $quantity;
    } else {
        $cart[] = ["productId" => $productId, "quantity" => $quantity];
    }

    if (saveCart($cart)) {
        echo json_encode(["message" => "Đã thêm vào giỏ hàng", "cart" => $cart]);
    } else {
        echo json_encode(["error" => "Không thể lưu giỏ hàng"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Cập nhật số lượng
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = $data['productId'] ?? null;
    $quantity = intval($data['quantity'] ?? 0);

    if (!$productId || $quantity < 0) {
        echo json_encode(["error" => "Dữ liệu không hợp lệ"]);
        exit;
    }

    $cart = getCart();
    $existing = array_search($productId, array_column($cart, 'productId'));

    if ($existing !== false) {
        if ($quantity == 0) {
            unset($cart[$existing]);
            $cart = array_values($cart);
        } else {
            $cart[$existing]['quantity'] = $quantity;
        }
        if (saveCart($cart)) {
            echo json_encode(["message" => "Đã cập nhật giỏ hàng", "cart" => $cart]);
        } else {
            echo json_encode(["error" => "Không thể lưu giỏ hàng"]);
        }
    } else {
        echo json_encode(["error" => "Sản phẩm không có trong giỏ"]);
    }
} else {
    echo json_encode(["error" => "Yêu cầu không hợp lệ"]);
}
?>