export function ensureAuthenticated(req, res, next) {
  if (!req.user) {
    return res.redirect('/login');
  }
  next();
}

export function ensureAdmin(req, res, next) {
  if (!req.user || req.user.role !== 'admin') {
    return res.status(403).send('Accès refusé');
  }
  next();
}