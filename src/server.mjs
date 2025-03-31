const express = require('express');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const low = require('lowdb');
const FileSync = require('lowdb/adapters/FileSync');

const app = express();
const port = 5000;

// Configuración de base de datos con lowdb
const adapter = new FileSync('db.json');
const db = low(adapter);

// Configuración de middleware
app.use(express.json());

// Inicializar base de datos con un array vacío de usuarios si no existe
db.defaults({ users: [] }).write();

// Ruta para registrar un usuario
app.post('/api/register', async (req, res) => {
  const { username, password } = req.body;

  // Validar datos de entrada
  if (!username || !password) {
    return res.status(400).json({ message: 'Por favor ingresa un nombre de usuario y contraseña' });
  }

  // Verificar si el usuario ya existe
  const existingUser = db.get('users').find({ username }).value();
  if (existingUser) {
    return res.status(400).json({ message: 'El usuario ya existe' });
  }

  // Encriptar la contraseña
  const hashedPassword = await bcrypt.hash(password, 10);

  // Guardar usuario en la base de datos
  db.get('users').push({ username, password: hashedPassword }).write();

  res.status(201).json({ message: 'Usuario registrado', user: { username } });
});

// Ruta para hacer login
app.post('/api/login', async (req, res) => {
  const { username, password } = req.body;

  // Validar datos de entrada
  if (!username || !password) {
    return res.status(400).json({ message: 'Por favor ingresa un nombre de usuario y contraseña' });
  }

  // Buscar usuario en la base de datos
  const user = db.get('users').find({ username }).value();
  if (!user) {
    return res.status(400).json({ message: 'El usuario no existe' });
  }

  // Verificar la contraseña
  const isMatch = await bcrypt.compare(password, user.password);
  if (!isMatch) {
    return res.status(400).json({ message: 'Contraseña incorrecta' });
  }

  // Generar token JWT
  const token = jwt.sign({ username }, 'mi_secreto', { expiresIn: '1h' });

  res.status(200).json({ message: 'Usuario logueado', token });
});

// Iniciar el servidor
app.listen(port, () => {
  console.log(`Servidor corriendo en http://localhost:${port}`);
});

