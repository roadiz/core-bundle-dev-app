import $ from 'jquery'

/**
 * Dynamic img directive to display image with a fade animation when load's complete.
 */
export default {
    bind(el, binding) {
        let img = new Image()
        img.src = binding.value
        img.onload = () => {
            el.src = binding.value
            $(el).css('opacity', 0).animate(
                {
                    opacity: 1,
                },
                1000
            )
        }
    },
}
