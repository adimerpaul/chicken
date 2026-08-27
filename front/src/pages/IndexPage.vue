<template>
  <q-page class="q-pa-sm bg-grey-3">
    <!-- HEADER + PERIODO -->
    <q-card flat class="hero q-mb-sm" v-if="$store.user.role=='Administrador'">
      <q-card-section class="q-pa-sm q-pb-xs">
        <div class="row items-start q-col-gutter-sm">
          <div class="col">
            <div class="text-h6 text-weight-bold">Dashboard general</div>
            <div class="text-caption hero-sub">{{ periodoLabel }}</div>
          </div>
          <div class="col-auto">
            <q-btn round flat dense icon="refresh" color="white" :loading="loading" @click="fetchDashboard">
              <q-tooltip>Actualizar</q-tooltip>
            </q-btn>
          </div>
        </div>

        <!-- Selector de periodo -->
        <div class="row items-center q-col-gutter-sm q-mt-xs">
          <div class="col-12 col-md-auto">
            <q-btn-toggle
              v-model="periodo"
              no-caps
              unelevated
              rounded
              class="periodo-toggle"
              toggle-color="white"
              toggle-text-color="primary"
              color="transparent"
              text-color="white"
              :options="[
                { label: 'Hoy', value: 'hoy', icon: 'today' },
                { label: 'Ayer', value: 'ayer' },
                { label: 'Semana', value: 'semana' },
                { label: 'Mes', value: 'mes' },
                { label: 'Rango', value: 'rango' }
              ]"
              @update:model-value="onPeriodo"
            />
          </div>
          <div class="col-6 col-md-2" v-if="periodo === 'rango'">
            <q-input v-model="filters.date_from" type="date" dense outlined dark label="Desde"
                     @update:model-value="fetchDashboard"/>
          </div>
          <div class="col-6 col-md-2" v-if="periodo === 'rango'">
            <q-input v-model="filters.date_to" type="date" dense outlined dark label="Hasta"
                     @update:model-value="fetchDashboard"/>
          </div>
        </div>
      </q-card-section>

      <!-- BOTONES RÁPIDOS -->
      <q-card-section class="q-pa-sm q-pt-none">
        <div class="row q-col-gutter-sm">
          <div class="col-6 col-sm-3">
            <q-btn unelevated no-caps class="full-width quick-btn" icon="point_of_sale" label="Nueva venta"
                   @click="$router.push({ path: '/ventas' })"/>
          </div>
          <div class="col-6 col-sm-3">
            <q-btn unelevated no-caps class="full-width quick-btn" icon="shopping_cart" label="Nueva compra"
                   @click="$router.push({ path: '/compras/insumos' })"/>
          </div>
          <div class="col-6 col-sm-3">
            <q-btn unelevated no-caps class="full-width quick-btn" icon="inventory_2" label="Productos"
                   @click="$router.push({ path: '/productos' })"/>
          </div>
          <div class="col-6 col-sm-3">
            <q-btn unelevated no-caps class="full-width quick-btn" icon="insights" label="Reportes"
                   @click="$router.push({ path: '/reportes' })"/>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- KPIs -->
    <div class="row q-col-gutter-sm q-mb-sm" v-if="$store.user.role=='Administrador'">
      <div class="col-6 col-md-3" v-for="k in kpiCards" :key="k.label">
        <q-card flat bordered class="kpi">
          <q-card-section class="q-pa-sm">
            <div class="row items-center no-wrap q-mb-xs">
              <div class="kpi-icon" :style="{ background: k.tint, color: k.color }">
                <q-icon :name="k.icon" size="18px"/>
              </div>
              <div class="text-caption text-grey-7 q-ml-sm">{{ k.label }}</div>
            </div>
            <div class="text-h6 text-weight-bold">{{ k.value }}</div>
            <div class="text-caption text-grey-6">{{ k.hint }}</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- GRÁFICOS -->
    <div class="row q-col-gutter-sm" v-if="$store.user.role=='Administrador'">
      <!-- Movimiento por día -->
      <div class="col-12 col-md-8">
        <q-card flat bordered class="full-height">
          <q-card-section class="q-pa-sm q-pb-none row items-center">
            <div class="col">
              <div class="text-subtitle1 text-weight-bold">
                {{ esUnDia ? 'Resumen del día' : 'Ingresos por día' }}
              </div>
              <div class="text-caption text-grey-6">{{ periodoLabel }}</div>
            </div>
            <q-btn-toggle
              v-if="!esUnDia"
              v-model="chartTipo"
              dense
              unelevated
              no-caps
              size="sm"
              toggle-color="primary"
              color="grey-3"
              text-color="grey-8"
              :options="[
                { value: 'area', slot: 'area' },
                { value: 'bar', slot: 'bar' }
              ]"
            >
              <template v-slot:area>
                <q-icon name="show_chart" size="18px"/>
                <q-tooltip>Vista de área</q-tooltip>
              </template>
              <template v-slot:bar>
                <q-icon name="bar_chart" size="18px"/>
                <q-tooltip>Vista de barras</q-tooltip>
              </template>
            </q-btn-toggle>
          </q-card-section>

          <q-card-section class="q-pa-sm">
            <!-- un solo día: número protagonista en vez de una barra suelta -->
            <div v-if="esUnDia" class="q-py-lg text-center">
              <div class="text-caption text-grey-6">Ingresos del día</div>
              <div class="hero-number">{{ money(kpis.ingresos) }} <span class="text-h5">Bs</span></div>
              <div class="row justify-center q-gutter-lg q-mt-md">
                <div>
                  <div class="text-h6 text-weight-bold">{{ kpis.ventas || 0 }}</div>
                  <div class="text-caption text-grey-6">ventas</div>
                </div>
                <div>
                  <div class="text-h6 text-weight-bold">{{ num(kpis.items) }}</div>
                  <div class="text-caption text-grey-6">ítems</div>
                </div>
                <div>
                  <div class="text-h6 text-weight-bold">{{ money(ticketPromedio) }}</div>
                  <div class="text-caption text-grey-6">ticket prom. (Bs)</div>
                </div>
              </div>
            </div>

            <apexchart
              v-else-if="chartVentas.series.length"
              :key="chartTipo"
              :type="chartTipo"
              height="260"
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
        <q-card flat bordered class="full-height">
          <q-card-section class="q-pa-sm q-pb-none">
            <div class="text-subtitle1 text-weight-bold">Ventas por tipo de pago</div>
            <div class="text-caption text-grey-6">Participación sobre el total cobrado</div>
          </q-card-section>
          <q-card-section class="q-pa-sm">
            <apexchart
              v-if="chartPagos.series.length"
              type="donut"
              height="250"
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
    <div class="row q-col-gutter-sm q-mt-sm" v-if="$store.user.role=='Administrador'">
      <div class="col-12">
        <q-card flat bordered>
          <q-card-section class="q-pa-sm row items-center">
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

          <q-card-section class="q-pa-sm">
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
      periodo: 'semana',
      chartTipo: 'area',
      // paleta categórica validada (contraste + daltonismo), en orden fijo
      paletaPagos: {
        EFECTIVO: '#E2542F',
        QR: '#1D4ED8',
        TARJETA: '#0D9488',
        ONLINE: '#7C3AED'
      },
      paletaExtra: ['#B45309', '#334155', '#9D174D', '#166534'],
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
    esUnDia () {
      return this.filters.date_from === this.filters.date_to
    },
    ticketPromedio () {
      const ventas = this.toNum(this.kpis.ventas)
      if (!ventas) return 0
      return this.toNum(this.kpis.ingresos) / ventas
    },
    periodoLabel () {
      const d = f => moment(f).format('DD/MM/YYYY')
      if (this.esUnDia) {
        const hoy = moment().format('YYYY-MM-DD')
        if (this.filters.date_from === hoy) return `Hoy · ${d(this.filters.date_from)}`
        return d(this.filters.date_from)
      }
      return `${d(this.filters.date_from)} al ${d(this.filters.date_to)}`
    },
    kpiCards () {
      return [
        {
          label: 'Ingresos',
          value: `${this.money(this.kpis.ingresos)} Bs`,
          hint: 'Total cobrado en el periodo',
          icon: 'payments',
          color: '#166534',
          tint: 'rgba(22, 101, 52, 0.12)'
        },
        {
          label: 'Ventas',
          value: this.num(this.kpis.ventas),
          hint: 'Comprobantes emitidos',
          icon: 'receipt_long',
          color: '#1D4ED8',
          tint: 'rgba(29, 78, 216, 0.12)'
        },
        {
          label: 'Ítems vendidos',
          value: this.num(this.kpis.items),
          hint: 'Unidades de productos',
          icon: 'lunch_dining',
          color: '#B45309',
          tint: 'rgba(180, 83, 9, 0.12)'
        },
        {
          label: 'Ticket promedio',
          value: `${this.money(this.ticketPromedio)} Bs`,
          hint: 'Ingresos / ventas',
          icon: 'trending_up',
          color: '#E2542F',
          tint: 'rgba(226, 84, 47, 0.12)'
        }
      ]
    },
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

  watch: {
    chartTipo () {
      this.buildChartVentas()
    }
  },

  mounted () {
    this.aplicarPeriodo('semana')
    this.fetchDashboard()
  },

  methods: {
    aplicarPeriodo (p) {
      const fmt = m => m.format('YYYY-MM-DD')
      if (p === 'hoy') {
        this.filters.date_from = fmt(moment())
        this.filters.date_to = fmt(moment())
      } else if (p === 'ayer') {
        const ayer = moment().subtract(1, 'day')
        this.filters.date_from = fmt(ayer)
        this.filters.date_to = fmt(ayer)
      } else if (p === 'semana') {
        this.filters.date_from = fmt(moment().startOf('isoWeek'))
        this.filters.date_to = fmt(moment().endOf('isoWeek'))
      } else if (p === 'mes') {
        this.filters.date_from = fmt(moment().startOf('month'))
        this.filters.date_to = fmt(moment().endOf('month'))
      }
    },

    onPeriodo (p) {
      if (p === 'rango') return
      this.aplicarPeriodo(p)
      this.fetchDashboard()
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
      const categories = this.porDia.map(r => moment(r.date).format('ddd DD/MM'))
      const ingresos = this.porDia.map(r => Number(r.ingreso || 0))

      this.chartVentas = {
        // una sola serie: un solo tono, sin leyenda (el título ya la nombra)
        series: [{ name: 'Ingresos', data: ingresos }],
        options: {
          chart: {
            type: this.chartTipo,
            toolbar: { show: false },
            fontFamily: 'inherit',
            animations: { speed: 400 },
            zoom: { enabled: false }
          },
          colors: ['#E2542F'],
          plotOptions: {
            bar: {
              columnWidth: categories.length > 15 ? '70%' : '45%',
              borderRadius: 4,
              borderRadiusApplication: 'end'
            }
          },
          // vista "cerros": curva suave con degradado bajo la línea
          stroke: {
            curve: 'smooth',
            width: this.chartTipo === 'area' ? 3 : 0,
            lineCap: 'round'
          },
          fill: this.chartTipo === 'area'
            ? {
                type: 'gradient',
                gradient: {
                  shadeIntensity: 1,
                  opacityFrom: 0.45,
                  opacityTo: 0.05,
                  stops: [0, 90, 100]
                }
              }
            : { type: 'solid' },
          markers: {
            size: 0,
            strokeWidth: 2,
            strokeColors: '#fff',
            hover: { size: 6 }
          },
          dataLabels: { enabled: false },
          grid: {
            borderColor: '#EEEEEE',
            strokeDashArray: 4,
            xaxis: { lines: { show: false } },
            padding: { left: 8, right: 8 }
          },
          xaxis: {
            categories,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
              rotate: categories.length > 10 ? -45 : 0,
              style: { colors: '#757575', fontSize: '11px' }
            }
          },
          yaxis: {
            labels: {
              style: { colors: '#9E9E9E', fontSize: '11px' },
              formatter: v => this.num(Math.round(Number(v || 0)))
            }
          },
          legend: { show: false },
          states: { hover: { filter: { type: 'darken', value: 0.9 } } },
          tooltip: {
            theme: 'light',
            x: { show: true },
            y: { formatter: val => this.money(val) + ' Bs' }
          }
        }
      }
    },

    buildChartPagos () {
      const labels = this.pagos.map(p => String(p.pago || 'SN').toUpperCase())
      const series = this.pagos.map(p => Number(p.total || 0))
      // el color sigue al medio de pago, no a su posición en el ranking
      let extra = 0
      const colors = labels.map(l => this.paletaPagos[l] || this.paletaExtra[extra++ % this.paletaExtra.length])

      this.chartPagos = {
        series,
        options: {
          chart: { fontFamily: 'inherit', animations: { speed: 400 } },
          labels,
          colors,
          stroke: { width: 2, colors: ['#fff'] },
          legend: {
            position: 'bottom',
            fontSize: '12px',
            markers: { width: 10, height: 10, radius: 3 },
            formatter: (label, opts) => {
              const val = opts.w.globals.series[opts.seriesIndex]
              return `${label} · ${this.money(val)} Bs`
            }
          },
          dataLabels: {
            enabled: true,
            style: { fontSize: '11px', fontWeight: 600 },
            dropShadow: { enabled: false },
            formatter: val => `${Math.round(val)}%`
          },
          plotOptions: {
            pie: {
              donut: {
                size: '65%',
                labels: {
                  show: true,
                  value: {
                    fontSize: '20px',
                    fontWeight: 700,
                    color: '#212121',
                    formatter: val => this.money(val)
                  },
                  total: {
                    show: true,
                    label: 'Total Bs',
                    fontSize: '12px',
                    color: '#757575',
                    formatter: w => this.money(
                      w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                    )
                  }
                }
              }
            }
          },
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
/* Cabecera */
.hero {
  border-radius: 16px;
  background: linear-gradient(135deg, #e2542f 0%, #c2431c 55%, #a33314 100%);
  color: #fff;
}
.hero-sub {
  color: rgba(255, 255, 255, 0.78);
}
.quick-btn {
  background: rgba(255, 255, 255, 0.16);
  color: #fff;
  border-radius: 10px;
  font-weight: 600;
}
.quick-btn:hover {
  background: rgba(255, 255, 255, 0.28);
}
.periodo-toggle {
  border: 1px solid rgba(255, 255, 255, 0.4);
  border-radius: 999px;
  padding: 2px;
}

/* KPIs */
.kpi {
  border-radius: 14px;
  transition: box-shadow 0.15s ease, transform 0.15s ease;
}
.kpi:hover {
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
  transform: translateY(-2px);
}
.kpi-icon {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex: 0 0 auto;
}

.hero-number {
  font-size: 44px;
  line-height: 1.1;
  font-weight: 800;
  color: #a33314;
}

.mini { border-radius: 14px; }
</style>
