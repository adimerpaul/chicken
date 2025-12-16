var g={},N;function S(){if(N)return g;N=1,Object.defineProperty(g,"__esModule",{value:!0}),g.Printd=g.createIFrame=g.createLinkStyle=g.createStyle=void 0;var T=/^(((http[s]?)|file):)?(\/\/)+([0-9a-zA-Z-_.=?&].+)$/,t=/^((\.|\.\.)?\/)([0-9a-zA-Z-_.=?&]+\/)*([0-9a-zA-Z-_.=?&]+)$/,h=function(n){return T.test(n)||t.test(n)};function l(n,e){var s=n.createElement("style");return s.appendChild(n.createTextNode(e)),s}g.createStyle=l;function c(n,e){var s=n.createElement("link");return s.type="text/css",s.rel="stylesheet",s.href=e,s}g.createLinkStyle=c;function p(n){var e=window.document.createElement("iframe");return e.setAttribute("src","about:blank"),e.setAttribute("style","visibility:hidden;width:0;height:0;position:absolute;z-index:-9999;bottom:0;"),e.setAttribute("width","0"),e.setAttribute("height","0"),e.setAttribute("wmode","opaque"),n.appendChild(e),e}g.createIFrame=p;var a={parent:window.document.body,headElements:[],bodyElements:[]},r=(function(){function n(e){this.isLoading=!1,this.hasEvents=!1,this.opts=[a,e||{}].reduce(function(s,i){return Object.keys(i).forEach(function(o){return s[o]=i[o]}),s},{}),this.iframe=p(this.opts.parent)}return n.prototype.getIFrame=function(){return this.iframe},n.prototype.print=function(e,s,i,o){if(!this.isLoading){var f=this.iframe,x=f.contentDocument,w=f.contentWindow;if(!(!x||!w)&&(this.iframe.src="about:blank",this.elCopy=e.cloneNode(!0),!!this.elCopy)){this.isLoading=!0,this.callback=o;var m=w.document;m.open(),m.write('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body></body></html>'),this.addEvents();var b=this.opts,$=b.headElements,u=b.bodyElements;Array.isArray($)&&$.forEach(function(d){return m.head.appendChild(d)}),Array.isArray(u)&&u.forEach(function(d){return m.body.appendChild(d)}),Array.isArray(s)&&s.forEach(function(d){d&&m.head.appendChild(h(d)?c(m,d):l(m,d))}),m.body.appendChild(this.elCopy),Array.isArray(i)&&i.forEach(function(d){if(d){var v=m.createElement("script");h(d)?v.src=d:v.innerText=d,m.body.appendChild(v)}}),m.close()}}},n.prototype.printURL=function(e,s){this.isLoading||(this.addEvents(),this.isLoading=!0,this.callback=s,this.iframe.src=e)},n.prototype.onBeforePrint=function(e){this.onbeforeprint=e},n.prototype.onAfterPrint=function(e){this.onafterprint=e},n.prototype.launchPrint=function(e){this.isLoading||e.print()},n.prototype.addEvents=function(){var e=this;if(!this.hasEvents){this.hasEvents=!0,this.iframe.addEventListener("load",function(){return e.onLoad()},!1);var s=this.iframe.contentWindow;s&&(this.onbeforeprint&&s.addEventListener("beforeprint",this.onbeforeprint),this.onafterprint&&s.addEventListener("afterprint",this.onafterprint))}},n.prototype.onLoad=function(){var e=this;if(this.iframe){this.isLoading=!1;var s=this.iframe,i=s.contentDocument,o=s.contentWindow;if(!i||!o)return;typeof this.callback=="function"?this.callback({iframe:this.iframe,element:this.elCopy,launchPrint:function(){return e.launchPrint(o)}}):this.launchPrint(o)}},n})();return g.Printd=r,g.default=r,g}var E=S();class y{static ticket(t){if(!t)return;const h=t.date||"",l=(t.time||"").substring(0,8),c=t.mesa||"MESA",p=t.pago||"EFECTIVO",a=t.numero||"",r=t.llamada||"",n=t.user?.name||"",e=t.comment||"",s=t.type||"INGRESO",i=t.detalles||t.details||[];let o="",f=0;i.forEach(u=>{const d=Number(u.qty||u.quantity||0),v=Number(u.price||0),_=Number(u.subtotal||d*v);f+=_,o+=`
        <tr>
          <td class="col-cant">${d}</td>
          <td class="col-detalle">${(u.name||u.product||"").toUpperCase()}</td>
          <td class="col-pu">${v.toFixed(0)}</td>
          <td class="col-total">${_.toFixed(0)}</td>
        </tr>`});const x=Number(t.total||f||0),w=`${window.location.origin}/chicken-logo.png`,m=`
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
          ${s==="INGRESO"&&r?`<div class="llamada-num">${r}</div>`:""}
          <div class="logo">
            <img src="${w}" alt="Chicken's Garden">
          </div>
          <div class="center nombre-local">CHICKEN'S GARDEN</div>
          <div class="center contacto">CONTACTOS: 77909517</div>
          <div class="center direccion">Mercado Campero - Calle 6 N° 21</div>
          ${s==="INGRESO"&&t.name&&t.name!=="SN"?`<div class="cliente-nombre">${t.name}</div>`:""}
          <div class="fecha-hora">
            <span>${h}</span>
            <span>${l}</span>
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
              ${o||'<tr><td colspan="4" style="text-align:center;">SIN DETALLE</td></tr>'}
            </tbody>
          </table>
          <div class="total-section">
            <div class="total-row">
              <span>TOTAL:</span>
              <span>${x.toFixed(2)}</span>
            </div>
            <div class="total-row">
              <span>Pago:</span>
              <span>${p}</span>
            </div>
          </div>
          <div class="ticket-line">
            TICKET ${a} <span class="mesa">${c}</span>
          </div>
          <div class="box-firma">
          ${e?`<div style="margin-top:4px;">${e}</div>`:""}
</div>
          ${s==="INGRESO"?'<div class="pie">GRACIAS POR SU COMPRA, BUEN PROVECHO</div>':""}
          <div class="usuario">
            Usuario: ${n}
          </div>
        </div>
      </div>
    `,b=y._getArea();b.innerHTML=m,new E.Printd().print(b)}static _getArea(){let t=document.getElementById("myelement");return t||(t=document.createElement("div"),t.id="myelement",t.style.position="fixed",t.style.left="-10000px",t.style.top="-10000px",document.body.appendChild(t)),t}static cierreCaja(t){if(!t)return;const h=t.date||"",l=t.user?.name||"",c=Number(t.total_ingresos||0),p=Number(t.total_egresos||0),a=Number(t.total_caja_inicial||0),r=Number(t.tickets||0),n=Number(t.monto_sistema||0),e=Number(t.monto_efectivo||0),s=Number(t.monto_qr||0),i=Number(t.diferencia||0),o=t.observacion||"",f=Number(t.ingresos_efectivo??c),x=Number(t.ingresos_qr||0),w=Number(t.ingresos_tarjeta||0),m=Number(t.ingresos_online||0),b=Number(t.esperado_total??n+x),$=Number(t.contado_total??e+s),u=e-n,d=s-x,v=i||$-b,k=`
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
      <div class="center">Fecha: ${h}</div>
      <div class="center">Usuario cierre: ${l}</div>
      <hr>

      <!-- RESUMEN POR MÉTODO DE PAGO (SISTEMA) -->
      <div class="resumen-row"><span>Ing. EFECTIVO:</span><span>${f.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Ing. QR:</span><span>${x.toFixed(2)} Bs</span></div>
      <!--div class="resumen-row"><span>Ing. TARJETA:</span><span>${w.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Ing. ONLINE:</span><span>${m.toFixed(2)} Bs</span></div-->
      <hr>

      <!-- RESUMEN SISTEMA SOLO EFECTIVO -->
      <!--div class="resumen-row"><span>Caja inicial:</span><span>${a.toFixed(2)} Bs</span></div-->
      <div class="resumen-row"><span>Ingresos caja (efectivo):</span><span>${f.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Egresos:</span><span>${p.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Tickets:</span><span>${r}</span></div>
      <hr>
      <div class="resumen-row"><span>Sistema (efectivo):</span><span>${n.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Efectivo contado:</span><span>${e.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Dif. efectivo:</span><span>${u.toFixed(2)} Bs</span></div>
      <hr>

      <!-- QR -->
      <div class="resumen-row"><span>QR esperado:</span><span>${x.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>QR contado:</span><span>${s.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Dif. QR:</span><span>${d.toFixed(2)} Bs</span></div>
      <hr>

      <!-- TOTALES -->
      <div class="resumen-row"><span>Total esperado:</span><span>${b.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Total contado:</span><span>${$.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Diferencia total:</span><span>${v.toFixed(2)} Bs</span></div>

      ${o?`<div class="pie" style="margin-top:4px;">Obs: ${o}</div>`:""}
      <hr>
      <div class="pie">Gracias por su trabajo</div>
      <div class="usuario">Firmado: ____________________</div>
    </div>
  `,A=y._getArea();A.innerHTML=k,new E.Printd().print(A)}static reporteUsuarios(t){const h=t?.usuarios||[],l=t?.date_from||"",c=t?.date_to||"",p=`${window.location.origin}/chicken-logo.png`;let a="",r=0;h.forEach(i=>{const o=Number(i.neto||0);r+=o,a+=`
      <tr>
        <td>${i.user_name}</td>
        <td class="num">${Number(i.total_ingresos||0).toFixed(2)}</td>
        <td class="num">${Number(i.total_egresos||0).toFixed(2)}</td>
        <!--td class="num">${Number(i.total_caja||0).toFixed(2)}</td!-->
        <td class="num">${o.toFixed(2)}</td>
        <td class="num">${Number(i.tickets||0)}</td>
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
        <img src="${p}" alt="Chicken's Garden">
      </div>
      <div class="center" style="font-weight:bold;">RESUMEN DE VENTAS POR USUARIO</div>
      <div class="center">Desde: ${l||"-"} Hasta: ${c||"-"}</div>
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
          ${a||'<tr><td colspan="6" class="center">Sin datos</td></tr>'}
        </tbody>
      </table>
      <div class="pie">Total neto: ${r.toFixed(2)} Bs</div>
    </div>
  `,e=y._getArea();e.innerHTML=n,new E.Printd().print(e)}static reporteProductosPorUsuario(t){const h=t?.productos||[],l=`${window.location.origin}/chicken-logo.png`;let c="";h.forEach(n=>{const e=n.user_name;let s="";n.items.forEach(i=>{s+=`
        <tr>
          <td>${i.name}</td>
          <td class="num">${Number(i.qty||0)}</td>
          <td class="num">${Number(i.subtotal||0).toFixed(2)}</td>
        </tr>
      `}),c+=`
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
            ${s||'<tr><td colspan="3">Sin productos</td></tr>'}
          </tbody>
        </table>
      </div>
      <hr>
    `});const p=`
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
        <img src="${l}" alt="Chicken's Garden">
      </div>
      <div class="center" style="font-weight:bold;">PRODUCTOS POR USUARIO</div>
      <hr>
      ${c||'<div class="center">Sin datos</div>'}
    </div>
  `,a=y._getArea();a.innerHTML=p,new E.Printd().print(a)}static reporteVentasPorUsuario(t){const h=t?.ventas||[],l=t?.usuarios&&t.usuarios[0]||null,c=`${window.location.origin}/chicken-logo.png`,p=t?.date_from||"",a=t?.date_to||"";let r="",n=0;h.forEach(o=>{const f=Number(o.total||0);n+=f,r+=`
        <tr>
          <td>${o.numero}</td>
          <td>${o.date}</td>
          <td>${String(o.time||"").substring(0,8)}</td>
          <td>${o.mesa}</td>
          <td>${o.pago}</td>
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
        <img src="${c}" alt="Chicken's Garden">
      </div>
      <div class="center titulo">VENTAS POR USUARIO</div>
      <div class="center">
        Usuario: ${l?l.user_name:""}
      </div>
      <div class="center">
        Desde: ${p||"-"} Hasta: ${a||"-"}
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
  `,s=y._getArea();s.innerHTML=e,new E.Printd().print(s)}static reporteUltimoCierreUsuarios(t){const h=`${window.location.origin}/chicken-logo.png`,l=t?.date||"",c=t?.usuarios||[],p=t?.resumen||{},a=i=>Number(i||0).toFixed(2),r=(i,o)=>`
    <div class="row">
      <span class="l">${i}</span>
      <span class="v">${o}</span>
    </div>
  `;let n="";c.forEach(i=>{n+=`
      <div class="user-block">
        <div class="user-title">${i.user_name||""}</div>
        ${r("EF sistema:",`${a(i.ef_sistema)} Bs`)}
        ${r("EF contado:",`${a(i.ef_contado)} Bs`)}
        <div class="row">
          <span class="l">Dif. EF:</span>
          <span class="v ${Number(i.dif_efectivo)<0?"neg":"pos"}">${a(i.dif_efectivo)} Bs</span>
        </div>

        <div class="sep"></div>

        ${r("QR sistema:",`${a(i.qr_sistema)} Bs`)}
        ${r("QR contado:",`${a(i.qr_contado)} Bs`)}
        <div class="row">
          <span class="l">Dif. QR:</span>
          <span class="v ${Number(i.dif_qr)<0?"neg":"pos"}">${a(i.dif_qr)} Bs</span>
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
    <div class="logo"><img src="${h}" alt="logo"></div>
    <div class="center title">CIERRE DEL DÍA</div>
    <div class="center sub">Fecha: ${l}</div>

    <div class="dash"></div>

    ${r("TOTAL EFECTIVO:",`${a(p.efectivo)} Bs`)}
    ${r("TOTAL QR:",`${a(p.qr)} Bs`)}

    <div class="dash"></div>

    ${n||'<div class="center">Sin datos</div>'}

    <div class="note">Diferencia = contado - sistema</div>
  </div>
  `,s=y._getArea();s.innerHTML=e,new E.Printd().print(s)}}export{y as I};
