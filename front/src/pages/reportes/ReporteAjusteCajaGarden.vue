<template>
  <q-page class="q-pa-sm bg-grey-3">
    <!-- HEADER -->
    <q-card flat bordered class="q-mb-sm">
      <q-card-section class="row items-end q-col-gutter-sm">
        <div class="col-12 col-sm-4">
          <div class="text-h6 text-weight-bold">{{ title }}</div>
          <div class="text-caption text-grey-7">
            Semana Lun-Dom: totales por día (arriba) + desglose por día (abajo).
          </div>
        </div>

        <div class="col-6 col-sm-2">
          <q-input v-model="filters.date_from" type="date" dense outlined label="Desde" />
        </div>

        <div class="col-6 col-sm-2">
          <q-input v-model="filters.date_to" type="date" dense outlined label="Hasta" />
        </div>

        <!-- NUEVO: PAGO -->
        <div class="col-6 col-sm-2">
          <q-select
            v-model="filters.pago"
            :options="pagoOptions"
            label="Pago"
            dense outlined
            emit-value map-options
            clearable
          />
        </div>

        <!-- NUEVO: USUARIO -->
        <div class="col-6 col-sm-2">
          <q-select
            v-model="filters.user_id"
            :options="userOptions"
            label="Usuario"
            dense outlined
            emit-value map-options
            clearable
          />
        </div>

        <div class="col-12 col-sm-2 text-right">
          <q-btn
            color="primary"
            icon="search"
            label="Actualizar"
            no-caps
            :loading="loading"
            @click="fetchData(false)"
            class="q-mr-sm"
          />
          <q-btn-dropdown color="grey-9" icon="more_horiz" label="Acciones" no-caps>
            <q-list>
              <q-item clickable v-close-popup @click="setWeek">
                <q-item-section avatar><q-icon name="date_range" /></q-item-section>
                <q-item-section>Semana (Lun-Dom)</q-item-section>
              </q-item>
              <q-item clickable v-close-popup @click="setToday">
                <q-item-section avatar><q-icon name="today" /></q-item-section>
                <q-item-section>Hoy</q-item-section>
              </q-item>
              <q-item clickable v-close-popup @click="exportExcel">
                <q-item-section avatar><q-icon name="grid_on" /></q-item-section>
                <q-item-section>Exportar Excel</q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </div>
      </q-card-section>
    </q-card>
    <div class="row">
      <!-- INGRESOS POR DÍA (TOTAL) -->
      <div class="col-12">
        <q-card flat bordered class="card-like q-mb-sm">
          <q-card-section class="q-pa-none">
            <div class="totales-box">
              <div class="totales-title">TOTALES</div>

              <div class="tot-row">
                <div class="label">INGRESOS</div>
                <div class="val">{{ money(totales.ingresos) }}</div>
              </div>

              <div class="tot-row">
                <div class="label">EGRESOS</div>
                <div class="val">{{ money(totales.egresos) }}</div>
              </div>

              <div class="tot-row tot-strong">
                <div class="label">EN CAJA</div>
                <div class="val">{{ money(totales.en_caja) }}</div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
    <q-card flat bordered class="q-mt-sm card-like">
      <q-card-section class="row items-center q-col-gutter-sm">
        <div class="col">
          <div class="text-subtitle1 text-weight-bold">Desglose del día</div>
          <div class="text-caption text-grey-7">
            Click en un día (arriba) para ver el detalle (ingresos por usuario + egresos itemizados).
          </div>
        </div>
        <div class="col-auto">
          <q-badge v-if="selectedRow" color="primary" :label="selectedRow.label" />
          <q-badge v-else color="grey-7" label="Selecciona un día" />
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section class="row q-col-gutter-sm">
        <!-- INGRESOS DETALLE -->
        <div class="col-12 col-md-6">
          <div class="panel-title blue">INGRESOS (por usuario)</div>
          <div class="panel-wrap">
            <table class="mini-table">
              <thead>
              <tr>
                <th class="th-left">Usuario</th>
                <th class="th-right">Total</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="u in users" :key="u.id">
                <td class="td-left">{{ u.name }}</td>
                <td class="td-right">
                  {{ money(ingSelected[String(u.id)] || 0) }}
                </td>
              </tr>
              <tr v-if="!users.length">
                <td colspan="2" class="td-center muted">Sin usuarios</td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- EGRESOS DETALLE -->
        <div class="col-12 col-md-6">
          <div class="panel-title orange">EGRESOS (detalle)</div>
          <div class="panel-wrap">
            <table class="mini-table">
              <thead>
              <tr>
                <th class="th-left">Detalle</th>
                <th class="th-right">Total</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="e in egrSelectedList" :key="e.id">
                <td class="td-left">{{ e.detalle }}</td>
                <td class="td-right">{{ money(e.total) }}</td>
              </tr>
              <tr v-if="!egrSelectedList.length">
                <td colspan="2" class="td-center muted">Sin egresos</td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- RESUMEN: dos tablas arriba -->
    <div class="row q-col-gutter-sm">
      <div class="col-12 col-md-7">
        <q-card flat bordered class="card-like">
          <q-card-section class="q-pa-none">
            <div class="pdf-title blue">
              <div class="pdf-title-left">INGRESOS (TOTAL POR DÍA)</div>
            </div>

            <div class="table-wrap blue">
              <table class="pdf-table">
                <thead>
                <tr>
                  <th class="th-center w-dia">DIA</th>
                  <th class="th-left">fecha</th>
                  <th class="th-right w-total">TOTAL</th>
                </tr>
                </thead>

                <tbody>
                <tr
                  v-for="r in rows"
                  :key="r.fecha"
                  class="click-row"
                  :class="{ active: selectedDay === r.fecha }"
                  @click="selectDay(r.fecha)"
                >
                  <td class="td-center">{{ r.dia }}</td>
                  <td class="td-left">{{ r.label }}</td>
                  <td class="td-right td-bold">{{ numberNoDecimals(r.ingresos_total) }}</td>
                </tr>

                <tr v-if="!rows.length">
                  <td colspan="3" class="td-center muted">Sin datos</td>
                </tr>
                </tbody>
              </table>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- DERECHA: EGRESOS POR DÍA + TOTALES -->
      <div class="col-12 col-md-5">

        <q-card flat bordered class="card-like">
          <q-card-section class="q-pa-none">
            <div class="pdf-title orange">
              <div class="pdf-title-left">EGRESOS (TOTAL POR DÍA)</div>
            </div>

            <div class="table-wrap orange">
              <table class="pdf-table">
                <thead>
                <tr>
                  <th class="th-center w-dia">DIA</th>
                  <th class="th-left">fecha</th>
                  <th class="th-right w-total">TOTAL</th>
                </tr>
                </thead>

                <tbody>
                <tr
                  v-for="r in rows"
                  :key="r.fecha + '-e'"
                  class="click-row"
                  :class="{ active: selectedDay === r.fecha }"
                  @click="selectDay(r.fecha)"
                >
                  <td class="td-center">{{ r.dia }}</td>
                  <td class="td-left">{{ r.label }}</td>
                  <td class="td-right td-bold">{{ numberNoDecimals(r.egresos_total) }}</td>
                </tr>

                <tr v-if="!rows.length">
                  <td colspan="3" class="td-center muted">Sin datos</td>
                </tr>
                </tbody>
              </table>
            </div>

            <!-- mini resumen del día seleccionado -->
            <div v-if="selectedRow" class="day-chip">
              <q-icon name="event" class="q-mr-xs" />
              <span class="text-weight-bold">{{ selectedRow.label }}</span>
              <span class="q-ml-sm">| Ingresos: <b>{{ money(selectedRow.ingresos_total) }}</b></span>
              <span class="q-ml-sm">| Egresos: <b>{{ money(selectedRow.egresos_total) }}</b></span>
              <span class="q-ml-sm">| En caja: <b>{{ money(selectedRow.en_caja) }}</b></span>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <q-inner-loading :showing="loading">
      <q-spinner size="40px" />
    </q-inner-loading>
  </q-page>
