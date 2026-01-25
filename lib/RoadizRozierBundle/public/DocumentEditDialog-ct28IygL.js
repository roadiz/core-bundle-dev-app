import{R as d}from"./RoadizElement-XU473doh.js";class i extends d{static INPUT_BASE_NAME="document_edit_dialog[proxy]";static INPUT_KEYS=["imageCropAlignment","hotspot"];static CSS_SELECTORS={DIALOG_CLOSE:".document-edit-dialog__close",DIALOG_ACTIONS:".document-edit-dialog__actions > button",DIALOG_CONTENT:".document-edit-dialog__content",ALIGNMENT_WIDGET:"document-alignment-widget"};dialog=null;connectedCallback(){this.createDialog(),this.setupEventListeners(),this.appendToDOM(),this.loadTemplate(),this.hasAttribute("open")&&this.dialog.showModal()}createDialog(){this.dialog=document.createElement("dialog"),this.dialog.setAttribute("closedby","any"),this.dialog.classList.add("uk-form","document-edit-dialog"),this.dialog.innerHTML=this.getDialogHTML()}getDialogHTML(){const{INPUT_BASE_NAME:t,INPUT_KEYS:e}=i;return`
            <header class="document-edit-dialog__header">
                <h2 class="document-edit-dialog__title">
                    <i class="uk-icon-image"></i>
                    <span>${this.getAttribute("title")}</span>
                </h2>
                <button class="document-edit-dialog__close" type="button" value="cancel">
                    <i class="uk-icon-close"></i>
                </button>
            </header>
            <div class="document-edit-dialog__content">
                <div class="document-edit-dialog__content__placeholder">
                    <div class="spinner"></div>
                </div>
            </div>
            ${e.map(o=>`<input type="hidden" name="${t}[${o}]">`).join(`
`)}
            <footer>
                <div class="document-edit-dialog__actions">
                    ${this.getEditLink()}
                    <button class="uk-button uk-button-small document-edit-dialog__cancel" type="button" value="cancel">
                        ${window.RozierConfig?.messages?.documentEditDialogCancel}
                    </button>
                    <button class="uk-button uk-button-small document-edit-dialog__submit" type="button" value="submit">
                        ${window.RozierConfig?.messages?.documentEditDialogSubmit}
                    </button>
                </div>
            </footer>
        `}setupEventListeners(){const{CSS_SELECTORS:t}=i,e=this.dialog?.querySelectorAll(`${t.DIALOG_CLOSE}, ${t.DIALOG_ACTIONS}`);e&&this.listen(e,"click",this.onButtonClick),this.listen(this.dialog,"close",this.onDialogClose)}appendToDOM(){this.appendChild(this.dialog)}loadTemplate(){const t=this.getAttribute("template-path");if(t)return this.fetchTemplate(t).then(e=>this.parseTemplate(e)).then(e=>this.setupWidget(e)).then(e=>this.replaceDialogContent(e)).catch(e=>{console.error("DocumentEditDialog: Failed to load template:",e)})}async fetchTemplate(t){const e=await fetch(t,{headers:{"X-Requested-With":"XMLHttpRequest"}});if(!e.ok)throw new Error("Network response was not ok");return e.text()}parseTemplate(t){const e=document.createElement("div");return e.innerHTML=t,e.querySelector(i.CSS_SELECTORS.ALIGNMENT_WIDGET)}setupWidget(t){const{INPUT_BASE_NAME:e}=i;if(t){this.applyInputValues(this.getAttribute("input-base-name"),e,document,this.dialog),t.setAttribute("image-path",this.getAttribute("image-path")),t.setAttribute("image-width",this.getAttribute("image-width")),t.setAttribute("image-height",this.getAttribute("image-height")),t.setAttribute("input-base-name",e),t.setAttribute("hotspot-overridable",!0);const o=this.getAttribute("original-hotspot");o&&t.setAttribute("original-hotspot",o)}return t?.parentNode||document.createElement("div")}replaceDialogContent(t){this.dialog.querySelector(i.CSS_SELECTORS.DIALOG_CONTENT).replaceChildren(...t.childNodes)}applyInputValues(t,e,o,l){const{INPUT_KEYS:u}=i;u.forEach(s=>{const a=o.querySelector(`input[name="${t}[${s}]"]`);if(a){const n=l.querySelector(`input[name="${e}[${s}]"]`);n&&(n.value=a.value)}})}getEditLink(){const t=this.getAttribute("edit-url");return t?`
            <a class="uk-button uk-button-small document-edit-dialog__edit-link" href="${t}">
                ${window.RozierConfig?.messages?.documentEditDialogEdit}
            </a>
        `:""}showModal(){this.dialog?.showModal()}onButtonClick(t){(t.currentTarget.value==="cancel"||t.target.value==="submit")&&this.dialog.close(t.target.value)}onDialogClose(){const{INPUT_BASE_NAME:t}=i;this.dialog.returnValue==="submit"&&this.applyInputValues(t,this.getAttribute("input-base-name"),this.dialog,document),this.remove()}}export{i as default};
