import authService from '../services/auth/authService.js'

// Track if we've already verified token in this session
let tokenVerified = false

// Function to reset verification flag (for logout)
export const resetTokenVerification = () => {
  tokenVerified = false

}

// Make it available globally for authService
if (typeof window !== 'undefined') {
  window.resetTokenVerification = resetTokenVerification
}

export const requireAuth = async (to, from, next) => {
  
  if (!authService.isAuthenticated()) {
    tokenVerified = false 
    next({ name: 'login' })
    return
  }


  if (from.name === 'login') {

    tokenVerified = true 
    next()
    return
  }

  if (tokenVerified) {
    next()
    return
  }

  const isValid = await authService.verifyToken()
  if (!isValid) {
    tokenVerified = false
    next({ name: 'login' })
    return
  }

  
  try {
    const { useNotifications } = await import('../stores/notificationStore')
    const notifications = useNotifications()
    notifications.startPolling()
  } catch (error) {
    console.error('[Guards] Failed to initialize notifications:', error)
  }
  
  next()
}

// Route guard to check user role
export const requireRole = (role) => {
  return (to, from, next) => {
    const user = authService.getUser();
    // Map student/teacher to borrower for route checking
    const userRole = (user?.role === 'student' || user?.role === 'teacher') ? 'borrower' : user?.role;
    
    if (user && userRole === role) {
      next();
    } else {

      if (user) {
        switch (user.role) {
          case 'admin':
            next({ name: 'admin.dashboard' });
            break;
          case 'staff': 
            next({ name: 'staff.dashboard' });
            break;
          case 'student':
          case 'teacher':
          case 'borrower':
            next({ name: 'borrower.dashboard' });
            break;
          default:
            next({ name: 'login' });
        }
      } else {
        next({ name: 'login' });
      }
    }
  };
};


export const redirectIfAuthenticated = (to, from, next) => {
  if (authService.isAuthenticated()) {
    const user = authService.getUser();

    let targetRoute;
    switch (user?.role) {
      case 'admin':
        targetRoute = 'admin.dashboard';
        break;
      case 'staff':
        targetRoute = 'staff.dashboard';
        break;
      case 'student':
      case 'teacher':
      case 'borrower':
        targetRoute = 'borrower.dashboard';
        break;
      default:
        next();
        return;
    }

    if (to.name !== targetRoute) {
      next({ name: targetRoute });
    } else {
      next();
    }
    return;
  }
  next();
}
