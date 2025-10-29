var p=Object.defineProperty;var g=(s,a,t)=>a in s?p(s,a,{enumerable:!0,configurable:!0,writable:!0,value:t}):s[a]=t;var l=(s,a,t)=>g(s,typeof a!="symbol"?a+"":a,t);import{R as m}from"./RoadizElement-XU473doh.js";const o=class o extends m{constructor(){super(...arguments);l(this,"dialog",null)}connectedCallback(){this.createDialog(),this.setupEventListeners(),this.appendToDOM(),this.loadTemplate(),this.hasAttribute("open")&&this.dialog.showModal()}createDialog(){this.dialog=document.createElement("dialog"),this.dialog.setAttribute("closedby","any"),this.dialog.classList.add("uk-form","document-edit-dialog"),this.dialog.innerHTML=this.getDialogHTML()}getDialogHTML(){var i,u,d,n;const{INPUT_BASE_NAME:t,INPUT_KEYS:e}=o;return`
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
            ${e.map(c=>`<input type="hidden" name="${t}[${c}]">`).join(`
`)}
            <footer>
                <div class="document-edit-dialog__actions">
                    ${this.getEditLink()}
                    <button class="uk-button uk-button-small document-edit-dialog__cancel" type="button" value="cancel">
                        ${(u=(i=window.RozierConfig)==null?void 0:i.messages)==null?void 0:u.documentEditDialogCancel}
                    </button>
                    <button class="uk-button uk-button-small document-edit-dialog__submit" type="button" value="submit">
                        ${(n=(d=window.RozierConfig)==null?void 0:d.messages)==null?void 0:n.documentEditDialogSubmit}
                    </button>
                </div>
            </footer>
        `}setupEventListeners(){var i;const{CSS_SELECTORS:t}=o,e=(i=this.dialog)==null?void 0:i.querySelectorAll(`${t.DIALOG_CLOSE}, ${t.DIALOG_ACTIONS}`);e&&this.listen(e,"click",this.onButtonClick),this.listen(this.dialog,"close",this.onDialogClose)}appendToDOM(){this.appendChild(this.dialog)}loadTemplate(){const t=this.getAttribute("template-path");if(t)return this.fetchTemplate(t).then(e=>this.parseTemplate(e)).then(e=>this.setupWidget(e)).then(e=>this.replaceDialogContent(e)).catch(e=>{console.error("DocumentEditDialog: Failed to load template:",e)})}async fetchTemplate(t){const e=await fetch(t,{headers:{"X-Requested-With":"XMLHttpRequest"}});if(!e.ok)throw new Error("Network response was not ok");return e.text()}parseTemplate(t){const e=document.createElement("div");return e.innerHTML=t,e.querySelector(o.CSS_SELECTORS.ALIGNMENT_WIDGET)}setupWidget(t){const{INPUT_BASE_NAME:e}=o;if(t){this.applyInputValues(this.getAttribute("input-base-name"),e,document,this.dialog),t.setAttribute("image-path",this.getAttribute("image-path")),t.setAttribute("image-width",this.getAttribute("image-width")),t.setAttribute("image-height",this.getAttribute("image-height")),t.setAttribute("input-base-name",e),t.setAttribute("hotspot-overridable",!0);const i=this.getAttribute("original-hotspot");i&&t.setAttribute("original-hotspot",i)}return(t==null?void 0:t.parentNode)||document.createElement("div")}replaceDialogContent(t){this.dialog.querySelector(o.CSS_SELECTORS.DIALOG_CONTENT).replaceChildren(...t.childNodes)}applyInputValues(t,e,i,u){const{INPUT_KEYS:d}=o;d.forEach(n=>{const c=i.querySelector(`input[name="${t}[${n}]"]`);if(c){const r=u.querySelector(`input[name="${e}[${n}]"]`);r&&(r.value=c.value)}})}getEditLink(){var e,i;const t=this.getAttribute("edit-url");return t?`
            <a class="uk-button uk-button-small document-edit-dialog__edit-link" href="${t}">
                ${(i=(e=window.RozierConfig)==null?void 0:e.messages)==null?void 0:i.documentEditDialogEdit}
            </a>
        `:""}showModal(){var t;(t=this.dialog)==null||t.showModal()}onButtonClick(t){(t.currentTarget.value==="cancel"||t.target.value==="submit")&&this.dialog.close(t.target.value)}onDialogClose(){const{INPUT_BASE_NAME:t}=o;this.dialog.returnValue==="submit"&&this.applyInputValues(t,this.getAttribute("input-base-name"),this.dialog,document),this.remove()}};l(o,"INPUT_BASE_NAME","document_edit_dialog[proxy]"),l(o,"INPUT_KEYS",["imageCropAlignment","hotspot"]),l(o,"CSS_SELECTORS",{DIALOG_CLOSE:".document-edit-dialog__close",DIALOG_ACTIONS:".document-edit-dialog__actions > button",DIALOG_CONTENT:".document-edit-dialog__content",ALIGNMENT_WIDGET:"document-alignment-widget"});let h=o;export{h as default};
