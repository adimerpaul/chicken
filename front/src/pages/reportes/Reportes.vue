<template>
  <q-page class="q-pa-md bg-grey-3">
    <!-- Filtros -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row q-col-gutter-sm items-end">
        <div class="col-12 col-sm-3">
          <q-input v-model="filters.date_from" type="date" label="Desde" dense outlined />
        </div>
        <div class="col-12 col-sm-3">
          <q-input v-model="filters.date_to" type="date" label="Hasta" dense outlined />
        </div>
        <div class="col-12 col-sm-3">
          <q-select
            v-model="filters.user_id"
            :options="users"
            option-value="id"
            option-label="name"
            emit-value map-options
            clearable dense outlined
            label="Usuario"
          />
        </div>
        <div class="col-12 col-sm-3">
          <q-btn color="primary" icon="search" label="Consultar" :loading="loading" no-caps class="full-width"
                 @click="fetchAll"/>
        </div>
      </q-card-section>
    </q-card>

    <!-- KPIs -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered class="kpi kpi-green">
          <q-card-section>
            <div class="text-caption text-positive">Ingresos</div>
            <div class="text-h5 text-weight-bold">{{ money(kpis.ingresos) }} Bs</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered class="kpi kpi-red">
          <q-card-section>
            <div class="text-caption text-negative">Egresos</div>
            <div class="text-h5 text-weight-bold">{{ money(kpis.egresos) }} Bs</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered class="kpi kpi-indigo">
          <q-card-section>
            <div class="text-caption">Neto</div>
            <div class="text-h5 text-weight-bold">{{ money(kpis.neto) }} Bs</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered class="kpi">
          <q-card-section>
            <div class="text-caption">Ticket promedio</div>
            <div class="text-h5 text-weight-bold">{{ money(kpis.ticket_promedio) }} Bs</div>
            <div class="text-caption text-grey">#ventas: {{ kpis.ventas }} · ítems: {{ kpis.items }}</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Pagos y QR, Mesas -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-6">
        <q-card flat bordered>
          <q-card-section class="row items-center">
            <div class="text-subtitle1 text-weight-bold">Pagos</div>
            <q-space/><q-badge color="blue" outline>QR: {{ money(qr.total) }} Bs ({{ qr.items }})</q-badge>
          </q-card-section>
          <q-separator />
          <q-table :rows="pagos" :columns="colsPagos" dense flat bordered :rows-per-page-options="[0]" />
        </q-card>
      </div>
      <div class="col-12 col-md-6">
        <q-card flat bordered>
          <q-card-section class="text-subtitle1 text-weight-bold">Mesas</q-card-section>
          <q-separator />
          <q-table :rows="mesas" :columns="colsMesas" dense flat bordered :rows-per-page-options="[0]" />
        </q-card>
      </div>
    </div>

    <!-- Serie por día -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="text-subtitle1 text-weight-bold">Movimiento por día</q-card-section>
      <q-separator />
      <q-table :rows="porDia" :columns="colsDia" dense flat bordered :rows-per-page-options="[0]" />
    </q-card>

    <!-- Consumo de insumos -->
    <q-card flat bordered>
      <q-card-section class="row items-center">
        <div class="text-subtitle1 text-weight-bold">Consumo de insumos</div>
        <q-space/>
        <q-badge color="orange" outline class="q-mr-sm">Costo: {{ money(insumosResumen.costo_insumos) }} Bs</q-badge>
        <q-badge color="green" outline>Ganancia aprox: {{ money(insumosResumen.ganancia_aprox) }} Bs</q-badge>
      </q-card-section>
      <q-separator />
      <q-table :rows="insumos" :columns="colsInsumos" dense flat bordered :rows-per-page-options="[10,20,0]" />
    </q-card>
  </q-page>
</template>

