export default function (){
    const statusReverseLabel = (status) => {
        const map = {
            pending: "Chờ duyệt",
            approved: "Đã duyệt",
            rejected: "Từ chối",
            cancelled: "Đã hủy",
            completed: "Hoàn thành",
        };
        return map[status] || status;
    }
    
    const statusBorrowLabel = (status) => {
        const map = {
            pending: "Chờ duyệt",
            approved: "Đã duyệt",
            rejected: "Từ chối",
            cancelled: "Đã hủy",
            completed: "Hoàn thành",
            borrowed: "Đang mượn",
            overdue: "Quá hạn",
        };
        return map[status] || status;
    }
    
    const statusClasses = (status) => {
        switch (status) {
            case "approved":
                return "bg-green-100 text-green-700";
            case "rejected":
                return "bg-red-100 text-red-600";
            case "cancelled":
                return "bg-red-100 text-red-600";
            case "completed":
                return "bg-emerald-100 text-emerald-600";
            default:
                return "bg-amber-100 text-amber-700";
        }
    }
    
    const statusActiveClass = (isActive) => {
        if(isActive){
            return "bg-green-100 text-green-700";
        }else{
            return "bg-gray-100 text-gray-600";
        }
    }
    
    const statusActive = (isActive) => {
        return isActive ? "Kích hoạt" : "Vô hiệu hóa";
    }
    
    const getRoleLabel = (role) => {
        const map = {
            admin: "Quản trị viên",
            staff: "Nhân viên",
            student: "Sinh viên",
            teacher: "Giảng viên",
        };
        return map[role] || role;
    }
    
    return {
        statusReverseLabel,
        statusClasses,
        statusActive,
        statusActiveClass,
        getRoleLabel,
        statusBorrowLabel
    }
}