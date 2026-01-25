import{R as s}from"./RoadizElement-XU473doh.js";class a extends s{constructor(){super(),this.entityClass=null,this.entityId=null,this.size="medium",this.loading=!0,this.error=null,this.thumbnailData=null,this.intersectionObserver=null,this.hasRequested=!1}connectedCallback(){this.entityClass=this.getAttribute("entity-class"),this.entityId=this.getAttribute("entity-id");const t=this.getAttribute("size");(t==="small"||t==="medium"||t==="large")&&(this.size=t),this.loading=!1,this.render(),this.intersectionObserver=new IntersectionObserver(i=>{for(const e of i)e.isIntersecting&&!this.hasRequested&&(this.hasRequested=!0,this.loading=!0,this.render(),this.fetchThumbnail(),this.intersectionObserver&&(this.intersectionObserver.disconnect(),this.intersectionObserver=null))},{root:null,threshold:.1}),this.intersectionObserver.observe(this)}disconnectedCallback(){this.intersectionObserver&&(this.intersectionObserver.disconnect(),this.intersectionObserver=null)}async fetchThumbnail(){if(!this.entityClass||!this.entityId){this.error="Missing entity-class or entity-id attribute",this.loading=!1,this.render();return}try{const t=new URLSearchParams({class:this.entityClass,id:this.entityId}),i=await fetch(`/rz-admin/ajax/entity-thumbnail?${t.toString()}`,{method:"GET",headers:{Accept:"application/json"},credentials:"same-origin"});if(!i.ok)throw new Error(`HTTP error! status: ${i.status}`);this.thumbnailData=await i.json(),this.loading=!1,this.render()}catch(t){this.error=t instanceof Error?t.message:"Unknown error",this.loading=!1,this.render()}}getSizeClass(){switch(this.size){case"small":return"rz-entity-thumbnail--small";case"large":return"rz-entity-thumbnail--large";default:return"rz-entity-thumbnail--medium"}}render(){const t=this.getSizeClass();if(this.loading){this.innerHTML=`
                <div class="rz-entity-thumbnail ${t} rz-entity-thumbnail--loading">
                    <div class="rz-entity-thumbnail__spinner"></div>
                </div>
            `;return}if(this.error){this.innerHTML=`
                <div class="rz-entity-thumbnail ${t} rz-entity-thumbnail--error">
                    <div class="rz-entity-thumbnail__placeholder">!</div>
                </div>
            `,this.setAttribute("title",this.error);return}if(!this.thumbnailData||!this.thumbnailData.url){this.innerHTML=`
                <div class="rz-entity-thumbnail ${t} rz-entity-thumbnail--empty">
                    <div class="rz-entity-thumbnail__placeholder"></div>
                </div>
            `,this.thumbnailData?.title&&this.setAttribute("title",this.thumbnailData.title);return}const i=this.thumbnailData.alt||"",e=this.thumbnailData.title||"";this.innerHTML=`
            <figure class="rz-entity-thumbnail ${t}">
                <img
                    data-uk-tooltip="{animation:true}"
                    class="uk-thumbnail rz-entity-thumbnail__image"
                    src="${this.thumbnailData.url}"
                    alt="${i}"
                    width="${this.thumbnailData.width||""}"
                    height="${this.thumbnailData.height||""}"
                    loading="lazy"
                />
            </figure>
        `,e&&this.setAttribute("title",e)}}export{a as default};
