<template>
  <q-page class="q-pa-md bg-grey-3">
    <q-card>
      <q-card-section class="q-pa-sm">
        <!-- Filtros -->
        <div class="row items-end">
          <div class="col-12 col-md-3 q-pa-xs">
            <q-input v-model="f.ini" label="Fecha Inicio" type="date" outlined dense/>
          </div>
          <div class="col-12 col-md-3 q-pa-xs">
            <q-input v-model="f.fin" label="Fecha Fin" type="date" outlined dense/>
          </div>
          <div class="col-12 col-md-3 q-pa-xs">
            <q-input v-model="f.q" label="Buscar (proveedor / nota / estado)" outlined dense clearable/>
          </div>
          <div class="col-12 col-md-3 q-pa-xs text-right">
<!--            http://localhost:9000/compras/insumos-->

<!--            crar compras de insumos-->
            <q-btn
              color="primary"
              label="Nueva Compra"
              icon="add_shopping_cart"
              no-caps
              class="text-bold q-mr-sm"
              :to="{ path: '/compras/insumos' }"
            />
            <q-btn
              color="indigo"
              label="Buscar"
              icon="search"
              no-caps
              class="text-bold"
              :loading="loading"
              @click="fetch"
            />
            <q-btn-dropdown class="q-ml-sm" color="green" label="Exportar" no-caps auto-close>
              <q-item clickable v-ripple @click="exportCSV">
                <q-item-section avatar><q-icon name="save_alt" /></q-item-section>
                <q-item-section>CSV</q-item-section>
              </q-item>
              <q-item clickable v-ripple @click="fetch">
                <q-item-section avatar><q-icon name="refresh" /></q-item-section>
                <q-item-section>Recargar</q-item-section>
              </q-item>
            </q-btn-dropdown>
          </div>
        </div>

        <!-- Tarjetas resumen -->
        <div class="row q-mt-sm">
          <div class="col-xs-12 col-md-4 q-pa-xs">
            <q-card flat bordered>
              <q-card-section class="row items-center">
                <q-icon name="trending_up" color="green" size="md" class="q-mr-sm"/>
                <div>
                  <div class="text-caption">Compras (Bs)</div>
                  <div class="text-h6 text-weight-bold">{{ money(totalCompras) }}</div>
                </div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-xs-12 col-md-4 q-pa-xs">
            <q-card flat bordered>
              <q-card-section class="row items-center">
                <q-icon name="block" color="red" size="md" class="q-mr-sm"/>
                <div>
                  <div class="text-caption">Anuladas (Bs)</div>
                  <div class="text-h6 text-weight-bold">{{ money(totalAnuladas) }}</div>
                </div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-xs-12 col-md-4 q-pa-xs">
            <q-card flat bordered>
              <q-card-section class="row items-center">
                <q-icon name="check_circle" color="blue" size="md" class="q-mr-sm"/>
                <div>
                  <div class="text-caption">Vigentes (Bs)</div>
                  <div class="text-h6 text-weight-bold">{{ money(totalVigentes) }}</div>
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>

        <!-- Tabla -->
        <div class="row q-mt-sm">
          <div class="col-12">
            <q-markup-table dense wrap-cells>
              <thead class="bg-black text-white">
              <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Proveedor</th>
                <th>Ítems</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Opciones</th>
              </tr>
              </thead>
              <tbody v-if="rows.length">
              <tr v-for="c in rows" :key="c.id">
                <td>{{ c.id }}</td>
                <td>{{ c.fecha }}</td>
                <td>{{ c.proveedor || '—' }}</td>
                <td class="text-right">{{ c.detalles_count }}</td>
                <td class="text-right text-bold">{{ money(c.total) }}</td>
                <td>
                  <q-chip
                    :color="c.estado === 'ANULADO' ? 'negative' : 'primary'"
                    text-color="white"
                    dense
                  >
                    {{ c.estado }}
                  </q-chip>
                </td>
                <td>
                  <q-btn flat dense round icon="visibility" @click="ver(c.id)"/>
                  <q-btn
                    flat dense round icon="block" color="negative"
                    v-if="c.estado === 'ACTIVO'"
                    @click="anular(c.id)"
                  />
                </td>
              </tr>
              </tbody>
              <tbody v-else>
              <tr>
                <td colspan="7" class="text-center">Sin compras</td>
              </tr>
              </tbody>
            </q-markup-table>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Ver compra -->
    <q-dialog v-model="dlgVer">
      <q-card style="min-width: 720px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1">Compra #{{ compra ? compra.id : '' }}</div>
          <q-space/>
          <q-btn flat round dense icon="close" @click="dlgVer = false"/>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <div class="q-mb-sm">
            <div><b>Fecha:</b> {{ compra ? compra.fecha : '' }}</div>
            <div><b>Proveedor:</b> {{ (compra && compra.proveedor) ? compra.proveedor : '—' }}</div>
            <div><b>Estado:</b> {{ compra ? compra.estado : '' }}</div>
            <div><b>Nota:</b> {{ (compra && compra.nota) ? compra.nota : '—' }}</div>
          </div>

          <q-table
            :rows="(compra && compra.detalles) ? compra.detalles : []"
            :columns="colsDetalles"
            flat
            bordered
            dense
            :rows-per-page-options="[0]"
          />

          <div class="text-right text-subtitle1 q-mt-sm">
            <b>Total:</b> {{ money(compra ? compra.total : 0) }} Bs
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'ComprasIndex',
  data () {
    return {
      f: {
        ini: new Date().toISOString().substr(0, 10),
        fin: new Date().toISOString().substr(0, 10),
        q: ''
      },
      loading: false,
      rows: [],
      dlgVer: false,
      compra: null,
      colsDetalles: [
        { name: 'insumo',   label: 'Insumo',   align: 'left',  field: r => (r.insumo && r.insumo.nombre) ? r.insumo.nombre : '' },
        { name: 'unidad',   label: 'Unidad',   align: 'left',  field: r => (r.insumo && r.insumo.unidad) ? r.insumo.unidad : '' },
        { name: 'cantidad', label: 'Cantidad', align: 'right', field: 'cantidad' },
        { name: 'costo',    label: 'Costo',    align: 'right', field: v => Number(v.costo || 0).toFixed(2) },
        { name: 'subtotal', label: 'Subtotal', align: 'right', field: v => Number(v.subtotal || 0).toFixed(2) }
      ]
    }
  },
  computed: {
    totalCompras () {
      return this.rows.reduce((a, r) => a + Number(r.total || 0), 0)
    },
    totalAnuladas () {
      return this.rows
        .filter(r => r.estado === 'ANULADO')
        .reduce((a, r) => a + Number(r.total || 0), 0)
    },
    totalVigentes () {
      return this.rows
        .filter(r => r.estado !== 'ANULADO')
        .reduce((a, r) => a + Number(r.total || 0), 0)
    }
  },
  mounted () {
    this.fetch()
  },
  methods: {
    money (v) {
      return Number(v || 0).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    },
    async fetch () {
      this.loading = true
      try {
        const res = await this.$axios.post('compras/report', {
          fechaInicio: this.f.ini,
          fechaFin: this.f.fin,
          q: this.f.q
        })
        this.rows = res.data
      } catch (e) {
        this.$alert && this.$alert.error && this.$alert.error(e.response?.data?.message || 'No se pudo cargar')
      } finally {
        this.loading = false
      }
    },
    async ver (id) {
      try {
        const res = await this.$axios.get(`compras/${id}`)
        this.compra = res.data
        this.dlgVer = true
      } catch (e) {
        this.$alert && this.$alert.error && this.$alert.error('No se pudo abrir la compra')
      }
    },
    anular (id) {
      this.$alert
        .dialog('¿Anular la compra? Esto revertirá el stock.')
        .onOk(async () => {
          this.loading = true
          try {
            await this.$axios.put(`compras/${id}/anular`)
            this.$alert && this.$alert.success && this.$alert.success('Compra anulada')
            this.fetch()
          } catch (e) {
            this.$alert && this.$alert.error && this.$alert.error(e.response?.data?.message || 'No se pudo anular')
          } finally {
            this.loading = false
          }
        })
    },
    exportCSV () {
      const header = ['ID', 'Fecha', 'Proveedor', 'Items', 'Total', 'Estado']
      const lines = this.rows.map(r => [
        r.id,
        r.fecha,
        (r.proveedor || ''),
        r.detalles_count,
        r.total,
        r.estado
      ])
      const csv = [header, ...lines].map(a => a.join(';')).join('\n')
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = 'compras.csv'
      a.click()
      URL.revokeObjectURL(url)
    }
  }
}
</script>
