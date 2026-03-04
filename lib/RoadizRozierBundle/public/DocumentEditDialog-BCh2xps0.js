import{R as c}from"./RoadizElement-XU473doh.js";import{r as o}from"./rzButton-J_yTZFMr.js";class s extends c{static INPUT_BASE_NAME="document_edit_dialog[proxy]";static INPUT_KEYS=["imageCropAlignment","hotspot"];static CSS_SELECTORS={DIALOG_CLOSE:".rz-dialog__close",DIALOG_ACTIONS:".rz-dialog__footer > button",DIALOG_CONTENT:".rz-dialog__content",ALIGNMENT_WIDGET:"document-alignment-widget"};dialog=null;connectedCallback(){this.createDialog(),this.setupEventListeners(),this.appendToDOM(),this.loadTemplate(),this.hasAttribute("open")&&this.dialog.showModal()}createDialog(){this.dialog=document.createElement("dialog",{is:"rz-dialog"}),this.dialog.classList.add("rz-dialog"),this.dialog.setAttribute("closedby","any"),this.dialog.innerHTML=this.getDialogHTML()}getBodyStyle(){let t="";const e=this.getAttribute("image-width");e&&(t+=`min-width: min(${e}px, 90vw);`);const i=this.getAttribute("image-height");return i&&(t+=`min-height: min(${i}px, 90vh);`),t}getDialogHTML(){const{INPUT_BASE_NAME:t,INPUT_KEYS:e}=s;return`
            <header class="rz-dialog__header">
                <span class="rz-dialog__icon rz-icon-ri--edit-line"></span>
                <h1 class="rz-dialog__title">${this.getAttribute("title")}</h1>
                ${o({iconClass:"rz-icon-ri--close-line",emphasis:"tertiary",attributes:{class:"rz-dialog__close",type:"button",value:"cancel",closetarget:"","aria-label":window.RozierConfig?.messages?.close}}).outerHTML}
            </header>
            <div class="rz-dialog__body" style="${this.getBodyStyle()}">
                <div class="rz-dialog__content">
                    <div class="rz-spinner rz-spinner--lg"></div>
                </div>
            </div>
            ${e.map(i=>`<input type="hidden" name="${t}[${i}]">`).join(`
`)}
            <footer class="rz-dialog__footer">
                ${this.getEditLink()}
                ${o({iconClass:"rz-icon-ri--close-line",label:window.RozierConfig?.messages?.documentEditDialogCancel,attributes:{type:"button",value:"cancel"}}).outerHTML}
                ${o({label:window.RozierConfig?.messages?.documentEditDialogSubmit,emphasis:"primary",iconClass:"rz-icon-ri--check-line",attributes:{type:"button",value:"submit"}}).outerHTML}
            </footer>
        `}getEditLink(){const t=this.getAttribute("edit-url");return t?`
            <a is="rz-link" href="${t}" class="rz-dialog__push-right">
                ${window.RozierConfig?.messages?.documentEditDialogEdit}
            </a>
        `:""}setupEventListeners(){const{CSS_SELECTORS:t}=s,e=this.dialog?.querySelectorAll(`${t.DIALOG_CLOSE}, ${t.DIALOG_ACTIONS}`);e&&this.listen(e,"click",this.onButtonClick),this.listen(this.dialog,"close",this.onDialogClose)}appendToDOM(){this.appendChild(this.dialog)}loadTemplate(){const t=this.getAttribute("template-path");if(t)return this.fetchTemplate(t).then(e=>this.parseTemplate(e)).then(e=>this.setupWidget(e)).then(e=>this.replaceDialogContent(e)).catch(e=>{console.error("DocumentEditDialog: Failed to load template:",e)})}async fetchTemplate(t){const e=await fetch(t,{headers:{"X-Requested-With":"XMLHttpRequest"}});if(!e.ok)throw new Error("Network response was not ok");return e.text()}parseTemplate(t){const e=document.createElement("div");return e.innerHTML=t,e.querySelector(s.CSS_SELECTORS.ALIGNMENT_WIDGET)}setupWidget(t){const{INPUT_BASE_NAME:e}=s;if(t){this.applyInputValues(this.getAttribute("input-base-name"),e,document,this.dialog),t.setAttribute("image-path",this.getAttribute("image-path")),t.setAttribute("image-width",this.getAttribute("image-width")),t.setAttribute("image-height",this.getAttribute("image-height")),t.setAttribute("input-base-name",e),t.setAttribute("hotspot-overridable",!0);const i=this.getAttribute("original-hotspot");i&&t.setAttribute("original-hotspot",i)}return t?.parentNode||document.createElement("div")}replaceDialogContent(t){this.dialog.querySelector(s.CSS_SELECTORS.DIALOG_CONTENT).replaceChildren(...t.childNodes)}applyInputValues(t,e,i,l){const{INPUT_KEYS:h}=s;h.forEach(a=>{const n=i.querySelector(`input[name="${t}[${a}]"]`);if(n){const r=l.querySelector(`input[name="${e}[${a}]"]`);r&&(r.value=n.value)}})}showModal(){this.dialog?.showModal()}onButtonClick(t){const e=t.currentTarget.value||t.target.value;(e==="cancel"||e==="submit")&&this.dialog.close(e)}onDialogClose(){const{INPUT_BASE_NAME:t}=s;this.dialog.returnValue==="submit"&&this.applyInputValues(t,this.getAttribute("input-base-name"),this.dialog,document),this.remove()}}export{s as default};
