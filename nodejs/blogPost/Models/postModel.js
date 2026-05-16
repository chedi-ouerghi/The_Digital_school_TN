const mongoose = require('mongoose');

const postSchema = new mongoose.Schema({
  title: {
    type: String,
    unique: true,
    minlength: 3,
    maxlength: 100,
    trim: true,
    required: true,
  },
  description: {
    type: String,
    unique: true,
    minlength: 3,
    maxlength: 255,
    trim: true,
    required: true,
  },
  author: {
    type: String,
  },
  keyWords: {
    type: [String],
  },
  createdAt: {
    type: Date,
    default: Date.now,
  },
});

const Post = mongoose.model('Post', postSchema);

module.exports = Post;