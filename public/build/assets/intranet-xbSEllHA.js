class r{constructor(e="table"){this.table=typeof e=="string"?document.querySelector(e):e,this.table&&this.setupSearchInput()}setupSearchInput(){const e=this.table.closest(".container")||document.body;if(!e.querySelector(".table-search-input")){const n=document.createElement("div");n.className="intranet-search-slot",n.innerHTML=`
                <div class="form-group">
                    <label for="table-search">Rechercher</label>
                <input 
                    type="text" 
                    id="table-search" 
                    class="table-search-input" 
                    placeholder="Tapez pour filtrer les résultats..."
                >
                </div>
            `;const a=this.table.previousElementSibling||this.table;a.parentNode.insertBefore(n,a)}e.querySelector(".table-search-input").addEventListener("input",n=>this.filter(n.target.value))}filter(e){const t=this.table.querySelectorAll("tbody tr"),o=e.toLowerCase();t.forEach(n=>{const a=n.textContent.toLowerCase();n.style.display=a.includes(o)?"":"none"})}}class s{constructor(){this.setupModal()}setupModal(){const e=document.createElement("div");e.id="confirmModal",e.className="confirm-modal",e.innerHTML=`
            <div class="confirm-modal-content">
                <h3 id="confirmTitle">Confirmation</h3>
                <p id="confirmMessage"></p>
                <div class="confirm-modal-actions">
                    <button class="button secondary" id="confirmCancel">Annuler</button>
                    <button class="button primary" id="confirmOk">Confirmer</button>
                </div>
            </div>
        `,document.body.appendChild(e);const t=document.createElement("style");t.textContent=`
            .confirm-modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1000;
                justify-content: center;
                align-items: center;
            }

            .confirm-modal.active {
                display: flex;
                animation: fadeIn 0.2s ease;
            }

            .confirm-modal-content {
                background: var(--color-bg-primary);
                border-radius: 1rem;
                padding: 2rem;
                max-width: 400px;
                box-shadow: 0 20px 45px rgba(15, 23, 42, 0.3);
                animation: slideUp 0.3s ease;
            }

            .confirm-modal-content h3 {
                margin-top: 0;
                color: var(--color-text-primary);
            }

            .confirm-modal-content p {
                color: var(--color-text-secondary);
                margin: 1rem 0 1.5rem 0;
            }

            .confirm-modal-actions {
                display: flex;
                gap: 1rem;
                justify-content: flex-end;
            }
        `,document.head.appendChild(t),document.getElementById("confirmCancel").addEventListener("click",()=>{this.reject()}),document.getElementById("confirmOk").addEventListener("click",()=>{this.resolve()}),e.addEventListener("click",o=>{o.target===e&&this.reject()})}show(e="Confirmation",t="Êtes-vous sûr ?"){return new Promise((o,n)=>{this.resolve=()=>{this.hide(),o(!0)},this.reject=()=>{this.hide(),n(!1)},document.getElementById("confirmTitle").textContent=e,document.getElementById("confirmMessage").textContent=t,document.getElementById("confirmModal").classList.add("active")})}hide(){document.getElementById("confirmModal").classList.remove("active")}}class c{constructor(){this.images=document.querySelectorAll("img[data-src]"),this.init()}init(){if("IntersectionObserver"in window){const e=new IntersectionObserver(t=>{t.forEach(o=>{o.isIntersecting&&(this.loadImage(o.target),e.unobserve(o.target))})});this.images.forEach(t=>e.observe(t))}else this.images.forEach(e=>this.loadImage(e))}loadImage(e){const t=e.getAttribute("data-src");t&&(e.src=t,e.removeAttribute("data-src"))}}function l(i){document.querySelectorAll("[data-confirm]").forEach(e=>{e.addEventListener("click",async t=>{var n;t.preventDefault();const o=e.getAttribute("data-confirm");try{await i.show("Confirmation",o),e.tagName==="FORM"?e.submit():e.tagName==="A"?window.location.href=e.href:e.tagName==="BUTTON"&&((n=e.form)==null||n.submit())}catch{}})})}document.addEventListener("DOMContentLoaded",()=>{document.querySelectorAll("table").forEach(t=>new r(t));const e=new s;l(e),new c});
