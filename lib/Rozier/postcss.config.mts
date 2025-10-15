import media from './app/assets/constants/media.ts'

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
