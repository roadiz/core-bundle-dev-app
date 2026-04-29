<template>
    <div ref="blanchetteEditor" class="blanchette-editor">
        <div class="canvas">
            <transition name="fade" v-if="isLoading">
                <div class="spinner-container">
                    <div class="spinner"></div>
                </div>
            </transition>

            <blanchette-toolbar
                :flip-horizontal="flipHorizontal"
                :flip-vertical="flipVertical"
                :zoom-in="zoomIn"
                :zoom-out="zoomOut"
                :rotate-left="rotateLeft"
                :rotate-right="rotateRight"
                :crop="crop"
                :clear="clear"
                :move="move"
                :cropped="cropped"
                :cropping="cropping"
                :undo="restore"
                :aspect-ratio="setAspectRatio"
                :overwrite="overwrite"
                :translations="translations.blanchetteEditor"
                :set-drag-mode="setDragMode"
            >
            </blanchette-toolbar>

            <div class="editor">
                <template v-if="url">
                    <img
                        :src="url"
                        :alt="name"
                        @load="load"
                        ref="image"
                        :width="width ? width : ''"
                        :height="height ? height : ''"
                    />
                </template>
            </div>
        </div>

        <slot ref="editForm"></slot>
    </div>
</template>
<style lang="less" scoped>
.blanchette-editor {
    margin-bottom: 25px;
}

.canvas {
    position: relative;
}

.spinner-container {
    position: absolute;
    z-index: 5;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background: rgba(255, 255, 255, 0.5);
}

.editor {
    height: 100%;
    overflow: hidden;

    img {
        display: block;
        max-width: 100%;
        max-height: 100%;
        margin: auto;
    }
}
</style>
<script>
import 'vue-cropperjs'
import Cropper from 'cropperjs'
import { mapState, mapActions } from 'vuex'

// Components
import BlanchetteToolbar from '../components/BlanchetteToolbar.vue'
import { sleep } from '~/utils/sleep'

export default {
    props: {
        srcUrl: {
            required: true,
            type: String,
        },
        filename: {
            required: true,
            type: String,
        },
        width: {
            type: [Number, String],
        },
        height: {
            type: [Number, String],
        },
    },
    data() {
        return {
            editable: false,
            cropper: false,
            cropping: true,
            data: null,
            canvasData: null,
            cropBoxData: null,
            image: null,
            type: 'image/png',
            originalMimeType: '',
            name: '',
            url: this.srcUrl,
            cropped: false,
            aspectRatio: null,
            currentWidth: this.width ? Number(this.width) : null,
            currentHeight: this.height ? Number(this.height) : null,
        }
    },
    mounted() {
        this.originalMimeType = this.normalizeMimeType(this.mimeType)
        this.type = this.resolveOutputMimeType(this.originalMimeType)

        this.blanchetteEditorInit({
            url: this.url,
            editor: this.$refs.blanchetteEditor,
        })
    },
    computed: {
        ...mapState({
            originalUrl: (state) => state.blanchetteEditor.originalUrl,
            isLoading: (state) => state.blanchetteEditor.isLoading,
            translations: (state) => state.translations,
        }),
    },
    methods: {
        ...mapActions(['blanchetteEditorInit', 'blanchetteEditorLoaded', 'blanchetteEditorSave']),
        overwrite() {
            console.log(this)
            this.blanchetteEditorSave({
                url: this.url,
                filename: this.getOverwriteFilename(),
            }).then((data) => {
                if (
                    data &&
                    typeof data.imageWidth !== 'undefined' &&
                    typeof data.imageHeight !== 'undefined'
                ) {
                    this.currentWidth = Number(data.imageWidth)
                    this.currentHeight = Number(data.imageHeight)
                }
                this.restore()
            })
        },
        normalizeMimeType(mimeType) {
            if (!mimeType || typeof mimeType !== 'string') {
                return ''
            }
            return mimeType.toLowerCase()
        },
        resolveOutputMimeType(mimeType) {
            switch (mimeType) {
                case 'image/jpeg':
                case 'image/png':
                case 'image/webp':
                    return mimeType
                case 'image/gif':
                default:
                    return 'image/png'
            }
        },
        getOverwriteFilename() {
            if (this.type !== 'image/png' || this.originalMimeType === 'image/png') {
                return this.filename
            }
            const extensionPosition = this.filename.lastIndexOf('.')
            if (extensionPosition > 0) {
                return `${this.filename.slice(0, extensionPosition)}.png`
            }
            return `${this.filename}.png`
        },
        async load() {
            await sleep(1000)
            if (!this.image) {
                this.image = this.$refs.image
                this.start()
                this.blanchetteEditorLoaded()
            }
        },
        setAspectRatio(e) {
            const ratio = e.target.value

            switch (ratio) {
                case '1:1':
                    this.aspectRatio = 1
                    break
                case '4:3':
                    this.aspectRatio = 4 / 3
                    break
                case '16:9':
                    this.aspectRatio = 16 / 9
                    break
                case '21:9':
                    this.aspectRatio = 21 / 9
                    break
                case '9:16':
                    this.aspectRatio = 9 / 16
                    break
                case '9:21':
                    this.aspectRatio = 9 / 21
                    break
                default:
                    this.aspectRatio = null
            }

            this.cropper.setAspectRatio(this.aspectRatio)
        },
        rotateRight() {
            this.cropper.rotate(90)
        },
        rotateLeft() {
            this.cropper.rotate(-90)
        },
        setDragMode(dragMode) {
            if (dragMode && this.cropper) {
                this.cropper.setDragMode(dragMode)
            }
        },
        zoomIn() {
            this.cropper.zoom(0.1)
        },
        zoomOut() {
            this.cropper.zoom(-0.1)
        },
        flipHorizontal() {
            this.cropper.scaleX(-this.cropper.getData().scaleX || -1)
        },
        flipVertical() {
            this.cropper.scaleY(-this.cropper.getData().scaleY || -1)
        },
        move(x, y) {
            this.cropper.move(x, y)
        },
        start() {
            if (this.cropper) {
                return
            }

            this.cropper = new Cropper(this.image, {
                autoCrop: false,
                dragMode: 'move',
                background: false,
                aspectRatio: this.aspectRatio,
                viewMode: 1,
                zoomOnWheel: false,
                ready() {
                    if (this.data) {
                        this.cropper
                            .crop()
                            .setData(this.data)
                            .setCanvasData(this.canvasData)
                            .setCropBoxData(this.cropBoxData)
                        this.data = null
                        this.canvasData = null
                        this.cropBoxData = null
                    }
                },
                crop(data) {
                    if (data.detail.width > 0 && data.detail.height > 0 && !this.cropping) {
                        this.cropping = true
                    }
                },
            })
        },
        stop() {
            if (this.cropper) {
                this.cropper.destroy()
                this.cropper = null
            }
        },
        crop() {
            const cropper = this.cropper
            const type = this.type

            if (this.cropping) {
                this.data = cropper.getData()
                this.canvasData = cropper.getCanvasData()
                this.cropBoxData = cropper.getCropBoxData()
                this.url = cropper
                    .getCroppedCanvas(
                        type === 'image/png'
                            ? null
                            : {
                                  fillColor: '#fff',
                              }
                    )
                    .toDataURL(type)

                this.cropped = true
                this.stop()
            }
        },
        clear() {
            if (this.cropping) {
                this.cropper.clear()
            }
        },
        restore() {
            if (!this.cropper) {
                this.image = null
                this.url = this.originalUrl
                this.cropped = false
            }
        },
    },
    components: {
        BlanchetteToolbar,
    },
}
</script>
