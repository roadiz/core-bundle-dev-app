/*
 * Roadiz Rozier Bundle entry point
 */
import ColorInput from "./widgets/ColorInput.js";
import GenericBulkActions from "./widgets/GenericBulkActions.js";

(function () {
    console.log('Roadiz Rozier Bundle entry point 🎉');

    /*
     * init generic bulk actions widget
     */
    GenericBulkActions()
    ColorInput()
    window.addEventListener('pageshowend', () => {
        GenericBulkActions()
        ColorInput()
    })
})();
