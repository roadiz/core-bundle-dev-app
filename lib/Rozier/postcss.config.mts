import media from './app/constants/media.ts'

const config = {
    plugins: {
        'postcss-pxtorem': {
            propList: ['*'],
        },
        'postcss-simple-vars': {
            variables: media,
        },
    },
}

export default config
