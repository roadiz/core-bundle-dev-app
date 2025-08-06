// https://eslint.org/docs/user-guide/configuring
module.exports = {
    root: true,
    env: {
        browser: true,
        es6: true,
    },
    parser: 'vue-eslint-parser',
    parserOptions: {
        parser: '@typescript-eslint/parser',
    },
    // https://github.com/standard/standard/blob/master/docs/RULES-en.md
    extends: [
        'plugin:vue/vue2-recommended',
        'plugin:@typescript-eslint/recommended',
        'prettier',
        'plugin:prettier/recommended',
    ],
    // required to lint *.vue files
    plugins: ['@typescript-eslint', 'html', 'prettier'],
    // add your custom rules here
    rules: {
        indent: ['warn', 4, { SwitchCase: 1 }],
        // allow async-await
        'generator-star-spacing': 'off',
        // allow debugger during development
        'no-debugger': process.env.NODE_ENV === 'production' ? 'error' : 'off',
    },
}
