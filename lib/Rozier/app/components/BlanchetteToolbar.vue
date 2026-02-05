<template>
    <div class="toolbar rz-button-group" ref="toolbar">
        <template v-if="!cropped">
            <button
                class="rz-button toolbar__button "
                :title="translations.move"
                v-if="setDragMode"
                @click="setDragMode('move')"
            >
                <span class="rz-button__icon rz-icon-ri--drag-move-2-line"></span>
            </button>
            <button
                class="rz-button toolbar__button "
                :title="translations.crop"
                v-if="setDragMode"
                @click="setDragMode('crop')"
            >
                <span class="rz-button__icon rz-icon-ri--crop-line"></span>
            </button>
            <button
                class="rz-button toolbar_button "
                :title="translations.zoomIn"
                v-if="zoomIn"
                @click="zoomIn"
            >
                <span class="rz-button__icon rz-icon-ri--zoom-in-line"></span>
            </button>
            <button
                class="rz-button toolbar_button "
                :title="translations.zoomOut"
                v-if="zoomOut"
                @click="zoomOut"
            >
                <span class="rz-button__icon rz-icon-ri--zoom-out-line"></span>
            </button>
            <button
                class="rz-button toolbar_button "
                :title="translations.rotateLeft"
                v-if="rotateLeft"
                @click="rotateLeft"
            >
                <span class="rz-button__icon rz-icon-ri--loop-left-line"></span>
            </button>
            <button
                class="rz-button toolbar_button "
                :title="translations.rotateRight"
                v-if="rotateRight"
                @click="rotateRight"
            >
                <span class="rz-button__icon rz-icon-ri--loop-right-line"></span>
            </button>
            <button
                class="rz-button toolbar_button "
                :title="translations.flipHorizontal"
                v-if="flipHorizontal"
                @click="flipHorizontal"
            >
                <span class="rz-button__icon rz-icon-ri--flip-horizontal-2-line"></span>
            </button>
            <button
                class="rz-button toolbar_button "
                :title="translations.flipVertical"
                v-if="flipVertical"
                @click="flipVertical"
            >
                <span class="rz-button__icon rz-icon-ri--flip-vertical-2-line"></span>
            </button>

            <select
                class="rz-select toolbar__select"
                v-model="ratio"
                :title="translations.aspectRatio"
                id="blanchette-toolbar-ratio-select"
                @change="aspectRatio"
            >
                <optgroup :label="translations.other">
                    <option value="free">{{ translations.free }}</option>
                    <option value="1:1">1:1</option>
                    <option value="4:3">4:3</option>
                </optgroup>
                <optgroup :label="translations.landscape">
                    <option value="16:9">16:9</option>
                    <option value="21:9">21:9</option>
                </optgroup>
                <optgroup :label="translations.portrait">
                    <option value="9:16">9:16</option>
                    <option value="9:21">9:21</option>
                </optgroup>
            </select>

            <button
                class="rz-button toolbar_button"
                v-if="cropping && !cropped"
                @click="crop"
            >
                <span class="rz-button__label">{{ translations.applyChange }}</span>
                <span class="rz-button__icon rz-icon-ri--check-line"></span>
            </button>
        </template>

        <button
            class="rz-button toolbar_button"
            :title="translations.undo"
             v-if="cropped"
            @click="undo"
        >
            <span class="rz-button__label">{{ translations.undo }}</span>
            <span class="rz-button__icon rz-icon-ri--arrow-go-back-line"></span>
        </button>
        <button
            class="rz-button toolbar_button"
             v-if="cropped"
            @click="overwrite"
        >
            <span class="rz-button__label">{{ translations.saveAndOverwrite }}</span>
            <span class="rz-button__icon rz-icon-ri--save-line"></span>
        </button>
    </div>
</template>
<style lang="less" scoped>
.toolbar {
    margin: 20px 0;
}

.toolbar__select {
    --rz-input-width: auto;
}
</style>
<script>
export default {
    props: {
        translations: {
            type: Object,
            required: true,
        },
        aspectRatio: {
            type: Function,
        },
        cropping: {
            type: Boolean,
            required: true,
        },
        cropped: {
            type: Boolean,
            required: true,
        },
        overwrite: {
            type: Function,
            required: true,
        },
        undo: {
            type: Function,
            required: true,
        },
        setDragMode: {
            type: Function,
        },
        zoomOut: {
            type: Function,
        },
        zoomIn: {
            type: Function,
        },
        rotateLeft: {
            type: Function,
        },
        rotateRight: {
            type: Function,
        },
        flipHorizontal: {
            type: Function,
        },
        flipVertical: {
            type: Function,
        },
        crop: {
            type: Function,
        },
        move: {
            type: Function,
        },
        clear: {
            type: Function,
        },
    },
    data() {
        return {
            ratio: 'free',
        }
    },
    mounted() {
        window.addEventListener('keydown', this.keydown, false)
    },
    beforeDestroy() {
        window.removeEventListener('keydown', this.keydown, false)
    },
    methods: {
        keydown(e) {
            const key = e.keyCode

            switch (key) {
                // Undo crop (Key: Ctrl + Z)
                case 90:
                    if (this.undo) {
                        e.preventDefault()
                        this.undo()
                    }
                    break
            }

            if (this.cropped) {
                return
            }

            switch (key) {
                // Crop the image (Key: Enter)
                case 13:
                    if (this.crop) {
                        this.crop()
                    }
                    break

                // Clear crop area (Key: Esc)
                case 27:
                    if (this.clear) {
                        this.clear()
                    }
                    break

                // Move to the left (Key: ←)
                case 37:
                    if (this.move) {
                        e.preventDefault()
                        this.move(-1, 0)
                    }
                    break

                // Move to the top (Key: ↑)
                case 38:
                    if (this.move) {
                        e.preventDefault()
                        this.move(0, -1)
                    }
                    break

                // Move to the right (Key: →)
                case 39:
                    if (this.move) {
                        e.preventDefault()
                        this.move(1, 0)
                    }
                    break

                // Move to the bottom (Key: ↓)
                case 40:
                    if (this.move) {
                        e.preventDefault()
                        this.move(0, 1)
                    }
                    break

                // Enter crop mode (Key: C)
                case 67:
                    if (this.setDragMode) {
                        this.setDragMode('crop')
                    }
                    break

                // Enter move mode (Key: M)
                case 77:
                    if (this.setDragMode) {
                        this.setDragMode('move')
                    }
                    break

                // Zoom in (Key: I)
                case 73:
                    if (this.zoomIn) {
                        this.zoomIn()
                    }
                    break

                // Zoom out (Key: O)
                case 79:
                    if (this.zoomOut) {
                        this.zoomOut()
                    }
                    break

                // Rotate left (Key: L)
                case 76:
                    if (this.rotateLeft) {
                        this.rotateLeft()
                    }
                    break

                // Rotate right (Key: R)
                case 82:
                    if (this.rotateRight) {
                        this.rotateRight()
                    }
                    break

                // Flip horizontal (Key: H)
                case 72:
                    if (this.flipHorizontal) {
                        this.flipHorizontal()
                    }
                    break

                // Flip vertical (Key: V)
                case 86:
                    if (this.flipVertical) {
                        this.flipVertical()
                    }
                    break
            }
        },
    },
}
</script>
