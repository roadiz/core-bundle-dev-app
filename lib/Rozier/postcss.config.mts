const config = {
    plugins: {
        'postcss-pxtorem': {
            propList: ['*'],
            exclude: /(node_modules|assets\/less)/i,
        },
        'postcss-custom-media': {},
    },
}

export default config
