<template>
  <q-page class="q-pa-md bg-grey-3">
    <!-- HEADER + FILTROS + ACCIONES RÁPIDAS -->
    <q-card flat bordered class="q-mb-md">
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
    <div class="row q-col-gutter-md q-mb-md">
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
    <div class="row q-col-gutter-md">
      <!-- Movimiento por día -->
      <div class="col-12 col-md-8">
        <q-card flat bordered>
          <q-card-section class="row items-center">
            <div class="text-subtitle1 text-weight-bold">
              Movimiento diario (Ingresos vs Egresos vs Neto)
            </div>
            <q-space />
            <q-btn
              dense
              flat
              icon="fullscreen"
              round
              @click="fullScreenChart = !fullScreenChart"
            >
              <q-tooltip>
                Cambiar tamaño
              </q-tooltip>
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
            <div
              v-else
              class="text-grey text-center q-pa-md text-caption"
            >
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
            <div
              v-else
              class="text-grey text-center q-pa-md text-caption"
            >
              No hay ventas por tipo de pago en este rango.
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script>
export default {
  name: 'IndexPage',
  data () {
    return {
      loading: false,
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
      chartVentas: {
        series: [],
        options: {}
      },
      chartPagos: {
        series: [],
        options: {}
      }
    }
  },
  mounted () {
    this.prefillDates()
    this.fetchDashboard()
  },
  methods: {
    prefillDates () {
      const today = new Date()
      const y = today.getFullYear()
      const m = String(today.getMonth() + 1).padStart(2, '0')
      const first = `${y}-${m}-01`
      const last = `${y}-${m}-${String(new Date(y, today.getMonth() + 1, 0).getDate()).padStart(2, '0')}`
      this.filters.date_from = first
      this.filters.date_to = last
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
      } catch (e) {
        const msg = e?.response?.data?.message || 'No se pudo cargar el dashboard'
        this.$q.notify?.({ type: 'negative', message: msg })
      } finally {
        this.loading = false
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
          chart: {
            type: 'bar',
            toolbar: { show: false }
          },
          dataLabels: { enabled: false },
          stroke: {
            curve: 'smooth',
            width: 2
          },
          xaxis: {
            categories,
            labels: { rotate: -45 }
          },
          yaxis: {
            labels: {
              formatter: v => Number(v || 0).toFixed(0)
            }
          },
          legend: {
            position: 'top'
          },
          tooltip: {
            y: {
              formatter: val => this.money(val) + ' Bs'
            }
          }
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
          legend: {
            position: 'bottom'
          },
          tooltip: {
            y: {
              formatter: val => this.money(val) + ' Bs'
            }
          }
        }
      }
    },
    money (v) {
      return Number(v || 0).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    }
  }
}
</script>

<style scoped>
.kpi {
  border-radius: 16px;
}
.kpi-green {
  box-shadow: inset 0 0 0 2px rgba(76, 175, 80, 0.25);
}
.kpi-red {
  box-shadow: inset 0 0 0 2px rgba(244, 67, 54, 0.25);
}
.kpi-indigo {
  box-shadow: inset 0 0 0 2px rgba(63, 81, 181, 0.25);
}
</style>
