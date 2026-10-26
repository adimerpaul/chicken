<template>
  <q-page class="q-pa-xs bg-grey-3">
    <template v-if="$store.user.role=='Administrador'">
      <!-- BARRA: TÍTULO + PERIODO -->
      <q-card flat class="hero q-mb-xs">
        <q-card-section class="q-py-xs q-px-sm row items-center q-col-gutter-xs">
          <div class="col-12 col-sm-auto q-mr-sm">
            <span class="text-subtitle1 text-weight-bold">Dashboard</span>
            <span class="text-caption hero-sub q-ml-sm">{{ periodoLabel }}</span>
          </div>
          <div class="col-auto">
            <q-btn-toggle
              v-model="periodo"
              no-caps
              unelevated
              rounded
              dense
              size="sm"
              class="periodo-toggle"
              toggle-color="white"
              toggle-text-color="primary"
              color="transparent"
              text-color="white"
              :options="[
                { label: 'Hoy', value: 'hoy' },
                { label: 'Ayer', value: 'ayer' },
                { label: '7 días', value: 'semana' },
                { label: 'Mes', value: 'mes' },
                { label: 'Rango', value: 'rango' }
              ]"
              @update:model-value="onPeriodo"
            />
          </div>
          <template v-if="periodo === 'rango'">
            <div class="col-6 col-sm-auto">
              <q-input v-model="filters.date_from" type="date" dense outlined dark
                       class="date-in" @update:model-value="fetchDashboard"/>
            </div>
            <div class="col-6 col-sm-auto">
              <q-input v-model="filters.date_to" type="date" dense outlined dark
                       class="date-in" @update:model-value="fetchDashboard"/>
            </div>
          </template>
          <q-space/>
          <div class="col-auto">
            <q-btn round flat dense size="sm" icon="refresh" color="white" :loading="loading" @click="fetchDashboard">
              <q-tooltip>Actualizar</q-tooltip>
            </q-btn>
          </div>
        </q-card-section>
      </q-card>

      <!-- KPIs -->
      <div class="row q-col-gutter-xs q-mb-xs">
        <div class="col-4 col-sm-4 col-md-2" v-for="k in kpiCards" :key="k.label">
          <q-card flat bordered class="kpi">
            <div class="row items-center no-wrap q-pa-xs">
              <div class="kpi-icon" :style="{ background: k.tint, color: k.color }">
                <q-icon :name="k.icon" size="16px"/>
              </div>
              <div class="q-ml-xs ellipsis">
                <div class="kpi-label ellipsis">{{ k.label }}</div>
                <div class="kpi-value ellipsis" :style="{ color: k.valueColor || '#212121' }">{{ k.value }}</div>
              </div>
            </div>
          </q-card>
        </div>
      </div>

      <!-- FILA 1: por día + tipo de pago + tipo de venta -->
      <div class="row q-col-gutter-xs q-mb-xs">
        <div class="col-12 col-md-6">
          <q-card flat bordered class="full-height">
            <div class="card-head row items-center no-wrap">
              <div class="card-title">{{ esUnDia ? 'Ingresos y egresos por hora' : 'Ingresos y egresos por día' }}</div>
              <q-space/>
              <q-btn-toggle
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
                  <q-tooltip>Vista de montaña</q-tooltip>
                </template>
                <template v-slot:bar>
                  <q-icon name="bar_chart" size="18px"/>
                  <q-tooltip>Vista de histograma</q-tooltip>
                </template>
              </q-btn-toggle>
            </div>
            <apexchart
              v-if="chartVentas.series.length"
              :key="chartTipo"
              :type="chartTipo"
              height="175"
              :options="chartVentas.options"
              :series="chartVentas.series"
            />
            <div v-else class="empty">Sin datos en el rango.</div>
          </q-card>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
          <q-card flat bordered class="full-height">
            <div class="card-head">
              <div class="card-title">Tipo de pago</div>
            </div>
            <apexchart
              v-if="chartPagos.series.length"
              type="donut"
              height="175"
              :options="chartPagos.options"
              :series="chartPagos.series"
            />
            <div v-else class="empty">Sin ventas por tipo de pago.</div>
          </q-card>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
          <q-card flat bordered class="full-height">
            <div class="card-head">
              <div class="card-title">Tipo de venta</div>
            </div>
            <apexchart
              v-if="chartMesas.series[0].data.length"
              type="bar"
              height="175"
              :options="chartMesas.options"
              :series="chartMesas.series"
            />
            <div v-else class="empty">Sin ventas en el rango.</div>
          </q-card>
        </div>
      </div>

      <!-- FILA 2: productos (pestañas) + por usuario -->
      <div class="row q-col-gutter-xs">
        <div class="col-12 col-md-9">
          <q-card flat bordered class="full-height">
            <div class="row items-center no-wrap q-pr-xs">
              <q-tabs
                v-model="tabProductos"
                dense
                no-caps
                inline-label
                align="left"
                active-color="primary"
                indicator-color="primary"
                class="text-grey-8"
              >
                <q-tab name="pollos" icon="lunch_dining">
                  <span class="q-ml-xs">Pollos</span>
                  <q-badge color="indigo" class="q-ml-xs">{{ money(totPollos.total_venta) }}</q-badge>
                </q-tab>
                <q-tab name="refrescos" icon="local_drink">
                  <span class="q-ml-xs">Refrescos</span>
                  <q-badge color="blue" class="q-ml-xs">{{ money(totRefrescos.total_venta) }}</q-badge>
                </q-tab>
              </q-tabs>
              <q-space/>
              <div class="text-caption text-grey-7 q-mr-xs gt-xs">
                Total <b class="text-primary">{{ money(totGeneralProductos) }} Bs</b>
                · {{ pctDelIngreso }}% del ingreso
              </div>
            </div>
            <q-separator/>

            <div class="row">
              <!-- histograma -->
              <div class="col-12 col-sm-7">
                <apexchart
                  v-if="productosTab.rows.length"
                  :key="tabProductos"
                  type="bar"
                  :height="chartProductosHeight"
                  :options="chartProductos.options"
                  :series="chartProductos.series"
                />
                <div v-else-if="!productosLoading" class="empty">Sin ventas en este rango.</div>
              </div>

              <!-- tabla compacta -->
              <div class="col-12 col-sm-5">
                <q-markup-table dense flat square separator="horizontal" class="tabla-prod">
                  <thead>
                  <tr>
                    <th class="text-left">Producto</th>
                    <th class="text-right">Cant.</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">%</th>
                  </tr>
                  </thead>
                  <tbody>
                  <tr v-for="r in productosTab.rows" :key="(r.product_id || r.name)">
                    <td class="text-left ellipsis" style="max-width: 160px;">{{ r.name }}</td>
                    <td class="text-right">{{ num(r.qty) }}</td>
                    <td class="text-right text-weight-medium">{{ money(r.total) }}</td>
                    <td class="text-right text-grey-7">{{ pct(r.total, productosTab.tot.total_venta) }}%</td>
                  </tr>
                  </tbody>
                  <tfoot>
                  <tr>
                    <td class="text-right text-grey-7">TOTAL</td>
                    <td class="text-right text-weight-bold">{{ num(productosTab.tot.cantidad) }}</td>
                    <td class="text-right text-weight-bold">{{ money(productosTab.tot.total_venta) }}</td>
                    <td class="text-right text-weight-bold">100%</td>
                  </tr>
                  </tfoot>
                </q-markup-table>
              </div>
            </div>

            <q-inner-loading :showing="productosLoading">
              <q-spinner size="32px"/>
            </q-inner-loading>
          </q-card>
        </div>

        <div class="col-12 col-md-3">
          <q-card flat bordered class="full-height">
            <div class="card-head">
              <div class="card-title">Ingresos por usuario</div>
            </div>
            <apexchart
              v-if="chartUsuarios.series[0].data.length"
              type="bar"
              :height="Math.max(130, chartUsuarios.series[0].data.length * 24 + 30)"
              :options="chartUsuarios.options"
              :series="chartUsuarios.series"
            />
            <div v-else class="empty">Sin datos.</div>
          </q-card>
        </div>
      </div>
    </template>
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
      tabProductos: 'pollos',
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
      porHora: [],
      pagos: [],
      mesas: [],
      porUsuario: [],
      chartVentas: { series: [], options: {} },
      chartTipo: 'area',
      diasEs: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
      apexLocale: {
        defaultLocale: 'es',
        locales: [{
          name: 'es',
          options: {
            months: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            shortMonths: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            days: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
            shortDays: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
            toolbar: {
              exportToSVG: 'Descargar SVG',
              exportToPNG: 'Descargar PNG',
              exportToCSV: 'Descargar CSV',
              menu: 'Menú',
              selection: 'Selección',
              selectionZoom: 'Zoom de selección',
              zoomIn: 'Acercar',
              zoomOut: 'Alejar',
              pan: 'Mover',
              reset: 'Restablecer zoom'
            }
          }
        }]
      },
      chartPagos: { series: [], options: {} },

      // el backend devuelve todos y aquí filtramos
      productosResumen: [],
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
      const neto = this.toNum(this.kpis.neto)
      return [
        { label: 'Ingresos', value: `${this.money(this.kpis.ingresos)} Bs`, icon: 'payments', color: '#166534', tint: 'rgba(22, 101, 52, 0.12)' },
        { label: 'Egresos', value: `${this.money(this.kpis.egresos)} Bs`, icon: 'money_off', color: '#9D174D', tint: 'rgba(157, 23, 77, 0.12)' },
        { label: 'Neto', value: `${this.money(neto)} Bs`, icon: 'account_balance_wallet', color: '#334155', tint: 'rgba(51, 65, 85, 0.12)', valueColor: neto < 0 ? '#C10015' : '#166534' },
        { label: 'Ventas', value: this.num(this.kpis.ventas), icon: 'receipt_long', color: '#1D4ED8', tint: 'rgba(29, 78, 216, 0.12)' },
        { label: 'Ítems vendidos', value: this.num(this.kpis.items), icon: 'lunch_dining', color: '#B45309', tint: 'rgba(180, 83, 9, 0.12)' },
        { label: 'Ticket promedio', value: `${this.money(this.ticketPromedio)} Bs`, icon: 'trending_up', color: '#E2542F', tint: 'rgba(226, 84, 47, 0.12)' }
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
    },
    productosTab () {
      return this.tabProductos === 'pollos'
        ? { rows: this.pollosRows, tot: this.totPollos, color: '#3F51B5' }
        : { rows: this.refrescosRows, tot: this.totRefrescos, color: '#1D4ED8' }
    },
    chartProductosHeight () {
      return Math.max(170, this.productosTab.rows.length * 20 + 30)
    },
    chartProductos () {
      const rows = this.productosTab.rows
      return {
        series: [{ name: 'Total', data: rows.map(r => this.toNum(r.total)) }],
        options: this.hbarOptions(rows.map(r => r.name), this.productosTab.color, {
          tooltipExtra: i => ` · ${this.num(rows[i]?.qty)} u.`
        })
      }
    },
    chartMesas () {
      // "mesa" también guarda marcas internas (INGRESO/GASTO/CAJA) que no son tipos de venta
      const internos = ['INGRESO', 'GASTO', 'CAJA']
      const rows = (this.mesas || [])
        .filter(m => !internos.includes(String(m.mesa || '').toUpperCase()))
        .sort((a, b) => this.toNum(b.total) - this.toNum(a.total))
      return {
        series: [{ name: 'Total', data: rows.map(r => this.toNum(r.total)) }],
        options: this.hbarOptions(rows.map(r => this.$filters?.textCapitalize ? this.$filters.textCapitalize(r.mesa || 'SN') : (r.mesa || 'SN')), '#0D9488', {
          tooltipExtra: i => ` · ${this.num(rows[i]?.items)} ventas`
        })
      }
    },
    chartUsuarios () {
      const rows = [...(this.porUsuario || [])]
        .filter(r => this.toNum(r.ingreso) > 0)
        .sort((a, b) => this.toNum(b.ingreso) - this.toNum(a.ingreso))
      return {
        series: [{ name: 'Ingreso', data: rows.map(r => this.toNum(r.ingreso)) }],
        options: this.hbarOptions(rows.map(r => r.name), '#E2542F')
      }
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
        // siempre los últimos 7 días contando hoy, no desde el lunes
        this.filters.date_from = fmt(moment().subtract(6, 'days'))
        this.filters.date_to = fmt(moment())
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
        this.porHora = data?.por_hora || []
        this.pagos = data?.pagos || []
        this.mesas = data?.mesas || []
        this.porUsuario = data?.por_usuario || []

        this.buildChartVentas()
        this.buildChartPagos()

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
      let categories, ingresos, egresos
      if (this.esUnDia) {
        // un solo día: histograma por hora, rellenando las horas sin movimiento
        const map = {}
        this.porHora.forEach(r => { map[Number(r.hora)] = r })
        const horas = Object.keys(map).map(Number)
        const desde = horas.length ? Math.min(...horas) : 0
        const hasta = horas.length ? Math.max(...horas) : -1
        categories = []; ingresos = []; egresos = []
        for (let h = desde; h <= hasta; h++) {
          categories.push(String(h).padStart(2, '0') + ':00')
          ingresos.push(this.toNum(map[h]?.ingreso))
          egresos.push(this.toNum(map[h]?.egreso))
        }
      } else {
        // rellena los días sin movimiento para que la curva no salte
        const map = {}
        this.porDia.forEach(r => { map[moment(r.date).format('YYYY-MM-DD')] = r })
        categories = []; ingresos = []; egresos = []
        const fin = moment(this.filters.date_to)
        for (let d = moment(this.filters.date_from); d.isSameOrBefore(fin, 'day'); d.add(1, 'day')) {
          const k = d.format('YYYY-MM-DD')
          categories.push(this.diasEs[d.day()] + ' ' + d.format('DD/MM'))
          ingresos.push(this.toNum(map[k]?.ingreso))
          egresos.push(this.toNum(map[k]?.egreso))
        }
      }

      this.chartVentas = {
        series: categories.length
          ? [{ name: 'Ingresos', data: ingresos }, { name: 'Egresos', data: egresos }]
          : [],
        options: {
          chart: {
            type: this.chartTipo,
            toolbar: { show: false },
            fontFamily: 'inherit',
            animations: { speed: 300 },
            zoom: { enabled: false },
            ...this.apexLocale
          },
          colors: ['#E2542F', '#334155'],
          plotOptions: {
            bar: {
              columnWidth: categories.length > 15 ? '85%' : (categories.length <= 1 ? '30%' : '65%'),
              borderRadius: 3,
              borderRadiusApplication: 'end'
            }
          },
          // montaña: curva suave con degradado; histograma: barras con separación
          stroke: this.chartTipo === 'area'
            ? { show: true, curve: 'monotoneCubic', width: 2.5, lineCap: 'round' }
            : { show: true, width: 1, colors: ['transparent'] },
          fill: this.chartTipo === 'area'
            ? {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.55, opacityTo: 0.02, stops: [0, 95, 100] }
              }
            : { type: 'solid' },
          markers: {
            size: this.chartTipo === 'area' && categories.length <= 1 ? 5 : 0,
            strokeWidth: 2,
            strokeColors: '#fff',
            hover: { size: 5 }
          },
          dataLabels: { enabled: false },
          grid: {
            borderColor: '#EEEEEE',
            strokeDashArray: 4,
            xaxis: { lines: { show: false } },
            padding: { left: 2, right: 2, top: -18, bottom: -8 }
          },
          xaxis: {
            categories,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
              rotate: categories.length > 10 ? -45 : 0,
              hideOverlappingLabels: true,
              style: { colors: '#757575', fontSize: '10px' }
            }
          },
          yaxis: {
            labels: {
              style: { colors: '#9E9E9E', fontSize: '10px' },
              formatter: v => this.num(Math.round(Number(v || 0)))
            }
          },
          legend: { show: true, position: 'top', horizontalAlign: 'right', fontSize: '10px', offsetY: -6, height: 16, markers: { width: 8, height: 8, radius: 2 } },
          states: { hover: { filter: { type: 'darken', value: 0.9 } } },
          tooltip: {
            shared: true,
            intersect: false,
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
          chart: { fontFamily: 'inherit', animations: { speed: 300 }, ...this.apexLocale },
          labels,
          colors,
          stroke: { width: 2, colors: ['#fff'] },
          legend: {
            position: 'bottom',
            fontSize: '10px',
            itemMargin: { horizontal: 3, vertical: 0 },
            markers: { width: 8, height: 8, radius: 2 },
            formatter: (label, opts) => {
              const val = opts.w.globals.series[opts.seriesIndex]
              return `${label} ${this.money(val)}`
            }
          },
          dataLabels: {
            enabled: true,
            style: { fontSize: '10px', fontWeight: 600 },
            dropShadow: { enabled: false },
            formatter: val => `${Math.round(val)}%`
          },
          plotOptions: {
            pie: {
              donut: {
                size: '66%',
                labels: {
                  show: true,
                  value: {
                    fontSize: '15px',
                    fontWeight: 700,
                    color: '#212121',
                    offsetY: 2,
                    formatter: val => this.money(val)
                  },
                  total: {
                    show: true,
                    label: 'Total Bs',
                    fontSize: '11px',
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

    // histograma horizontal compacto (ranking)
    hbarOptions (categories, color, { tooltipExtra } = {}) {
      return {
        chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'inherit', animations: { speed: 300 }, ...this.apexLocale },
        colors: [color],
        plotOptions: {
          bar: {
            horizontal: true,
            barHeight: '78%',
            borderRadius: 3,
            borderRadiusApplication: 'end',
            dataLabels: { position: 'top' }
          }
        },
        dataLabels: {
          enabled: true,
          offsetX: 28,
          style: { fontSize: '10px', fontWeight: 600, colors: ['#424242'] },
          formatter: v => this.num(Math.round(v))
        },
        grid: {
          borderColor: '#EEEEEE',
          strokeDashArray: 4,
          yaxis: { lines: { show: false } },
          padding: { left: 0, right: 30, top: -18, bottom: -10 }
        },
        xaxis: {
          categories,
          axisBorder: { show: false },
          axisTicks: { show: false },
          labels: { style: { colors: '#9E9E9E', fontSize: '10px' }, formatter: v => this.num(Math.round(Number(v || 0))) }
        },
        yaxis: {
          labels: { maxWidth: 130, style: { colors: '#424242', fontSize: '11px' } }
        },
        legend: { show: false },
        tooltip: {
          y: {
            formatter: (val, { dataPointIndex }) =>
              this.money(val) + ' Bs' + (tooltipExtra ? tooltipExtra(dataPointIndex) : '')
          }
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
/* Barra superior */
.hero {
  border-radius: 10px;
  background: linear-gradient(135deg, #e2542f 0%, #c2431c 55%, #a33314 100%);
  color: #fff;
}
.hero-sub {
  color: rgba(255, 255, 255, 0.8);
}
.periodo-toggle {
  border: 1px solid rgba(255, 255, 255, 0.4);
  border-radius: 999px;
  padding: 1px;
}
.date-in {
  min-width: 140px;
}

/* KPIs */
.kpi {
  border-radius: 8px;
}
.kpi-icon {
  width: 28px;
  height: 28px;
  border-radius: 7px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex: 0 0 auto;
}
.kpi-label {
  font-size: 11px;
  line-height: 1.1;
  color: #757575;
}
.kpi-value {
  font-size: 14px;
  line-height: 1.25;
  font-weight: 700;
}

/* Tarjetas */
.card-head {
  padding: 4px 6px 0;
}
.card-title {
  font-size: 12px;
  font-weight: 700;
  color: #424242;
}
.empty {
  color: #9e9e9e;
  font-size: 12px;
  text-align: center;
  padding: 16px 8px;
}

.tabla-prod {
  max-height: 340px;
  overflow: auto;
}
.tabla-prod th,
.tabla-prod td {
  font-size: 11px;
  padding: 1px 5px;
  height: 20px;
}
</style>
