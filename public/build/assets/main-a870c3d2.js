import{s as d}from"./index-dd82ac63.js";const n={button:document.getElementById("notificationButton"),count:document.getElementById("notificationCount"),countHeader:document.getElementById("notificationCountHeader"),list:document.getElementById("notificationList"),markAllBtn:document.getElementById("markAllAsRead"),scrollContainer:null};let i={currentPage:1,isLoading:!1,hasMore:!0,totalCount:0};function p(t){n.count.textContent=t,n.countHeader.textContent=t,n.count.style.display=t>0?"block":"none"}function g(){const t=document.getElementById("loadingMoreIndicator");t&&t.remove()}function b(){const t=document.createElement("li");t.className="list-group-item text-center text-muted",t.id="loadingMoreIndicator",t.innerHTML=`
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Cargando más...</span>
            </div>
        `,n.list.appendChild(t)}function C(){n.list.innerHTML=`
        <li class="list-group-item text-center text-muted">
            <div class="spinner-border spinner-border-sm mb-2" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <div>Cargando notificaciones...</div>
        </li>
    `}function m(t,e=!1){const{count:a,notifications:r}=t;if(n.count.textContent=a,n.countHeader.textContent=a,n.count.style.display=a>0?"block":"none",e||(n.list.innerHTML=""),r.length===0&&!e){n.list.innerHTML=`
            <li class="list-group-item text-center text-muted">
                <i class="fi fi-rr-bell fs-3 d-block mb-2"></i>
                No hay notificaciones
            </li>
        `;return}if(r.forEach(o=>{const s=h(o.type_object),c=document.createElement("li");c.className="list-group-item d-flex justify-content-between align-items-start position-relative notification-item",c.dataset.id=o.id,c.dataset.type=o.type_object,c.dataset.objectId=o.object_id,c.innerHTML=`
            <div class="avatar avatar-xs ${s.bgClass} rounded-circle text-white">
                <i class="${s.icon}"></i>
            </div>
            <div class="flex-grow-1 ms-2">
                <h6 class="mb-1">${escapeHtml(o.name)}</h6>
                <small class="text-body d-block">${escapeHtml(o.description||"")}</small>
                <small class="text-muted">${escapeHtml(o.time_ago)}</small>
            </div>
            <button class="btn btn-sm btn-link text-muted p-0 mark-as-read"
                    data-id="${o.id}"
                    title="Marcar como leída">
                <i class="bi bi-x-lg"></i>
            </button>
        `,n.list.appendChild(c)}),!i.hasMore&&e&&r.length>0){const o=document.createElement("li");o.className="list-group-item text-center text-muted small",o.innerHTML=`
            <i class="fi fi-rr-check-circle"></i> No hay más notificaciones
        `,n.list.appendChild(o)}}function h(t){return{ORDEN_TRABAJO:{icon:"fi fi-rr-tool-box",bgClass:"bg-primary"},COTIZACION:{icon:"fi fi-rr-calculator",bgClass:"bg-info"},VENTA:{icon:"fi fi-rr-shopping-cart",bgClass:"bg-success"},PRODUCCION:{icon:"fi fi-rr-settings",bgClass:"bg-warning"},COMPRA:{icon:"fi fi-rr-shopping-bag",bgClass:"bg-secondary"}}[t]||{icon:"fi fi-rr-bell",bgClass:"bg-dark"}}function y(){n.list.innerHTML=`
        <li class="list-group-item text-center text-danger">
            <i class="fi fi-rr-cross-circle fs-3 d-block mb-2"></i>
            Error al cargar notificaciones
        </li>
    `}const u={count:"notifications.count",index:"notifications.index",notificationIndex:"tenant.consultas.notificaciones.index",notified:"notifications.notified"};async function E(){try{const{data:t}=await axios.get(d(u.count));return t.count}catch(t){return toastr.error(t,"ERROR EN LA PETICIÓN OBTENER CANTIDAD DE NOTIFICACIONES"),null}}async function f(t){try{const{data:e}=await axios.get(d(u.index),{params:{page:t}});return e}catch(e){return toastr.error(e,"ERROR EN LA PETICIÓN OBTENER CANTIDAD DE NOTIFICACIONES"),null}}async function L(t){try{const e=new FormData;e.append("alert_id",t);const a=await axios.post(d(u.notified),e);return a.data.success?a.data:(toastr.error(a.data.message,"ERROR AL MARCAR NOTIFICADO"),null)}catch(e){return toastr.error(e,"ERROR EN LA PETICIÓN MARCAR NOTIFICADO"),null}}async function N(){i.currentPage=1,i.hasMore=!0,C();try{console.log("🔔 Cargando notificaciones del servidor...");const t=await f(1);console.log("✅ Notificaciones recibidas:",t),i.hasMore=t.has_more,i.totalCount=t.count,m(t,!1)}catch(t){console.error("❌ Error al cargar notificaciones:",t),y()}}function I(){const t=document.querySelector("[data-simplebar]")||document.querySelector(".simplebar-content-wrapper")||document.querySelector('.p-2[style*="height"]');if(!t){console.error("❌ No se encontró el contenedor de scroll");return}n.scrollContainer=t,console.log("✅ Contenedor de scroll encontrado:",t),t.classList.contains("simplebar-content-wrapper")?(n.scrollContainer.addEventListener("scroll",l),console.log("📜 Listener agregado a SimpleBar")):t.hasAttribute("data-simplebar")?setTimeout(()=>{const e=t.querySelector(".simplebar-content-wrapper");e?(n.scrollContainer=e,e.addEventListener("scroll",l),console.log("📜 Listener agregado a SimpleBar (con delay)")):(t.addEventListener("scroll",l),console.log("📜 Listener agregado al contenedor directo"))},500):(t.addEventListener("scroll",l),console.log("📜 Listener agregado (scroll normal)"))}async function x(){try{const t=E();p(t)}catch(t){console.error("Error al actualizar contador:",t)}}function l(t){const e=t.target,a=e.scrollTop,r=e.scrollHeight,o=e.clientHeight,s=(a+o)/r;console.log("Scroll:",{scrollTop:a.toFixed(0),scrollHeight:r.toFixed(0),clientHeight:o.toFixed(0),porcentaje:(s*100).toFixed(2)+"%",hasMore:i.hasMore,isLoading:i.isLoading}),s>.8&&i.hasMore&&!i.isLoading&&(console.log("🚀 Activando carga de más notificaciones..."),A())}async function A(){if(!(i.isLoading||!i.hasMore)){i.isLoading=!0,i.currentPage++,b();try{const t=await f(i.currentPage);console.log(`✅ Página ${i.currentPage} cargada:`,t),i.hasMore=t.has_more,g(),m(t,!0)}catch(t){console.error("❌ Error al cargar más notificaciones:",t),g()}finally{i.isLoading=!1}}}async function v(t){await L(t)}window.markAsNotified=v;function T(){var t,e,a;(t=n.button)==null||t.addEventListener("click",()=>{N()}),(e=n.markAllBtn)==null||e.addEventListener("click",markAllAsRead),(a=n.list)==null||a.addEventListener("click",r=>{if(r.target.closest(".mark-as-read")){r.stopPropagation();const s=r.target.closest(".mark-as-read");markAsRead(s.dataset.id);return}const o=r.target.closest(".notification-item");o&&navigateToNotification(o.dataset.type,o.dataset.objectId)})}document.addEventListener("DOMContentLoaded",()=>{x(),I(),T()});
