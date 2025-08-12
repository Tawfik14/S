import sqlite3 from 'sqlite3';
import bcrypt from 'bcrypt';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const dbFile = path.join(__dirname, 'database.sqlite');
const db = new sqlite3.Database(dbFile);

function run(sql, params = []) {
  return new Promise((resolve, reject) => {
    db.run(sql, params, function (err) {
      if (err) return reject(err);
      resolve(this);
    });
  });
}

function get(sql, params = []) {
  return new Promise((resolve, reject) => {
    db.get(sql, params, (err, row) => {
      if (err) return reject(err);
      resolve(row);
    });
  });
}

function all(sql, params = []) {
  return new Promise((resolve, reject) => {
    db.all(sql, params, (err, rows) => {
      if (err) return reject(err);
      resolve(rows);
    });
  });
}

export async function initDatabase() {
  await run(`PRAGMA foreign_keys = ON;`);

  await run(`
    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT NOT NULL UNIQUE,
      password_hash TEXT NOT NULL,
      role TEXT NOT NULL CHECK(role IN ('user','admin'))
    );
  `);

  await run(`
    CREATE TABLE IF NOT EXISTS movies (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      title TEXT NOT NULL,
      poster_path TEXT,
      synopsis TEXT,
      video_url TEXT,
      actors_text TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
  `);

  // Seed admin user if none exists
  const admin = await get(`SELECT * FROM users WHERE role = 'admin' LIMIT 1;`);
  if (!admin) {
    const passwordHash = await bcrypt.hash('admin123', 10);
    await run(`INSERT INTO users (username, password_hash, role) VALUES (?,?,?)`, [
      'admin',
      passwordHash,
      'admin',
    ]);
  }

  // Seed example movies if table is empty
  const movieCountRow = await get(`SELECT COUNT(1) as count FROM movies;`);
  if (!movieCountRow || movieCountRow.count === 0) {
    await run(
      `INSERT INTO movies (title, poster_path, synopsis, video_url, actors_text) VALUES (?,?,?,?,?)`,
      [
        'Inception',
        'https://m.media-amazon.com/images/I/51s+N1GZ5iL._AC_.jpg',
        "Un voleur qui s'immisce dans les rêves doit accomplir l'impossible: l'inception.",
        'https://www.youtube.com/embed/YoHD9XEInc0',
        'Leonardo DiCaprio, Joseph Gordon-Levitt, Ellen Page',
      ]
    );
    await run(
      `INSERT INTO movies (title, poster_path, synopsis, video_url, actors_text) VALUES (?,?,?,?,?)`,
      [
        'The Matrix',
        'https://m.media-amazon.com/images/I/51EG732BV3L.jpg',
        'Un hacker découvre la réalité simulée qui emprisonne l\'humanité.',
        'https://www.youtube.com/embed/vKQi3bBA1y8',
        'Keanu Reeves, Carrie-Anne Moss, Laurence Fishburne',
      ]
    );
  }
}

export async function findUserByUsername(username) {
  return get(`SELECT * FROM users WHERE username = ?`, [username]);
}

export async function findUserById(id) {
  return get(`SELECT * FROM users WHERE id = ?`, [id]);
}

export async function createUser({ username, password, role = 'user' }) {
  const passwordHash = await bcrypt.hash(password, 10);
  await run(`INSERT INTO users (username, password_hash, role) VALUES (?,?,?)`, [
    username,
    passwordHash,
    role,
  ]);
  return findUserByUsername(username);
}

export async function getAllMovies() {
  return all(`SELECT * FROM movies ORDER BY created_at DESC;`);
}

export async function getMovieById(id) {
  return get(`SELECT * FROM movies WHERE id = ?`, [id]);
}

export async function createMovie({ title, poster_path, synopsis, video_url, actors_text }) {
  const result = await run(
    `INSERT INTO movies (title, poster_path, synopsis, video_url, actors_text) VALUES (?,?,?,?,?)`,
    [title, poster_path, synopsis, video_url, actors_text]
  );
  return get(`SELECT * FROM movies WHERE id = ?`, [result.lastID]);
}

export async function verifyUserPassword(user, password) {
  return bcrypt.compare(password, user.password_hash);
}