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

// Route guard to check authentication
export const requireAuth = async (to, from, next) => {
  
  if (!authService.isAuthenticated()) {
    tokenVerified = false 
    next({ name: 'login' })
    return
  }


  if (from.name === 'login') {

    tokenVerified = true // Mark as verified since we just logged in
    next()
    return
  }

  // If we've already verified token in this session, skip verification
  if (tokenVerified) {
    console.log('Token already verified in this session, allowing access')
    next()
    return
  }

  // For all other cases, verify token with backend
  console.log('Verifying token with backend')
  const isValid = await authService.verifyToken()
  if (!isValid) {
    console.log('Token verification failed, redirecting to login')
    tokenVerified = false
    next({ name: 'login' })
    return
  }

  tokenVerified = true
  next()
}

// Route guard to check user role
export const requireRole = (role) => {
  return (to, from, next) => {
    const user = authService.getUser();
    if (user && user.role === role) {
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
      case 'borrower':
        targetRoute = 'borrower.dashboard';
        break;
      default:
        // Nếu role không hợp lệ, cho phép ở lại login
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
