export function useUserHelpers() {
  const getInitials = (name) => {
    if (!name) return 'U';
    return name
      .split(' ')
      .map((word) => word.charAt(0).toUpperCase())
      .join('')
      .slice(0, 2);
  };

  const roleLabel = (role) => {
    const map = {
      borrower: 'Người mượn',
      staff: 'Nhân viên',
      admin: 'Quản trị viên',
    };
    return map[role] || role;
  };

  return {
    getInitials,
    roleLabel,
  };
}
