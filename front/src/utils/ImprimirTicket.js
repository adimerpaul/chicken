// src/utils/ImprimirTicket.js
import { Printd } from 'printd'

export class Imprimir {
  /**
   * Imprime un ticket pequeño de venta (tipo INGRESO).
   * Recibe el objeto que retorna tu API: Venta + detalles
   */
  static ticket (venta) {
    if (!venta) return

    const date = venta.date || ''
    const time = (venta.time || '').substring(0, 8)
    const mesa = venta.mesa || 'MESA'
    const pago = venta.pago || 'EFECTIVO'
    const numero = venta.numero || ''
    const llamada = venta.llamada || ''
    const userName = venta.user?.name || ''
    const comment = venta.comment || ''
    const type = venta.type || 'INGRESO'

    const detalles = venta.detalles || venta.details || []

    let filas = ''
    let totalCalc = 0
    detalles.forEach(d => {
      const qty = Number(d.qty || d.quantity || 0)
      const price = Number(d.price || 0)
      const subtotal = Number(d.subtotal || qty * price)
      totalCalc += subtotal

      filas += `
        <tr>
          <td class="col-cant">${qty}</td>
          <td class="col-detalle">${(d.name || d.product || '').toUpperCase()}</td>
          <td class="col-pu">${price.toFixed(0)}</td>
          <td class="col-total">${subtotal.toFixed(0)}</td>
        </tr>`
    })

    const total = Number(venta.total || totalCalc || 0)

    // Logo: pon tu logo en /public/chicken-logo.png o cambia la ruta
    const logoSrc = `${window.location.origin}/chicken-logo.png`
    console.log('Logo src:', logoSrc)

    const html = `
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
          ${type === 'INGRESO' && llamada
      ? `<div class="llamada-num">${llamada}</div>`
      : ''
    }

          <!-- Logo + nombre local -->
          <div class="logo">
            <img src="${logoSrc}" alt="Chicken's Garden">
          </div>

          <div class="center nombre-local">CHICKEN'S GARDEN</div>
          <div class="center contacto">CONTACTOS: 77909517</div>
          <div class="center direccion">
            Mercado Campero - Calle 6 N° 21
          </div>

          ${type === 'INGRESO' && venta.name && venta.name !== 'SN'
      ? `<div class="cliente-nombre">${venta.name}</div>`
      : ''
    }

          <div class="fecha-hora">
            <span>${date}</span>
            <span>${time}</span>
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
              ${filas || '<tr><td colspan="4" style="text-align:center;">SIN DETALLE</td></tr>'}
            </tbody>
          </table>

          <!-- Total y forma de pago -->
          <div class="total-section">
            <div class="total-row">
              <span>TOTAL:</span>
              <span>${total.toFixed(2)}</span>
            </div>
            <div class="total-row">
              <span>Pago:</span>
              <span>${pago}</span>
            </div>
          </div>

          <div class="ticket-line">
            TICKET ${numero} <span class="mesa">${mesa}</span>
          </div>

          ${comment
      ? `<div class="pie" style="margin-top:4px;">${comment}</div>`
      : ''
    }

          <div class="box-firma"></div>

          ${type === 'INGRESO'
      ? `<div class="pie">GRACIAS POR SU COMPRA, BUEN PROVECHO</div>`
      : ''
    }

          <div class="usuario">
            Usuario: ${userName}
          </div>
        </div>
      </div>
    `

    const area = Imprimir._getArea()
    area.innerHTML = html

    const d = new Printd()
    d.print(area)
  }

  // crea (si no existe) un div oculto para imprimir
  static _getArea () {
    let el = document.getElementById('myelement')
    if (!el) {
      el = document.createElement('div')
      el.id = 'myelement'
      el.style.position = 'fixed'
      el.style.left = '-10000px'
      el.style.top = '-10000px'
      document.body.appendChild(el)
    }
    return el
  }
}
