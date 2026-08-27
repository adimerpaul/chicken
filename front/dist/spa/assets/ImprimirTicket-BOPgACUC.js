import{p as f}from"./index-DPxdJb2F.js";class h{static ticket(t){if(!t)return;const p=t.date||"",l=(t.time||"").substring(0,8),n=t.mesa||"MESA",i=t.pago||"EFECTIVO",s=t.numero||"",a=t.llamada||"",d=t.user?.name||"",c=t.comment||"",r=t.type||"INGRESO",e=t.detalles||t.details||[];let o="",m=0;e.forEach(g=>{const $=Number(g.qty||g.quantity||0),w=Number(g.price||0),y=Number(g.subtotal||$*w);m+=y,o+=`
        <tr>
          <td class="col-cant">${$}</td>
          <td class="col-detalle">${(g.name||g.product||"").toUpperCase()}</td>
          <td class="col-pu">${w.toFixed(0)}</td>
          <td class="col-total">${y.toFixed(0)}</td>
        </tr>`});const x=Number(t.total||m||0),u=`
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
          ${r==="INGRESO"&&a?`<div class="llamada-num">${n}: ${a}</div>`:""}
          ${r==="INGRESO"&&t.name&&t.name!=="SN"?`<div class="cliente-nombre">${t.name}</div>`:""}
          <div class="fecha-hora">
            <span>${p}</span>
            <span style="font-weight:bold;"></span>
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
              <span>${i}</span>
            </div>
          </div>
          <div class="ticket-line">
            TICKET ${s} <span class="mesa">${n}</span>
          </div>
          <div class="box-firma">
          ${c?`<div style="margin-top:4px; font-size:16px; font-weight:bold;">${c}</div>`:""}
</div>
          ${r==="INGRESO"?'<div class="pie">GRACIAS POR SU COMPRA, BUEN PROVECHO</div>':""}
          <div class="usuario">
            Usuario: ${d}
          </div>
        </div>
      </div>
    `,b=h._getArea();b.innerHTML=u,new f.Printd().print(b)}static _getArea(){let t=document.getElementById("myelement");return t||(t=document.createElement("div"),t.id="myelement",t.style.position="fixed",t.style.left="-10000px",t.style.top="-10000px",document.body.appendChild(t)),t}static cierreCaja(t){if(!t)return;const p=t.date||"",l=t.user?.name||"",n=Number(t.total_ingresos||0),i=Number(t.total_egresos||0),s=Number(t.total_caja_inicial||0),a=Number(t.tickets||0),d=Number(t.monto_sistema||0),c=Number(t.monto_efectivo||0),r=Number(t.monto_qr||0),e=Number(t.diferencia||0),o=t.observacion||"",m=Number(t.ingresos_efectivo??n),x=Number(t.ingresos_qr||0),u=Number(t.ingresos_tarjeta||0),b=Number(t.ingresos_online||0),v=Number(t.esperado_total??d+x),g=Number(t.contado_total??c+r),$=c-d,w=r-x,y=e||g-v,N=`
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
      <div class="center">Usuario cierre: ${l}</div>
      <hr>

      <!-- RESUMEN POR MÉTODO DE PAGO (SISTEMA) -->
      <div class="resumen-row"><span>Ing. EFECTIVO:</span><span>${m.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Ing. QR:</span><span>${x.toFixed(2)} Bs</span></div>
      <!--div class="resumen-row"><span>Ing. TARJETA:</span><span>${u.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Ing. ONLINE:</span><span>${b.toFixed(2)} Bs</span></div-->
      <hr>

      <!-- RESUMEN SISTEMA SOLO EFECTIVO -->
      <!--div class="resumen-row"><span>Caja inicial:</span><span>${s.toFixed(2)} Bs</span></div-->
      <div class="resumen-row"><span>Ingresos caja (efectivo):</span><span>${m.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Egresos:</span><span>${i.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Tickets:</span><span>${a}</span></div>
      <hr>
      <div class="resumen-row"><span>Sistema (efectivo):</span><span>${d.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Efectivo contado:</span><span>${c.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Dif. efectivo:</span><span>${$.toFixed(2)} Bs</span></div>
      <hr>

      <!-- QR -->
      <div class="resumen-row"><span>QR esperado:</span><span>${x.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>QR contado:</span><span>${r.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Dif. QR:</span><span>${w.toFixed(2)} Bs</span></div>
      <hr>

      <!-- TOTALES -->
      <div class="resumen-row"><span>Total esperado:</span><span>${v.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Total contado:</span><span>${g.toFixed(2)} Bs</span></div>
      <div class="resumen-row"><span>Diferencia total:</span><span>${y.toFixed(2)} Bs</span></div>

      ${o?`<div class="pie" style="margin-top:4px;">Obs: ${o}</div>`:""}
      <hr>
      <div class="pie">Gracias por su trabajo</div>
      <div class="usuario">Firmado: ____________________</div>
    </div>
  `,E=h._getArea();E.innerHTML=N,new f.Printd().print(E)}static reporteUsuarios(t){const p=t?.usuarios||[],l=t?.date_from||"",n=t?.date_to||"",i=`${window.location.origin}/chicken-logo.png`;let s="",a=0;p.forEach(e=>{const o=Number(e.neto||0);a+=o,s+=`
      <tr>
        <td>${e.user_name}</td>
        <td class="num">${Number(e.total_ingresos||0).toFixed(2)}</td>
        <td class="num">${Number(e.total_egresos||0).toFixed(2)}</td>
        <!--td class="num">${Number(e.total_caja||0).toFixed(2)}</td!-->
        <td class="num">${o.toFixed(2)}</td>
        <td class="num">${Number(e.tickets||0)}</td>
      </tr>
    `});const d=`
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
        <img src="${i}" alt="Chicken's Garden">
      </div>
      <div class="center" style="font-weight:bold;">RESUMEN DE VENTAS POR USUARIO</div>
      <div class="center">Desde: ${l||"-"} Hasta: ${n||"-"}</div>
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
          ${s||'<tr><td colspan="6" class="center">Sin datos</td></tr>'}
        </tbody>
      </table>
      <div class="pie">Total neto: ${a.toFixed(2)} Bs</div>
    </div>
  `,c=h._getArea();c.innerHTML=d,new f.Printd().print(c)}static reporteProductosPorUsuario(t){const p=t?.productos||[],l=`${window.location.origin}/chicken-logo.png`;let n="";p.forEach(d=>{const c=d.user_name;let r="";d.items.forEach(e=>{r+=`
        <tr>
          <td>${e.name}</td>
          <td class="num">${Number(e.qty||0)}</td>
          <td class="num">${Number(e.subtotal||0).toFixed(2)}</td>
        </tr>
      `}),n+=`
      <div class="user-block">
        <div class="user-title">Usuario: ${c}</div>
        <table>
          <thead>
            <tr>
              <th>Producto</th>
              <th>Cant.</th>
              <th>Total Bs</th>
            </tr>
          </thead>
          <tbody>
            ${r||'<tr><td colspan="3">Sin productos</td></tr>'}
          </tbody>
        </table>
      </div>
      <hr>
    `});const i=`
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
      ${n||'<div class="center">Sin datos</div>'}
    </div>
  `,s=h._getArea();s.innerHTML=i,new f.Printd().print(s)}static reporteVentasPorUsuario(t){const p=t?.ventas||[],l=t?.usuarios&&t.usuarios[0]||null,n=`${window.location.origin}/chicken-logo.png`,i=t?.date_from||"",s=t?.date_to||"";let a="",d=0;p.forEach(o=>{const m=Number(o.total||0);d+=m,a+=`
        <tr>
          <td>${o.numero}</td>
          <td>${o.date}</td>
          <td>${String(o.time||"").substring(0,8)}</td>
          <td>${o.mesa}</td>
          <td>${o.pago}</td>
          <td class="num">${m.toFixed(2)}</td>
        </tr>
      `});const c=`
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
        <img src="${n}" alt="Chicken's Garden">
      </div>
      <div class="center titulo">VENTAS POR USUARIO</div>
      <div class="center">
        Usuario: ${l?l.user_name:""}
      </div>
      <div class="center">
        Desde: ${i||"-"} Hasta: ${s||"-"}
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
          ${a||'<tr><td colspan="6" class="center">Sin ventas</td></tr>'}
        </tbody>
      </table>
      <div class="center" style="margin-top:4px;">
        Total ventas: ${d.toFixed(2)} Bs
      </div>
    </div>
  `,r=h._getArea();r.innerHTML=c,new f.Printd().print(r)}static cierreCajaUsuario(t){if(!t)return;const p=`${window.location.origin}/chicken-logo.png`,l=t?.date||"",n=t?.user?.name||"Usuario",i=Number(t.ef_sistema??t.monto_sistema??0),s=Number(t.ef_contado??t.monto_efectivo??0),a=Number(t.dif_efectivo??s-i),d=Number(t.qr_sistema??t.ingresos_qr??0),c=Number(t.qr_contado??t.monto_qr??0),r=Number(t.dif_qr??c-d),e={efectivo:i,qr:d},o=v=>Number(v||0).toFixed(2),m=(v,g)=>`
    <div class="row">
      <span class="l">${v}</span>
      <span class="v">${g}</span>
    </div>
  `,x=`
    <div class="user-block">
      <div class="user-title">${n}</div>

      ${m("EF sistema:",`${o(i)} Bs`)}
      ${m("EF contado:",`${o(s)} Bs`)}
      <div class="row">
        <span class="l">Dif. EF:</span>
        <span class="v ${a<0?"neg":"pos"}">${o(a)} Bs</span>
      </div>

      <div class="sep"></div>

      ${m("QR sistema:",`${o(d)} Bs`)}
      ${m("QR contado:",`${o(c)} Bs`)}
      <div class="row">
        <span class="l">Dif. QR:</span>
        <span class="v ${r<0?"neg":"pos"}">${o(r)} Bs</span>
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
      <div class="logo"><img src="${p}" alt="logo"></div>

      <div class="center title">CIERRE DE CAJA</div>
      <div class="center sub">Fecha: ${l}</div>

      <div class="dash"></div>

      ${m("TOTAL EFECTIVO:",`${o(e.efectivo)} Bs`)}
      ${m("TOTAL QR:",`${o(e.qr)} Bs`)}

      <div class="dash"></div>

      ${x}

      <div class="dash"></div>
      <div class="note">Diferencia = contado - sistema</div>
    </div>
  `,b=h._getArea();b.innerHTML=u,new f.Printd().print(b)}static reporteUltimoCierreUsuarios(t){const p=`${window.location.origin}/chicken-logo.png`,l=t?.date||"",n=t?.usuarios||[],i=t?.resumen||{},s=e=>Number(e||0).toFixed(2),a=(e,o)=>`
    <div class="row">
      <span class="l">${e}</span>
      <span class="v">${o}</span>
    </div>
  `;let d="";n.forEach(e=>{d+=`
      <div class="user-block">
        <div class="user-title">${e.user_name||""}</div>
        ${a("EF sistema:",`${s(e.ef_sistema)} Bs`)}
        ${a("EF contado:",`${s(e.ef_contado)} Bs`)}
        <div class="row">
          <span class="l">Dif. EF:</span>
          <span class="v ${Number(e.dif_efectivo)<0?"neg":"pos"}">${s(e.dif_efectivo)} Bs</span>
        </div>

        <div class="sep"></div>

        ${a("QR sistema:",`${s(e.qr_sistema)} Bs`)}
        ${a("QR contado:",`${s(e.qr_contado)} Bs`)}
        <div class="row">
          <span class="l">Dif. QR:</span>
          <span class="v ${Number(e.dif_qr)<0?"neg":"pos"}">${s(e.dif_qr)} Bs</span>
        </div>
      </div>
      <div class="dash"></div>
    `});const c=`
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
    <div class="logo"><img src="${p}" alt="logo"></div>
    <div class="center title">CIERRE DEL DÍA</div>
    <div class="center sub">Fecha: ${l}</div>

    <div class="dash"></div>

    ${a("TOTAL EFECTIVO:",`${s(i.efectivo)} Bs`)}
    ${a("TOTAL QR:",`${s(i.qr)} Bs`)}

    <div class="dash"></div>

    ${d||'<div class="center">Sin datos</div>'}

    <div class="note">Diferencia = contado - sistema</div>
  </div>
  `,r=h._getArea();r.innerHTML=c,new f.Printd().print(r)}static gasto(t){const p=`${t.date||""} ${String(t.time||"").substring(0,8)}`,l=Number(t.total||0).toLocaleString("es-BO",{minimumFractionDigits:2}),n=`
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
          <div>${l} Bs</div>
        </div>
        <div style="margin-top:6px; text-align:center; font-size:11px;">
          Usuario: ${t.user?.name||"—"}
        </div>
      </div>
    `,i=document.getElementById("myelement");i&&(i.innerHTML=n);const s=window.open("","PRINT","height=600,width=400");s.document.write(`<html><head><title>Gasto</title></head><body>${n}</body></html>`),s.document.close(),s.focus(),s.print(),s.close()}static ingreso(t){const p=`${t.date||""} ${String(t.time||"").substring(0,8)}`,l=Number(t.total||0).toLocaleString("es-BO",{minimumFractionDigits:2}),n=`
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
          <div>${l} Bs</div>
        </div>
        <div style="margin-top:6px; text-align:center; font-size:11px;">
          Usuario: ${t.user?.name||"—"}
        </div>
      </div>
    `,i=document.getElementById("myelement");i&&(i.innerHTML=n);const s=window.open("","PRINT","height=600,width=400");s.document.write(`<html><head><title>Ingreso</title></head><body>${n}</body></html>`),s.document.close(),s.focus(),s.print(),s.close()}}export{h as I};
