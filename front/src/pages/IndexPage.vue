<template>
  <q-page class="q-pa-md bg-grey-3">
    <!-- HEADER + FILTROS + ACCIONES RÁPIDAS -->
    <q-card flat bordered class="q-mb-md" v-if="$store.user.role=='Administrador'">
      <q-card-section>
        <div class="row items-center q-col-gutter-sm">
          <div class="col-12 col-md-6">
            <div class="text-h5 text-weight-bold">
              Dashboard general
            </div>
            <div class="text-caption text-grey-7">
              Resumen rápido de ventas, caja e insumos
            </div>
          </div>

          <div class="col-12 col-sm-3">
            <q-input
              v-model="filters.date_from"
              type="date"
              label="Desde"
              dense
              outlined
            />
          </div>
          <div class="col-12 col-sm-3">
            <q-input
              v-model="filters.date_to"
              type="date"
              label="Hasta"
              dense
              outlined
            />
          </div>

          <div class="col-12 col-sm-3 col-md-2">
            <q-btn
              color="primary"
              icon="refresh"
              label="Actualizar"
              no-caps
              class="full-width"
              :loading="loading"
              @click="fetchDashboard"
            />
          </div>
        </div>
      </q-card-section>

      <q-separator />

      <!-- BOTONES RÁPIDOS -->
      <q-card-section>
        <div class="row q-col-gutter-sm">
          <div class="col-6 col-sm-3">
            <q-btn
              color="primary"
              icon="point_of_sale"
              label="Nueva venta"
              no-caps
              class="full-width"
              @click="$router.push({ path: '/ventas' })"
            />
          </div>
          <div class="col-6 col-sm-3">
            <q-btn
              color="teal"
              icon="shopping_cart"
              label="Nueva compra"
              no-caps
              class="full-width"
              @click="$router.push({ path: '/compras' })"
            />
          </div>
          <div class="col-6 col-sm-3">
            <q-btn
              color="indigo"
              icon="inventory_2"
              label="Productos"
              no-caps
              class="full-width"
              @click="$router.push({ path: '/productos' })"
            />
          </div>
          <div class="col-6 col-sm-3">
            <q-btn
              color="orange"
              outline
              icon="insights"
              label="Reportes detallados"
              no-caps
              class="full-width"
              @click="$router.push({ path: '/reportes' })"
            />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- KPIs -->
    <div class="row q-col-gutter-md q-mb-md" v-if="$store.user.role=='Administrador'">
      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered class="kpi kpi-green">
          <q-card-section>
            <div class="text-caption text-positive">Ingresos</div>
            <div class="text-h5 text-weight-bold">
              {{ money(kpis.ingresos) }} Bs
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered class="kpi kpi-red">
          <q-card-section>
            <div class="text-caption text-negative">Egresos</div>
            <div class="text-h5 text-weight-bold">
              {{ money(kpis.egresos) }} Bs
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered class="kpi kpi-indigo">
          <q-card-section>
            <div class="text-caption">Neto</div>
            <div class="text-h5 text-weight-bold">
              {{ money(kpis.neto) }} Bs
            </div>
            <div class="text-caption text-grey">
              Ticket prom.: {{ money(kpis.ticket_promedio) }} Bs
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered class="kpi">
          <q-card-section>
            <div class="text-caption">Resumen de ventas</div>
            <div class="text-h5 text-weight-bold">
              {{ kpis.ventas }} ventas
            </div>
            <div class="text-caption text-grey">
              Ítems vendidos: {{ kpis.items }}
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- GRÁFICOS -->
    <div class="row q-col-gutter-md" v-if="$store.user.role=='Administrador'">
      <!-- Movimiento por día -->
      <div class="col-12 col-md-8">
        <q-card flat bordered>
          <q-card-section class="row items-center">
            <div class="text-subtitle1 text-weight-bold">
              Movimiento diario (Ingresos vs Egresos vs Neto)
            </div>
            <q-space />
            <q-btn dense flat icon="fullscreen" round @click="fullScreenChart = !fullScreenChart">
              <q-tooltip>Cambiar tamaño</q-tooltip>
            </q-btn>
          </q-card-section>
          <q-separator />
          <q-card-section>
            <apexchart
              v-if="chartVentas.series.length"
              type="bar"
              height="320"
              :options="chartVentas.options"
              :series="chartVentas.series"
            />
            <div v-else class="text-grey text-center q-pa-md text-caption">
              No hay datos en el rango seleccionado.
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Distribución por tipo de pago -->
      <div class="col-12 col-md-4">
        <q-card flat bordered>
          <q-card-section class="text-subtitle1 text-weight-bold">
            Ventas por tipo de pago
          </q-card-section>
          <q-separator />
          <q-card-section>
            <apexchart
              v-if="chartPagos.series.length"
              type="donut"
              height="320"
              :options="chartPagos.options"
              :series="chartPagos.series"
            />
            <div v-else class="text-grey text-center q-pa-md text-caption">
              No hay ventas por tipo de pago en este rango.
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- ✅ NUEVO BLOQUE: PRODUCTOS POR CATEGORÍA -->
    <div class="row q-col-gutter-md q-mt-md" v-if="$store.user.role=='Administrador'">
      <div class="col-12">
        <q-card flat bordered>
          <q-card-section class="row items-center">
            <div>
              <div class="text-subtitle1 text-weight-bold">
                Productos (Pollos / Refrescos)
              </div>
              <div class="text-caption text-grey-7">
                Resumen por producto dentro del rango seleccionado
              </div>
            </div>
            <q-space />

            <q-chip dense color="indigo" text-color="white" icon="lunch_dining" class="q-mr-xs">
              Pollos: {{ pollosRows.length }}
            </q-chip>
            <q-chip dense color="blue" text-color="white" icon="local_drink" class="q-mr-sm">
              Refrescos: {{ refrescosRows.length }}
            </q-chip>

            <q-btn
              dense
              flat
              icon="refresh"
              round
              :loading="productosLoading"
              @click="fetchProductosResumen"
            >
              <q-tooltip>Actualizar productos</q-tooltip>
            </q-btn>
          </q-card-section>

          <q-separator />

          <q-card-section class="q-pt-sm">
            <!-- mini cards resumen -->
            <div class="row q-col-gutter-sm q-mb-sm">
              <div class="col-12 col-sm-4">
                <q-card flat bordered class="mini">
                  <q-card-section class="q-pa-sm">
                    <div class="text-caption text-grey-7">Total Pollos</div>
                    <div class="text-h6 text-weight-bold">
                      {{ money(totPollos.total_venta) }} Bs
                    </div>
                    <div class="text-caption text-grey-7">
                      Cantidad: <b>{{ num(totPollos.cantidad) }}</b>
                    </div>
                  </q-card-section>
                </q-card>
              </div>

              <div class="col-12 col-sm-4">
                <q-card flat bordered class="mini">
                  <q-card-section class="q-pa-sm">
                    <div class="text-caption text-grey-7">Total Refrescos</div>
                    <div class="text-h6 text-weight-bold">
                      {{ money(totRefrescos.total_venta) }} Bs
                    </div>
                    <div class="text-caption text-grey-7">
                      Cantidad: <b>{{ num(totRefrescos.cantidad) }}</b>
                    </div>
                  </q-card-section>
                </q-card>
              </div>

              <div class="col-12 col-sm-4">
                <q-card flat bordered class="mini">
                  <q-card-section class="q-pa-sm">
                    <div class="text-caption text-grey-7">Total (Pollos + Refrescos)</div>
                    <div class="text-h6 text-weight-bold text-primary">
                      {{ money(totGeneralProductos) }} Bs
                    </div>
                    <div class="text-caption text-grey-7">
                      % del ingreso: <b>{{ pctDelIngreso }}%</b>
                    </div>
                  </q-card-section>
                </q-card>
              </div>
            </div>

            <!-- tablas lado a lado -->
            <div class="row q-col-gutter-md">
              <!-- POLLOS -->
              <div class="col-12 col-md-6">
                <q-card flat bordered>
                  <q-card-section class="row items-center q-pa-sm">
                    <div class="text-subtitle1 text-weight-bold">
                      <q-icon name="lunch_dining" class="q-mr-xs" />
                      Pollos
                    </div>
                    <q-space />
                    <q-chip dense color="indigo" text-color="white">
                      {{ money(totPollos.total_venta) }} Bs
                    </q-chip>
                  </q-card-section>

                  <q-separator />

                  <q-card-section class="q-pa-none">
                    <q-markup-table dense flat bordered separator="horizontal">
                      <thead>
                      <tr>
                        <th class="text-left">Producto</th>
                        <th class="text-right" style="width: 90px;">Cant.</th>
                        <th class="text-right" style="width: 120px;">Total</th>
                        <th class="text-right" style="width: 90px;">%</th>
                      </tr>
                      </thead>

                      <tbody>
                      <tr v-for="r in pollosRows" :key="'po-' + (r.product_id || r.name)">
                        <td class="text-left">
                          <div class="row items-center no-wrap">
                            <q-avatar size="38px" class="q-mr-sm">
                              <img v-if="r.image" :src="img(r.image)" />
                              <q-icon v-else name="image" />
                            </q-avatar>

                            <div>
                              <div class="text-weight-medium">{{ r.name }}</div>
                              <div class="text-caption text-grey-7">
                                {{ r.categoria || 'Pollos' }}
                              </div>
                            </div>
                          </div>
                        </td>

                        <td class="text-right text-weight-bold">
                          {{ num(r.qty) }}
                        </td>

                        <td class="text-right text-weight-bold">
                          {{ money(r.total) }}
                        </td>

                        <td class="text-right">
                          <q-chip dense square color="grey-3" text-color="dark">
                            {{ pct(r.total, totPollos.total_venta) }}%
                          </q-chip>
                        </td>
                      </tr>

                      <tr v-if="!productosLoading && pollosRows.length === 0">
                        <td colspan="4" class="text-center text-grey q-pa-md">
                          Sin ventas de Pollos en este rango.
                        </td>
                      </tr>
                      </tbody>

                      <tfoot>
                      <tr>
                        <td class="text-right text-grey-7">TOTAL</td>
                        <td class="text-right text-weight-bold">{{ num(totPollos.cantidad) }}</td>
                        <td class="text-right text-weight-bold">{{ money(totPollos.total_venta) }}</td>
                        <td class="text-right text-weight-bold">100%</td>
                      </tr>
                      </tfoot>
                    </q-markup-table>
                  </q-card-section>
                </q-card>
              </div>

              <!-- REFRESCOS -->
              <div class="col-12 col-md-6">
                <q-card flat bordered>
                  <q-card-section class="row items-center q-pa-sm">
                    <div class="text-subtitle1 text-weight-bold">
                      <q-icon name="local_drink" class="q-mr-xs" />
                      Refrescos y Bebidas
                    </div>
                    <q-space />
                    <q-chip dense color="blue" text-color="white">
                      {{ money(totRefrescos.total_venta) }} Bs
                    </q-chip>
                  </q-card-section>

                  <q-separator />

                  <q-card-section class="q-pa-none">
                    <q-markup-table dense flat bordered separator="horizontal">
                      <thead>
                      <tr>
                        <th class="text-left">Producto</th>
                        <th class="text-right" style="width: 90px;">Cant.</th>
                        <th class="text-right" style="width: 120px;">Total</th>
                        <th class="text-right" style="width: 90px;">%</th>
                      </tr>
                      </thead>

                      <tbody>
                      <tr v-for="r in refrescosRows" :key="'re-' + (r.product_id || r.name)">
                        <td class="text-left">
                          <div class="row items-center no-wrap">
                            <q-avatar size="38px" class="q-mr-sm">
                              <img v-if="r.image" :src="img(r.image)" />
                              <q-icon v-else name="image" />
                            </q-avatar>

                            <div>
                              <div class="text-weight-medium">{{ r.name }}</div>
                              <div class="text-caption text-grey-7">
                                {{ r.categoria || 'Refrescos y Bebidas' }}
                              </div>
                            </div>
                          </div>
                        </td>

                        <td class="text-right text-weight-bold">{{ num(r.qty) }}</td>
                        <td class="text-right text-weight-bold">{{ money(r.total) }}</td>

                        <td class="text-right">
                          <q-chip dense square color="grey-3" text-color="dark">
                            {{ pct(r.total, totRefrescos.total_venta) }}%
                          </q-chip>
                        </td>
                      </tr>

                      <tr v-if="!productosLoading && refrescosRows.length === 0">
                        <td colspan="4" class="text-center text-grey q-pa-md">
                          Sin ventas de Refrescos en este rango.
                        </td>
                      </tr>
                      </tbody>

                      <tfoot>
                      <tr>
                        <td class="text-right text-grey-7">TOTAL</td>
                        <td class="text-right text-weight-bold">{{ num(totRefrescos.cantidad) }}</td>
                        <td class="text-right text-weight-bold">{{ money(totRefrescos.total_venta) }}</td>
                        <td class="text-right text-weight-bold">100%</td>
                      </tr>
                      </tfoot>
                    </q-markup-table>
                  </q-card-section>
                </q-card>
              </div>
            </div>
          </q-card-section>

          <q-inner-loading :showing="productosLoading">
            <q-spinner size="40px" />
          </q-inner-loading>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script>
