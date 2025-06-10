/*
 * Roadiz Rozier Bundle entry point
 */
import ColorInput from "./widgets/ColorInput.js";
import GenericBulkActions from "./widgets/GenericBulkActions.js";
import Mobile from "./behaviours/Mobile.js";

(function () {
    console.log('Roadiz Rozier Bundle entry point 🎉');

    /*
     * init generic bulk actions widget
     */
    GenericBulkActions()
    ColorInput()
    new Mobile()
    window.addEventListener('pageshowend', () => {
        GenericBulkActions()
        ColorInput()
    })
})();
