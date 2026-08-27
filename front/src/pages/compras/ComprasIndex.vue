<template>
  <q-page class="q-pa-md bg-grey-3">
    <q-card>
      <q-card-section class="q-pa-sm">
        <!-- Filtros -->
        <div class="row items-end q-col-gutter-sm">
          <div class="col-12 col-md-3 q-pa-xs">
            <q-input v-model="f.ini" label="Fecha Inicio" type="date" outlined dense/>
          </div>
          <div class="col-12 col-md-3 q-pa-xs">
            <q-input v-model="f.fin" label="Fecha Fin" type="date" outlined dense/>
          </div>
          <div class="col-12 col-md-3 q-pa-xs">
            <q-input
              v-model="f.q"
              label="Buscar proveedor / comentario / estado"
              outlined
              dense
              clearable
            >
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
          </div>
          <div class="col-12 col-md-3 q-pa-xs">
            <div class="row q-col-gutter-xs justify-end">
              <div class="col-6 col-md-auto">
                <q-btn
                  color="primary"
                  label="Nueva"
                  icon="add_shopping_cart"
                  no-caps
                  dense
                  class="text-bold full-width"
                  :to="{ path: '/compras/insumos' }"
                />
              </div>
              <div class="col-6 col-md-auto">
                <q-btn
                  color="indigo"
                  label="Buscar"
                  icon="search"
                  no-caps
                  dense
                  class="text-bold full-width"
                  :loading="loading"
                  @click="fetch"
                />
              </div>
              <div class="col-12 col-md-auto">
                <q-btn-dropdown color="green" label="Exportar" no-caps dense auto-close class="full-width">
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

        <!-- Lista en tarjetas (móvil / tablet) -->
        <div class="row q-col-gutter-sm q-mt-sm lt-md">
          <div v-for="c in rows" :key="`m-${c.id}`" class="col-12 col-sm-6">
            <q-card flat bordered>
              <q-card-section class="q-pa-sm">
                <div class="row items-center no-wrap">
                  <div class="col">
                    <div class="text-weight-bold">Compra #{{ c.id }}</div>
                    <div class="text-caption text-grey-7">{{ c.fecha }}</div>
                  </div>
                  <q-chip
                    dense
                    :color="c.estado === 'ANULADO' ? 'negative' : 'primary'"
                    text-color="white"
                    class="q-ma-none"
                  >
                    {{ c.estado }}
                  </q-chip>
                </div>

                <div class="q-mt-xs text-body2">
                  <q-icon name="local_shipping" size="16px" class="q-mr-xs text-grey-7"/>
                  {{ c.proveedor || '—' }}
                </div>
                <div v-if="c.nota" class="text-caption text-grey-7 ellipsis-2-lines">
                  {{ c.nota }}
                </div>

                <div class="row items-center q-gutter-xs q-mt-xs">
                  <q-chip dense square size="11px" color="blue-grey-1" text-color="dark" class="q-ma-none">
                    {{ c.pago || 'EFECTIVO' }}
                  </q-chip>
                  <q-chip dense square size="11px" color="grey-3" text-color="dark" class="q-ma-none">
                    {{ c.detalles_count }} ítems
                  </q-chip>
                  <q-space/>
                  <div class="text-subtitle1 text-weight-bold">{{ money(c.total) }} Bs</div>
                </div>
              </q-card-section>

              <q-separator/>

              <q-card-actions align="right" class="q-pa-xs">
                <q-btn flat dense no-caps size="sm" color="primary" icon="visibility" label="Ver detalle" @click="ver(c.id)"/>
                <q-btn flat dense no-caps size="sm" color="teal" icon="print" label="Imprimir" @click="imprimir(c.id)"/>
                <q-btn
                  v-if="c.estado === 'ACTIVO'"
                  flat dense no-caps size="sm" color="negative" icon="cancel" label="Anular"
                  @click="anular(c.id)"
                />
              </q-card-actions>
            </q-card>
          </div>

          <div v-if="!rows.length" class="col-12 text-center text-grey q-pa-lg">
            <q-icon name="inbox" size="32px"/>
            <div class="q-mt-xs">Sin compras</div>
          </div>
        </div>

        <!-- Tabla (escritorio) -->
        <div class="row q-mt-sm gt-sm">
          <div class="col-12" style="overflow-x: auto">
            <q-markup-table dense flat bordered wrap-cells>
              <thead class="bg-black text-white">
              <tr>
                <th style="width: 130px;">Opciones</th>
                <th>#</th>
                <th>Fecha</th>
                <th>Proveedor</th>
                <th>Comentario</th>
                <th>Pago</th>
                <th>Detalle</th>
                <th>Total</th>
                <th>Estado</th>
              </tr>
              </thead>
              <tbody v-if="rows.length">
              <tr v-for="c in rows" :key="c.id">
                <td>
                  <q-btn-dropdown dense label="Opciones" no-caps size="xs" color="primary">
                    <q-list>
                      <q-item clickable @click="ver(c.id)" v-close-popup>
                        <q-item-section avatar><q-icon name="visibility" /></q-item-section>
                        <q-item-section>Ver detalle</q-item-section>
                      </q-item>

                      <q-item clickable @click="imprimir(c.id)" v-close-popup>
                        <q-item-section avatar><q-icon name="print" /></q-item-section>
                        <q-item-section>Imprimir</q-item-section>
                      </q-item>

                      <q-item
                        clickable
                        v-if="c.estado === 'ACTIVO'"
                        @click="anular(c.id)"
                        v-close-popup
                      >
                        <q-item-section avatar><q-icon name="cancel" /></q-item-section>
                        <q-item-section>Anular compra</q-item-section>
                      </q-item>
                    </q-list>
                  </q-btn-dropdown>
                </td>
                <td>{{ c.id }}</td>
                <td>{{ c.fecha }}</td>
                <td>{{ c.proveedor || '—' }}</td>
                <td style="max-width: 280px;">
                  <div class="ellipsis" :title="c.nota || '—'">
                    {{ c.nota || '—' }}
                  </div>
                </td>
                <td>
                  <q-chip dense color="blue-grey-1" text-color="dark">
                    {{ c.pago || 'EFECTIVO' }}
                  </q-chip>
                </td>
                <td class="text-right">
                  <q-chip dense color="grey-3" text-color="dark">
                    {{ c.detalles_count }}
                  </q-chip>
                </td>
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
              </tr>
              </tbody>
              <tbody v-else>
              <tr>
                <td colspan="9" class="text-center">Sin compras</td>
              </tr>
              </tbody>
            </q-markup-table>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Ver compra -->
    <q-dialog v-model="dlgVer" :maximized="$q.screen.lt.sm">
      <q-card style="width: 760px; max-width: 96vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1 text-weight-bold">Compra #{{ compra ? compra.id : '' }}</div>
          <q-space/>
          <q-btn flat round dense icon="print" color="teal" @click="imprimirCompra(compra)">
            <q-tooltip>Imprimir</q-tooltip>
          </q-btn>
          <q-btn flat round dense icon="close" @click="dlgVer = false"/>
        </q-card-section>

        <q-card-section class="q-pt-sm scroll" style="max-height: 75vh">
          <!-- Datos de cabecera -->
          <div class="row q-col-gutter-xs q-mb-sm">
            <div class="col-12 col-sm-6">
              <div class="text-caption text-grey-7">Fecha</div>
              <div class="text-body2 text-weight-medium">{{ compra ? compra.fecha : '' }}</div>
            </div>
            <div class="col-12 col-sm-6">
              <div class="text-caption text-grey-7">Proveedor</div>
              <div class="text-body2 text-weight-medium">{{ (compra && compra.proveedor) ? compra.proveedor : '—' }}</div>
            </div>
            <div class="col-6 col-sm-3">
              <div class="text-caption text-grey-7">Pago</div>
              <div class="text-body2 text-weight-medium">{{ (compra && compra.pago) ? compra.pago : 'EFECTIVO' }}</div>
            </div>
            <div class="col-6 col-sm-3">
              <div class="text-caption text-grey-7">Estado</div>
              <q-chip
                dense
                class="q-ma-none"
                :color="(compra && compra.estado === 'ANULADO') ? 'negative' : 'primary'"
                text-color="white"
              >
                {{ compra ? compra.estado : '' }}
              </q-chip>
            </div>
            <div class="col-12">
              <div class="text-caption text-grey-7">Nota</div>
              <div class="text-body2">{{ (compra && compra.nota) ? compra.nota : '—' }}</div>
            </div>
          </div>

          <q-separator class="q-mb-sm"/>

          <!-- Detalle en tarjetas (móvil) -->
          <div class="lt-sm">
            <q-card
              v-for="(d, i) in (compra && compra.detalles) ? compra.detalles : []"
              :key="`d-${i}`"
              flat
              bordered
              class="q-mb-xs"
            >
              <q-card-section class="q-pa-sm">
                <div class="text-weight-bold text-body2">
                  {{ (d.insumo && d.insumo.nombre) ? d.insumo.nombre : '—' }}
                </div>
                <div class="row items-center q-mt-xs">
                  <div class="col text-caption text-grey-7">
                    {{ to2(d.cantidad) }} {{ (d.insumo && d.insumo.unidad) ? d.insumo.unidad : '' }}
                    × {{ to2(d.costo) }} Bs
                  </div>
                  <div class="text-weight-bold">{{ to2(d.subtotal) }} Bs</div>
                </div>
              </q-card-section>
            </q-card>
            <div v-if="!compra || !compra.detalles || !compra.detalles.length"
                 class="text-center text-grey q-pa-md">
              Sin detalles
            </div>
          </div>

          <!-- Detalle en tabla (escritorio) -->
          <div class="gt-xs" style="overflow-x: auto">
            <q-table
              :rows="(compra && compra.detalles) ? compra.detalles : []"
              :columns="colsDetalles"
              flat
              bordered
              dense
              row-key="id"
              hide-pagination
              :rows-per-page-options="[0]"
            />
          </div>

          <div class="text-right text-subtitle1 q-mt-sm">
            <b>Total:</b> {{ money(compra ? compra.total : 0) }} Bs
          </div>
        </q-card-section>

        <q-separator/>

        <q-card-actions align="right" class="q-pa-sm">
          <q-btn no-caps flat color="grey-8" label="Cerrar" @click="dlgVer = false"/>
          <q-btn no-caps color="teal" icon="print" label="Imprimir" @click="imprimirCompra(compra)"/>
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import { ImprimirCompra } from 'src/utils/ImprimirCompra'

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
    to2 (v) { return this.money(v) },
    imprimirCompra (compra) {
      if (!compra) return
      ImprimirCompra.compra(compra)
    },
    async imprimir (id) {
      try {
        const res = await this.$axios.get(`compras/${id}`)
        ImprimirCompra.compra(res.data)
      } catch (e) {
        this.$alert && this.$alert.error && this.$alert.error('No se pudo imprimir la compra')
      }
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
      const header = ['ID', 'Fecha', 'Proveedor', 'Comentario', 'Pago', 'Detalle', 'Total', 'Estado']
      const lines = this.rows.map(r => [
        r.id,
        r.fecha,
        (r.proveedor || ''),
        (r.nota || ''),
        (r.pago || 'EFECTIVO'),
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
