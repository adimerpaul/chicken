// src/utils/ImprimirCompra.js
import { Printd } from 'printd'

export class ImprimirCompra {
  static money (v) {
    return Number(v || 0).toLocaleString('es-BO', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    })
  }

  static compra (compra) {
    if (!compra) return

    const detalles = compra.detalles || []
    let filas = ''
    detalles.forEach(d => {
      const nombre = (d.insumo && d.insumo.nombre) ? d.insumo.nombre : ''
      const unidad = (d.insumo && d.insumo.unidad) ? d.insumo.unidad : ''
      filas += `
        <tr>
          <td class="col-cant">${this.money(d.cantidad)}<br><span class="und">${unidad}</span></td>
          <td class="col-detalle">${String(nombre).toUpperCase()}</td>
          <td class="col-pu">${this.money(d.costo)}</td>
          <td class="col-total">${this.money(d.subtotal)}</td>
        </tr>`
    })

    const html = `
      <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; }
        .wrapper { width: 7.2cm; padding: 4px 6px; font-size: 11px; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .mt-4 { margin-top: 4px; }
        .mt-8 { margin-top: 8px; }
        .titulo { font-size: 14px; font-weight: bold; }
        .sub { font-size: 11px; }
        hr { border: none; border-top: 1px dashed #000; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; }
        th { font-size: 10px; text-align: left; border-bottom: 1px solid #000; padding-bottom: 2px; }
        td { font-size: 10px; padding: 2px 0; vertical-align: top; }
        .und { font-size: 8px; color: #444; }
        .col-cant { width: 18%; }
        .col-detalle { width: 44%; }
        .col-pu { width: 18%; text-align: right; }
        .col-total { width: 20%; text-align: right; }
        .total { font-size: 13px; font-weight: bold; text-align: right; margin-top: 6px; }
        .anulado { color: #b00; font-weight: bold; text-align: center; font-size: 13px; }
        @media print { @page { margin: 0; } body { margin: 0; } }
      </style>
      <div class="wrapper">
        <div class="center titulo">CHICKEN'S GARDEN</div>
        <div class="center sub">COMPRA DE INSUMOS</div>
        <hr>
        <div><b>N°:</b> ${compra.id || ''}</div>
        <div><b>Fecha:</b> ${compra.fecha || ''} ${String(compra.hora || '').substr(0, 5)}</div>
        <div><b>Proveedor:</b> ${compra.proveedor || '—'}</div>
        <div><b>Pago:</b> ${compra.pago || 'EFECTIVO'}</div>
        <div><b>Estado:</b> ${compra.estado || ''}</div>
        ${compra.nota ? `<div><b>Nota:</b> ${compra.nota}</div>` : ''}
        <hr>
        <table>
          <thead>
            <tr>
              <th class="col-cant">Cant.</th>
              <th class="col-detalle">Insumo</th>
              <th class="col-pu">Costo</th>
              <th class="col-total">Subt.</th>
            </tr>
          </thead>
          <tbody>${filas}</tbody>
        </table>
        <hr>
        <div class="total">TOTAL: ${this.money(compra.total)} Bs</div>
        ${compra.estado === 'ANULADO' ? '<div class="anulado mt-8">*** ANULADO ***</div>' : ''}
        <div class="center mt-8 sub">Impreso: ${new Date().toLocaleString('es-BO')}</div>
      </div>`

    const area = ImprimirCompra._getArea()
    area.innerHTML = html
    const d = new Printd()
    d.print(area)
  }

  static _getArea () {
    let el = document.getElementById('area-impresion-compra')
    if (!el) {
      el = document.createElement('div')
      el.id = 'area-impresion-compra'
      el.style.position = 'fixed'
      el.style.left = '-10000px'
      el.style.top = '-10000px'
      document.body.appendChild(el)
    }
    return el
  }
}
