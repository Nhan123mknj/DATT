# Biểu đồ Ca sử dụng (Use Case Diagram) - Quản lý Mượn Trả Thiết Bị

Biểu đồ dưới đây mô tả các tương tác chính giữa **Người mượn (Borrower)** và **Nhân viên (Staff)** trong hệ thống quản lý thiết bị.

```mermaid
usecaseDiagram
    actor "Người mượn (Borrower)" as B
    actor "Nhân viên (Staff)" as S
    actor "Hệ thống (System)" as Sys

    package "Quản lý Mượn/Trả" {
        usecase "Xem danh sách thiết bị" as UC1
        usecase "Tạo yêu cầu đặt trước (Reservation)" as UC2
        usecase "Tạo yêu cầu mượn (Borrow Request)" as UC3
        usecase "Xem lịch sử mượn" as UC4
        
        usecase "Duyệt yêu cầu mượn/đặt trước" as UC5
        usecase "Từ chối yêu cầu" as UC6
        usecase "Xuất kho (Issue Device)" as UC7
        usecase "Tạo phiếu mượn nhanh (Quick Borrow)" as UC8
        usecase "Nhận trả thiết bị (Return Device)" as UC9
        usecase "Hủy phiếu mượn/đặt trước" as UC10
        
        usecase "Tự động tạo phiếu từ đặt trước" as UC11
        usecase "Tự động hủy phiếu treo" as UC12
    }

    %% Borrower Actions
    B --> UC1
    B --> UC2
    B --> UC3
    B --> UC4
    B --> UC10 : "Hủy khi chưa duyệt"

    %% Staff Actions
    S --> UC5
    S --> UC6
    S --> UC7
    S --> UC8
    S --> UC9
    S --> UC10 : "Hủy bất kỳ lúc nào"

    %% System Actions
    Sys --> UC11
    Sys --> UC12

    %% Relationships
    UC2 ..> UC11 : "Trigger"
    UC11 ..> UC5 : "Auto-approve (Optional)"
    UC5 --> UC7 : "Pre-condition"
    UC8 --> UC7 : "Includes Issue"
    UC3 --> UC5 : "Requires Approval"
```

## Giải thích chi tiết

### 1. Người mượn (Borrower)
*   **Xem danh sách thiết bị**: Tìm kiếm và xem tình trạng thiết bị (Sẵn sàng, Đã hết).
*   **Tạo yêu cầu đặt trước**: Đặt lịch mượn cho tương lai.
*   **Tạo yêu cầu mượn**: Tạo phiếu mượn cho hiện tại (cần duyệt).
*   **Xem lịch sử**: Theo dõi trạng thái các phiếu mượn của mình.

### 2. Nhân viên (Staff)
*   **Duyệt/Từ chối**: Xử lý các yêu cầu mượn hoặc đặt trước từ người dùng.
*   **Xuất kho (Issue)**: Bước quan trọng nhất - Giao thiết bị vật lý cho người mượn và cập nhật trạng thái hệ thống thành `borrowed`.
*   **Mượn nhanh (Quick Borrow)**: Staff tự tạo phiếu và xuất kho luôn cho khách (bỏ qua bước duyệt).
*   **Nhận trả (Return)**: Kiểm tra thiết bị, ghi nhận tình trạng hỏng hóc (nếu có) và nhập kho (`available`).

### 3. Hệ thống (System)
*   **Tự động tạo phiếu**: Khi đến giờ đặt trước (`reserved_from`), hệ thống tự chuyển Reservation thành Borrow Slip.
*   **Tự động hủy**: Quét các phiếu đã duyệt nhưng quá hạn lấy (`abandoned`) để hủy và nhả thiết bị.
