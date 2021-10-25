require('./bootstrap')

import {createApp, h} from 'vue'
import {createInertiaApp, Link} from '@inertiajs/inertia-vue3'
import {InertiaProgress} from '@inertiajs/progress'
import {Workbox} from 'workbox-window'

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Stocktake'

createInertiaApp({
    title: (title) => `${title} | ${appName}`,
    resolve: (name) => require(`./Pages/${name}.vue`),
    setup({el, app, props, plugin}) {
        return createApp({render: () => h(app, props)})
            .use(plugin)
            .component('InertiaLink', Link)
            .mixin({methods: {route}})
            .mount(el)
    }
})

InertiaProgress.init({color: '#29227D'})

if ('serviceWorker' in navigator) {

    self.addEventListener('activate', event => {
        event.waitUntil(
            (async () => {
                const keys = await caches.keys();
                return keys.map(async (cache) => {
                    if (cache !== cacheName) {
                        return await caches.delete(cache)
                    }
                })
            })()
        )
    })

    const wb = new Workbox('/service-worker.js')
    wb.register()
    wb.update()
}
