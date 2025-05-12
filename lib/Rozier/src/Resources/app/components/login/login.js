;(function () {
    const onLoad = function (data) {
        const splashContainer = document.getElementById('splash-container')
        splashContainer.style.backgroundImage = 'url(' + data.url + ')'
        splashContainer.classList.add('visible')
    }

    const requestImage = function () {
        fetch(window.RozierRoot.routes.splashRequest, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
            credentials: 'omit',
        })
            .then(async (response) => {
                const data = await response.json()
                if (typeof data !== 'undefined' && typeof data.url !== 'undefined') {
                    let myImage = new Image(window.width, window.height)
                    myImage.src = data.url
                    myImage.onload = $.proxy(onLoad, this, data)
                }
            })
            .catch(async (error) => {
                console.error((await error.response.json()).humanMessage)
            })
    }

    if (typeof window.RozierRoot.routes.splashRequest !== 'undefined') {
        requestImage()
    }
})()
