<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    if ($action === 'register') {
        // Đăng ký
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $name = trim($data['name'] ?? '');

        if (empty($email) || empty($password)) {
            echo json_encode(["error" => "Vui lòng điền đầy đủ thông tin"]);
            exit;
        }

        $filePath = '../users.json';
        if (!file_exists($filePath)) {
            file_put_contents($filePath, json_encode([])); // Tạo file nếu chưa tồn tại
        }

        $users = json_decode(file_get_contents($filePath), true);
        if ($users === null) {
            echo json_encode(["error" => "Không thể đọc dữ liệu người dùng"]);
            exit;
        }

        // Kiểm tra email đã tồn tại
        if (array_filter($users, function($user) use ($email) { 
            return $user['email'] === $email; 
        })) {
            echo json_encode(["error" => "Email đã tồn tại"]);
            exit;
        }

        // Thêm người dùng mới
        $newUser = [
            "id" => count($users) + 1,
            "name" => $name ?: null,
            "email" => $email,
            "password" => password_hash($password, PASSWORD_DEFAULT),
            "role" => "customer"
        ];
        $users[] = $newUser;

        // Ghi dữ liệu vào tệp JSON
        if (file_put_contents($filePath, json_encode($users, JSON_PRETTY_PRINT))) {
            echo json_encode(["message" => "Đăng ký thành công"]);
        } else {
            echo json_encode(["error" => "Không thể lưu dữ liệu, vui lòng thử lại"]);
        }
    } elseif ($action === 'login') {
        // Đăng nhập
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode(["error" => "Vui lòng điền đầy đủ thông tin"]);
            exit;
        }

        $users = json_decode(file_get_contents('../users.json'), true) ?: [];
        $user = array_filter($users, function($user) use ($email) { return $user['email'] === $email; });

        if ($user) {
            $user = reset($user);
            if (password_verify($password, $user['password'])) {
                echo json_encode(["message" => "Đăng nhập thành công", "user" => ["id" => $user['id'], "name" => $user['name'], "role" => $user['role']]]);
            } else {
                echo json_encode(["error" => "Mật khẩu không đúng"]);
            }
        } else {
            echo json_encode(["error" => "Email không tồn tại"]);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Lấy danh sách người dùng
    $users = json_decode(file_get_contents('../users.json'), true) ?: [];
    echo json_encode($users);
}
?>