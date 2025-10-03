import { fadeIn } from '../utils/animation'

/**
 * Dynamic img directive to display image with a fade animation when load's complete.
 */
export default {
  bind(el, binding) {
    let img = new Image()
    img.src = binding.value
    img.onload = async () => {
      el.src = binding.value
      await fadeIn(el, 1000)
    }
  },
}