</template>

<script>
import moment from 'moment'

export default {
  name: 'ReporteAjusteCajaGarden',
  data () {
    return {
      loading: false,
      title: 'AJUSTE EN CAJA GARDEN ORURO',
      filters: {
        date_from: null,
        date_to: null,
        pago: null,     // NUEVO
        user_id: null   // NUEVO
      },

      users: [],
      rows: [],
      detalle: { ingresos_by_day: {}, egresos_by_day: {} },
      totales: { ingresos: 0, egresos: 0, en_caja: 0 },

      selectedDay: null,

      pagoOptions: [
        { label: 'Todos', value: null },
        { label: 'EFECTIVO', value: 'EFECTIVO' },
        { label: 'QR', value: 'QR' }
      ]
    }
  },
  computed: {
    userOptions () {
      return [
        { label: 'Todos', value: null },
        ...this.users.map(u => ({ label: u.name, value: u.id }))
      ]
    },
    selectedRow () {
      return this.rows.find(r => r.fecha === this.selectedDay) || null
    },
    ingSelected () {
      if (!this.selectedDay) return {}
      return (this.detalle.ingresos_by_day?.[this.selectedDay]) || {}
    },
    egrSelectedList () {
      if (!this.selectedDay) return []
      return (this.detalle.egresos_by_day?.[this.selectedDay]) || []
    }
  },
  mounted () {
    this.setWeek()
  },
  methods: {
    money (v) {
      return Number(v || 0).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    },
    numberNoDecimals (v) {
      return Number(v || 0).toLocaleString('es-BO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      })
    },

    setToday () {
      const today = moment().format('YYYY-MM-DD')
      this.filters.date_from = today
      this.filters.date_to = today
      this.fetchData(false)
    },

    setWeek () {
      const start = moment().startOf('month')
      const end = moment().endOf('month')
      this.filters.date_from = start.format('YYYY-MM-DD')
      this.filters.date_to = end.format('YYYY-MM-DD')
      this.fetchData(false)
    },

    selectDay (fecha) {
      this.selectedDay = fecha
    },

    async fetchData (week = false) {
      this.loading = true
      try {
        const params = week
          ? { week: 1 }
          : {
            date_from: this.filters.date_from,
            date_to: this.filters.date_to,
            pago: this.filters.pago,
            user_id: this.filters.user_id
          }

        const { data } = await this.$axios.get('reportes/ajuste-caja', { params })

        this.title = data.title || this.title
        this.filters.date_from = data.date_from
        this.filters.date_to = data.date_to

        // mantener lo seleccionado (si backend devuelve null, se mantiene igual)
        this.filters.pago = data.pago ?? this.filters.pago
        this.filters.user_id = data.user_id ?? this.filters.user_id

        this.users = data.users || []
        this.rows = data.rows || []
        this.detalle = data.detalle || { ingresos_by_day: {}, egresos_by_day: {} }
        this.totales = data.totales || this.totales

        this.selectedDay = this.rows?.[0]?.fecha || null
        // selectDay del dia de hoy
        const today = moment().format('YYYY-MM-DD')
        this.selectDay(today)
      } catch (e) {
        this.$q.notify?.({
          type: 'negative',
          message: 'No se pudo cargar el reporte de ajuste en caja'
        })
      } finally {
        this.loading = false
      }
    },

    exportExcel () {
      const params = new URLSearchParams({
        date_from: this.filters.date_from,
        date_to: this.filters.date_to,
        pago: this.filters.pago || '',
        user_id: this.filters.user_id || ''
      }).toString()

      window.open(`${this.$axios.defaults.baseURL}/reportes/ajuste-caja/excel?${params}`, '_blank')
    }
  }
}
</script>

<style scoped>
.card-like{ border-radius: 10px; overflow: hidden; }

.pdf-title{ padding: 10px 12px; border-bottom: 2px solid #111; }
.pdf-title.blue{ background: #8fb0de; }
.pdf-title.orange{ background: #f2c7a9; }
.pdf-title-left{ font-weight: 900; letter-spacing: .2px; }

.table-wrap{ padding: 0; }
.table-wrap.blue{ background: #8fb0de; }
.table-wrap.orange{ background: #f2c7a9; }

.pdf-table{ width:100%; border-collapse: collapse; font-size: 13px; }
.pdf-table th,.pdf-table td{ border: 1px solid #111; padding: 6px 8px; }

.th-center{ text-align:center; font-weight:900; }
.th-left{ text-align:left; font-weight:900; }
.th-right{ text-align:right; font-weight:900; }

.td-center{ text-align:center; }
.td-left{ text-align:left; }
.td-right{ text-align:right; }
.td-bold{ font-weight:900; }

.muted{ color: rgba(0,0,0,.65); }

.w-dia{ width: 70px; }
.w-total{ width: 110px; }

.click-row{ cursor: pointer; }
.click-row:hover{ filter: brightness(0.98); }
.click-row.active{ outline: 2px solid rgba(0,0,0,.55); outline-offset: -2px; }

.totales-box{
  background: #8fa0b8;
  border: 2px solid #111;
  padding: 10px 12px;
}
.totales-title{ font-weight:900; text-align:center; margin-bottom:8px; }
.tot-row{
  display:flex; justify-content:space-between;
  border-top: 1px solid rgba(0,0,0,.35);
  padding: 6px 0;
}
.tot-row:first-of-type{ border-top:none; }
.tot-strong .label,.tot-strong .val{ font-weight:900; }
.label,.val{ font-weight:900; }

.day-chip{
  padding: 8px 10px;
  border-top: 2px solid rgba(0,0,0,.35);
  font-size: 12px;
  display:flex;
  align-items:center;
  flex-wrap: wrap;
  gap: 6px;
}

.panel-title{
  font-weight: 900;
  padding: 8px 10px;
  border: 2px solid #111;
  border-bottom: none;
}
.panel-title.blue{ background:#8fb0de; }
.panel-title.orange{ background:#f2c7a9; }

.panel-wrap{
  border: 2px solid #111;
  background: #fff;
  padding: 0;
  overflow: hidden;
}
.mini-table{
  width:100%;
  border-collapse: collapse;
  font-size: 13px;
}
.mini-table th,.mini-table td{
  border-top: 1px solid rgba(0,0,0,.15);
  padding: 8px 10px;
}
</style>
