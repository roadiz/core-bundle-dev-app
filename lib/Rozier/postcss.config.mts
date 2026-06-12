const config = {
    plugins: {
        'postcss-pxtorem': {
            propList: ['*'],
            exclude: (filePath: string) => {
                if (!filePath) return true
                return /node_modules|\.less/i.test(filePath)
            },
        },
        'postcss-custom-media': {},
    },
}

export default config
