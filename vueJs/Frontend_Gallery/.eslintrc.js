module.exports = {
  root: true,
  env: {
    node: true,
  },
  extends: [
    'plugin:vue/essential',
    'eslint:recommended',
  ],
  rules: {
    'vue/multi-word-component-names': 'off',
  },
  parserOptions: {
    parser: 'babel-eslint',
  },
};