import moment from "moment";

export default {
  name: 'IndexPage',
  data () {
    return {
      loading: false,
      productosLoading: false,
      fullScreenChart: false,
      filters: {
        date_from: '',
        date_to: ''
      },
      kpis: {
        ingresos: 0,
        egresos: 0,
        neto: 0,
        ventas: 0,
        items: 0,
        ticket_promedio: 0
      },
      porDia: [],
      pagos: [],
      chartVentas: { series: [], options: {} },
      chartPagos: { series: [], options: {} },

      // ✅ NUEVO: productos resumen
      productosResumen: [], // el backend devuelve todos y aquí filtramos
    }
  },

  computed: {
    pollosRows () {
      return (this.productosResumen || []).filter(r => String(r.categoria || '').toLowerCase() === 'pollos')
    },
    refrescosRows () {
      return (this.productosResumen || []).filter(r => String(r.categoria || '').toLowerCase() === 'refrescos y bebidas')
    },

    totPollos () {
      return this.sumRows(this.pollosRows)
    },
    totRefrescos () {
      return this.sumRows(this.refrescosRows)
    },
    totGeneralProductos () {
      return this.toNum(this.totPollos.total_venta) + this.toNum(this.totRefrescos.total_venta)
    },
    pctDelIngreso () {
      const ingreso = this.toNum(this.kpis.ingresos)
      if (!ingreso) return 0
      return this.pct(this.totGeneralProductos, ingreso)
    }
  },

  mounted () {
    this.prefillDates()
    this.fetchDashboard()
  },

  methods: {
    prefillDates () {
      this.filters.date_from = moment().format('YYYY-MM-DD')
      this.filters.date_to = moment().format('YYYY-MM-DD')
    },

    async fetchDashboard () {
      this.loading = true
      try {
        const params = { ...this.filters }
        const { data } = await this.$axios.get('reportes/ventas', { params })

        this.kpis = data?.kpis || this.kpis
        this.porDia = data?.por_dia || []
        this.pagos = data?.pagos || []

        this.buildChartVentas()
        this.buildChartPagos()

        // ✅ NUEVO: productos por categorías
        await this.fetchProductosResumen()
      } catch (e) {
        const msg = e?.response?.data?.message || 'No se pudo cargar el dashboard'
        this.$q.notify?.({ type: 'negative', message: msg })
      } finally {
        this.loading = false
      }
    },

    async fetchProductosResumen () {
      this.productosLoading = true
      try {
        const params = { ...this.filters }
        // ✅ nuevo endpoint
        const { data } = await this.$axios.get('reportes/productos-resumen', { params })
        this.productosResumen = Array.isArray(data) ? data : []
      } catch (e) {
        this.productosResumen = []
        this.$q.notify?.({ type: 'negative', message: 'No se pudo cargar el resumen de productos' })
      } finally {
        this.productosLoading = false
      }
    },

    buildChartVentas () {
      const categories = this.porDia.map(r => r.date)
      const ingresos = this.porDia.map(r => Number(r.ingreso || 0))
      const egresos = this.porDia.map(r => Number(r.egreso || 0))
      const neto = this.porDia.map(r => Number(r.neto || 0))

      this.chartVentas = {
        series: [
          { name: 'Ingresos', data: ingresos },
          { name: 'Egresos', data: egresos },
          { name: 'Neto', data: neto }
        ],
        options: {
          chart: { type: 'bar', toolbar: { show: false } },
          dataLabels: { enabled: false },
          stroke: { curve: 'smooth', width: 2 },
          xaxis: { categories, labels: { rotate: -45 } },
          yaxis: { labels: { formatter: v => Number(v || 0).toFixed(0) } },
          legend: { position: 'top' },
          tooltip: { y: { formatter: val => this.money(val) + ' Bs' } }
        }
      }
    },

    buildChartPagos () {
      const labels = this.pagos.map(p => p.pago || 'SN')
      const series = this.pagos.map(p => Number(p.total || 0))

      this.chartPagos = {
        series,
        options: {
          labels,
          legend: { position: 'bottom' },
          tooltip: { y: { formatter: val => this.money(val) + ' Bs' } }
        }
      }
    },

    // ------- helpers -------
    toNum (v) {
      if (v === null || v === undefined) return 0
      if (typeof v === 'number') return isFinite(v) ? v : 0
      const s = String(v).trim().replace(/\s/g, '').replace(/,/g, '')
      const n = Number(s)
      return isFinite(n) ? n : 0
    },

    sumRows (rows) {
      return (rows || []).reduce((acc, r) => {
        acc.cantidad += this.toNum(r.qty)
        acc.total_venta += this.toNum(r.total)
        return acc
      }, { cantidad: 0, total_venta: 0 })
    },

    pct (part, total) {
      const p = this.toNum(part)
      const t = this.toNum(total)
      if (!t) return 0
      return Math.round((p / t) * 100)
    },

    img (path) {
      // path puede ser "productos/xxx.webp"
      return `${this.$url}/../storage/${path}`
    },

    money (v) {
      return Number(v || 0).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    },
    num (v) {
      return Number(v || 0).toLocaleString('es-BO', {
        maximumFractionDigits: 2
      })
    }
  }
}
</script>

<style scoped>
.kpi { border-radius: 16px; }
.kpi-green { box-shadow: inset 0 0 0 2px rgba(76, 175, 80, 0.25); }
.kpi-red { box-shadow: inset 0 0 0 2px rgba(244, 67, 54, 0.25); }
.kpi-indigo { box-shadow: inset 0 0 0 2px rgba(63, 81, 181, 0.25); }

.mini { border-radius: 14px; }
</style>
