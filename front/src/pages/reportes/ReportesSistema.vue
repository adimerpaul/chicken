<template>
  <q-page class="q-pa-md bg-grey-3">
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row q-col-gutter-sm items-end">
        <div class="col-12 col-sm-3">
          <q-input v-model="filters.date_from" type="date" label="Desde" dense outlined />
        </div>
        <div class="col-12 col-sm-3">
          <q-input v-model="filters.date_to" type="date" label="Hasta" dense outlined />
        </div>
        <div class="col-12 col-sm-3">
          <q-btn
            color="primary"
            icon="search"
            label="Consultar"
            :loading="loading"
            no-caps
            class="full-width"
            @click="fetchAll"
          />
        </div>
        <div class="col-12 col-sm-3">
          <q-btn
            color="teal"
            icon="picture_as_pdf"
            label="Imprimir PDF"
            no-caps
            class="full-width"
            @click="printPdf"
          />
        </div>
      </q-card-section>
    </q-card>

    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row items-center">
        <div class="text-subtitle1 text-weight-bold">Reportes de Alimentos</div>
        <q-space />
        <q-badge color="orange" outline>Usado: {{ qty(alimentos.resumen.cantidad_usada) }}</q-badge>
        <q-badge color="indigo" outline>Stock: {{ qty(alimentos.resumen.stock_actual) }}</q-badge>
        <q-badge color="teal" outline>Total: {{ qty(alimentos.resumen.cantidad_total) }}</q-badge>
        <q-badge color="brown" outline>Costo usado: {{ money(alimentos.resumen.costo_usado) }} Bs</q-badge>
        <q-badge color="green" outline>Costo actual: {{ money(alimentos.resumen.costo_actual) }} Bs</q-badge>
      </q-card-section>
      <q-separator />
      <q-table
        :rows="alimentos.items"
        :columns="colsInsumos"
        dense
        flat
        bordered
        :rows-per-page-options="[0]"
      />
    </q-card>

    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row items-center">
        <div class="text-subtitle1 text-weight-bold">Reportes de Gaseosas</div>
        <q-space />
        <q-badge color="orange" outline>Usado: {{ qty(gaseosas.resumen.cantidad_usada) }}</q-badge>
        <q-badge color="indigo" outline>Stock: {{ qty(gaseosas.resumen.stock_actual) }}</q-badge>
        <q-badge color="teal" outline>Total: {{ qty(gaseosas.resumen.cantidad_total) }}</q-badge>
        <q-badge color="brown" outline>Costo usado: {{ money(gaseosas.resumen.costo_usado) }} Bs</q-badge>
        <q-badge color="green" outline>Costo actual: {{ money(gaseosas.resumen.costo_actual) }} Bs</q-badge>
      </q-card-section>
      <q-separator />
      <q-table
        :rows="gaseosas.items"
        :columns="colsInsumos"
        dense
        flat
        bordered
        :rows-per-page-options="[0]"
      />
    </q-card>

    <q-card flat bordered>
      <q-card-section class="row items-center">
        <div class="text-subtitle1 text-weight-bold">Reportes de Extras</div>
        <q-space />
        <q-badge color="orange" outline>Usado: {{ qty(extras.resumen.cantidad_usada) }}</q-badge>
        <q-badge color="indigo" outline>Stock: {{ qty(extras.resumen.stock_actual) }}</q-badge>
        <q-badge color="teal" outline>Total: {{ qty(extras.resumen.cantidad_total) }}</q-badge>
        <q-badge color="brown" outline>Costo usado: {{ money(extras.resumen.costo_usado) }} Bs</q-badge>
        <q-badge color="green" outline>Costo actual: {{ money(extras.resumen.costo_actual) }} Bs</q-badge>
      </q-card-section>
      <q-separator />
      <q-table
        :rows="extras.items"
        :columns="colsInsumos"
        dense
        flat
        bordered
        :rows-per-page-options="[0]"
      />
    </q-card>
  </q-page>
