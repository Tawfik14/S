import express from 'express';
import multer from 'multer';
import path from 'path';
import { fileURLToPath } from 'url';
import { ensureAdmin } from '../middleware/auth.js';
import { createMovie } from '../db.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const storage = multer.diskStorage({
  destination: function (req, file, cb) {
    cb(null, path.join(__dirname, '..', 'public', 'uploads'));
  },
  filename: function (req, file, cb) {
    const ext = path.extname(file.originalname);
    const base = path.basename(file.originalname, ext).replace(/[^a-zA-Z0-9-_]/g, '_');
    cb(null, `${Date.now()}_${base}${ext}`);
  },
});

const upload = multer({ storage });

const router = express.Router();

router.get('/movies/new', ensureAdmin, (req, res) => {
  res.render('admin/new_movie', { title: 'Ajouter un film', error: null });
});

router.post('/movies', ensureAdmin, upload.single('poster'), async (req, res) => {
  const { title, synopsis, video_url, actors_text, poster_url } = req.body;
  try {
    if (!title) throw new Error('Le titre est requis');

    let poster_path = null;
    if (req.file) {
      poster_path = `/public/uploads/${req.file.filename}`;
    } else if (poster_url && poster_url.trim()) {
      poster_path = poster_url.trim();
    }

    const movie = await createMovie({
      title: title.trim(),
      synopsis: synopsis || '',
      video_url: video_url || '',
      actors_text: actors_text || '',
      poster_path,
    });

    res.redirect(`/movies/${movie.id}`);
  } catch (err) {
    res.status(400).render('admin/new_movie', { title: 'Ajouter un film', error: err.message || 'Erreur' });
  }
});

export default router;