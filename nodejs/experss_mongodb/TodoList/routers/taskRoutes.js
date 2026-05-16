const express = require('express');
const router = express.Router();
const taskController = require('../controllers/task/task');

router.get('/get', taskController.getAllTasks);
router.get('/get/:id', taskController.getTaskById);
router.post('/post', taskController.createTask);
router.put('/edit/:id', taskController.updateTask);
router.delete('/delete/:id', taskController.deleteTask);
router.delete('/delete', taskController.deleteAllTasks);

module.exports = router;
