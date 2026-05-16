const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const taskRoutes = require('./routers/taskRoutes');
const mongoose = require('mongoose');

mongoose.connect("mongodb://localhost:27017/todolist");

const app = express();
const port = 6532;

app.use(bodyParser.json());
app.use(cors());

app.use('/tasks', taskRoutes);

const db = mongoose.connection;

db.on('error', console.error.bind(console, 'MongoDB connection error:'));
db.once('open', () => {
  console.log('Connected to MongoDB');
  app.listen(port, () => {
    console.log(`Server is running on port ${port}`);
  });
});