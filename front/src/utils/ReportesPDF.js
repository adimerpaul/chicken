// src/utils/ReportesPDF.js
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'

export class ReportesPDF {
  // ===== Helpers =====
  static money (v) {
    return Number(v || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
  }

  static _title(doc, title, sub) {
    doc.setFont('helvetica', 'bold')
    doc.setFontSize(14)
    doc.text(title, 14, 16)

    doc.setFont('helvetica', 'normal')
    doc.setFontSize(10)
    if (sub) doc.text(sub, 14, 22)

    doc.setDrawColor(220)
    doc.line(14, 25, 196, 25)
  }

  static async _saveOrShare(doc, filename) {
    // genera file blob
    const blob = doc.output('blob')
    const file = new File([blob], filename, { type: 'application/pdf' })

    // ✅ Share directo (celulares / algunos navegadores)
    const canShareFiles = !!navigator?.canShare && navigator.canShare({ files: [file] })
    if (canShareFiles && navigator.share) {
      try {
        await navigator.share({
          title: filename,
          text: 'Te envío el reporte en PDF.',
          files: [file]
        })
        return
      } catch (e) {
        // si cancela o falla, caemos a descarga
      }
    }

    // ✅ fallback: descargar
    doc.save(filename)
  }

  // ===== 1) Ventas por usuario (RESUMEN) =====
  static async ventasPorUsuario(data) {
    const dateFrom = data?.date_from || '-'
    const dateTo = data?.date_to || '-'
    const usuarios = data?.usuarios || []

    const doc = new jsPDF('p', 'mm', 'a4')
    this._title(doc, 'RESUMEN DE VENTAS POR USUARIO', `Desde: ${dateFrom}  Hasta: ${dateTo}`)

    const body = usuarios.map(u => ([
      u.user_name || '',
      this.money(u.total_ingresos),
      this.money(u.total_egresos),
      this.money(u.neto),
      String(u.tickets || 0)
    ]))

    autoTable(doc, {
      startY: 30,
      head: [['Usuario', 'Ingresos (Bs)', 'Egresos (Bs)', 'Neto (Bs)', 'Tickets']],
      body,
      styles: { fontSize: 9, cellPadding: 2 },
      headStyles: { fillColor: [30, 30, 30] },
      columnStyles: {
        1: { halign: 'right' },
        2: { halign: 'right' },
        3: { halign: 'right' },
        4: { halign: 'right' }
      }
    })

    const totalNeto = usuarios.reduce((a, b) => a + Number(b?.neto || 0), 0)
    const y = doc.lastAutoTable?.finalY ? doc.lastAutoTable.finalY + 8 : 40

    doc.setFont('helvetica', 'bold')
    doc.text(`Total neto: ${this.money(totalNeto)} Bs`, 14, y)

    await this._saveOrShare(doc, `ventas_por_usuario_${dateFrom}_a_${dateTo}.pdf`)
  }

  // ===== 2) Productos por usuario =====
  static async productosPorUsuario(data) {
    const dateFrom = data?.date_from || '-'
    const dateTo = data?.date_to || '-'
    const bloques = data?.productos || []

    const doc = new jsPDF('p', 'mm', 'a4')
    this._title(doc, 'PRODUCTOS POR USUARIO', `Desde: ${dateFrom}  Hasta: ${dateTo}`)

    let y = 30

    for (const bloque of bloques) {
      const userName = bloque?.user_name || '—'
      const items = bloque?.items || []

      doc.setFont('helvetica', 'bold')
      doc.setFontSize(11)
      doc.text(`Usuario: ${userName}`, 14, y)
      y += 4

      autoTable(doc, {
        startY: y,
        head: [['Producto', 'Cantidad', 'Total (Bs)']],
        body: items.map(it => ([
          it.name || '',
          String(Number(it.qty || 0)),
          this.money(it.subtotal || 0)
        ])),
        styles: { fontSize: 9, cellPadding: 2 },
        headStyles: { fillColor: [30, 30, 30] },
        columnStyles: {
          1: { halign: 'right' },
          2: { halign: 'right' }
        },
        margin: { left: 14, right: 14 }
      })

      y = (doc.lastAutoTable?.finalY || y) + 10

      // salto de página si está muy abajo
      if (y > 270) {
        doc.addPage()
        y = 20
      }
    }

    if (!bloques.length) {
      doc.setFont('helvetica', 'normal')
      doc.text('Sin datos en este rango.', 14, 35)
    }

    await this._saveOrShare(doc, `productos_por_usuario_${dateFrom}_a_${dateTo}.pdf`)
  }

  // ===== 3) Ventas detalladas por usuario (tu reporteVentasPorUsuario actual) =====
  static async ventasDetalladasPorUsuario(data) {
    const dateFrom = data?.date_from || '-'
    const dateTo = data?.date_to || '-'
    const ventas = data?.ventas || []

    // en tu impresión estabas leyendo usuario desde data.usuarios[0]
    const usuario = (data?.usuarios && data.usuarios[0]) ? data.usuarios[0] : null
    const userName = usuario?.user_name || '—'

    const doc = new jsPDF('p', 'mm', 'a4')
    this._title(doc, 'VENTAS DETALLADAS POR USUARIO', `Usuario: ${userName}  |  ${dateFrom} a ${dateTo}`)

    autoTable(doc, {
      startY: 30,
      head: [['#', 'Fecha', 'Hora', 'Mesa', 'Pago', 'Total (Bs)']],
      body: ventas.map(v => ([
        String(v.numero || ''),
        String(v.date || ''),
        String(v.time || '').substring(0, 8),
        String(v.mesa || ''),
        String(v.pago || ''),
        this.money(v.total || 0)
      ])),
      styles: { fontSize: 9, cellPadding: 2 },
      headStyles: { fillColor: [30, 30, 30] },
      columnStyles: { 5: { halign: 'right' } }
    })

    const total = ventas.reduce((a, b) => a + Number(b?.total || 0), 0)
    const y = doc.lastAutoTable?.finalY ? doc.lastAutoTable.finalY + 8 : 40
    doc.setFont('helvetica', 'bold')
    doc.text(`Total ventas: ${this.money(total)} Bs`, 14, y)

    await this._saveOrShare(doc, `ventas_usuario_${userName}_${dateFrom}_a_${dateTo}.pdf`)
  }

  // ===== 4) Cierre del día (todos los usuarios) =====
  static async cierreDiaUsuarios(data) {
    const date = data?.date || '-'
    const users = data?.usuarios || []
    const resumen = data?.resumen || {}

    const doc = new jsPDF('p', 'mm', 'a4')
    this._title(doc, 'CIERRE DEL DÍA', `Fecha: ${date}`)

    doc.setFont('helvetica', 'bold')
    doc.setFontSize(10)
    doc.text(`TOTAL EFECTIVO: ${this.money(resumen.efectivo)} Bs`, 14, 32)
    doc.text(`TOTAL QR: ${this.money(resumen.qr)} Bs`, 120, 32)

    autoTable(doc, {
      startY: 38,
      head: [['Usuario', 'EF sistema', 'EF contado', 'Dif EF', 'QR sistema', 'QR contado', 'Dif QR']],
      body: users.map(u => ([
        u.user_name || '',
        this.money(u.ef_sistema),
        this.money(u.ef_contado),
        this.money(u.dif_efectivo),
        this.money(u.qr_sistema),
        this.money(u.qr_contado),
        this.money(u.dif_qr)
      ])),
      styles: { fontSize: 9, cellPadding: 2 },
      headStyles: { fillColor: [30, 30, 30] },
      columnStyles: {
        1: { halign: 'right' },
        2: { halign: 'right' },
        3: { halign: 'right' },
        4: { halign: 'right' },
        5: { halign: 'right' },
        6: { halign: 'right' }
      }
    })

    await this._saveOrShare(doc, `cierre_del_dia_${date}.pdf`)
  }

  // ===== 5) Cierre de caja (1 usuario) =====
  static async cierreCajaUsuario(cierre) {
    const date = cierre?.date || '-'
    const userName = cierre?.user?.name || 'Usuario'

    const efSistema = Number(cierre.ef_sistema ?? cierre.monto_sistema ?? 0)
    const efContado = Number(cierre.ef_contado ?? cierre.monto_efectivo ?? 0)
    const difEf = Number(cierre.dif_efectivo ?? (efContado - efSistema))

    const qrSistema = Number(cierre.qr_sistema ?? cierre.ingresos_qr ?? 0)
    const qrContado = Number(cierre.qr_contado ?? cierre.monto_qr ?? 0)
    const difQr = Number(cierre.dif_qr ?? (qrContado - qrSistema))

    const doc = new jsPDF('p', 'mm', 'a4')
    this._title(doc, 'CIERRE DE CAJA (USUARIO)', `Fecha: ${date}  |  Usuario: ${userName}`)

    autoTable(doc, {
      startY: 30,
      head: [['Concepto', 'Sistema', 'Contado', 'Diferencia']],
      body: [
        ['EFECTIVO', this.money(efSistema), this.money(efContado), this.money(difEf)],
        ['QR', this.money(qrSistema), this.money(qrContado), this.money(difQr)]
      ],
      styles: { fontSize: 10, cellPadding: 3 },
      headStyles: { fillColor: [30, 30, 30] },
      columnStyles: {
        1: { halign: 'right' },
        2: { halign: 'right' },
        3: { halign: 'right' }
      }
    })

    await this._saveOrShare(doc, `cierre_caja_${userName}_${date}.pdf`)
  }
}
