const express = require('express');
const router = express.Router();


let users = [];


router.post('/register', (req, res) => {
  const { username, password } = req.body;

 
  if (!username || !password) {
    return res.status(400).json({ message: 'Faltan datos' });
  }


  const newUser = { username, password };
  users.push(newUser);

 
  res.status(201).json({ message: 'Usuario registrado exitosamente', user: newUser });
});


router.post('/login', (req, res) => {
  const { username, password } = req.body;

  if (!username || !password) {
    return res.status(400).json({ message: 'Faltan datos' });
  }


  const user = users.find(u => u.username === username && u.password === password);

  if (!user) {
    return res.status(401).json({ message: 'Usuario o contraseña incorrectos' });
  }


  res.status(200).json({ message: 'Inicio de sesión exitoso', user });
});

module.exports = router;
