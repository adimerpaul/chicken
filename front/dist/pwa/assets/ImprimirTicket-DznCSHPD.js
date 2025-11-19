var d={},_;function k(){if(_)return d;_=1,Object.defineProperty(d,"__esModule",{value:!0}),d.Printd=d.createIFrame=d.createLinkStyle=d.createStyle=void 0;var N=/^(((http[s]?)|file):)?(\/\/)+([0-9a-zA-Z-_.=?&].+)$/,t=/^((\.|\.\.)?\/)([0-9a-zA-Z-_.=?&]+\/)*([0-9a-zA-Z-_.=?&]+)$/,h=function(o){return N.test(o)||t.test(o)};function m(o,e){var i=o.createElement("style");return i.appendChild(o.createTextNode(e)),i}d.createStyle=m;function l(o,e){var i=o.createElement("link");return i.type="text/css",i.rel="stylesheet",i.href=e,i}d.createLinkStyle=l;function f(o){var e=window.document.createElement("iframe");return e.setAttribute("src","about:blank"),e.setAttribute("style","visibility:hidden;width:0;height:0;position:absolute;z-index:-9999;bottom:0;"),e.setAttribute("width","0"),e.setAttribute("height","0"),e.setAttribute("wmode","opaque"),o.appendChild(e),e}d.createIFrame=f;var c={parent:window.document.body,headElements:[],bodyElements:[]},p=(function(){function o(e){this.isLoading=!1,this.hasEvents=!1,this.opts=[c,e||{}].reduce(function(i,n){return Object.keys(n).forEach(function(s){return i[s]=n[s]}),i},{}),this.iframe=f(this.opts.parent)}return o.prototype.getIFrame=function(){return this.iframe},o.prototype.print=function(e,i,n,s){if(!this.isLoading){var g=this.iframe,x=g.contentDocument,b=g.contentWindow;if(!(!x||!b)&&(this.iframe.src="about:blank",this.elCopy=e.cloneNode(!0),!!this.elCopy)){this.isLoading=!0,this.callback=s;var r=b.document;r.open(),r.write('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body></body></html>'),this.addEvents();var y=this.opts,$=y.headElements,u=y.bodyElements;Array.isArray($)&&$.forEach(function(a){return r.head.appendChild(a)}),Array.isArray(u)&&u.forEach(function(a){return r.body.appendChild(a)}),Array.isArray(i)&&i.forEach(function(a){a&&r.head.appendChild(h(a)?l(r,a):m(r,a))}),r.body.appendChild(this.elCopy),Array.isArray(n)&&n.forEach(function(a){if(a){var v=r.createElement("script");h(a)?v.src=a:v.innerText=a,r.body.appendChild(v)}}),r.close()}}},o.prototype.printURL=function(e,i){this.isLoading||(this.addEvents(),this.isLoading=!0,this.callback=i,this.iframe.src=e)},o.prototype.onBeforePrint=function(e){this.onbeforeprint=e},o.prototype.onAfterPrint=function(e){this.onafterprint=e},o.prototype.launchPrint=function(e){this.isLoading||e.print()},o.prototype.addEvents=function(){var e=this;if(!this.hasEvents){this.hasEvents=!0,this.iframe.addEventListener("load",function(){return e.onLoad()},!1);var i=this.iframe.contentWindow;i&&(this.onbeforeprint&&i.addEventListener("beforeprint",this.onbeforeprint),this.onafterprint&&i.addEventListener("afterprint",this.onafterprint))}},o.prototype.onLoad=function(){var e=this;if(this.iframe){this.isLoading=!1;var i=this.iframe,n=i.contentDocument,s=i.contentWindow;if(!n||!s)return;typeof this.callback=="function"?this.callback({iframe:this.iframe,element:this.elCopy,launchPrint:function(){return e.launchPrint(s)}}):this.launchPrint(s)}},o})();return d.Printd=p,d.default=p,d}var E=k();class w{static ticket(t){if(!t)return;const h=t.date||"",m=(t.time||"").substring(0,8),l=t.mesa||"MESA",f=t.pago||"EFECTIVO",c=t.numero||"",p=t.llamada||"",o=t.user?.name||"",e=t.comment||"",i=t.type||"INGRESO",n=t.detalles||t.details||[];let s="",g=0;n.forEach(u=>{const a=Number(u.qty||u.quantity||0),v=Number(u.price||0),A=Number(u.subtotal||a*v);g+=A,s+=`
        <tr>
          <td class="col-cant">${a}</td>
          <td class="col-detalle">${(u.name||u.product||"").toUpperCase()}</td>
          <td class="col-pu">${v.toFixed(0)}</td>
          <td class="col-total">${A.toFixed(0)}</td>
        </tr>`});const x=Number(t.total||g||0),b=`${window.location.origin}/chicken-logo.png`;console.log("Logo src:",b);const r=`
      <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
          font-family: Arial, sans-serif;
        }
        .ticket-wrapper {
          width: 7.2cm; /* POS-80 aprox */
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
          ${i==="INGRESO"&&p?`<div class="llamada-num">${p}</div>`:""}

          <!-- Logo + nombre local -->
          <div class="logo">
            <img src="${b}" alt="Chicken's Garden">
          </div>

          <div class="center nombre-local">CHICKEN'S GARDEN</div>
          <div class="center contacto">CONTACTOS: 77909517</div>
          <div class="center direccion">
            Mercado Campero - Calle 6 N° 21
          </div>

          ${i==="INGRESO"&&t.name&&t.name!=="SN"?`<div class="cliente-nombre">${t.name}</div>`:""}

          <div class="fecha-hora">
            <span>${h}</span>
            <span>${m}</span>
          </div>

          <hr>

          <!-- Tabla items -->
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

          <!-- Total y forma de pago -->
          <div class="total-section">
            <div class="total-row">
              <span>TOTAL:</span>
              <span>${x.toFixed(2)}</span>
            </div>
            <div class="total-row">
              <span>Pago:</span>
              <span>${f}</span>
            </div>
          </div>

          <div class="ticket-line">
            TICKET ${c} <span class="mesa">${l}</span>
          </div>

          ${e?`<div class="pie" style="margin-top:4px;">${e}</div>`:""}

          <div class="box-firma"></div>

          ${i==="INGRESO"?'<div class="pie">GRACIAS POR SU COMPRA, BUEN PROVECHO</div>':""}

          <div class="usuario">
            Usuario: ${o}
          </div>
        </div>
      </div>
    `,y=w._getArea();y.innerHTML=r,new E.Printd().print(y)}static _getArea(){let t=document.getElementById("myelement");return t||(t=document.createElement("div"),t.id="myelement",t.style.position="fixed",t.style.left="-10000px",t.style.top="-10000px",document.body.appendChild(t)),t}static cierreCaja(t){if(!t)return;const h=t.date||"",m=t.user?.name||"",l=Number(t.total_ingresos||0),f=Number(t.total_egresos||0),c=Number(t.total_caja_inicial||0),p=Number(t.tickets||0),o=Number(t.monto_sistema||0),e=Number(t.monto_efectivo||0),i=Number(t.diferencia||0),n=t.observacion||"",g=`
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
      <div class="center">Usuario: ${m}</div>
      <hr>

      <div class="resumen-row"><span>Ingresos:</span><span>${l.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Egresos:</span><span>${f.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Caja inicial:</span><span>${c.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Tickets:</span><span>${p}</span></div>
      <hr>
      <div class="resumen-row"><span>Sistema:</span><span>${o.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Efectivo contado:</span><span>${e.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Diferencia:</span><span>${i.toFixed(2)} Bs</span></div>

      ${n?`<div class="pie" style="margin-top:4px;">Obs: ${n}</div>`:""}

      <hr>
      <div class="pie">Gracias por su trabajo</div>
      <div class="usuario">Firmado: ____________________</div>
    </div>
  `,x=w._getArea();x.innerHTML=g,new E.Printd().print(x)}static reporteUsuarios(t){const h=t?.usuarios||[],m=t?.date_from||"",l=t?.date_to||"",f=`${window.location.origin}/chicken-logo.png`;let c="",p=0;h.forEach(n=>{const s=Number(n.neto||0);p+=s,c+=`
      <tr>
        <td>${n.user_name}</td>
        <td class="num">${Number(n.total_ingresos||0).toFixed(2)}</td>
        <td class="num">${Number(n.total_egresos||0).toFixed(2)}</td>
        <td class="num">${Number(n.total_caja||0).toFixed(2)}</td>
        <td class="num">${s.toFixed(2)}</td>
        <td class="num">${Number(n.tickets||0)}</td>
      </tr>
    `});const o=`
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
        <img src="${f}" alt="Chicken's Garden">
      </div>
      <div class="center" style="font-weight:bold;">RESUMEN DE VENTAS POR USUARIO</div>
      <div class="center">Desde: ${m||"-"} Hasta: ${l||"-"}</div>
      <hr>
      <table>
        <thead>
          <tr>
            <th>Usuario</th>
            <th>Ingreso</th>
            <th>Egreso</th>
            <th>Caja</th>
            <th>Neto</th>
            <th>Tickets</th>
          </tr>
        </thead>
        <tbody>
          ${c||'<tr><td colspan="6" class="center">Sin datos</td></tr>'}
        </tbody>
      </table>
      <div class="pie">Total neto: ${p.toFixed(2)} Bs</div>
    </div>
  `,e=w._getArea();e.innerHTML=o,new E.Printd().print(e)}static reporteProductosPorUsuario(t){const h=t?.productos||[],m=`${window.location.origin}/chicken-logo.png`;let l="";h.forEach(o=>{const e=o.user_name;let i="";o.items.forEach(n=>{i+=`
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
    `});const f=`
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
        <img src="${m}" alt="Chicken's Garden">
      </div>
      <div class="center" style="font-weight:bold;">PRODUCTOS POR USUARIO</div>
      <hr>
      ${l||'<div class="center">Sin datos</div>'}
    </div>
  `,c=w._getArea();c.innerHTML=f,new E.Printd().print(c)}}export{w as I};
