var g={},T;function k(){if(T)return g;T=1,Object.defineProperty(g,"__esModule",{value:!0}),g.Printd=g.createIFrame=g.createLinkStyle=g.createStyle=void 0;var N=/^(((http[s]?)|file):)?(\/\/)+([0-9a-zA-Z-_.=?&].+)$/,t=/^((\.|\.\.)?\/)([0-9a-zA-Z-_.=?&]+\/)*([0-9a-zA-Z-_.=?&]+)$/,m=function(n){return N.test(n)||t.test(n)};function p(n,e){var i=n.createElement("style");return i.appendChild(n.createTextNode(e)),i}g.createStyle=p;function d(n,e){var i=n.createElement("link");return i.type="text/css",i.rel="stylesheet",i.href=e,i}g.createLinkStyle=d;function l(n){var e=window.document.createElement("iframe");return e.setAttribute("src","about:blank"),e.setAttribute("style","visibility:hidden;width:0;height:0;position:absolute;z-index:-9999;bottom:0;"),e.setAttribute("width","0"),e.setAttribute("height","0"),e.setAttribute("wmode","opaque"),n.appendChild(e),e}g.createIFrame=l;var o={parent:window.document.body,headElements:[],bodyElements:[]},r=(function(){function n(e){this.isLoading=!1,this.hasEvents=!1,this.opts=[o,e||{}].reduce(function(i,s){return Object.keys(s).forEach(function(a){return i[a]=s[a]}),i},{}),this.iframe=l(this.opts.parent)}return n.prototype.getIFrame=function(){return this.iframe},n.prototype.print=function(e,i,s,a){if(!this.isLoading){var f=this.iframe,x=f.contentDocument,w=f.contentWindow;if(!(!x||!w)&&(this.iframe.src="about:blank",this.elCopy=e.cloneNode(!0),!!this.elCopy)){this.isLoading=!0,this.callback=a;var h=w.document;h.open(),h.write('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body></body></html>'),this.addEvents();var b=this.opts,$=b.headElements,u=b.bodyElements;Array.isArray($)&&$.forEach(function(c){return h.head.appendChild(c)}),Array.isArray(u)&&u.forEach(function(c){return h.body.appendChild(c)}),Array.isArray(i)&&i.forEach(function(c){c&&h.head.appendChild(m(c)?d(h,c):p(h,c))}),h.body.appendChild(this.elCopy),Array.isArray(s)&&s.forEach(function(c){if(c){var v=h.createElement("script");m(c)?v.src=c:v.innerText=c,h.body.appendChild(v)}}),h.close()}}},n.prototype.printURL=function(e,i){this.isLoading||(this.addEvents(),this.isLoading=!0,this.callback=i,this.iframe.src=e)},n.prototype.onBeforePrint=function(e){this.onbeforeprint=e},n.prototype.onAfterPrint=function(e){this.onafterprint=e},n.prototype.launchPrint=function(e){this.isLoading||e.print()},n.prototype.addEvents=function(){var e=this;if(!this.hasEvents){this.hasEvents=!0,this.iframe.addEventListener("load",function(){return e.onLoad()},!1);var i=this.iframe.contentWindow;i&&(this.onbeforeprint&&i.addEventListener("beforeprint",this.onbeforeprint),this.onafterprint&&i.addEventListener("afterprint",this.onafterprint))}},n.prototype.onLoad=function(){var e=this;if(this.iframe){this.isLoading=!1;var i=this.iframe,s=i.contentDocument,a=i.contentWindow;if(!s||!a)return;typeof this.callback=="function"?this.callback({iframe:this.iframe,element:this.elCopy,launchPrint:function(){return e.launchPrint(a)}}):this.launchPrint(a)}},n})();return g.Printd=r,g.default=r,g}var E=k();class y{static ticket(t){if(!t)return;const m=t.date||"",p=(t.time||"").substring(0,8),d=t.mesa||"MESA",l=t.pago||"EFECTIVO",o=t.numero||"",r=t.llamada||"",n=t.user?.name||"",e=t.comment||"",i=t.type||"INGRESO",s=t.detalles||t.details||[];let a="",f=0;s.forEach(u=>{const c=Number(u.qty||u.quantity||0),v=Number(u.price||0),_=Number(u.subtotal||c*v);f+=_,a+=`
        <tr>
          <td class="col-cant">${c}</td>
          <td class="col-detalle">${(u.name||u.product||"").toUpperCase()}</td>
          <td class="col-pu">${v.toFixed(0)}</td>
          <td class="col-total">${_.toFixed(0)}</td>
        </tr>`});const x=Number(t.total||f||0),w=`${window.location.origin}/chicken-logo.png`,h=`
      <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; }
        .ticket-wrapper {
          width: 7.2cm;
          padding: 4px 6px;
          font-size: 11px;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .mt-4 { margin-top: 4px; }
        .mt-8 { margin-top: 8px; }
        .mb-4 { margin-bottom: 4px; }
        .logo img {
          max-width: 80px;
          display: block;
          margin: 0 auto 2px auto;
        }
        .nombre-local {
          font-size: 14px;
          font-weight: bold;
        }
        .contacto {
          font-size: 11px;
        }
        .direccion {
          font-size: 10px;
        }
        .fecha-hora {
          font-size: 11px;
          margin-top: 4px;
          margin-bottom: 4px;
          display: flex;
          justify-content: space-between;
        }
        hr {
          border: none;
          border-top: 1px dashed #000;
          margin: 4px 0;
        }
        table.items {
          width: 100%;
          border-collapse: collapse;
          margin-top: 4px;
        }
        table.items th,
        table.items td {
          border: 1px solid #000;
          padding: 2px 3px;
        }
        table.items th {
          font-size: 10px;
          text-align: center;
        }
        .col-cant { width: 16%; text-align: center; }
        .col-detalle { width: 44%; text-align: left; }
        .col-pu { width: 15%; text-align: right; }
        .col-total { width: 25%; text-align: right; }
        .total-section {
          margin-top: 6px;
          font-size: 12px;
        }
        .total-row {
          display: flex;
          justify-content: flex-end;
          margin-top: 2px;
        }
        .total-row span:first-child {
          margin-right: 4px;
          font-weight: bold;
        }
        .ticket-line {
          margin-top: 10px;
          text-align: center;
          font-size: 15px;
          font-weight: bold;
        }
        .ticket-line span.mesa {
          font-size: 18px;
          font-style: italic;
        }
        .box-firma {
          margin-top: 6px;
          width: 100%;
          height: 70px;
          border: 1px solid #000;
        }
        .pie {
          margin-top: 4px;
          text-align: center;
          font-size: 9px;
        }
        .usuario {
          margin-top: 2px;
          text-align: left;
          font-size: 9px;
          font-weight: bold;
        }
        .llamada-num {
          position: absolute;
          right: 6px;
          top: 4px;
          font-size: 22px;
          font-weight: bold;
        }
        .cliente-nombre {
          text-align: center;
          font-size: 14px;
          font-weight: bold;
          margin-top: 2px;
        }
      </style>

      <div class="ticket-wrapper">
        <div style="position:relative;">
          ${i==="INGRESO"&&r?`<div class="llamada-num">${r}</div>`:""}
          <div class="logo">
            <img src="${w}" alt="Chicken's Garden">
          </div>
          <div class="center nombre-local">CHICKEN'S GARDEN</div>
          <div class="center contacto">CONTACTOS: 77909517</div>
          <div class="center direccion">Mercado Campero - Calle 6 N° 21</div>
          ${i==="INGRESO"&&t.name&&t.name!=="SN"?`<div class="cliente-nombre">${t.name}</div>`:""}
          <div class="fecha-hora">
            <span>${m}</span>
            <span>${p}</span>
          </div>
          <hr>
          <table class="items">
            <thead>
              <tr>
                <th>CANT</th>
                <th>DETALLE</th>
                <th>P/U</th>
                <th>TOTAL</th>
              </tr>
            </thead>
            <tbody>
              ${a||'<tr><td colspan="4" style="text-align:center;">SIN DETALLE</td></tr>'}
            </tbody>
          </table>
          <div class="total-section">
            <div class="total-row">
              <span>TOTAL:</span>
              <span>${x.toFixed(2)}</span>
            </div>
            <div class="total-row">
              <span>Pago:</span>
              <span>${l}</span>
            </div>
          </div>
          <div class="ticket-line">
            TICKET ${o} <span class="mesa">${d}</span>
          </div>
          <div class="box-firma">
          ${e?`<div style="margin-top:4px;">${e}</div>`:""}
</div>
          ${i==="INGRESO"?'<div class="pie">GRACIAS POR SU COMPRA, BUEN PROVECHO</div>':""}
          <div class="usuario">
            Usuario: ${n}
          </div>
        </div>
      </div>
    `,b=y._getArea();b.innerHTML=h,new E.Printd().print(b)}static _getArea(){let t=document.getElementById("myelement");return t||(t=document.createElement("div"),t.id="myelement",t.style.position="fixed",t.style.left="-10000px",t.style.top="-10000px",document.body.appendChild(t)),t}static cierreCaja(t){if(!t)return;const m=t.date||"",p=t.user?.name||"",d=Number(t.total_ingresos||0),l=Number(t.total_egresos||0),o=Number(t.total_caja_inicial||0),r=Number(t.tickets||0),n=Number(t.monto_sistema||0),e=Number(t.monto_efectivo||0),i=Number(t.monto_qr||0),s=Number(t.diferencia||0),a=t.observacion||"",f=Number(t.ingresos_efectivo??d),x=Number(t.ingresos_qr||0),w=Number(t.ingresos_tarjeta||0),h=Number(t.ingresos_online||0),b=Number(t.esperado_total??n+x),$=Number(t.contado_total??e+i),u=e-n,c=i-x,v=s||$-b,S=`
    <style>
      * { box-sizing: border-box; margin: 0; padding: 0; }
      body { font-family: Arial, sans-serif; }
      .ticket-wrapper {
        width: 7.2cm;
        padding: 4px 6px;
        font-size: 11px;
      }
      .center { text-align: center; }
      .bold { font-weight: bold; }
      .logo img {
        max-width: 80px;
        display: block;
        margin: 0 auto 2px auto;
      }
      hr { border: none; border-top: 1px dashed #000; margin: 4px 0; }
      .titulo {
        font-size: 14px;
        font-weight: bold;
        text-align: center;
        margin-top: 4px;
      }
      .resumen-row {
        display: flex;
        justify-content: space-between;
        margin-top: 2px;
      }
      .resumen-row span:first-child {
        font-weight: bold;
      }
      .pie {
        margin-top: 6px;
        font-size: 9px;
        text-align: center;
      }
      .usuario {
        margin-top: 2px;
        font-size: 9px;
        font-weight: bold;
      }
    </style>

    <div class="ticket-wrapper">
      <div class="logo">
        <img src="${`${window.location.origin}/chicken-logo.png`}" alt="Chicken's Garden">
      </div>
      <div class="titulo">CIERRE DE CAJA</div>
      <div class="center">Fecha: ${m}</div>
      <div class="center">Usuario cierre: ${p}</div>
      <hr>

      <!-- RESUMEN POR MÉTODO DE PAGO (SISTEMA) -->
      <div class="resumen-row"><span>Ing. EFECTIVO:</span><span>${f.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Ing. QR:</span><span>${x.toFixed(2)} Bs</span></div>
      <!--div class="resumen-row"><span>Ing. TARJETA:</span><span>${w.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Ing. ONLINE:</span><span>${h.toFixed(2)} Bs</span></div-->
      <hr>

      <!-- RESUMEN SISTEMA SOLO EFECTIVO -->
      <!--div class="resumen-row"><span>Caja inicial:</span><span>${o.toFixed(2)} Bs</span></div-->
      <div class="resumen-row"><span>Ingresos caja (efectivo):</span><span>${f.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Egresos:</span><span>${l.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Tickets:</span><span>${r}</span></div>
      <hr>
      <div class="resumen-row"><span>Sistema (efectivo):</span><span>${n.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Efectivo contado:</span><span>${e.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Dif. efectivo:</span><span>${u.toFixed(2)} Bs</span></div>
      <hr>

      <!-- QR -->
      <div class="resumen-row"><span>QR esperado:</span><span>${x.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>QR contado:</span><span>${i.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Dif. QR:</span><span>${c.toFixed(2)} Bs</span></div>
      <hr>

      <!-- TOTALES -->
      <div class="resumen-row"><span>Total esperado:</span><span>${b.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Total contado:</span><span>${$.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Diferencia total:</span><span>${v.toFixed(2)} Bs</span></div>

      ${a?`<div class="pie" style="margin-top:4px;">Obs: ${a}</div>`:""}
      <hr>
      <div class="pie">Gracias por su trabajo</div>
      <div class="usuario">Firmado: ____________________</div>
    </div>
  `,A=y._getArea();A.innerHTML=S,new E.Printd().print(A)}static reporteUsuarios(t){const m=t?.usuarios||[],p=t?.date_from||"",d=t?.date_to||"",l=`${window.location.origin}/chicken-logo.png`;let o="",r=0;m.forEach(s=>{const a=Number(s.neto||0);r+=a,o+=`
      <tr>
        <td>${s.user_name}</td>
        <td class="num">${Number(s.total_ingresos||0).toFixed(2)}</td>
        <td class="num">${Number(s.total_egresos||0).toFixed(2)}</td>
        <!--td class="num">${Number(s.total_caja||0).toFixed(2)}</td!-->
        <td class="num">${a.toFixed(2)}</td>
        <td class="num">${Number(s.tickets||0)}</td>
      </tr>
    `});const n=`
    <style>
      * { box-sizing: border-box; margin: 0; padding: 0; }
      body { font-family: Arial, sans-serif; }
      .ticket-wrapper {
        width: 8cm;
        padding: 4px 6px;
        font-size: 10px;
      }
      .center { text-align: center; }
      .logo img { max-width: 70px; display:block; margin:0 auto 2px auto; }
      hr { border: none; border-top: 1px dashed #000; margin: 4px 0; }
      table { width: 100%; border-collapse: collapse; margin-top: 4px; }
      th, td {
        border: 1px solid #000;
        padding: 2px 3px;
      }
      th { font-size: 9px; }
      .num { text-align: right; }
      .pie { margin-top: 4px; text-align:center; font-size:9px; }
    </style>

    <div class="ticket-wrapper">
      <div class="logo">
        <img src="${l}" alt="Chicken's Garden">
      </div>
      <div class="center" style="font-weight:bold;">RESUMEN DE VENTAS POR USUARIO</div>
      <div class="center">Desde: ${p||"-"} Hasta: ${d||"-"}</div>
      <hr>
      <table>
        <thead>
          <tr>
            <th>Usuario</th>
            <th>Ingreso</th>
            <th>Egreso</th>
            <!--th>Caja</th!-->
            <th>Neto</th>
            <th>Tickets</th>
          </tr>
        </thead>
        <tbody>
          ${o||'<tr><td colspan="6" class="center">Sin datos</td></tr>'}
        </tbody>
      </table>
      <div class="pie">Total neto: ${r.toFixed(2)} Bs</div>
    </div>
  `,e=y._getArea();e.innerHTML=n,new E.Printd().print(e)}static reporteProductosPorUsuario(t){const m=t?.productos||[],p=`${window.location.origin}/chicken-logo.png`;let d="";m.forEach(n=>{const e=n.user_name;let i="";n.items.forEach(s=>{i+=`
        <tr>
          <td>${s.name}</td>
          <td class="num">${Number(s.qty||0)}</td>
          <td class="num">${Number(s.subtotal||0).toFixed(2)}</td>
        </tr>
      `}),d+=`
      <div class="user-block">
        <div class="user-title">Usuario: ${e}</div>
        <table>
          <thead>
            <tr>
              <th>Producto</th>
              <th>Cant.</th>
              <th>Total Bs</th>
            </tr>
          </thead>
          <tbody>
            ${i||'<tr><td colspan="3">Sin productos</td></tr>'}
          </tbody>
        </table>
      </div>
      <hr>
    `});const l=`
    <style>
      * { box-sizing: border-box; margin: 0; padding: 0; }
      body { font-family: Arial, sans-serif; }
      .ticket-wrapper {
        width: 8cm;
        padding: 4px 6px;
        font-size: 10px;
      }
      .center { text-align: center; }
      .logo img { max-width: 70px; display:block; margin:0 auto 2px auto; }
      hr { border: none; border-top: 1px dashed #000; margin: 4px 0; }
      table { width: 100%; border-collapse: collapse; margin-top: 3px; }
      th, td {
        border: 1px solid #000;
        padding: 2px 3px;
      }
      th { font-size: 9px; }
      .num { text-align: right; }
      .user-title { font-weight:bold; margin-top:4px; }
    </style>

    <div class="ticket-wrapper">
      <div class="logo">
        <img src="${p}" alt="Chicken's Garden">
      </div>
      <div class="center" style="font-weight:bold;">PRODUCTOS POR USUARIO</div>
      <hr>
      ${d||'<div class="center">Sin datos</div>'}
    </div>
  `,o=y._getArea();o.innerHTML=l,new E.Printd().print(o)}static reporteVentasPorUsuario(t){const m=t?.ventas||[],p=t?.usuarios&&t.usuarios[0]||null,d=`${window.location.origin}/chicken-logo.png`,l=t?.date_from||"",o=t?.date_to||"";let r="",n=0;m.forEach(a=>{const f=Number(a.total||0);n+=f,r+=`
        <tr>
          <td>${a.numero}</td>
          <td>${a.date}</td>
          <td>${String(a.time||"").substring(0,8)}</td>
          <td>${a.mesa}</td>
          <td>${a.pago}</td>
          <td class="num">${f.toFixed(2)}</td>
        </tr>
      `});const e=`
    <style>
      * { box-sizing: border-box; margin: 0; padding: 0; }
      body { font-family: Arial, sans-serif; }
      .ticket-wrapper {
        width: 8cm;
        padding: 4px 6px;
        font-size: 10px;
      }
      .center { text-align: center; }
      .logo img { max-width: 70px; display:block; margin:0 auto 2px auto; }
      hr { border: none; border-top: 1px dashed #000; margin: 4px 0; }
      table { width: 100%; border-collapse: collapse; margin-top: 3px; }
      th, td {
        border: 1px solid #000;
        padding: 2px 3px;
      }
      th { font-size: 9px; }
      .num { text-align: right; }
      .titulo { font-weight:bold; margin-top:2px; }
    </style>

    <div class="ticket-wrapper">
      <div class="logo">
        <img src="${d}" alt="Chicken's Garden">
      </div>
      <div class="center titulo">VENTAS POR USUARIO</div>
      <div class="center">
        Usuario: ${p?p.user_name:""}
      </div>
      <div class="center">
        Desde: ${l||"-"} Hasta: ${o||"-"}
      </div>
      <hr>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Mesa</th>
            <th>Pago</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          ${r||'<tr><td colspan="6" class="center">Sin ventas</td></tr>'}
        </tbody>
      </table>
      <div class="center" style="margin-top:4px;">
        Total ventas: ${n.toFixed(2)} Bs
      </div>
    </div>
  `,i=y._getArea();i.innerHTML=e,new E.Printd().print(i)}static reporteUltimoCierreUsuarios(t){const m=`${window.location.origin}/chicken-logo.png`,p=t?.date||"",d=t?.usuarios||[],l=t?.resumen||{},o=s=>Number(s||0).toFixed(2),r=(s,a)=>`
    <div class="row">
      <span class="l">${s}</span>
      <span class="v">${a}</span>
    </div>
  `;let n="";d.forEach(s=>{n+=`
      <div class="user-block">
        <div class="user-title">${s.user_name||""}</div>
        ${r("EF sistema:",`${o(s.ef_sistema)} Bs`)}
        ${r("EF contado:",`${o(s.ef_contado)} Bs`)}
        <div class="row">
          <span class="l">Dif. EF:</span>
          <span class="v ${Number(s.dif_efectivo)<0?"neg":"pos"}">${o(s.dif_efectivo)} Bs</span>
        </div>

        <div class="sep"></div>

        ${r("QR sistema:",`${o(s.qr_sistema)} Bs`)}
        ${r("QR contado:",`${o(s.qr_contado)} Bs`)}
        <div class="row">
          <span class="l">Dif. QR:</span>
          <span class="v ${Number(s.dif_qr)<0?"neg":"pos"}">${o(s.dif_qr)} Bs</span>
        </div>
      </div>
      <div class="dash"></div>
    `});const e=`
  <style>
    *{ box-sizing:border-box; margin:0; padding:0; }
    body{ font-family: Arial, sans-serif; font-size:11px; }
    .ticket{ width:7.2cm; padding:4px 6px; }
    .center{ text-align:center; }
    .logo img{ max-width:80px; display:block; margin:0 auto 3px auto; }
    .title{ font-size:14px; font-weight:bold; }
    .sub{ font-size:10px; }
    .dash{ border-top:1px dashed #000; margin:6px 0; }
    .sep{ border-top:1px solid #000; margin:4px 0; opacity:.25; }
    .row{ display:flex; justify-content:space-between; margin-top:2px; }
    .l{ font-weight:bold; }
    .v{ text-align:right; }
    .user-title{ font-size:12px; font-weight:bold; text-align:center; margin-top:4px; }
    .neg{ color:red; font-weight:bold; }
    .pos{ color:green; font-weight:bold; }
    .note{ margin-top:4px; font-size:9px; text-align:center; }
  </style>

  <div class="ticket">
    <div class="logo"><img src="${m}" alt="logo"></div>
    <div class="center title">CIERRE DEL DÍA</div>
    <div class="center sub">Fecha: ${p}</div>

    <div class="dash"></div>

    ${r("TOTAL EFECTIVO:",`${o(l.efectivo)} Bs`)}
    ${r("TOTAL QR:",`${o(l.qr)} Bs`)}

    <div class="dash"></div>

    ${n||'<div class="center">Sin datos</div>'}

    <div class="note">Diferencia = contado - sistema</div>
  </div>
  `,i=y._getArea();i.innerHTML=e,new E.Printd().print(i)}static gasto(t){const m=`${t.date||""} ${String(t.time||"").substring(0,8)}`,p=Number(t.total||0).toLocaleString("es-BO",{minimumFractionDigits:2}),d=`
      <div style="font-family: Arial; font-size: 12px; width: 280px;">
        <div style="text-align:center; font-weight:700; font-size:14px;">GASTO</div>
        <div style="text-align:center; margin-top:4px;">Nro: <b>${t.numero??""}</b></div>
        <div style="text-align:center; margin-top:2px;">${m}</div>
        <hr/>
        <div><b>Descripción:</b> ${t.name||""}</div>
        ${t.comment?`<div style="margin-top:4px;"><b>Comentario:</b> ${t.comment}</div>`:""}
        <div style="margin-top:4px;"><b>Pago:</b> ${t.pago||"EFECTIVO"}</div>
        <hr/>
        <div style="display:flex; justify-content:space-between; font-size:14px; font-weight:700;">
          <div>TOTAL</div>
          <div>${p} Bs</div>
        </div>
        <div style="margin-top:6px; text-align:center; font-size:11px;">
          Usuario: ${t.user?.name||"—"}
        </div>
      </div>
    `,l=document.getElementById("myelement");l&&(l.innerHTML=d);const o=window.open("","PRINT","height=600,width=400");o.document.write(`<html><head><title>Gasto</title></head><body>${d}</body></html>`),o.document.close(),o.focus(),o.print(),o.close()}}export{y as I};
