import express from 'express';
import path from 'path';
import morgan from 'morgan';
import session from 'express-session';
import connectSqlite3 from 'connect-sqlite3';
import { fileURLToPath } from 'url';
import fs from 'fs';
import expressEjsLayouts from 'express-ejs-layouts';

import { initDatabase, findUserById } from './db.js';
import authRouter from './routes/auth.js';
import moviesRouter from './routes/movies.js';
import adminRouter from './routes/admin.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();

// Views
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));
app.set('layout', 'layout');
app.use(expressEjsLayouts);

// Static assets
app.use('/public', express.static(path.join(__dirname, 'public')));

// Ensure upload directory exists
const uploadsDir = path.join(__dirname, 'public', 'uploads');
if (!fs.existsSync(uploadsDir)) {
  fs.mkdirSync(uploadsDir, { recursive: true });
}

// Middleware
app.use(morgan('dev'));
app.use(express.urlencoded({ extended: true }));

const SQLiteStore = connectSqlite3(session);
app.use(
  session({
    store: new SQLiteStore({ db: 'sessions.sqlite', dir: __dirname }),
    secret: process.env.SESSION_SECRET || 'devsecret',
    resave: false,
    saveUninitialized: false,
    cookie: { maxAge: 1000 * 60 * 60 * 24 * 7 },
  })
);

// Load current user from session
app.use(async (req, res, next) => {
  try {
    const userId = req.session?.userId;
    if (userId) {
      const user = await findUserById(userId);
      if (user) {
        req.user = user;
        res.locals.currentUser = { id: user.id, username: user.username, role: user.role };
      } else {
        req.user = null;
        res.locals.currentUser = null;
      }
    } else {
      req.user = null;
      res.locals.currentUser = null;
    }
  } catch (err) {
    // Do not crash the request because of user load
    req.user = null;
    res.locals.currentUser = null;
  }
  next();
});

// Routes
app.use('/', moviesRouter);
app.use('/', authRouter);
app.use('/admin', adminRouter);

// 404
app.use((req, res) => {
  res.status(404).render('404', { title: 'Page non trouvée' });
});

// Start server after DB init
const PORT = process.env.PORT || 3000;
initDatabase()
  .then(() => {
    app.listen(PORT, () => {
      // eslint-disable-next-line no-console
      console.log(`Serveur démarré sur http://localhost:${PORT}`);
    });
  })
  .catch((err) => {
    // eslint-disable-next-line no-console
    console.error('Erreur lors de l\'initialisation de la base de données:', err);
    process.exit(1);
  });