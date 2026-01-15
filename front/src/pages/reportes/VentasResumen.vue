<template>
  <q-page class="q-pa-sm bg-grey-3">
    <!-- HEADER -->
    <q-card flat bordered class="q-mb-sm">
      <q-card-section class="row items-end q-col-gutter-sm">
        <div class="col-12 col-sm-3">
          <div class="text-h6 text-weight-bold">Resumen de Ventas</div>
          <div class="text-caption text-grey-7">
            Ventas, costos, ganancias y productos por tipo (Mesa / Llevar)
          </div>
        </div>

        <div class="col-6 col-sm-3">
          <q-input v-model="filters.date_from" type="date" dense outlined label="Desde" />
        </div>

        <div class="col-6 col-sm-3">
          <q-input v-model="filters.date_to" type="date" dense outlined label="Hasta" />
        </div>

        <div class="col-12 col-sm-3 text-right">
          <q-btn
            color="primary"
            icon="search"
            label="Actualizar"
            no-caps
            :loading="loading"
            @click="fetchAll"
            class="q-mr-sm"
          />
          <q-btn
            outline
            color="primary"
            icon="filter_alt_off"
            label="Hoy"
            no-caps
            @click="setToday"
          />
        </div>
      </q-card-section>
    </q-card>

    <!-- TARJETAS -->
    <div class="row q-col-gutter-sm">
      <div class="col-12 col-sm-3">
        <q-card flat bordered>
          <q-card-section class="q-pa-sm">
            <div class="text-caption text-grey-7">Vendido (INGRESO)</div>
            <div class="text-h6 text-bold">
              {{ money(resumen.ventas_brutas) }} Bs
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-3">
        <q-card flat bordered>
          <q-card-section class="q-pa-sm">
            <div class="text-caption text-grey-7">Costo insumos vendidos</div>
            <div class="text-h6 text-negative text-bold">
              {{ money(resumen.costo_insumos_vendidos) }} Bs
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-3">
        <q-card flat bordered>
          <q-card-section class="q-pa-sm">
            <div class="text-caption text-grey-7">Gastos / Egresos</div>
            <div class="text-h6 text-negative text-bold">
              {{ money(resumen.gastos) }} Bs
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-3">
        <q-card flat bordered>
          <q-card-section class="q-pa-sm">
            <div class="text-caption text-grey-7">Ganancia neta</div>
            <div class="text-h6 text-positive text-bold">
              {{ money(resumen.utilidad_neta) }} Bs
            </div>
            <div class="text-caption text-grey-7">
              (Utilidad bruta:
              <b>{{ money(resumen.utilidad_bruta) }} Bs</b>)
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- TEXTO RESUMEN -->
    <q-card flat bordered class="q-mt-sm">
      <q-card-section class="q-pa-sm">
        <div class="text-body2">
          Entre <b>{{ resumen.date_from || '-' }}</b> y <b>{{ resumen.date_to || '-' }}</b>
          vendiste <b>{{ money(resumen.ventas_brutas) }} Bs</b>, gastaste
          <b>{{ money(resumen.costo_insumos_vendidos + resumen.gastos) }} Bs</b>
          (insumos + egresos) y tu ganancia neta fue de
          <b>{{ money(resumen.utilidad_neta) }} Bs</b>.
        </div>
      </q-card-section>
    </q-card>

    <!-- TABLAS: MESA / LLEVAR -->
    <div class="row q-col-gutter-sm q-mt-sm">
      <!-- MESA -->
      <div class="col-12 col-md-6">
        <q-card flat bordered>
          <q-card-section class="row items-center q-pa-sm">
            <div class="text-subtitle1 text-weight-bold">
              <q-icon name="table_restaurant" class="q-mr-xs" />
              Productos en MESA
            </div>
            <q-space />
            <q-chip dense color="indigo" text-color="white" icon="inventory_2">
              {{ mesaRows.length }} items
            </q-chip>
          </q-card-section>

          <q-separator />

          <q-card-section class="q-pa-none">
            <q-markup-table dense flat bordered separator="horizontal">
              <thead>
              <tr>
                <th class="text-left">Producto</th>
                <th class="text-left" style="width: 70px;">Und</th>
                <th class="text-right" style="width: 95px;">Cant.</th>
                <th class="text-right" style="width: 120px;">Venta</th>
                <th class="text-right" style="width: 120px;">Costo</th>
                <th class="text-right" style="width: 120px;">Utilidad</th>
              </tr>
              </thead>

              <tbody>
              <tr v-for="r in mesaRows" :key="'mesa-' + (r.product_id || r.producto)">
                <td class="text-left">
                  <div class="text-weight-medium">{{ r.producto }}</div>
                  <div class="text-caption text-grey-7">ID: {{ r.product_id }}</div>
                </td>
                <td class="text-left">
                  <q-badge outline color="grey-7">{{ r.unidad || 'UND' }}</q-badge>
                </td>
                <td class="text-right text-weight-bold">{{ num(r.cantidad) }}</td>
                <td class="text-right">{{ money(r.total_venta) }}</td>
                <td class="text-right text-negative">{{ money(r.costo_insumos) }}</td>
                <td
                  class="text-right text-weight-bold"
                  :class="toNum(r.utilidad) >= 0 ? 'text-positive' : 'text-negative'"
                >
                  {{ money(r.utilidad) }}
                </td>
              </tr>

              <tr v-if="!loading && mesaRows.length === 0">
                <td colspan="6" class="text-center text-grey q-pa-md">
                  No hay ventas en MESA para este rango.
                </td>
              </tr>
              </tbody>

              <tfoot>
              <tr>
                <td colspan="2" class="text-right text-grey-7">TOTALES</td>
                <td class="text-right text-weight-bold">{{ num(mesaTot.cantidad) }}</td>
                <td class="text-right text-weight-bold">{{ money(mesaTot.venta) }}</td>
                <td class="text-right text-weight-bold text-negative">{{ money(mesaTot.costo) }}</td>
                <td
                  class="text-right text-weight-bold"
                  :class="mesaTot.utilidad >= 0 ? 'text-positive' : 'text-negative'"
                >
                  {{ money(mesaTot.utilidad) }}
                </td>
              </tr>
              </tfoot>
            </q-markup-table>
          </q-card-section>
        </q-card>
      </div>

      <!-- LLEVAR -->
      <div class="col-12 col-md-6">
        <q-card flat bordered>
          <q-card-section class="row items-center q-pa-sm">
            <div class="text-subtitle1 text-weight-bold">
              <q-icon name="takeout_dining" class="q-mr-xs" />
              Productos para LLEVAR
            </div>
            <q-space />
            <q-chip dense color="deep-orange" text-color="white" icon="inventory_2">
              {{ llevarRows.length }} items
            </q-chip>
          </q-card-section>

          <q-separator />

          <q-card-section class="q-pa-none">
            <q-markup-table dense flat bordered separator="horizontal">
              <thead>
              <tr>
                <th class="text-left">Producto</th>
                <th class="text-left" style="width: 70px;">Und</th>
                <th class="text-right" style="width: 95px;">Cant.</th>
                <th class="text-right" style="width: 120px;">Venta</th>
                <th class="text-right" style="width: 120px;">Costo</th>
                <th class="text-right" style="width: 120px;">Utilidad</th>
              </tr>
              </thead>

              <tbody>
              <tr v-for="r in llevarRows" :key="'llevar-' + (r.product_id || r.producto)">
                <td class="text-left">
                  <div class="text-weight-medium">{{ r.producto }}</div>
                  <div class="text-caption text-grey-7">ID: {{ r.product_id }}</div>
                </td>
                <td class="text-left">
                  <q-badge outline color="grey-7">{{ r.unidad || 'UND' }}</q-badge>
                </td>
                <td class="text-right text-weight-bold">{{ num(r.cantidad) }}</td>
                <td class="text-right">{{ money(r.total_venta) }}</td>
                <td class="text-right text-negative">{{ money(r.costo_insumos) }}</td>
                <td
                  class="text-right text-weight-bold"
                  :class="toNum(r.utilidad) >= 0 ? 'text-positive' : 'text-negative'"
                >
                  {{ money(r.utilidad) }}
                </td>
              </tr>

              <tr v-if="!loading && llevarRows.length === 0">
                <td colspan="6" class="text-center text-grey q-pa-md">
                  No hay ventas para LLEVAR en este rango.
                </td>
              </tr>
              </tbody>

              <tfoot>
              <tr>
                <td colspan="2" class="text-right text-grey-7">TOTALES</td>
                <td class="text-right text-weight-bold">{{ num(llevarTot.cantidad) }}</td>
                <td class="text-right text-weight-bold">{{ money(llevarTot.venta) }}</td>
                <td class="text-right text-weight-bold text-negative">{{ money(llevarTot.costo) }}</td>
                <td
                  class="text-right text-weight-bold"
                  :class="llevarTot.utilidad >= 0 ? 'text-positive' : 'text-negative'"
                >
                  {{ money(llevarTot.utilidad) }}
                </td>
              </tr>
              </tfoot>
            </q-markup-table>
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
  name: 'VentasResumen',
  data () {
    const today = moment().format('YYYY-MM-DD')
    return {
      loading: false,
      filters: {
        date_from: today,
        date_to: today
      },
      resumen: {
        date_from: null,
        date_to: null,
        ventas_brutas: 0,
        costo_insumos_vendidos: 0,
        gastos: 0,
        utilidad_bruta: 0,
        utilidad_neta: 0
      },

      // NUEVO: tablas mesa/llevar
      mesaRows: [],
      llevarRows: []
    }
  },

  computed: {
    mesaTot () {
      return this.sumTotales(this.mesaRows)
    },
    llevarTot () {
      return this.sumTotales(this.llevarRows)
    }
  },

  mounted () {
    this.fetchAll()
  },

  methods: {
    // ---------- helpers ----------
    toNum (v) {
      if (v === null || v === undefined) return 0
      if (typeof v === 'number') return isFinite(v) ? v : 0
      const s = String(v).trim().replace(/\s/g, '').replace(/,/g, '')
      const n = Number(s)
      return isFinite(n) ? n : 0
    },

    money (v) {
      return this.toNum(v).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    },

    num (v) {
      // cantidad sin Bs
      const n = this.toNum(v)
      return n.toLocaleString('es-BO', { maximumFractionDigits: 2 })
    },

    sumTotales (rows) {
      return (rows || []).reduce((acc, r) => {
        acc.cantidad += this.toNum(r.cantidad)
        acc.venta    += this.toNum(r.total_venta)
        acc.costo    += this.toNum(r.costo_insumos)
        acc.utilidad += this.toNum(r.utilidad)
        return acc
      }, { cantidad: 0, venta: 0, costo: 0, utilidad: 0 })
    },

    // ---------- filtros ----------
    setToday () {
      const today = moment().format('YYYY-MM-DD')
      this.filters.date_from = today
      this.filters.date_to = today
      this.fetchAll()
    },

    // ---------- fetch ----------
    async fetchAll () {
      this.loading = true
      try {
        await Promise.all([
          this.fetchResumen(),
          this.fetchMesa(),
          this.fetchLlevar()
        ])
      } finally {
        this.loading = false
      }
    },

    async fetchResumen () {
      const params = {
        date_from: this.filters.date_from,
        date_to: this.filters.date_to
      }
      const { data } = await this.$axios.get('salesResumen', { params })
      this.resumen = {
        date_from: data.date_from,
        date_to: data.date_to,
        ventas_brutas: data.ventas_brutas || 0,
        costo_insumos_vendidos: data.costo_insumos_vendidos || 0,
        gastos: data.gastos || 0,
        utilidad_bruta: data.utilidad_bruta || 0,
        utilidad_neta: data.utilidad_neta || 0
      }
    },

    // ✅ NUEVO: productos vendidos en MESA
    async fetchMesa () {
      try {
        const params = {
          date_from: this.filters.date_from,
          date_to: this.filters.date_to
        }
        const { data } = await this.$axios.get('salesResumenProductosMesa', { params })
        this.mesaRows = Array.isArray(data) ? data : []
      } catch (e) {
        this.mesaRows = []
        this.$q.notify?.({ type: 'negative', message: 'No se pudo cargar productos en MESA' })
      }
    },

    // ✅ NUEVO: productos vendidos para LLEVAR
    async fetchLlevar () {
      try {
        const params = {
          date_from: this.filters.date_from,
          date_to: this.filters.date_to
        }
        const { data } = await this.$axios.get('salesResumenProductosLlevar', { params })
        this.llevarRows = Array.isArray(data) ? data : []
      } catch (e) {
        this.llevarRows = []
        this.$q.notify?.({ type: 'negative', message: 'No se pudo cargar productos para LLEVAR' })
      }
    }
  }
}
</script>

<style scoped>
</style>
