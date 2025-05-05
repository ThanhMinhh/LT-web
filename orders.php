<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $customerName = trim($data['customerName'] ?? '');
    $customerEmail = trim($data['customerEmail'] ?? '');
    $items = $data['items'] ?? [];
    $total = floatval($data['total'] ?? 0);

    if (empty($customerName) || empty($customerEmail) || empty($items) || $total <= 0) {
        echo json_encode(["error" => "Dữ liệu không hợp lệ"]);
        exit;
    }

    $orders = json_decode(file_get_contents('../orders.json'), true) ?: [];
    $orderId = count($orders) + 1;

    $orders[] = [
        "id" => $orderId,
        "customerName" => $customerName,
        "customerEmail" => $customerEmail,
        "items" => $items,
        "total" => $total,
        "status" => "pending",
        "createdAt" => date('Y-m-d H:i:s')
    ];

    if (file_put_contents('../orders.json', json_encode($orders, JSON_PRETTY_PRINT))) {
        // Xóa giỏ hàng sau khi đặt hàng
        file_put_contents('../cart.json', json_encode([]));
        echo json_encode(["message" => "Đặt hàng thành công", "orderId" => $orderId]);
    } else {
        echo json_encode(["error" => "Không thể lưu đơn hàng"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Lấy danh sách đơn hàng
    $orders = json_decode(file_get_contents('../orders.json'), true) ?: [];
    echo json_encode($orders);
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Cập nhật trạng thái đơn hàng
    $data = json_decode(file_get_contents('php://input'), true);
    $orderId = $data['orderId'] ?? null;
    $status = $data['status'] ?? '';

    if (!$orderId || !in_array($status, ['pending', 'confirmed', 'cancelled'])) {
        echo json_encode(["error" => "Dữ liệu không hợp lệ"]);
        exit;
    }

    $orders = json_decode(file_get_contents('../orders.json'), true) ?: [];
    $index = array_search($orderId, array_column($orders, 'id'));

    if ($index !== false) {
        $orders[$index]['status'] = $status;
        if (file_put_contents('../orders.json', json_encode($orders, JSON_PRETTY_PRINT))) {
            echo json_encode(["message" => "Cập nhật trạng thái thành công"]);
        } else {
            echo json_encode(["error" => "Không thể cập nhật đơn hàng"]);
        }
    } else {
        echo json_encode(["error" => "Đơn hàng không tồn tại"]);
    }
}
?>