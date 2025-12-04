var g={},N;function k(){if(N)return g;N=1,Object.defineProperty(g,"__esModule",{value:!0}),g.Printd=g.createIFrame=g.createLinkStyle=g.createStyle=void 0;var _=/^(((http[s]?)|file):)?(\/\/)+([0-9a-zA-Z-_.=?&].+)$/,t=/^((\.|\.\.)?\/)([0-9a-zA-Z-_.=?&]+\/)*([0-9a-zA-Z-_.=?&]+)$/,h=function(n){return _.test(n)||t.test(n)};function c(n,e){var i=n.createElement("style");return i.appendChild(n.createTextNode(e)),i}g.createStyle=c;function r(n,e){var i=n.createElement("link");return i.type="text/css",i.rel="stylesheet",i.href=e,i}g.createLinkStyle=r;function m(n){var e=window.document.createElement("iframe");return e.setAttribute("src","about:blank"),e.setAttribute("style","visibility:hidden;width:0;height:0;position:absolute;z-index:-9999;bottom:0;"),e.setAttribute("width","0"),e.setAttribute("height","0"),e.setAttribute("wmode","opaque"),n.appendChild(e),e}g.createIFrame=m;var p={parent:window.document.body,headElements:[],bodyElements:[]},d=(function(){function n(e){this.isLoading=!1,this.hasEvents=!1,this.opts=[p,e||{}].reduce(function(i,o){return Object.keys(o).forEach(function(s){return i[s]=o[s]}),i},{}),this.iframe=m(this.opts.parent)}return n.prototype.getIFrame=function(){return this.iframe},n.prototype.print=function(e,i,o,s){if(!this.isLoading){var u=this.iframe,E=u.contentDocument,b=u.contentWindow;if(!(!E||!b)&&(this.iframe.src="about:blank",this.elCopy=e.cloneNode(!0),!!this.elCopy)){this.isLoading=!0,this.callback=s;var l=b.document;l.open(),l.write('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body></body></html>'),this.addEvents();var x=this.opts,v=x.headElements,f=x.bodyElements;Array.isArray(v)&&v.forEach(function(a){return l.head.appendChild(a)}),Array.isArray(f)&&f.forEach(function(a){return l.body.appendChild(a)}),Array.isArray(i)&&i.forEach(function(a){a&&l.head.appendChild(h(a)?r(l,a):c(l,a))}),l.body.appendChild(this.elCopy),Array.isArray(o)&&o.forEach(function(a){if(a){var y=l.createElement("script");h(a)?y.src=a:y.innerText=a,l.body.appendChild(y)}}),l.close()}}},n.prototype.printURL=function(e,i){this.isLoading||(this.addEvents(),this.isLoading=!0,this.callback=i,this.iframe.src=e)},n.prototype.onBeforePrint=function(e){this.onbeforeprint=e},n.prototype.onAfterPrint=function(e){this.onafterprint=e},n.prototype.launchPrint=function(e){this.isLoading||e.print()},n.prototype.addEvents=function(){var e=this;if(!this.hasEvents){this.hasEvents=!0,this.iframe.addEventListener("load",function(){return e.onLoad()},!1);var i=this.iframe.contentWindow;i&&(this.onbeforeprint&&i.addEventListener("beforeprint",this.onbeforeprint),this.onafterprint&&i.addEventListener("afterprint",this.onafterprint))}},n.prototype.onLoad=function(){var e=this;if(this.iframe){this.isLoading=!1;var i=this.iframe,o=i.contentDocument,s=i.contentWindow;if(!o||!s)return;typeof this.callback=="function"?this.callback({iframe:this.iframe,element:this.elCopy,launchPrint:function(){return e.launchPrint(s)}}):this.launchPrint(s)}},n})();return g.Printd=d,g.default=d,g}var $=k();class w{static ticket(t){if(!t)return;const h=t.date||"",c=(t.time||"").substring(0,8),r=t.mesa||"MESA",m=t.pago||"EFECTIVO",p=t.numero||"",d=t.llamada||"",n=t.user?.name||"",e=t.comment||"",i=t.type||"INGRESO",o=t.detalles||t.details||[];let s="",u=0;o.forEach(f=>{const a=Number(f.qty||f.quantity||0),y=Number(f.price||0),A=Number(f.subtotal||a*y);u+=A,s+=`
        <tr>
          <td class="col-cant">${a}</td>
          <td class="col-detalle">${(f.name||f.product||"").toUpperCase()}</td>
          <td class="col-pu">${y.toFixed(0)}</td>
          <td class="col-total">${A.toFixed(0)}</td>
        </tr>`});const E=Number(t.total||u||0),b=`${window.location.origin}/chicken-logo.png`,l=`
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
            <span>${h}</span>
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
              ${s||'<tr><td colspan="4" style="text-align:center;">SIN DETALLE</td></tr>'}
            </tbody>
          </table>
          <div class="total-section">
            <div class="total-row">
              <span>TOTAL:</span>
              <span>${E.toFixed(2)}</span>
            </div>
            <div class="total-row">
              <span>Pago:</span>
              <span>${m}</span>
            </div>
          </div>
          <div class="ticket-line">
            TICKET ${p} <span class="mesa">${r}</span>
          </div>
          ${e?`<div class="pie" style="margin-top:4px;">${e}</div>`:""}
          <div class="box-firma"></div>
          ${i==="INGRESO"?'<div class="pie">GRACIAS POR SU COMPRA, BUEN PROVECHO</div>':""}
          <div class="usuario">
            Usuario: ${n}
          </div>
        </div>
      </div>
    `,x=w._getArea();x.innerHTML=l,new $.Printd().print(x)}static _getArea(){let t=document.getElementById("myelement");return t||(t=document.createElement("div"),t.id="myelement",t.style.position="fixed",t.style.left="-10000px",t.style.top="-10000px",document.body.appendChild(t)),t}static cierreCaja(t){if(!t)return;const h=t.date||"",c=t.user?.name||"",r=Number(t.total_ingresos||0),m=Number(t.total_egresos||0),p=Number(t.total_caja_inicial||0),d=Number(t.tickets||0),n=Number(t.monto_sistema||0),e=Number(t.monto_efectivo||0),i=Number(t.diferencia||0),o=t.observacion||"",s=Number(t.ingresos_efectivo??r),u=Number(t.ingresos_qr||0),E=Number(t.ingresos_tarjeta||0),b=Number(t.ingresos_online||0),x=`
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
      <div class="center">Usuario: ${c}</div>
      <hr>

      <!-- RESUMEN POR MÉTODO DE PAGO -->
      <div class="resumen-row"><span>Ing. EFECTIVO:</span><span>${s.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Ing. QR:</span><span>${u.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Ing. TARJETA:</span><span>${E.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Ing. ONLINE:</span><span>${b.toFixed(2)} Bs</span></div>
      <hr>

      <!-- RESUMEN PARA CUADRE DE CAJA (SOLO EFECTIVO) -->
      <div class="resumen-row"><span>Caja inicial:</span><span>${p.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Ingresos caja (efectivo):</span><span>${r.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Egresos:</span><span>${m.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Tickets:</span><span>${d}</span></div>
      <hr>
      <div class="resumen-row"><span>Sistema (solo efectivo):</span><span>${n.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Efectivo contado:</span><span>${e.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Diferencia:</span><span>${i.toFixed(2)} Bs</span></div>
      ${o?`<div class="pie" style="margin-top:4px;">Obs: ${o}</div>`:""}
      <hr>
      <div class="pie">Gracias por su trabajo</div>
      <div class="usuario">Firmado: ____________________</div>
    </div>
  `,v=w._getArea();v.innerHTML=x,new $.Printd().print(v)}static reporteUsuarios(t){const h=t?.usuarios||[],c=t?.date_from||"",r=t?.date_to||"",m=`${window.location.origin}/chicken-logo.png`;let p="",d=0;h.forEach(o=>{const s=Number(o.neto||0);d+=s,p+=`
      <tr>
        <td>${o.user_name}</td>
        <td class="num">${Number(o.total_ingresos||0).toFixed(2)}</td>
        <td class="num">${Number(o.total_egresos||0).toFixed(2)}</td>
        <!--td class="num">${Number(o.total_caja||0).toFixed(2)}</td!-->
        <td class="num">${s.toFixed(2)}</td>
        <td class="num">${Number(o.tickets||0)}</td>
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
        <img src="${m}" alt="Chicken's Garden">
      </div>
      <div class="center" style="font-weight:bold;">RESUMEN DE VENTAS POR USUARIO</div>
      <div class="center">Desde: ${c||"-"} Hasta: ${r||"-"}</div>
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
          ${p||'<tr><td colspan="6" class="center">Sin datos</td></tr>'}
        </tbody>
      </table>
      <div class="pie">Total neto: ${d.toFixed(2)} Bs</div>
    </div>
  `,e=w._getArea();e.innerHTML=n,new $.Printd().print(e)}static reporteProductosPorUsuario(t){const h=t?.productos||[],c=`${window.location.origin}/chicken-logo.png`;let r="";h.forEach(n=>{const e=n.user_name;let i="";n.items.forEach(o=>{i+=`
        <tr>
          <td>${o.name}</td>
          <td class="num">${Number(o.qty||0)}</td>
          <td class="num">${Number(o.subtotal||0).toFixed(2)}</td>
        </tr>
      `}),r+=`
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
    `});const m=`
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
      ${r||'<div class="center">Sin datos</div>'}
    </div>
  `,p=w._getArea();p.innerHTML=m,new $.Printd().print(p)}static reporteVentasPorUsuario(t){const h=t?.ventas||[],c=t?.usuarios&&t.usuarios[0]||null,r=`${window.location.origin}/chicken-logo.png`,m=t?.date_from||"",p=t?.date_to||"";let d="",n=0;h.forEach(s=>{const u=Number(s.total||0);n+=u,d+=`
        <tr>
          <td>${s.numero}</td>
          <td>${s.date}</td>
          <td>${String(s.time||"").substring(0,8)}</td>
          <td>${s.mesa}</td>
          <td>${s.pago}</td>
          <td class="num">${u.toFixed(2)}</td>
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
        <img src="${r}" alt="Chicken's Garden">
      </div>
      <div class="center titulo">VENTAS POR USUARIO</div>
      <div class="center">
        Usuario: ${c?c.user_name:""}
      </div>
      <div class="center">
        Desde: ${m||"-"} Hasta: ${p||"-"}
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
        Total ventas: ${n.toFixed(2)} Bs
      </div>
    </div>
  `,i=w._getArea();i.innerHTML=e,new $.Printd().print(i)}}export{w as I};
