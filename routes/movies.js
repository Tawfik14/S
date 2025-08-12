import express from 'express';
import { getAllMovies, getMovieById } from '../db.js';

const router = express.Router();

router.get('/', async (req, res) => {
  const movies = await getAllMovies();
  res.render('index', { title: 'Accueil', movies });
});

router.get('/movies/:id', async (req, res) => {
  const movie = await getMovieById(req.params.id);
  if (!movie) return res.status(404).render('404', { title: 'Film introuvable' });
  res.render('movie', { title: movie.title, movie });
});

export default router;