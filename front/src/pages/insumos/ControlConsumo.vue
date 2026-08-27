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
                <q-btn no-caps dense color="teal" icon="inventory" label="Inventario inicial" class="full-width"
                       @click="abrirInventario"/>
              </div>
              <div class="col-12 col-md-auto">
                <q-btn no-caps dense color="green-8" icon="file_download" label="Excel" class="full-width"
                       :loading="excelLoading" @click="exportarExcel"/>
              </div>
            </div>
          </div>
        </div>

        <!-- RESUMEN -->
        <div class="row q-col-gutter-sm q-mt-xs">
          <div class="col-6 col-md-3">
            <q-card flat bordered>
              <q-card-section class="q-pa-sm row items-center no-wrap">
                <q-icon name="receipt_long" color="indigo" size="28px" class="q-mr-sm"/>
                <div>
                  <div class="text-caption text-grey-7">Ventas de la semana</div>
                  <div class="text-h6 text-weight-bold">{{ totales.ventas || 0 }}</div>
                </div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-6 col-md-3">
            <q-card flat bordered>
              <q-card-section class="q-pa-sm row items-center no-wrap">
                <q-icon name="payments" color="green" size="28px" class="q-mr-sm"/>
                <div>
                  <div class="text-caption text-grey-7">Vendido (Bs)</div>
                  <div class="text-h6 text-weight-bold">{{ money(totales.vendido) }}</div>
                </div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-6 col-md-3">
            <q-card flat bordered>
              <q-card-section class="q-pa-sm row items-center no-wrap">
                <q-icon name="add_shopping_cart" size="28px" class="q-mr-sm" :style="{ color: colorCompras }"/>
                <div>
                  <div class="text-caption text-grey-7">Compras (entradas)</div>
                  <div class="text-h6 text-weight-bold" :style="{ color: colorCompras }">
                    {{ money(totales.compras) }}
                  </div>
                </div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-6 col-md-3">
            <q-card flat bordered>
              <q-card-section class="q-pa-sm row items-center no-wrap">
                <q-icon name="local_fire_department" size="28px" class="q-mr-sm" :style="{ color: colorVentas }"/>
                <div>
                  <div class="text-caption text-grey-7">Ventas (consumo)</div>
                  <div class="text-h6 text-weight-bold" :style="{ color: colorVentas }">
                    {{ money(totales.cantidad) }}
                  </div>
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>

        <!-- VENTAS POR DÍA -->
        <div class="q-mt-md">
          <div class="text-subtitle2 text-weight-bold q-mb-xs">Ventas por día</div>
          <div class="row q-col-gutter-xs">
            <div v-for="d in dias" :key="`v-${d.fecha}`" class="col-6 col-sm-4 col-md">
              <q-card flat bordered :class="d.ventas ? '' : 'bg-grey-2'">
                <q-card-section class="q-pa-sm text-center">
                  <div class="text-caption text-grey-7">{{ d.label }}</div>
                  <div class="text-weight-bold">{{ d.ventas }} ventas</div>
                  <div class="text-caption text-green-8">{{ money(d.total) }} Bs</div>
                </q-card-section>
              </q-card>
            </div>
          </div>
        </div>

        <q-separator class="q-my-md"/>

        <!-- CONSUMO POR INSUMO -->
        <div class="text-subtitle2 text-weight-bold q-mb-xs">
          Consumo por insumo
          <span class="text-caption text-grey-7">({{ data.desde }} al {{ data.hasta }})</span>
        </div>

        <!-- móvil: tarjetas -->
        <div class="lt-md">
          <q-card v-for="i in insumos" :key="`m-${i.id}`" flat bordered class="q-mb-sm">
            <q-card-section class="q-pa-sm">
              <div class="row items-center no-wrap">
                <div class="col">
                  <div class="text-weight-bold">{{ i.nombre }}</div>
                  <div class="text-caption text-grey-7">
                    Inicial: {{ money(i.inicial) }} {{ i.unidad }}
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-weight-bold" :style="{ color: colorCompras }">+{{ money(i.compras) }}</div>
                  <div class="text-weight-bold" :style="{ color: colorVentas }">−{{ money(i.cantidad) }}</div>
                </div>
              </div>

              <div class="row q-col-gutter-xs q-mt-xs">
                <div v-for="d in dias" :key="`md-${i.id}-${d.fecha}`" class="col-4">
                  <div class="dia-box" :class="{ 'dia-box--cero': !i.por_dia[d.fecha] && !i.compras_dia[d.fecha] }">
                    <div class="text-caption text-grey-7">{{ d.label }}</div>
                    <div class="text-weight-bold" :style="{ color: i.compras_dia[d.fecha] ? colorCompras : '#bdbdbd' }">
                      +{{ money(i.compras_dia[d.fecha]) }}
                    </div>
                    <div class="text-weight-bold" :style="{ color: i.por_dia[d.fecha] ? colorVentas : '#bdbdbd' }">
                      −{{ money(i.por_dia[d.fecha]) }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="row items-center q-mt-xs">
                <div class="text-caption text-grey-7">
                  Inicial + compras − ventas
                </div>
                <q-space/>
                <div class="text-caption">
                  Esperado:
                  <b :class="{ 'text-negative': i.esperado < 0 }">{{ money(i.esperado) }}</b>
                  {{ i.unidad }}
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>

        <!-- leyenda de colores -->
        <div class="row items-center q-gutter-sm q-mb-xs">
          <div class="row items-center">
            <span class="leyenda-punto" :style="{ background: colorCompras }"></span>
            <span class="text-caption q-ml-xs">Compras (entradas)</span>
          </div>
          <div class="row items-center">
            <span class="leyenda-punto" :style="{ background: colorVentas }"></span>
            <span class="text-caption q-ml-xs">Ventas (consumo)</span>
          </div>
          <q-space/>
          <div class="text-caption text-grey-7">Esperado = Inicial + Compras − Ventas</div>
        </div>

        <!-- escritorio: tabla -->
        <div class="gt-sm" style="overflow-x: auto">
          <q-markup-table dense flat bordered wrap-cells>
            <thead>
            <tr class="bg-grey-9 text-white">
              <th class="text-left">Insumo</th>
              <th class="text-center">Unid.</th>
              <th class="text-right">Inicial</th>
              <th v-for="d in dias" :key="`h-${d.fecha}`" class="text-right">{{ d.label }}</th>
              <th class="text-right">Compras</th>
              <th class="text-right">Ventas</th>
              <th class="text-right">Esperado</th>
            </tr>
            </thead>
            <tbody v-if="insumos.length">
            <tr v-for="i in insumos" :key="`t-${i.id}`">
              <td class="text-left text-weight-medium">{{ i.nombre }}</td>
              <td class="text-center">
                <q-chip dense square size="11px" color="grey-3" text-color="dark" class="q-ma-none">
                  {{ i.unidad }}
                </q-chip>
              </td>
              <td class="text-right text-weight-medium">
                {{ money(i.inicial) }}
                <q-tooltip v-if="i.inicial_fecha">Registrado el {{ i.inicial_fecha }}</q-tooltip>
              </td>
              <td v-for="d in dias" :key="`c-${i.id}-${d.fecha}`" class="text-right celda-dia">
                <div :style="{ color: i.compras_dia[d.fecha] ? colorCompras : '#bdbdbd' }">
                  +{{ money(i.compras_dia[d.fecha]) }}
                </div>
                <div :style="{ color: i.por_dia[d.fecha] ? colorVentas : '#bdbdbd' }">
                  −{{ money(i.por_dia[d.fecha]) }}
                </div>
              </td>
              <td class="text-right text-weight-bold" :style="{ color: colorCompras }">
                {{ money(i.compras) }}
              </td>
              <td class="text-right text-weight-bold" :style="{ color: colorVentas }">
                {{ money(i.cantidad) }}
              </td>
              <td class="text-right text-weight-bold" :class="{ 'text-negative': i.esperado < 0 }">
                {{ money(i.esperado) }}
              </td>
            </tr>
            </tbody>
            <tbody v-else>
            <tr>
              <td :colspan="dias.length + 6" class="text-center text-grey q-pa-md">
                Sin insumos marcados con «Control de consumo»
              </td>
            </tr>
            </tbody>
            <tfoot v-if="insumos.length" class="bg-grey-2">
            <tr>
              <td class="text-left text-weight-bold" colspan="2">Total</td>
              <td class="text-right text-weight-bold">{{ money(totales.inicial) }}</td>
              <td v-for="d in dias" :key="`f-${d.fecha}`" class="text-right celda-dia">
                <div class="text-weight-bold" :style="{ color: colorCompras }">+{{ money(totalDia(d.fecha, 'compras_dia')) }}</div>
                <div class="text-weight-bold" :style="{ color: colorVentas }">−{{ money(totalDia(d.fecha)) }}</div>
              </td>
              <td class="text-right text-weight-bold" :style="{ color: colorCompras }">{{ money(totales.compras) }}</td>
              <td class="text-right text-weight-bold" :style="{ color: colorVentas }">{{ money(totales.cantidad) }}</td>
              <td class="text-right text-weight-bold">{{ money(totalEsperado) }}</td>
            </tr>
            </tfoot>
          </q-markup-table>
        </div>

        <div v-if="!insumos.length" class="lt-md text-center text-grey q-pa-lg">
          <q-icon name="fact_check" size="32px"/>
          <div class="q-mt-xs">Sin insumos marcados con «Control de consumo»</div>
          <q-btn flat no-caps color="primary" label="Ir a Insumos" to="/insumos" class="q-mt-sm"/>
        </div>
      </q-card-section>
    </q-card>

    <!-- DIALOGO: INVENTARIO INICIAL -->
    <q-dialog v-model="invDlg" :maximized="$q.screen.lt.sm">
      <q-card style="width: 640px; max-width: 96vw">
        <q-card-section class="row items-center q-pb-sm">
          <div>
            <div class="text-subtitle1 text-weight-bold">Inventario inicial</div>
            <div class="text-caption text-grey-7">
              Si ya existe un inicial para esa fecha, se actualiza.
            </div>
          </div>
          <q-space/>
          <q-btn flat round dense icon="close" @click="invDlg = false"/>
        </q-card-section>

        <q-separator/>

        <q-card-section class="q-pa-sm">
          <div class="row items-center q-col-gutter-sm">
            <div class="col-12 col-sm-5">
              <q-input v-model="invFecha" type="date" dense outlined label="Fecha del inicial"
                       @update:model-value="cargarInventario"/>
            </div>
            <div class="col-12 col-sm-7">
              <div class="text-caption text-grey-7">
                Cargue el conteo físico. El control no usa el stock del sistema.
              </div>
            </div>
          </div>
        </q-card-section>

        <q-card-section class="q-pa-sm scroll" style="max-height: 58vh">
          <q-markup-table dense flat bordered>
            <thead class="bg-grey-2">
            <tr>
              <th class="text-left">Insumo</th>
              <th class="text-center">Unid.</th>
              <th class="text-right text-grey-7">Stock sistema (ref.)</th>
              <th class="text-right" style="width: 140px">Cantidad inicial</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="it in invItems" :key="`inv-${it.insumo_id}`">
              <td class="text-left">
                {{ it.nombre }}
                <q-badge v-if="it.registrado" color="teal" class="q-ml-xs">guardado</q-badge>
              </td>
              <td class="text-center">{{ it.unidad }}</td>
              <td class="text-right text-grey-5">{{ money(it.stock) }}</td>
              <td>
                <q-input v-model.number="it.cantidad" type="number" step="0.01" dense outlined/>
              </td>
            </tr>
            <tr v-if="!invItems.length">
              <td colspan="4" class="text-center text-grey q-pa-md">
                No hay insumos marcados con «Control de consumo»
              </td>
            </tr>
            </tbody>
          </q-markup-table>
        </q-card-section>

        <q-separator/>

        <q-card-actions align="right" class="q-pa-sm">
          <q-btn flat no-caps color="grey-8" label="Cancelar" @click="invDlg = false"/>
          <q-btn no-caps color="teal" icon="save" label="Guardar inicial"
                 :loading="invLoading" :disable="!invItems.length" @click="guardarInventario"/>
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'ControlConsumo',
  data () {
    return {
      loading: false,
      excelLoading: false,
      // compras y ventas con colores distintos (paleta validada)
      colorCompras: '#0D9488',
      colorVentas: '#E2542F',
      f: { ini: '', fin: '' },
      data: { desde: '', hasta: '', dias: [], insumos: [], totales: {} },
      // inventario inicial
      invDlg: false,
      invLoading: false,
      invFecha: '',
      invItems: []
    }
  },
  computed: {
    dias () { return this.data.dias || [] },
    insumos () { return this.data.insumos || [] },
    totales () { return this.data.totales || {} },
    totalEsperado () {
      return this.insumos.reduce((a, i) => a + Number(i.esperado || 0), 0)
    }
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
    totalDia (fecha, campo = 'por_dia') {
      return this.insumos.reduce((a, i) => a + Number((i[campo] || {})[fecha] || 0), 0)
    },

    // ---------- Inventario inicial ----------
    abrirInventario () {
      this.invFecha = this.f.ini
      this.invDlg = true
      this.cargarInventario()
    },
    async cargarInventario () {
      if (!this.invFecha) return
      this.invLoading = true
      try {
        const res = await this.$axios.get('inventario-inicial', { params: { fecha: this.invFecha } })
        this.invItems = res.data.items || []
      } catch (e) {
        this.$alert?.error?.('No se pudo cargar el inventario inicial')
      } finally {
        this.invLoading = false
      }
    },
    async guardarInventario () {
      this.invLoading = true
      try {
        await this.$axios.post('inventario-inicial', {
          fecha: this.invFecha,
          items: this.invItems.map(it => ({
            insumo_id: it.insumo_id,
            cantidad: Number(it.cantidad || 0)
          }))
        })
        this.$alert?.success?.('Inventario inicial guardado')
        this.invDlg = false
        this.fetch()
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo guardar el inventario inicial')
      } finally {
        this.invLoading = false
      }
    },

    // ---------- Excel ----------
    async exportarExcel () {
      this.excelLoading = true
      try {
        const res = await this.$axios.post('insumos/control-consumo/excel', {
          fechaInicio: this.f.ini,
          fechaFin: this.f.fin
        }, { responseType: 'blob' })

        const url = URL.createObjectURL(new Blob([res.data], {
          type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        }))
        const a = document.createElement('a')
        a.href = url
        a.download = `control_consumo_${this.f.ini}_${this.f.fin}.xlsx`
        a.click()
        URL.revokeObjectURL(url)
      } catch (e) {
        this.$alert?.error?.('No se pudo generar el Excel')
      } finally {
        this.excelLoading = false
      }
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
        const res = await this.$axios.post('insumos/control-consumo', {
          fechaInicio: this.f.ini,
          fechaFin: this.f.fin
        })
        this.data = res.data
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo cargar el control de consumo')
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.dia-box {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 4px 6px;
  text-align: center;
}
.dia-box--cero {
  background: #fafafa;
  color: #9e9e9e;
}
.celda-dia {
  line-height: 1.25;
  font-size: 12px;
  white-space: nowrap;
}
.leyenda-punto {
  width: 10px;
  height: 10px;
  border-radius: 3px;
  display: inline-block;
}
</style>
