<template>
  <q-page class="q-pa-md bg-grey-3">
    <q-card flat bordered>
      <q-card-section class="q-pa-sm">
        <!-- FILTROS -->
        <div class="row items-end q-col-gutter-sm">
          <div class="col-6 col-md-3 q-pa-xs">
            <q-input v-model="f.ini" label="Desde" type="date" outlined dense/>
          </div>
          <div class="col-6 col-md-3 q-pa-xs">
            <q-input v-model="f.fin" label="Hasta" type="date" outlined dense/>
          </div>
          <div class="col-12 col-md-6 q-pa-xs">
            <div class="row q-col-gutter-xs justify-end">
              <div class="col-6 col-md-auto">
                <q-btn no-caps dense outline color="primary" label="Semana actual" class="full-width"
                       @click="setSemana(0)"/>
              </div>
              <div class="col-6 col-md-auto">
                <q-btn no-caps dense outline color="primary" label="Semana anterior" class="full-width"
                       @click="setSemana(-1)"/>
              </div>
              <div class="col-6 col-md-auto">
                <q-btn no-caps dense color="indigo" icon="search" label="Consultar" class="full-width"
                       :loading="loading" @click="fetch"/>
              </div>
              <div class="col-6 col-md-auto">
                <q-btn no-caps dense color="green-8" icon="file_download" label="Excel" class="full-width"
                       :loading="excelLoading" @click="exportarExcel"/>
              </div>
            </div>
          </div>
        </div>

        <div class="text-subtitle2 text-weight-bold q-mt-md">
          INSUMOS CONTABLES
          <span class="text-caption text-grey-7">({{ fecha(data.desde) }} al {{ fecha(data.hasta) }})</span>
        </div>

        <!-- UNA TABLA POR PRODUCTO -->
        <div class="row q-col-gutter-md q-mt-xs">
          <div v-for="p in productos" :key="p.id" class="col-12 col-md-6">
            <q-markup-table dense flat bordered class="tabla-producto">
              <thead>
              <tr>
                <th colspan="4" class="text-center text-weight-bold bg-grey-9 text-white">
                  {{ String(p.nombre || '').toUpperCase() }}
                </th>
              </tr>
              <tr class="bg-grey-2">
                <th class="text-left">FECHA</th>
                <th class="text-right">KILOS</th>
                <th class="text-right">PRECIO</th>
                <th class="text-right">RELACIÓN KILO/PRECIO</th>
              </tr>
              </thead>
              <tbody>
              <tr class="fila-inicio">
                <td class="text-left">
                  Inicio
                  <q-tooltip v-if="p.inicial_fecha">Registrado el {{ fecha(p.inicial_fecha) }}</q-tooltip>
                </td>
                <td class="text-right">{{ money(p.inicial) }}</td>
                <td></td>
                <td></td>
              </tr>
              <tr v-for="r in p.filas" :key="`${p.id}-${r.fecha}`">
                <td class="text-left">{{ fecha(r.fecha) }}</td>
                <td class="text-right">{{ money(r.kilos) }}</td>
                <td class="text-right">{{ money(r.precio) }}</td>
                <td class="text-right text-weight-bold">
                  {{ r.relacion === null ? '—' : `${money(r.relacion)} bs` }}
                </td>
              </tr>
              <tr v-if="!p.filas.length">
                <td colspan="4" class="text-center text-grey">Sin compras en el periodo</td>
              </tr>
              </tbody>
            </q-markup-table>
          </div>
        </div>

        <div v-if="!productos.length && !loading" class="text-center text-grey q-pa-lg">
          <q-icon name="table_view" size="32px"/>
          <div class="q-mt-xs">Sin insumos marcados con «Control de consumo»</div>
          <q-btn flat no-caps color="primary" label="Ir a Insumos" to="/insumos" class="q-mt-sm"/>
        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
export default {
  name: 'ControlConsumoTabla',
  data () {
    return {
      loading: false,
      excelLoading: false,
      f: { ini: '', fin: '' },
      data: { desde: '', hasta: '', productos: [] }
    }
  },
  computed: {
    productos () { return this.data.productos || [] }
  },
  mounted () {
    this.setSemana(0)
    this.fetch()
  },
  methods: {
    money (v) {
      return Number(v || 0).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    },
    // YYYY-MM-DD -> DD/MM/YYYY
    fecha (v) {
      if (!v) return ''
      const [y, m, d] = String(v).substring(0, 10).split('-')
      return `${d}/${m}/${y}`
    },
    // offset 0 = semana actual, -1 = semana anterior (lunes a domingo)
    setSemana (offset) {
      const hoy = new Date()
      const dow = (hoy.getDay() + 6) % 7 // 0 = lunes
      const lunes = new Date(hoy)
      lunes.setDate(hoy.getDate() - dow + (offset * 7))
      const domingo = new Date(lunes)
      domingo.setDate(lunes.getDate() + 6)
      this.f.ini = this.toISO(lunes)
      this.f.fin = this.toISO(domingo)
      if (offset !== 0) this.fetch()
    },
    toISO (d) {
      const mm = String(d.getMonth() + 1).padStart(2, '0')
      const dd = String(d.getDate()).padStart(2, '0')
      return `${d.getFullYear()}-${mm}-${dd}`
    },
    async fetch () {
      this.loading = true
      try {
        const res = await this.$axios.post('insumos/control-consumo-tabla', {
          fechaInicio: this.f.ini,
          fechaFin: this.f.fin
        })
        this.data = res.data
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo cargar la tabla de insumos')
      } finally {
        this.loading = false
      }
    },
    async exportarExcel () {
      this.excelLoading = true
      try {
        const res = await this.$axios.post('insumos/control-consumo-tabla/excel', {
          fechaInicio: this.f.ini,
          fechaFin: this.f.fin
        }, { responseType: 'blob' })

        const url = URL.createObjectURL(new Blob([res.data], {
          type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        }))
        const a = document.createElement('a')
        a.href = url
        a.download = `insumos_contables_${this.f.ini}_${this.f.fin}.xlsx`
        a.click()
        URL.revokeObjectURL(url)
      } catch (e) {
        this.$alert?.error?.('No se pudo generar el Excel')
      } finally {
        this.excelLoading = false
      }
    }
  }
}
</script>

<style scoped>
.tabla-producto th,
.tabla-producto td {
  white-space: nowrap;
}
.fila-inicio td {
  font-weight: 600;
  background: #fafafa;
}
</style>
