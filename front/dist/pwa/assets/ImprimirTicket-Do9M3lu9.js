var u={},_;function S(){if(_)return u;_=1,Object.defineProperty(u,"__esModule",{value:!0}),u.Printd=u.createIFrame=u.createLinkStyle=u.createStyle=void 0;var A=/^(((http[s]?)|file):)?(\/\/)+([0-9a-zA-Z-_.=?&].+)$/,t=/^((\.|\.\.)?\/)([0-9a-zA-Z-_.=?&]+\/)*([0-9a-zA-Z-_.=?&]+)$/,p=function(s){return A.test(s)||t.test(s)};function c(s,e){var i=s.createElement("style");return i.appendChild(s.createTextNode(e)),i}u.createStyle=c;function l(s,e){var i=s.createElement("link");return i.type="text/css",i.rel="stylesheet",i.href=e,i}u.createLinkStyle=l;function r(s){var e=window.document.createElement("iframe");return e.setAttribute("src","about:blank"),e.setAttribute("style","visibility:hidden;width:0;height:0;position:absolute;z-index:-9999;bottom:0;"),e.setAttribute("width","0"),e.setAttribute("height","0"),e.setAttribute("wmode","opaque"),s.appendChild(e),e}u.createIFrame=r;var o={parent:window.document.body,headElements:[],bodyElements:[]},d=(function(){function s(e){this.isLoading=!1,this.hasEvents=!1,this.opts=[o,e||{}].reduce(function(i,n){return Object.keys(n).forEach(function(a){return i[a]=n[a]}),i},{}),this.iframe=r(this.opts.parent)}return s.prototype.getIFrame=function(){return this.iframe},s.prototype.print=function(e,i,n,a){if(!this.isLoading){var m=this.iframe,v=m.contentDocument,b=m.contentWindow;if(!(!v||!b)&&(this.iframe.src="about:blank",this.elCopy=e.cloneNode(!0),!!this.elCopy)){this.isLoading=!0,this.callback=a;var g=b.document;g.open(),g.write('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body></body></html>'),this.addEvents();var f=this.opts,y=f.headElements,x=f.bodyElements;Array.isArray(y)&&y.forEach(function(h){return g.head.appendChild(h)}),Array.isArray(x)&&x.forEach(function(h){return g.body.appendChild(h)}),Array.isArray(i)&&i.forEach(function(h){h&&g.head.appendChild(p(h)?l(g,h):c(g,h))}),g.body.appendChild(this.elCopy),Array.isArray(n)&&n.forEach(function(h){if(h){var $=g.createElement("script");p(h)?$.src=h:$.innerText=h,g.body.appendChild($)}}),g.close()}}},s.prototype.printURL=function(e,i){this.isLoading||(this.addEvents(),this.isLoading=!0,this.callback=i,this.iframe.src=e)},s.prototype.onBeforePrint=function(e){this.onbeforeprint=e},s.prototype.onAfterPrint=function(e){this.onafterprint=e},s.prototype.launchPrint=function(e){this.isLoading||e.print()},s.prototype.addEvents=function(){var e=this;if(!this.hasEvents){this.hasEvents=!0,this.iframe.addEventListener("load",function(){return e.onLoad()},!1);var i=this.iframe.contentWindow;i&&(this.onbeforeprint&&i.addEventListener("beforeprint",this.onbeforeprint),this.onafterprint&&i.addEventListener("afterprint",this.onafterprint))}},s.prototype.onLoad=function(){var e=this;if(this.iframe){this.isLoading=!1;var i=this.iframe,n=i.contentDocument,a=i.contentWindow;if(!n||!a)return;typeof this.callback=="function"?this.callback({iframe:this.iframe,element:this.elCopy,launchPrint:function(){return e.launchPrint(a)}}):this.launchPrint(a)}},s})();return u.Printd=d,u.default=d,u}var E=S();class w{static ticket(t){if(!t)return;const p=t.date||"",c=(t.time||"").substring(0,8),l=t.mesa||"MESA",r=t.pago||"EFECTIVO",o=t.numero||"",d=t.llamada||"",s=t.user?.name||"",e=t.comment||"",i=t.type||"INGRESO",n=t.detalles||t.details||[];let a="",m=0;n.forEach(x=>{const h=Number(x.qty||x.quantity||0),$=Number(x.price||0),N=Number(x.subtotal||h*$);m+=N,a+=`
        <tr>
          <td class="col-cant">${h}</td>
          <td class="col-detalle">${(x.name||x.product||"").toUpperCase()}</td>
          <td class="col-pu">${$.toFixed(0)}</td>
          <td class="col-total">${N.toFixed(0)}</td>
        </tr>`});const v=Number(t.total||m||0),b=`${window.location.origin}/chicken-logo.png`,g=`
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
          ${i==="INGRESO"&&d?`<div class="llamada-num">${d}</div>`:""}
          <div class="logo">
            <img src="${b}" alt="Chicken's Garden">
          </div>
          <div class="center nombre-local">CHICKEN'S GARDEN</div>
          <div class="center contacto">CONTACTOS: 77909517</div>
          <div class="center direccion">Mercado Campero - Calle 6 N° 21</div>
          ${i==="INGRESO"&&t.name&&t.name!=="SN"?`<div class="cliente-nombre">${t.name}</div>`:""}
          <div class="fecha-hora">
            <span>${p}</span>
            <span>${c}</span>
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
              <span>${v.toFixed(2)}</span>
            </div>
            <div class="total-row">
              <span>Pago:</span>
              <span>${r}</span>
            </div>
          </div>
          <div class="ticket-line">
            TICKET ${o} <span class="mesa">${l}</span>
          </div>
          <div class="box-firma">
          ${e?`<div style="margin-top:4px;">${e}</div>`:""}
</div>
          ${i==="INGRESO"?'<div class="pie">GRACIAS POR SU COMPRA, BUEN PROVECHO</div>':""}
          <div class="usuario">
            Usuario: ${s}
          </div>
        </div>
      </div>
    `,f=w._getArea();f.innerHTML=g,new E.Printd().print(f)}static _getArea(){let t=document.getElementById("myelement");return t||(t=document.createElement("div"),t.id="myelement",t.style.position="fixed",t.style.left="-10000px",t.style.top="-10000px",document.body.appendChild(t)),t}static cierreCaja(t){if(!t)return;const p=t.date||"",c=t.user?.name||"",l=Number(t.total_ingresos||0),r=Number(t.total_egresos||0),o=Number(t.total_caja_inicial||0),d=Number(t.tickets||0),s=Number(t.monto_sistema||0),e=Number(t.monto_efectivo||0),i=Number(t.monto_qr||0),n=Number(t.diferencia||0),a=t.observacion||"",m=Number(t.ingresos_efectivo??l),v=Number(t.ingresos_qr||0),b=Number(t.ingresos_tarjeta||0),g=Number(t.ingresos_online||0),f=Number(t.esperado_total??s+v),y=Number(t.contado_total??e+i),x=e-s,h=i-v,$=n||y-f,F=`
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
      <div class="center">Fecha: ${p}</div>
      <div class="center">Usuario cierre: ${c}</div>
      <hr>

      <!-- RESUMEN POR MÉTODO DE PAGO (SISTEMA) -->
      <div class="resumen-row"><span>Ing. EFECTIVO:</span><span>${m.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Ing. QR:</span><span>${v.toFixed(2)} Bs</span></div>
      <!--div class="resumen-row"><span>Ing. TARJETA:</span><span>${b.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Ing. ONLINE:</span><span>${g.toFixed(2)} Bs</span></div-->
      <hr>

      <!-- RESUMEN SISTEMA SOLO EFECTIVO -->
      <!--div class="resumen-row"><span>Caja inicial:</span><span>${o.toFixed(2)} Bs</span></div-->
      <div class="resumen-row"><span>Ingresos caja (efectivo):</span><span>${m.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Egresos:</span><span>${r.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Tickets:</span><span>${d}</span></div>
      <hr>
      <div class="resumen-row"><span>Sistema (efectivo):</span><span>${s.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Efectivo contado:</span><span>${e.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Dif. efectivo:</span><span>${x.toFixed(2)} Bs</span></div>
      <hr>

      <!-- QR -->
      <div class="resumen-row"><span>QR esperado:</span><span>${v.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>QR contado:</span><span>${i.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Dif. QR:</span><span>${h.toFixed(2)} Bs</span></div>
      <hr>

      <!-- TOTALES -->
      <div class="resumen-row"><span>Total esperado:</span><span>${f.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Total contado:</span><span>${y.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Diferencia total:</span><span>${$.toFixed(2)} Bs</span></div>

      ${a?`<div class="pie" style="margin-top:4px;">Obs: ${a}</div>`:""}
      <hr>
      <div class="pie">Gracias por su trabajo</div>
      <div class="usuario">Firmado: ____________________</div>
    </div>
  `,T=w._getArea();T.innerHTML=F,new E.Printd().print(T)}static reporteUsuarios(t){const p=t?.usuarios||[],c=t?.date_from||"",l=t?.date_to||"",r=`${window.location.origin}/chicken-logo.png`;let o="",d=0;p.forEach(n=>{const a=Number(n.neto||0);d+=a,o+=`
      <tr>
        <td>${n.user_name}</td>
        <td class="num">${Number(n.total_ingresos||0).toFixed(2)}</td>
        <td class="num">${Number(n.total_egresos||0).toFixed(2)}</td>
        <!--td class="num">${Number(n.total_caja||0).toFixed(2)}</td!-->
        <td class="num">${a.toFixed(2)}</td>
        <td class="num">${Number(n.tickets||0)}</td>
      </tr>
    `});const s=`
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
        <img src="${r}" alt="Chicken's Garden">
      </div>
      <div class="center" style="font-weight:bold;">RESUMEN DE VENTAS POR USUARIO</div>
      <div class="center">Desde: ${c||"-"} Hasta: ${l||"-"}</div>
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
      <div class="pie">Total neto: ${d.toFixed(2)} Bs</div>
    </div>
  `,e=w._getArea();e.innerHTML=s,new E.Printd().print(e)}static reporteProductosPorUsuario(t){const p=t?.productos||[],c=`${window.location.origin}/chicken-logo.png`;let l="";p.forEach(s=>{const e=s.user_name;let i="";s.items.forEach(n=>{i+=`
        <tr>
          <td>${n.name}</td>
          <td class="num">${Number(n.qty||0)}</td>
          <td class="num">${Number(n.subtotal||0).toFixed(2)}</td>
        </tr>
      `}),l+=`
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
    `});const r=`
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
        <img src="${c}" alt="Chicken's Garden">
      </div>
      <div class="center" style="font-weight:bold;">PRODUCTOS POR USUARIO</div>
      <hr>
      ${l||'<div class="center">Sin datos</div>'}
    </div>
  `,o=w._getArea();o.innerHTML=r,new E.Printd().print(o)}static reporteVentasPorUsuario(t){const p=t?.ventas||[],c=t?.usuarios&&t.usuarios[0]||null,l=`${window.location.origin}/chicken-logo.png`,r=t?.date_from||"",o=t?.date_to||"";let d="",s=0;p.forEach(a=>{const m=Number(a.total||0);s+=m,d+=`
        <tr>
          <td>${a.numero}</td>
          <td>${a.date}</td>
          <td>${String(a.time||"").substring(0,8)}</td>
          <td>${a.mesa}</td>
          <td>${a.pago}</td>
          <td class="num">${m.toFixed(2)}</td>
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
        <img src="${l}" alt="Chicken's Garden">
      </div>
      <div class="center titulo">VENTAS POR USUARIO</div>
      <div class="center">
        Usuario: ${c?c.user_name:""}
      </div>
      <div class="center">
        Desde: ${r||"-"} Hasta: ${o||"-"}
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
          ${d||'<tr><td colspan="6" class="center">Sin ventas</td></tr>'}
        </tbody>
      </table>
      <div class="center" style="margin-top:4px;">
        Total ventas: ${s.toFixed(2)} Bs
      </div>
    </div>
  `,i=w._getArea();i.innerHTML=e,new E.Printd().print(i)}static cierreCajaUsuario(t){if(!t)return;const p=`${window.location.origin}/chicken-logo.png`,c=t?.date||"",l=t?.user?.name||"Usuario",r=Number(t.ef_sistema??t.monto_sistema??0),o=Number(t.ef_contado??t.monto_efectivo??0),d=Number(t.dif_efectivo??o-r),s=Number(t.qr_sistema??t.ingresos_qr??0),e=Number(t.qr_contado??t.monto_qr??0),i=Number(t.dif_qr??e-s),n={efectivo:r,qr:s},a=f=>Number(f||0).toFixed(2),m=(f,y)=>`
    <div class="row">
      <span class="l">${f}</span>
      <span class="v">${y}</span>
    </div>
  `,v=`
    <div class="user-block">
      <div class="user-title">${l}</div>

      ${m("EF sistema:",`${a(r)} Bs`)}
      ${m("EF contado:",`${a(o)} Bs`)}
      <div class="row">
        <span class="l">Dif. EF:</span>
        <span class="v ${d<0?"neg":"pos"}">${a(d)} Bs</span>
      </div>

      <div class="sep"></div>

      ${m("QR sistema:",`${a(s)} Bs`)}
      ${m("QR contado:",`${a(e)} Bs`)}
      <div class="row">
        <span class="l">Dif. QR:</span>
        <span class="v ${i<0?"neg":"pos"}">${a(i)} Bs</span>
      </div>
    </div>
  `,b=`
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
      <div class="logo"><img src="${p}" alt="logo"></div>

      <div class="center title">CIERRE DE CAJA</div>
      <div class="center sub">Fecha: ${c}</div>

      <div class="dash"></div>

      ${m("TOTAL EFECTIVO:",`${a(n.efectivo)} Bs`)}
      ${m("TOTAL QR:",`${a(n.qr)} Bs`)}

      <div class="dash"></div>

      ${v}

      <div class="dash"></div>
      <div class="note">Diferencia = contado - sistema</div>
    </div>
  `,g=w._getArea();g.innerHTML=b,new E.Printd().print(g)}static reporteUltimoCierreUsuarios(t){const p=`${window.location.origin}/chicken-logo.png`,c=t?.date||"",l=t?.usuarios||[],r=t?.resumen||{},o=n=>Number(n||0).toFixed(2),d=(n,a)=>`
    <div class="row">
      <span class="l">${n}</span>
      <span class="v">${a}</span>
    </div>
  `;let s="";l.forEach(n=>{s+=`
      <div class="user-block">
        <div class="user-title">${n.user_name||""}</div>
        ${d("EF sistema:",`${o(n.ef_sistema)} Bs`)}
        ${d("EF contado:",`${o(n.ef_contado)} Bs`)}
        <div class="row">
          <span class="l">Dif. EF:</span>
          <span class="v ${Number(n.dif_efectivo)<0?"neg":"pos"}">${o(n.dif_efectivo)} Bs</span>
        </div>

        <div class="sep"></div>

        ${d("QR sistema:",`${o(n.qr_sistema)} Bs`)}
        ${d("QR contado:",`${o(n.qr_contado)} Bs`)}
        <div class="row">
          <span class="l">Dif. QR:</span>
          <span class="v ${Number(n.dif_qr)<0?"neg":"pos"}">${o(n.dif_qr)} Bs</span>
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
    <div class="logo"><img src="${p}" alt="logo"></div>
    <div class="center title">CIERRE DEL DÍA</div>
    <div class="center sub">Fecha: ${c}</div>

    <div class="dash"></div>

    ${d("TOTAL EFECTIVO:",`${o(r.efectivo)} Bs`)}
    ${d("TOTAL QR:",`${o(r.qr)} Bs`)}

    <div class="dash"></div>

    ${s||'<div class="center">Sin datos</div>'}

    <div class="note">Diferencia = contado - sistema</div>
  </div>
  `,i=w._getArea();i.innerHTML=e,new E.Printd().print(i)}static gasto(t){const p=`${t.date||""} ${String(t.time||"").substring(0,8)}`,c=Number(t.total||0).toLocaleString("es-BO",{minimumFractionDigits:2}),l=`
      <div style="font-family: Arial; font-size: 12px; width: 280px;">
        <div style="text-align:center; font-weight:700; font-size:14px;">GASTO</div>
        <div style="text-align:center; margin-top:4px;">Nro: <b>${t.numero??""}</b></div>
        <div style="text-align:center; margin-top:2px;">${p}</div>
        <hr/>
        <div><b>Descripción:</b> ${t.name||""}</div>
        ${t.comment?`<div style="margin-top:4px;"><b>Comentario:</b> ${t.comment}</div>`:""}
        <div style="margin-top:4px;"><b>Pago:</b> ${t.pago||"EFECTIVO"}</div>
        <hr/>
        <div style="display:flex; justify-content:space-between; font-size:14px; font-weight:700;">
          <div>TOTAL</div>
          <div>${c} Bs</div>
        </div>
        <div style="margin-top:6px; text-align:center; font-size:11px;">
          Usuario: ${t.user?.name||"—"}
        </div>
      </div>
    `,r=document.getElementById("myelement");r&&(r.innerHTML=l);const o=window.open("","PRINT","height=600,width=400");o.document.write(`<html><head><title>Gasto</title></head><body>${l}</body></html>`),o.document.close(),o.focus(),o.print(),o.close()}static ingreso(t){const p=`${t.date||""} ${String(t.time||"").substring(0,8)}`,c=Number(t.total||0).toLocaleString("es-BO",{minimumFractionDigits:2}),l=`
      <div style="font-family: Arial; font-size: 12px; width: 280px;">
        <div style="text-align:center; font-weight:700; font-size:14px;">INGRESO</div>
        <div style="text-align:center; margin-top:4px;">Nro: <b>${t.numero??""}</b></div>
        <div style="text-align:center; margin-top:2px;">${p}</div>
        <hr/>
        <div><b>Descripción:</b> ${t.name||""}</div>
        ${t.comment?`<div style="margin-top:4px;"><b>Comentario:</b> ${t.comment}</div>`:""}
        <div style="margin-top:4px;"><b>Pago:</b> ${t.pago||"EFECTIVO"}</div>
        <hr/>
        <div style="display:flex; justify-content:space-between; font-size:14px; font-weight:700;">
          <div>TOTAL</div>
          <div>${c} Bs</div>
        </div>
        <div style="margin-top:6px; text-align:center; font-size:11px;">
          Usuario: ${t.user?.name||"—"}
        </div>
      </div>
    `,r=document.getElementById("myelement");r&&(r.innerHTML=l);const o=window.open("","PRINT","height=600,width=400");o.document.write(`<html><head><title>Ingreso</title></head><body>${l}</body></html>`),o.document.close(),o.focus(),o.print(),o.close()}}export{w as I};
