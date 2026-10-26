import{p as h}from"./index-DPxdJb2F.js";class x{static ticket(t){if(!t)return;const d=t.date||"",a=(t.time||"").substring(0,8),s=t.mesa||"MESA",o=t.pago||"EFECTIVO",e=t.numero||"",r=t.llamada||"",l=t.user?.name||"",p=t.comment||"",c=t.type||"INGRESO",i=t.detalles||t.details||[];let n="",m=0;i.forEach(g=>{const $=Number(g.qty||g.quantity||0),w=Number(g.price||0),y=Number(g.subtotal||$*w);m+=y,n+=`
        <tr>
          <td class="col-cant">${$}</td>
          <td class="col-detalle">${(g.name||g.product||"").toUpperCase()}</td>
          <td class="col-pu">${w.toFixed(0)}</td>
          <td class="col-total">${y.toFixed(0)}</td>
        </tr>`});const v=Number(t.total||m||0),u=`
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
          font-size: 16px;
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
          font-size: 24px;
          font-weight: bold;
        }
        .ticket-line span.mesa {
          font-size: 28px;
          font-weight: bold;
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
          text-align: right;
          font-size: 22px;
          font-weight: bold;
          margin-bottom: 2px;
        }
        .cliente-nombre {
          text-align: center;
          font-size: 14px;
          font-weight: bold;
          margin-top: 2px;
        }
      </style>

      <div class="ticket-wrapper">
        <div>
          ${c==="INGRESO"&&r?`<div class="llamada-num">${s}: ${r}</div>`:""}
          ${c==="INGRESO"&&t.name&&t.name!=="SN"?`<div class="cliente-nombre">${t.name}</div>`:""}
          <div class="fecha-hora">
            <span>${d}</span>
            <span style="font-weight:bold;"></span>
            <span>${a}</span>
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
              ${n||'<tr><td colspan="4" style="text-align:center;">SIN DETALLE</td></tr>'}
            </tbody>
          </table>
          <div class="total-section">
            <div class="total-row">
              <span>TOTAL:</span>
              <span>${v.toFixed(2)}</span>
            </div>
            <div class="total-row">
              <span>Pago:</span>
              <span>${o}</span>
            </div>
          </div>
          <div class="ticket-line">
            TICKET ${e} <span class="mesa">${s}</span>
          </div>
          <div class="box-firma">
          ${p?`<div style="margin-top:4px; font-size:16px; font-weight:bold;">${p}</div>`:""}
</div>
          ${c==="INGRESO"?'<div class="pie">GRACIAS POR SU COMPRA, BUEN PROVECHO</div>':""}
          <div class="usuario">
            Usuario: ${l}
          </div>
        </div>
      </div>
    `,b=x._getArea();b.innerHTML=u,new h.Printd().print(b)}static _getArea(){let t=document.getElementById("myelement");return t||(t=document.createElement("div"),t.id="myelement",t.className="hidden",document.body.appendChild(t)),t}static _inventarioBloque(t){const d=(t?.items||[]).filter(e=>e.inicial||e.aumento||e.vendido);if(!d.length)return"";const a=e=>Number(e||0).toLocaleString("es-BO",{maximumFractionDigits:2}),s="border:1px solid #000;padding:1px 2px;text-align:center;",o=d.map(e=>`
      <tr>
        <td style="${s}text-align:left;">${String(e.name||"").toUpperCase()}</td>
        <td style="${s}">${a(e.inicial)}</td>
        <td style="${s}">${a(e.aumento)}</td>
        <td style="${s}">${a(e.vendido)}</td>
        <td style="${s}font-weight:bold;">${a(e.queda)}</td>
      </tr>`).join("");return`
      <div style="border-top:1px dashed #000;margin:6px 0;"></div>
      <div style="text-align:center;font-weight:bold;font-size:12px;">INVENTARIO REFRESCOS</div>
      <table style="width:100%;border-collapse:collapse;margin-top:3px;font-size:10px;">
        <tr>
          <th style="${s}">PRODUCTO</th>
          <th style="${s}">INI</th>
          <th style="${s}">+</th>
          <th style="${s}">VEND</th>
          <th style="${s}">QUEDA</th>
        </tr>
        ${o}
      </table>
      <div style="margin-top:3px;font-size:9px;text-align:center;">Queda = inicial + aumentos - vendido</div>
    `}static inventarioCaja(t,d=""){if(!t)return;const a=`${window.location.origin}/chicken-logo.png`,s=new Date().toLocaleString("es-BO"),o=`
      <style>
        *{ box-sizing:border-box; margin:0; padding:0; }
        body{ font-family: Arial, sans-serif; font-size:11px; }
        .ticket{ width:7.2cm; padding:4px 6px; }
        .center{ text-align:center; }
        .logo img{ max-width:80px; display:block; margin:0 auto 3px auto; }
        .firma{ margin-top:28px; border-top:1px solid #000; text-align:center; font-size:10px; padding-top:2px; }
      </style>
      <div class="ticket">
        <div class="logo"><img src="${a}" alt="logo"></div>
        <div class="center" style="font-size:14px;font-weight:bold;">INVENTARIO DE CAJA</div>
        <div class="center" style="font-size:10px;">Fecha: ${t.date||""}</div>
        <div class="center" style="font-size:10px;">Vendedor: ${d}</div>
        ${x._inventarioBloque(t)||'<div class="center" style="margin-top:6px;">Sin inventario registrado</div>'}
        <div class="firma">Firma vendedor</div>
        <div class="center" style="font-size:9px;margin-top:4px;">Impreso: ${s}</div>
      </div>
    `,e=x._getArea();e.innerHTML=o,new h.Printd().print(e)}static cierreCaja(t){if(!t)return;const d=t.date||"",a=t.user?.name||"",s=Number(t.total_ingresos||0),o=Number(t.total_egresos||0),e=Number(t.total_caja_inicial||0),r=Number(t.tickets||0),l=Number(t.monto_sistema||0),p=Number(t.monto_efectivo||0),c=Number(t.monto_qr||0),i=Number(t.diferencia||0),n=t.observacion||"",m=Number(t.ingresos_efectivo??s),v=Number(t.ingresos_qr||0),u=Number(t.ingresos_tarjeta||0),b=Number(t.ingresos_online||0),f=Number(t.esperado_total??l+v),g=Number(t.contado_total??p+c),$=p-l,w=c-v,y=i||g-f,N=`
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
      <div class="center">Fecha: ${d}</div>
      <div class="center">Usuario cierre: ${a}</div>
      <hr>

      <!-- RESUMEN POR MÉTODO DE PAGO (SISTEMA) -->
      <div class="resumen-row"><span>Ing. EFECTIVO:</span><span>${m.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Ing. QR:</span><span>${v.toFixed(2)} Bs</span></div>
      <!--div class="resumen-row"><span>Ing. TARJETA:</span><span>${u.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Ing. ONLINE:</span><span>${b.toFixed(2)} Bs</span></div-->
      <hr>

      <!-- RESUMEN SISTEMA SOLO EFECTIVO -->
      <!--div class="resumen-row"><span>Caja inicial:</span><span>${e.toFixed(2)} Bs</span></div-->
      <div class="resumen-row"><span>Ingresos caja (efectivo):</span><span>${m.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Egresos:</span><span>${o.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Tickets:</span><span>${r}</span></div>
      <hr>
      <div class="resumen-row"><span>Sistema (efectivo):</span><span>${l.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Efectivo contado:</span><span>${p.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Dif. efectivo:</span><span>${$.toFixed(2)} Bs</span></div>
      <hr>

      <!-- QR -->
      <div class="resumen-row"><span>QR esperado:</span><span>${v.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>QR contado:</span><span>${c.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Dif. QR:</span><span>${w.toFixed(2)} Bs</span></div>
      <hr>

      <!-- TOTALES -->
      <div class="resumen-row"><span>Total esperado:</span><span>${f.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Total contado:</span><span>${g.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Diferencia total:</span><span>${y.toFixed(2)} Bs</span></div>

      ${n?`<div class="pie" style="margin-top:4px;">Obs: ${n}</div>`:""}
      <hr>
      <div class="pie">Gracias por su trabajo</div>
      <div class="usuario">Firmado: ____________________</div>
    </div>
  `,E=x._getArea();E.innerHTML=N,new h.Printd().print(E)}static reporteUsuarios(t){const d=t?.usuarios||[],a=t?.date_from||"",s=t?.date_to||"",o=`${window.location.origin}/chicken-logo.png`;let e="",r=0;d.forEach(i=>{const n=Number(i.neto||0);r+=n,e+=`
      <tr>
        <td>${i.user_name}</td>
        <td class="num">${Number(i.total_ingresos||0).toFixed(2)}</td>
        <td class="num">${Number(i.total_egresos||0).toFixed(2)}</td>
        <!--td class="num">${Number(i.total_caja||0).toFixed(2)}</td!-->
        <td class="num">${n.toFixed(2)}</td>
        <td class="num">${Number(i.tickets||0)}</td>
      </tr>
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
        <img src="${o}" alt="Chicken's Garden">
      </div>
      <div class="center" style="font-weight:bold;">RESUMEN DE VENTAS POR USUARIO</div>
      <div class="center">Desde: ${a||"-"} Hasta: ${s||"-"}</div>
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
          ${e||'<tr><td colspan="6" class="center">Sin datos</td></tr>'}
        </tbody>
      </table>
      <div class="pie">Total neto: ${r.toFixed(2)} Bs</div>
    </div>
  `,p=x._getArea();p.innerHTML=l,new h.Printd().print(p)}static reporteProductosPorUsuario(t){const d=t?.productos||[],a=`${window.location.origin}/chicken-logo.png`;let s="";d.forEach(l=>{const p=l.user_name;let c="";l.items.forEach(i=>{c+=`
        <tr>
          <td>${i.name}</td>
          <td class="num">${Number(i.qty||0)}</td>
          <td class="num">${Number(i.subtotal||0).toFixed(2)}</td>
        </tr>
      `}),s+=`
      <div class="user-block">
        <div class="user-title">Usuario: ${p}</div>
        <table>
          <thead>
            <tr>
              <th>Producto</th>
              <th>Cant.</th>
              <th>Total Bs</th>
            </tr>
          </thead>
          <tbody>
            ${c||'<tr><td colspan="3">Sin productos</td></tr>'}
          </tbody>
        </table>
      </div>
      <hr>
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
        <img src="${a}" alt="Chicken's Garden">
      </div>
      <div class="center" style="font-weight:bold;">PRODUCTOS POR USUARIO</div>
      <hr>
      ${s||'<div class="center">Sin datos</div>'}
    </div>
  `,e=x._getArea();e.innerHTML=o,new h.Printd().print(e)}static reporteVentasPorUsuario(t){const d=t?.ventas||[],a=t?.usuarios&&t.usuarios[0]||null,s=`${window.location.origin}/chicken-logo.png`,o=t?.date_from||"",e=t?.date_to||"";let r="",l=0;d.forEach(n=>{const m=Number(n.total||0);l+=m,r+=`
        <tr>
          <td>${n.numero}</td>
          <td>${n.date}</td>
          <td>${String(n.time||"").substring(0,8)}</td>
          <td>${n.mesa}</td>
          <td>${n.pago}</td>
          <td class="num">${m.toFixed(2)}</td>
        </tr>
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
      .titulo { font-weight:bold; margin-top:2px; }
    </style>

    <div class="ticket-wrapper">
      <div class="logo">
        <img src="${s}" alt="Chicken's Garden">
      </div>
      <div class="center titulo">VENTAS POR USUARIO</div>
      <div class="center">
        Usuario: ${a?a.user_name:""}
      </div>
      <div class="center">
        Desde: ${o||"-"} Hasta: ${e||"-"}
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
        Total ventas: ${l.toFixed(2)} Bs
      </div>
    </div>
  `,c=x._getArea();c.innerHTML=p,new h.Printd().print(c)}static cierreCajaUsuario(t){if(!t)return;const d=`${window.location.origin}/chicken-logo.png`,a=t?.date||"",s=t?.user?.name||"Usuario",o=Number(t.ef_sistema??t.monto_sistema??0),e=Number(t.ef_contado??t.monto_efectivo??0),r=Number(t.dif_efectivo??e-o),l=Number(t.qr_sistema??t.ingresos_qr??0),p=Number(t.qr_contado??t.monto_qr??0),c=Number(t.dif_qr??p-l),i={efectivo:o,qr:l},n=f=>Number(f||0).toFixed(2),m=(f,g)=>`
    <div class="row">
      <span class="l">${f}</span>
      <span class="v">${g}</span>
    </div>
  `,v=`
    <div class="user-block">
      <div class="user-title">${s}</div>

      ${m("EF sistema:",`${n(o)} Bs`)}
      ${m("EF contado:",`${n(e)} Bs`)}
      <div class="row">
        <span class="l">Dif. EF:</span>
        <span class="v ${r<0?"neg":"pos"}">${n(r)} Bs</span>
      </div>

      <div class="sep"></div>

      ${m("QR sistema:",`${n(l)} Bs`)}
      ${m("QR contado:",`${n(p)} Bs`)}
      <div class="row">
        <span class="l">Dif. QR:</span>
        <span class="v ${c<0?"neg":"pos"}">${n(c)} Bs</span>
      </div>
    </div>
  `,u=`
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
      <div class="logo"><img src="${d}" alt="logo"></div>

      <div class="center title">CIERRE DE CAJA</div>
      <div class="center sub">Fecha: ${a}</div>

      <div class="dash"></div>

      ${m("TOTAL EFECTIVO:",`${n(i.efectivo)} Bs`)}
      ${m("TOTAL QR:",`${n(i.qr)} Bs`)}

      <div class="dash"></div>

      ${v}

      <div class="dash"></div>
      <div class="note">Diferencia = contado - sistema</div>

      ${x._inventarioBloque(t.inventario)}
    </div>
  `,b=x._getArea();b.innerHTML=u,new h.Printd().print(b)}static reporteUltimoCierreUsuarios(t){const d=`${window.location.origin}/chicken-logo.png`,a=t?.date||"",s=t?.usuarios||[],o=t?.resumen||{},e=i=>Number(i||0).toFixed(2),r=(i,n)=>`
    <div class="row">
      <span class="l">${i}</span>
      <span class="v">${n}</span>
    </div>
  `;let l="";s.forEach(i=>{l+=`
      <div class="user-block">
        <div class="user-title">${i.user_name||""}</div>
        ${r("EF sistema:",`${e(i.ef_sistema)} Bs`)}
        ${r("EF contado:",`${e(i.ef_contado)} Bs`)}
        <div class="row">
          <span class="l">Dif. EF:</span>
          <span class="v ${Number(i.dif_efectivo)<0?"neg":"pos"}">${e(i.dif_efectivo)} Bs</span>
        </div>

        <div class="sep"></div>

        ${r("QR sistema:",`${e(i.qr_sistema)} Bs`)}
        ${r("QR contado:",`${e(i.qr_contado)} Bs`)}
        <div class="row">
          <span class="l">Dif. QR:</span>
          <span class="v ${Number(i.dif_qr)<0?"neg":"pos"}">${e(i.dif_qr)} Bs</span>
        </div>
      </div>
      <div class="dash"></div>
    `});const p=`
  <style>
    @page { size: auto; margin: 6mm; }
    *{
      box-sizing:border-box;
      margin:0;
      padding:0;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
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
    <div class="logo"><img src="${d}" alt="logo"></div>
    <div class="center title">CIERRE DEL DÍA</div>
    <div class="center sub">Fecha: ${a}</div>

    <div class="dash"></div>

    ${r("TOTAL EFECTIVO:",`${e(o.efectivo)} Bs`)}
    ${r("TOTAL QR:",`${e(o.qr)} Bs`)}

    <div class="dash"></div>

    ${l||'<div class="center">Sin datos</div>'}

    <div class="note">Diferencia = contado - sistema</div>
  </div>
  `,c=x._getArea();c.innerHTML=p,new h.Printd().print(c)}static gasto(t){const d=`${t.date||""} ${String(t.time||"").substring(0,8)}`,a=Number(t.total||0).toLocaleString("es-BO",{minimumFractionDigits:2}),s=`
      <div style="font-family: Arial; font-size: 12px; width: 280px;">
        <div style="text-align:center; font-weight:700; font-size:14px;">GASTO</div>
        <div style="text-align:center; margin-top:4px;">Nro: <b>${t.numero??""}</b></div>
        <div style="text-align:center; margin-top:2px;">${d}</div>
        <hr/>
        <div><b>Descripción:</b> ${t.name||""}</div>
        ${t.comment?`<div style="margin-top:4px;"><b>Comentario:</b> ${t.comment}</div>`:""}
        <div style="margin-top:4px;"><b>Pago:</b> ${t.pago||"EFECTIVO"}</div>
        <hr/>
        <div style="display:flex; justify-content:space-between; font-size:14px; font-weight:700;">
          <div>TOTAL</div>
          <div>${a} Bs</div>
        </div>
        <div style="margin-top:6px; text-align:center; font-size:11px;">
          Usuario: ${t.user?.name||"—"}
        </div>
      </div>
    `,o=document.getElementById("myelement");o&&(o.innerHTML=s);const e=window.open("","PRINT","height=600,width=400");e.document.write(`<html><head><title>Gasto</title></head><body>${s}</body></html>`),e.document.close(),e.focus(),e.print(),e.close()}static ingreso(t){const d=`${t.date||""} ${String(t.time||"").substring(0,8)}`,a=Number(t.total||0).toLocaleString("es-BO",{minimumFractionDigits:2}),s=`
      <div style="font-family: Arial; font-size: 12px; width: 280px;">
        <div style="text-align:center; font-weight:700; font-size:14px;">INGRESO</div>
        <div style="text-align:center; margin-top:4px;">Nro: <b>${t.numero??""}</b></div>
        <div style="text-align:center; margin-top:2px;">${d}</div>
        <hr/>
        <div><b>Descripción:</b> ${t.name||""}</div>
        ${t.comment?`<div style="margin-top:4px;"><b>Comentario:</b> ${t.comment}</div>`:""}
        <div style="margin-top:4px;"><b>Pago:</b> ${t.pago||"EFECTIVO"}</div>
        <hr/>
        <div style="display:flex; justify-content:space-between; font-size:14px; font-weight:700;">
          <div>TOTAL</div>
          <div>${a} Bs</div>
        </div>
        <div style="margin-top:6px; text-align:center; font-size:11px;">
          Usuario: ${t.user?.name||"—"}
        </div>
      </div>
    `,o=document.getElementById("myelement");o&&(o.innerHTML=s);const e=window.open("","PRINT","height=600,width=400");e.document.write(`<html><head><title>Ingreso</title></head><body>${s}</body></html>`),e.document.close(),e.focus(),e.print(),e.close()}}export{x as I};
