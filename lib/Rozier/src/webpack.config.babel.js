import getConfig from './Resources/webpack/config'
import getWebpackConfig from './Resources/webpack/build'

module.exports = getWebpackConfig(getConfig())
