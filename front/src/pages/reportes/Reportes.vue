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
            emit-value
            map-options
            clearable
            dense
            outlined
            label="Usuario"
          />
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
            <div class="text-caption text-grey">
              #ventas: {{ kpis.ventas }} · ítems: {{ kpis.items }}
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Pagos y Mesas -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-6">
        <q-card flat bordered>
          <q-card-section class="row items-center">
            <div class="text-subtitle1 text-weight-bold">Pagos</div>
            <q-space />
            <q-badge color="blue" outline>
              QR: {{ money(qr.total) }} Bs ({{ qr.items }})
            </q-badge>
          </q-card-section>
          <q-separator />
          <q-table
            :rows="pagos"
            :columns="colsPagos"
            dense
            flat
            bordered
            :rows-per-page-options="[0]"
          />
        </q-card>
      </div>

      <div class="col-12 col-md-6">
        <q-card flat bordered>
          <q-card-section class="text-subtitle1 text-weight-bold">
            Mesas
          </q-card-section>
          <q-separator />
          <q-table
            :rows="mesas"
            :columns="colsMesas"
            dense
            flat
            bordered
            :rows-per-page-options="[0]"
          />
        </q-card>
      </div>
    </div>

    <!-- Movimiento por día -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="text-subtitle1 text-weight-bold">
        Movimiento por día
      </q-card-section>
      <q-separator />
      <q-table
        :rows="porDia"
        :columns="colsDia"
        dense
        flat
        bordered
        :rows-per-page-options="[0]"
      />
    </q-card>

    <!-- Consumo de insumos (GENERAL) -->
    <q-card flat bordered>
      <q-card-section class="row items-center">
        <div class="text-subtitle1 text-weight-bold">Consumo de insumos</div>
      </q-card-section>
      <q-separator />
      <q-table
        :rows="insumos"
        :columns="colsInsumos"
        dense
        flat
        bordered
        :rows-per-page-options="[0, 20, 50, 100]"
      />
      <q-separator />
      <q-card-section class="row items-center q-gutter-sm">
        <q-badge color="orange" outline>
          Cantidad usada: {{ qty(insumosResumen.cantidad_usada) }}
        </q-badge>
        <q-badge color="indigo" outline>
          Stock actual: {{ qty(insumosResumen.stock_actual) }}
        </q-badge>
        <q-badge color="teal" outline>
          Total (usado + stock): {{ qty(insumosResumen.cantidad_total) }}
        </q-badge>

        <q-space />

        <q-badge color="brown" outline>
          Costo insumos: {{ money(insumosResumen.costo_insumos) }} Bs
        </q-badge>
        <q-badge color="green" outline>
          Ganancia aprox: {{ money(insumosResumen.ganancia_aprox) }} Bs
        </q-badge>
      </q-card-section>
    </q-card>

    <!-- ✅ NUEVO: Consumo filtrado (BEBIDAS + POLLO/PAPA/ARROZ) -->
    <q-card flat bordered class="q-mt-md">
      <q-card-section class="row items-center">
        <div class="text-subtitle1 text-weight-bold">
          Consumo (Bebidas + Pollo/Papa/Arroz)
        </div>

        <q-space />

        <q-badge color="orange" outline>
          Cantidad usada: {{ qty(insumosFiltroResumen.cantidad_usada) }}
        </q-badge>
        <q-badge color="indigo" outline>
          Stock actual: {{ qty(insumosFiltroResumen.stock_actual) }}
        </q-badge>
        <q-badge color="teal" outline>
          Total: {{ qty(insumosFiltroResumen.cantidad_total) }}
        </q-badge>

        <q-badge color="brown" outline>
          Costo: {{ money(insumosFiltroResumen.costo_insumos) }} Bs
        </q-badge>
      </q-card-section>

      <q-separator />

      <q-table
        :rows="insumosFiltro"
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

      kpis: {
        ingresos: 0,
        egresos: 0,
        neto: 0,
        ventas: 0,
        items: 0,
        ticket_promedio: 0
      },

      pagos: [],
      mesas: [],
      qr: { total: 0, items: 0 },
      porDia: [],

      insumos: [],
      insumosResumen: {
        costo_insumos: 0,
        ingresos: 0,
        ganancia_aprox: 0,
        cantidad_usada: 0,
        stock_actual: 0,
        cantidad_total: 0
      },

      // ✅ NUEVO
      insumosFiltro: [],
      insumosFiltroResumen: {
        costo_insumos: 0,
        cantidad_usada: 0,
        stock_actual: 0,
        cantidad_total: 0
      },

      colsPagos: [
        { name: 'pago', label: 'Pago', field: 'pago', align: 'left' },
        { name: 'items', label: '# ventas', field: 'items', align: 'right', format: v => Number(v).toLocaleString() },
        { name: 'total', label: 'Total (Bs)', field: 'total', align: 'right', format: v => Number(v || 0).toFixed(2) }
      ],

      colsMesas: [
        { name: 'mesa', label: 'Mesa', field: 'mesa', align: 'left' },
        { name: 'items', label: '# ventas', field: 'items', align: 'right', format: v => Number(v).toLocaleString() },
        { name: 'total', label: 'Total (Bs)', field: 'total', align: 'right', format: v => Number(v || 0).toFixed(2) }
      ],

      colsDia: [
        { name: 'date', label: 'Fecha', field: 'date', align: 'left' },
        { name: 'ingreso', label: 'Ingreso', field: 'ingreso', align: 'right', format: v => Number(v || 0).toFixed(2) },
        { name: 'egreso', label: 'Egreso', field: 'egreso', align: 'right', format: v => Number(v || 0).toFixed(2) },
        { name: 'neto', label: 'Neto', field: 'neto', align: 'right', format: v => Number(v || 0).toFixed(2) }
      ],

      colsInsumos: [
        { name: 'nombre', label: 'Insumo', field: 'nombre', align: 'left' },
        { name: 'unidad', label: 'Unidad', field: 'unidad', align: 'left' },
        { name: 'total_cant', label: 'Total', field: 'total_cant', align: 'right', format: v => Number(v || 0).toFixed(2) },
        { name: 'usado', label: 'Cant. usada', field: 'usado', align: 'right', format: v => Number(v || 0).toFixed(2) },
        { name: 'stock_actual', label: 'Stock actual', field: 'stock_actual', align: 'right', format: v => Number(v || 0).toFixed(2) },
        { name: 'costo', label: 'Costo unit. (Bs)', field: 'costo', align: 'right', format: v => Number(v || 0).toFixed(2) },
        { name: 'costo_total', label: 'Costo total (Bs)', field: 'costo_total', align: 'right', format: v => Number(v || 0).toFixed(2) }
      ]
    }
  },

  mounted () {
    this.prefillDates()
    this.loadUsers()
    this.fetchAll()
  },

  methods: {
    prefillDates () {
      this.filters.date_from = moment().format('YYYY-MM-DD')
      this.filters.date_to = moment().format('YYYY-MM-DD')
    },

    async loadUsers () {
      try {
        const { data } = await this.$axios.get('users')
        this.users = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : [])
      } catch (e) {}
    },

    async fetchAll () {
      this.loading = true
      try {
        const params = { ...this.filters }

        const a = await this.$axios.get('reportes/ventas', { params })
        this.kpis = a.data?.kpis || this.kpis
        this.pagos = a.data?.pagos || []
        this.qr = a.data?.qr || { total: 0, items: 0 }
        this.mesas = a.data?.mesas || []
        this.porDia = a.data?.por_dia || []

        const b = await this.$axios.get('reportes/insumos', { params })
        this.insumos = b.data?.consumo || []
        this.insumosResumen = b.data?.resumen || this.insumosResumen

        // ✅ NUEVO
        this.insumosFiltro = b.data?.consumo_filtrado || []
        this.insumosFiltroResumen = b.data?.resumen_filtrado || this.insumosFiltroResumen
      } catch (e) {
        const msg = e?.response?.data?.message || 'No se pudieron obtener los reportes'
        this.$q.notify?.({ type: 'negative', message: msg })
      } finally {
        this.loading = false
      }
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

<style scoped>
.kpi { border-radius: 16px; }
.kpi-green { box-shadow: inset 0 0 0 2px rgba(76, 175, 80, 0.25); }
.kpi-red { box-shadow: inset 0 0 0 2px rgba(244, 67, 54, 0.25); }
.kpi-indigo { box-shadow: inset 0 0 0 2px rgba(63, 81, 181, 0.25); }
</style>
