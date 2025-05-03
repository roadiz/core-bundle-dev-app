// https://eslint.org/docs/user-guide/configuring
module.exports = {
    root: true,
    parser: 'babel-eslint',
    parserOptions: {
        sourceType: 'module',
    },
    env: {
        browser: true,
        es6: true,
    },
    // https://github.com/standard/standard/blob/master/docs/RULES-en.md
    extends: ['prettier', 'plugin:prettier/recommended', 'plugin:vue/base'],
    // required to lint *.vue files
    plugins: ['html', 'prettier'],
    // add your custom rules here
    rules: {
        indent: ['warn', 4, { SwitchCase: 1 }],
        // allow async-await
        'generator-star-spacing': 'off',
        // allow debugger during development
        'no-debugger': process.env.NODE_ENV === 'production' ? 'error' : 'off',
    },
}
