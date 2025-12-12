<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <title>Phiếu Trả Thiết Bị #{{ $borrow->id }}</title>
    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #4F46E5;
            padding-bottom: 15px;
        }

        .header h1 {
            margin: 0 0 10px 0;
            color: #4F46E5;
            font-size: 24px;
            font-weight: bold;
        }

        .header .subtitle {
            color: #666;
            font-size: 12px;
        }

        .info-section {
            margin-bottom: 20px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .info-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        .info-table td:first-child {
            background-color: #f9fafb;
            font-weight: bold;
            width: 25%;
        }

        .devices-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .devices-table th {
            background-color: #4F46E5;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
        }

        .devices-table td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 10px;
        }

        .devices-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .condition-excellent {
            color: #10b981;
            font-weight: bold;
        }

        .condition-good {
            color: #3b82f6;
            font-weight: bold;
        }

        .condition-fair {
            color: #f59e0b;
            font-weight: bold;
        }

        .condition-damaged {
            color: #ef4444;
            font-weight: bold;
        }

        .condition-broken {
            color: #991b1b;
            font-weight: bold;
        }

        .notes-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px;
            margin: 15px 0;
        }

        .notes-box strong {
            color: #92400e;
        }

        .signatures {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .signatures-container {
            display: table;
            width: 100%;
        }

        .signature-box {
            display: table-cell;
            text-align: center;
            width: 48%;
            vertical-align: top;
        }

        .signature-box:first-child {
            padding-right: 2%;
        }

        .signature-box:last-child {
            padding-left: 2%;
        }

        .signature-box h4 {
            margin: 0 0 10px 0;
            font-size: 12px;
            color: #374151;
        }

        .signature-box .signature-image {
            border: 2px dashed #d1d5db;
            background-color: #f9fafb;
            padding: 10px;
            min-height: 80px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signature-box .signature-image img {
            max-width: 200px;
            max-height: 100px;
        }

        .signature-box .name {
            font-weight: bold;
            margin-top: 5px;
        }

        .qr-section {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
        }

        .qr-section img {
            margin: 10px auto;
        }

        .qr-section p {
            font-size: 9px;
            color: #6b7280;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #9ca3af;
        }

        .damage-alert {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 10px;
            margin: 10px 0;
        }

        .damage-alert strong {
            color: #991b1b;
        }

        h3 {
            color: #1f2937;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 8px;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>PHIẾU TRẢ THIẾT BỊ</h1>
        <div class="subtitle">
            <strong>Mã phiếu:</strong> #{{ str_pad($borrow->id, 5, '0', STR_PAD_LEFT) }}<br>
            <strong>Ngày trả:</strong> {{ $borrow->actual_return_date->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="info-section">
        <h3>Thông Tin Người Mượn</h3>
        <table class="info-table">
            <tr>
                <td>Họ và Tên:</td>
                <td><strong>{{ $borrow->borrower->name }}</strong></td>
                <td>Mã SV/GV:</td>
                <td><strong>{{ $borrow->borrower->student->student_code ?? $borrow->borrower->teacher->teacher_code ?? 'N/A' }}</strong></td>
            </tr>
            <tr>
                <td>Email:</td>
                <td>{{ $borrow->borrower->email }}</td>
                <td>Loại:</td>
                <td>{{ $borrow->borrower->role === 'student' ? 'Sinh viên' : 'Giảng viên' }}</td>
            </tr>
        </table>
    </div>

    <div class="info-section">
        <h3>Thông Tin Mượn & Trả</h3>
        <table class="info-table">
            <tr>
                <td>Ngày mượn:</td>
                <td>{{ $borrow->borrowed_date ? $borrow->borrowed_date->format('d/m/Y H:i') : 'N/A' }}</td>
                <td>Hạn trả:</td>
                <td>{{ $borrow->expected_return_date->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Ngày trả thực tế:</td>
                <td><strong>{{ $borrow->actual_return_date->format('d/m/Y H:i') }}</strong></td>
                <td>Trạng thái:</td>
                <td>
                    @if($borrow->actual_return_date->gt($borrow->expected_return_date))
                    <span style="color: #ef4444; font-weight: bold;">TRẢ TRỄ</span>
                    @else
                    <span style="color: #10b981; font-weight: bold;">ĐÚNG HẠN</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td>Nhân viên xử lý:</td>
                <td colspan="3"><strong>{{ $borrow->returnedByStaff->name }}</strong></td>
            </tr>
        </table>
    </div>

    <h3>Danh Sách Thiết Bị Trả</h3>
    <table class="devices-table">
        <thead>
            <tr>
                <th style="width: 5%;">STT</th>
                <th style="width: 25%;">Tên Thiết Bị</th>
                <th style="width: 15%;">Serial Number</th>
                <th style="width: 13%;">Tình Trạng Mượn</th>
                <th style="width: 13%;">Tình Trạng Trả</th>
                <th style="width: 19%;">Ghi Chú Hư Hỏng</th>
                <th style="width: 10%;">Phí Bồi Thường</th>
            </tr>
        </thead>
        <tbody>
            @foreach($borrow->details as $index => $detail)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td><strong>{{ $detail->deviceUnit->device->name }}</strong></td>
                <td>{{ $detail->deviceUnit->serial_number }}</td>
                <td class="condition-{{ $detail->condition_at_borrow }}">
                    {{ ucfirst($detail->condition_at_borrow) }}
                </td>
                <td class="condition-{{ $detail->condition_at_return }}">
                    {{ strtoupper($detail->condition_at_return) }}
                </td>
                <td style="text-align: right;">
                    @if($detail->damage_fee > 0)
                    <strong style="color: #ef4444;">{{ number_format($detail->damage_fee, 0, ',', '.') }} đ</strong>
                    @else
                    -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align: right; font-weight: bold;">TỔNG PHÍ BỒI THƯỜNG:</td>
                <td style="text-align: right; font-weight: bold; color: #ef4444;">
                    {{ number_format($borrow->details->sum('damage_fee'), 0, ',', '.') }} đ
                </td>
            </tr>
        </tfoot>
    </table>

    @php
    $hasDamage = $borrow->details->contains(function($detail) {
    return in_array($detail->condition_at_return, ['damaged', 'broken']);
    });
    @endphp

    @if($hasDamage)
    <div class="damage-alert">
        <strong>⚠️ CẢNH BÁO:</strong> Có thiết bị bị hư hỏng. Vui lòng thanh toán phí bồi thường theo quy định.
    </div>
    @endif

    @if($borrow->return_notes)
    <div class="notes-box">
        <strong>📝 Ghi chú từ nhân viên:</strong><br>
        {{ $borrow->return_notes }}
    </div>
    @endif

    <div class="signatures">
        <div class="signatures-container">
            <div class="signature-box">
                <h4>NGƯỜI TRẢ THIẾT BỊ</h4>
                <div class="signature-image">
                    @if($borrow->borrower_signature && file_exists(storage_path('app/public/' . $borrow->borrower_signature)))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(storage_path('app/public/' . $borrow->borrower_signature))) }}" alt="Chữ ký người trả">
                    @else
                    <span style="color: #9ca3af;">Chưa có chữ ký</span>
                    @endif
                </div>
                <div class="name">{{ $borrow->borrower->name }}</div>
                <div style="font-size: 10px; color: #6b7280;">
                    {{ $borrow->borrower->student->student_code ?? $borrow->borrower->teacher->teacher_code ?? '' }}
                </div>
            </div>

            <div class="signature-box">
                <h4>NHÂN VIÊN XÁC NHẬN</h4>
                <div class="signature-image">
                    @if($borrow->staff_signature && file_exists(storage_path('app/public/' . $borrow->staff_signature)))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(storage_path('app/public/' . $borrow->staff_signature))) }}" alt="Chữ ký staff">
                    @else
                    <span style="color: #9ca3af;">Chưa có chữ ký</span>
                    @endif
                </div>
                <div class="name">{{ $borrow->returnedByStaff->name }}</div>
                <div style="font-size: 10px; color: #6b7280;">Nhân viên</div>
            </div>
        </div>
    </div>

    <div class="qr-section">
        <p><strong>MÃ XÁC THỰC PHIẾU TRẢ</strong></p>
        <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code" width="120">
        <p>Quét mã QR để xác minh tính hợp lệ của phiếu trả</p>
    </div>

    <div class="footer">
        <p>Phiếu được tạo tự động bởi Hệ Thống Quản Lý Thiết Bị</p>
        <p>Thời gian tạo: {{ $generatedAt->format('d/m/Y H:i:s') }}</p>
        <p>© {{ date('Y') }} - Tất cả quyền được bảo lưu</p>
    </div>
</body>

</html>