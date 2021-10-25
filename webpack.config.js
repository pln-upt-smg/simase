const path = require('path')

module.exports = {
    stats: {
        warnings: false
    },
    resolve: {
        alias: {
            '@': path.resolve('resources/js')
        }
    }
}
