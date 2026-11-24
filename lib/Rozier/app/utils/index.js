export function dataURItoBlob(dataURI) {
    let binary = atob(dataURI.split(',')[1])
    let array = []

    for (let i = 0; i < binary.length; i++) {
        array.push(binary.charCodeAt(i))
    }

    // separate out the mime component
    const mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0]

    return new Blob([new Uint8Array(array)], { type: mimeString })
}
