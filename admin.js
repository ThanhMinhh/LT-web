$(document).ready(function() {
    let adminPassword = '';

    // Xác thực admin
    $('#auth-btn').on('click', function() {
        adminPassword = $('#admin-password').val();
        if (!adminPassword) {
            alert('Vui lòng nhập mật khẩu admin');
            return;
        }
        loadData();
    });

    // Hàm tải dữ liệu
    function loadData() {
        // Tải sản phẩm
        $.get(`api/products.php`, function(products) {
            $('#product-list').empty();
            products.forEach(p => {
                $('#product-list').append(`
                    <tr>
                        <td>${p.id}</td>
                        <td>${p.name}</td>
                        <td>${p.category}</td>
                        <td>${parseFloat(p.price).toLocaleString('vi-VN')}</td>
                        <td><img src="images/${p.image}" width="50"></td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-product" data-id="${p.id}">Sửa</button>
                            <button class="btn btn-danger btn-sm delete-product" data-id="${p.id}">Xóa</button>
                        </td>
                    </tr>
                `);
            });
        });

        // Tải đơn hàng
        $.get(`api/orders.php?password=${adminPassword}`, function(orders) {
            if (orders.error) {
                alert('Lỗi: ' + orders.error);
                return;
            }
            $('#order-list').empty();
            orders.forEach(o => {
                $('#order-list').append(`
                    <tr>
                        <td>${o.id}</td>
                        <td>${o.customerName}</td>
                        <td>${o.customerEmail}</td>
                        <td>${parseFloat(o.total).toLocaleString('vi-VN')}</td>
                        <td>${o.status}</td>
                        <td>
                            <select class="form-control status-select" data-id="${o.id}">
                                <option value="pending" ${o.status === 'pending' ? 'selected' : ''}>Chờ xử lý</option>
                                <option value="confirmed" ${o.status === 'confirmed' ? 'selected' : ''}>Đã xác nhận</option>
                                <option value="cancelled" ${o.status === 'cancelled' ? 'selected' : ''}>Đã hủy</option>
                            </select>
                        </td>
                    </tr>
                `);
            });
        });

        // Tải người dùng
        $.get(`api/users.php?password=${adminPassword}`, function(users) {
            if (users.error) {
                alert('Lỗi: ' + users.error);
                return;
            }
            $('#user-list').empty();
            users.forEach(u => {
                $('#user-list').append(`
                    <tr>
                        <td>${u.id}</td>
                        <td>${u.name}</td>
                        <td>${u.email}</td>
                        <td>${u.role}</td>
                    </tr>
                `);
            });
        });
    }

    // Thêm sản phẩm
    $('#add-product-form').on('submit', function(e) {
        e.preventDefault();
        const product = {
            name: $('#product-name').val().trim(),
            category: $('#product-category').val(),
            price: parseFloat($('#product-price').val()),
            image: $('#product-image').val().trim(),
            password: adminPassword
        };

        $.ajax({
            url: 'api/add-product.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(product),
            success: function(response) {
                if (response.error) {
                    alert('Lỗi: ' + response.error);
                } else {
                    alert(response.message);
                    $('#addProductModal').modal('hide');
                    $('#add-product-form')[0].reset();
                    loadData();
                }
            }
        });
    });

    // Sửa sản phẩm
    $(document).on('click', '.edit-product', function() {
        const id = $(this).data('id');
        $.get(`api/products.php?id=${id}`, function(product) {
            $('#edit-product-id').val(product.id);
            $('#edit-product-name').val(product.name);
            $('#edit-product-category').val(product.category);
            $('#edit-product-price').val(product.price);
            $('#edit-product-image').val(product.image);
            $('#editProductModal').modal('show');
        });
    });

    $('#edit-product-form').on('submit', function(e) {
        e.preventDefault();
        const product = {
            id: parseInt($('#edit-product-id').val()),
            name: $('#edit-product-name').val().trim(),
            category: $('#edit-product-category').val(),
            price: parseFloat($('#edit-product-price').val()),
            image: $('#edit-product-image').val().trim(),
            password: adminPassword
        };

        $.ajax({
            url: 'api/products.php',
            type: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(product),
            success: function(response) {
                if (response.error) {
                    alert('Lỗi: ' + response.error);
                } else {
                    alert(response.message);
                    $('#editProductModal').modal('hide');
                    loadData();
                }
            }
        });
    });

    // Xóa sản phẩm
    $(document).on('click', '.delete-product', function() {
        if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;
        const id = $(this).data('id');
        $.ajax({
            url: 'api/products.php',
            type: 'DELETE',
            contentType: 'application/json',
            data: JSON.stringify({ id: id, password: adminPassword }),
            success: function(response) {
                if (response.error) {
                    alert('Lỗi: ' + response.error);
                } else {
                    alert(response.message);
                    loadData();
                }
            }
        });
    });

    // Cập nhật trạng thái đơn hàng
    $(document).on('change', '.status-select', function() {
        const orderId = $(this).data('id');
        const status = $(this).val();
        $.ajax({
            url: 'api/orders.php',
            type: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify({ orderId: orderId, status: status, password: adminPassword }),
            success: function(response) {
                if (response.error) {
                    alert('Lỗi: ' + response.error);
                } else {
                    alert(response.message);
                    loadData();
                }
            }
        });
    });
});