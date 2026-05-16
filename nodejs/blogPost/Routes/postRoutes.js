const express = require('express');
const router = express.Router();
const postController = require('../controllers/postController');

// /posts route
router.get('/', postController.getPosts);
router.post('/', postController.createPost);

// /post/:id route
router.get('/:id', postController.getPost);
router.put('/:id', postController.updatePost);
router.delete('/:id', postController.deletePost);

module.exports = router;