<script>
export default {
  name: 'ReportesPage',
  data () {
    return {
      loading: false,
      users: [],

      filters: {
        date_from: '',
        date_to: '',
        user_id: null
      },

      // data
      kpis: { ingresos:0, egresos:0, neto:0, ventas:0, items:0, ticket_promedio:0 },
      pagos: [],
      mesas: [],
      qr: { total:0, items:0 },
      porDia: [],
      insumos: [],
      insumosResumen: { costo_insumos: 0, ingresos: 0, ganancia_aprox: 0 },

      // tablas
      colsPagos: [
        { name:'pago', label:'Pago', field:'pago', align:'left' },
        { name:'items', label:'# ventas', field:'items', align:'right', format:v=>Number(v).toLocaleString() },
        { name:'total', label:'Total (Bs)', field:'total', align:'right', format:v=>Number(v||0).toFixed(2) }
      ],
      colsMesas: [
        { name:'mesa',  label:'Mesa', field:'mesa', align:'left' },
        { name:'items', label:'# ventas', field:'items', align:'right', format:v=>Number(v).toLocaleString() },
        { name:'total', label:'Total (Bs)', field:'total', align:'right', format:v=>Number(v||0).toFixed(2) }
      ],
      colsDia: [
        { name:'date', label:'Fecha', field:'date', align:'left' },
        { name:'ingreso', label:'Ingreso', field:'ingreso', align:'right', format:v=>Number(v||0).toFixed(2) },
        { name:'egreso',  label:'Egreso',  field:'egreso',  align:'right', format:v=>Number(v||0).toFixed(2) },
        { name:'neto',    label:'Neto',    field:'neto',    align:'right', format:v=>Number(v||0).toFixed(2) }
      ],
      colsInsumos: [
        { name:'nombre', label:'Insumo', field:'nombre', align:'left' },
        { name:'unidad', label:'Unidad', field:'unidad', align:'left' },
        { name:'usado',  label:'Cantidad usada', field:'usado', align:'right', format:v=>Number(v||0).toFixed(2) },
        { name:'costo',  label:'Costo unit.', field:'costo', align:'right', format:v=>Number(v||0).toFixed(2) },
        { name:'costo_total', label:'Costo total', field:'costo_total', align:'right', format:v=>Number(v||0).toFixed(2) },
      ],
    }
  },
  mounted () {
    this.prefillDates()
    this.loadUsers()
    this.fetchAll()
  },
  methods: {
    prefillDates () {
      const today = new Date()
      const y = today.getFullYear(), m = String(today.getMonth()+1).padStart(2,'0')
      const first = `${y}-${m}-01`
      const last  = `${y}-${m}-${String(new Date(y, today.getMonth()+1, 0).getDate()).padStart(2,'0')}`
      this.filters.date_from = first
      this.filters.date_to   = last
    },
    async loadUsers () {
      try {
        const { data } = await this.$axios.get('users') // ajusta si tu endpoint es otro
        this.users = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : [])
      } catch (_) {}
    },
    async fetchAll () {
      this.loading = true
      try {
        const params = { ...this.filters }
        const a = await this.$axios.get('reportes/ventas', { params })
        this.kpis   = a.data?.kpis || this.kpis
        this.pagos  = a.data?.pagos || []
        this.qr     = a.data?.qr || { total:0, items:0 }
        this.mesas  = a.data?.mesas || []
        this.porDia = a.data?.por_dia || []

        const b = await this.$axios.get('reportes/insumos', { params })
        this.insumos        = b.data?.consumo || []
        this.insumosResumen = b.data?.resumen || this.insumosResumen
      } catch (e) {
        const msg = e?.response?.data?.message || 'No se pudieron obtener los reportes'
        this.$q.notify?.({ type:'negative', message: msg })
      } finally {
        this.loading = false
      }
    },
    money (v) {
      return Number(v || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    }
  }
}
</script>

<style scoped>
.kpi { border-radius: 16px; }
.kpi-green { box-shadow: inset 0 0 0 2px rgba(76,175,80,.25); }
.kpi-red   { box-shadow: inset 0 0 0 2px rgba(244,67,54,.25); }
.kpi-indigo{ box-shadow: inset 0 0 0 2px rgba(63,81,181,.25); }
</style>
