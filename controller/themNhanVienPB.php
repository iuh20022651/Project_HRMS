<?php
session_start();
include_once ('connect.php');

// Sử dụng lớp Database để kết nối đến cơ sở dữ liệu
$dbs = new Database();
$db = $dbs->connect();

// Kiểm tra xem bảng nhan_vien_phong_ban có dữ liệu hay không
$sql_check_data = "SELECT COUNT(*) AS total FROM nhan_vien_phong_ban";
$result_check_data = $db->query($sql_check_data);
$row_check_data = $result_check_data->fetch_assoc();
$total_records = $row_check_data['total'];

if ($total_records == 0) {
    // Nếu không có dữ liệu trong bảng, thiết lập lại số tự động tăng cho bảng nhan_vien_phong_ban
    $dbs->resetAutoIncrement("nhan_vien_phong_ban");
}

if (isset($_POST['submit'])) {
    // Kiểm tra xem tất cả các trường dữ liệu cần thiết đã được gửi hay không
    if (isset($_POST['maPhongBan']) && isset($_POST['users'])) {
        // Lấy dữ liệu từ form
        $maPhongBan = $_POST['maPhongBan'];
        $maNhanVien = $_POST['users'];
        // Kiểm tra xem phòng ban có tồn tại hay không
        $check_phongban_sql = $db->prepare("SELECT * FROM phong_ban WHERE maPhongBan = ?");
        $check_phongban_sql->bind_param("i", $maPhongBan);
        $check_phongban_sql->execute();
        $check_phongban_result = $check_phongban_sql->get_result();
        if ($check_phongban_result->num_rows == 0) {
            $message = "Phòng ban không tồn tại!";
            echo "<script>alert('$message'); window.history.back();</script>";
            exit();
        }

        // Kiểm tra xem nhân viên có tồn tại hay không
        $check_nhanvien_sql = $db->prepare("SELECT * FROM nhan_vien WHERE maNhanVien = ?");
        $check_nhanvien_sql->bind_param("i", $maNhanVien);
        $check_nhanvien_sql->execute();
        $check_nhanvien_result = $check_nhanvien_sql->get_result();
        if ($check_nhanvien_result->num_rows == 0) {
            $message = "Nhân viên không tồn tại!";
            echo "<script>alert('$message'); window.history.back();</script>";
            exit();
        }

        // Kiểm tra xem nhân viên đã được thêm vào phòng ban chưa
        $check_sql = $db->prepare("SELECT * FROM nhan_vien_phong_ban WHERE maPhongBan = ? AND maNhanVien = ?");
        $check_sql->bind_param("ii", $maPhongBan, $maNhanVien);
        $check_sql->execute();
        $check_result = $check_sql->get_result();
        if ($check_result->num_rows == 0) {
            // Thêm nhân viên vào phòng ban
            $insert_sql = $db->prepare("INSERT INTO nhan_vien_phong_ban (maPhongBan, maNhanVien) VALUES (?, ?)");
            $insert_sql->bind_param("ii", $maPhongBan, $maNhanVien);
            if ($insert_sql->execute()) {
                $message = "Thêm nhân viên vào phòng ban thành công!";
            } else {
                $message = "Lỗi: " . $db->error;
            }
        } else {
            $message = "Nhân viên đã tồn tại trong phòng ban!";
        }

    } else {
        // Nếu không đủ dữ liệu, in ra thông báo lỗi
        $message = "Vui lòng nhập đầy đủ thông tin!";
    }

    $username = $_SESSION['quanly_user']['username'];
    // Thiết lập URL chuyển hướng
    $redirect_url = "../user/quanly/home.php?user=quanly&username=$username&table=department";
    // Hiển thị thông báo và chuyển hướng sau khi thêm phòng ban
    echo "<script>alert('$message'); window.location.href = '$redirect_url';</script>";
    exit(); // Đảm bảo không có mã HTML hoặc lệnh PHP nào được thực hiện sau khi chuyển hướng
}
?>