</template>

<script>
import moment from 'moment'
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'

export default {
  name: 'ReportesSistema',
  data () {
    return {
      loading: false,
      filters: {
        date_from: '',
        date_to: ''
      },
      alimentos: { items: [], resumen: {} },
      gaseosas: { items: [], resumen: {} },
      extras: { items: [], resumen: {} },
      colsInsumos: [
        { name: 'nombre', label: 'Insumo', field: 'nombre', align: 'left' },
        { name: 'unidad', label: 'Unidad', field: 'unidad', align: 'left' },
        { name: 'total_cant', label: 'Total', field: 'total_cant', align: 'right', format: v => Number(v || 0).toFixed(2) },
        { name: 'usado', label: 'Cant. usada', field: 'usado', align: 'right', format: v => Number(v || 0).toFixed(2) },
        { name: 'stock_actual', label: 'Stock actual', field: 'stock_actual', align: 'right', format: v => Number(v || 0).toFixed(2) },
        { name: 'costo_usado', label: 'Costo usado (Bs)', field: 'costo_usado', align: 'right', format: v => Number(v || 0).toFixed(2) },
        { name: 'costo_actual', label: 'Costo actual (Bs)', field: 'costo_actual', align: 'right', format: v => Number(v || 0).toFixed(2) }
      ]
    }
  },

  mounted () {
    this.prefillDates()
    this.fetchAll()
  },

  methods: {
    prefillDates () {
      this.filters.date_from = moment().format('YYYY-MM-DD')
      this.filters.date_to = moment().format('YYYY-MM-DD')
    },

    async fetchAll () {
      this.loading = true
      try {
        const params = { ...this.filters }
        const { data } = await this.$axios.get('reportes/insumos-sistema', { params })
        this.alimentos = data?.alimentos || { items: [], resumen: {} }
        this.gaseosas = data?.gaseosas || { items: [], resumen: {} }
        this.extras = data?.extras || { items: [], resumen: {} }
      } catch (e) {
        const msg = e?.response?.data?.message || 'No se pudieron obtener los reportes'
        this.$q.notify?.({ type: 'negative', message: msg })
      } finally {
        this.loading = false
      }
    },

    printPdf () {
      const doc = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' })
      const title = 'Reportes del Sistema'
      const subtitle = `Desde: ${this.filters.date_from || '-'}  Hasta: ${this.filters.date_to || '-'}`

      doc.setFontSize(14)
      doc.text(title, 40, 40)
      doc.setFontSize(10)
      doc.text(subtitle, 40, 58)

      let cursorY = 80

      const addSection = (label, rows) => {
        doc.setFontSize(12)
        doc.text(label, 40, cursorY)
        cursorY += 10

        autoTable(doc, {
          startY: cursorY,
          head: [[
            'INSUMO',
            'UNIDAD',
            'TOTAL',
            'CANT. USADO',
            'STOCK ACTUAL',
            'COSTO USADO',
            'COSTO ACTUAL'
          ]],
          body: rows.map(r => ([
            r.nombre || '',
            r.unidad || '',
            this.qty(r.total_cant),
            this.qty(r.usado),
            this.qty(r.stock_actual),
            this.money(r.costo_usado),
            this.money(r.costo_actual)
          ])),
          styles: { fontSize: 8 }
        })

        cursorY = doc.lastAutoTable.finalY + 18
      }

      addSection('REPORTES DE ALIMENTOS', this.alimentos.items || [])
      addSection('REPORTES DE GASEOSAS', this.gaseosas.items || [])
      addSection('REPORTES DE EXTRAS', this.extras.items || [])

      doc.save(`reportes-sistema-${this.filters.date_from || 'desde'}-${this.filters.date_to || 'hasta'}.pdf`)
    },

    money (v) {
      return Number(v || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    },

    qty (v) {
      return Number(v || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    }
  }
}
</script>
