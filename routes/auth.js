import express from 'express';
import { createUser, findUserByUsername, verifyUserPassword } from '../db.js';

const router = express.Router();

router.get('/login', (req, res) => {
  if (req.user) return res.redirect('/');
  res.render('login', { title: 'Connexion', error: null });
});

router.post('/login', async (req, res) => {
  const { username, password } = req.body;
  const user = await findUserByUsername(username);
  if (!user) {
    return res.status(401).render('login', { title: 'Connexion', error: 'Identifiants invalides' });
  }
  const ok = await verifyUserPassword(user, password);
  if (!ok) {
    return res.status(401).render('login', { title: 'Connexion', error: 'Identifiants invalides' });
  }
  req.session.userId = user.id;
  res.redirect('/');
});

router.get('/signup', (req, res) => {
  if (req.user) return res.redirect('/');
  res.render('signup', { title: "S'inscrire", error: null });
});

router.post('/signup', async (req, res) => {
  const { username, password } = req.body;
  if (!username || !password) {
    return res.status(400).render('signup', { title: "S'inscrire", error: 'Veuillez remplir tous les champs' });
  }
  try {
    const newUser = await createUser({ username, password, role: 'user' });
    req.session.userId = newUser.id;
    res.redirect('/');
  } catch (err) {
    let message = 'Erreur lors de la création du compte';
    if (err && err.message && err.message.includes('UNIQUE')) {
      message = "Nom d'utilisateur déjà pris";
    }
    res.status(400).render('signup', { title: "S'inscrire", error: message });
  }
});

router.post('/logout', (req, res) => {
  req.session.destroy(() => {
    res.redirect('/');
  });
});

export default router;