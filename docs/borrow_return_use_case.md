# Tài liệu Ca sử dụng (Use Case) - Hệ thống Quản lý Thiết bị

Dưới đây là mô tả chi tiết các tác nhân và ca sử dụng trong hệ thống, bao gồm các mối quan hệ **Include** (Bao gồm) và **Extend** (Mở rộng).

## 1. Tác nhân (Actors)

- **👤 Người mượn (Borrower)**: Sinh viên/Giáo viên.
- **👮 Nhân viên (Staff)**: Quản lý kho.
- **⏰ Bộ lập lịch (Scheduler)**: Tiến trình tự động.

---

## 2. Quan hệ Ca sử dụng (Relationships)

### 🔗 Include (Bao gồm - Bắt buộc)

Là hành động con **bắt buộc phải thực hiện** khi thực hiện hành động cha.

1.  **Tạo yêu cầu mượn** `<<include>>` **Kiểm tra tồn kho**:
    - Khi người dùng tạo phiếu, hệ thống _luôn luôn_ phải kiểm tra xem thiết bị còn trong kho hay không.
2.  **Mượn nhanh** `<<include>>` **Xuất kho**:
    - Quy trình mượn nhanh của Staff _bao gồm luôn_ bước xuất kho (giao thiết bị ngay lập tức).

### ➕ Extend (Mở rộng - Có điều kiện)

Là hành động phụ **chỉ xảy ra khi có điều kiện cụ thể**.

1.  **Nộp cam kết** `<<extend>>` **Tạo yêu cầu mượn**:
    - _Điều kiện_: Thiết bị thuộc nhóm "Đắt tiền" (Category ID = 2).
    - Nếu mượn thiết bị thường -> Không cần nộp cam kết.
2.  **Tính phí bồi thường** `<<extend>>` **Nhận trả thiết bị**:
    - _Điều kiện_: Thiết bị bị "Hỏng" hoặc "Mất" khi trả.
    - Nếu trả thiết bị nguyên vẹn -> Không kích hoạt tính phí.

---

## 3. Danh sách Ca sử dụng chi tiết

### Nhóm chức năng: Người mượn (Borrower)

- **Xem danh sách thiết bị**: Tìm kiếm, xem trạng thái.
- **Tạo yêu cầu mượn/đặt trước**:
  - _Luồng chính_: Chọn thiết bị -> Nhập ngày trả -> Gửi yêu cầu.
  - _Luồng phụ (Extend)_: Nếu thiết bị đắt tiền -> Hệ thống yêu cầu upload file cam kết.

### Nhóm chức năng: Nhân viên (Staff)

- **Duyệt yêu cầu**: Chấp nhận cho mượn.
- **Xuất kho (Issue)**: Giao thiết bị, đổi trạng thái sang `borrowed`.
- **Mượn nhanh (Quick Borrow)**:
  - Staff chọn thiết bị -> Chọn người mượn -> Bấm "Mượn nhanh".
  - Hệ thống tự động tạo phiếu `approved` VÀ thực hiện `Issue` ngay lập tức.
- **Nhận trả thiết bị (Return)**:
  - _Luồng chính_: Kiểm tra thiết bị -> Xác nhận trả.
  - _Luồng phụ (Extend)_: Nếu phát hiện hỏng hóc -> Nhập tình trạng hỏng -> Hệ thống tính phí bồi thường.

### Nhóm chức năng: Tự động (Scheduler)

- **Tự động tạo phiếu**: Chuyển Reservation -> Borrow Slip khi đến giờ.
