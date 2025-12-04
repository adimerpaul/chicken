<template>
  <q-page class="q-pa-md bg-grey-3">
    <q-card flat bordered class="q-pa-sm">

      <!-- FILTROS -->
      <q-card-section class="q-pa-sm">
        <div class="row items-end q-col-gutter-sm">
          <div class="col-12 col-md-3">
            <q-input v-model="f.ini" label="Fecha inicio" type="date" dense outlined />
          </div>
          <div class="col-12 col-md-3">
            <q-input v-model="f.fin" label="Fecha fin" type="date" dense outlined />
          </div>
          <div class="col-12 col-md-3">
            <q-input
              v-model="f.q"
              label="Buscar (proveedor / nota / estado)"
              dense
              outlined
              clearable
            >
              <template #append><q-icon name="search" /></template>
            </q-input>
          </div>
          <div class="col-12 col-md-3 text-right">
            <q-btn
              color="primary"
              label="Nueva Compra"
              icon="add_shopping_cart"
              no-caps
              class="q-mr-sm"
              :to="{ path: '/compras-almacen/nueva' }"
            />
            <q-btn
              color="indigo"
              label="Buscar"
              icon="search"
              no-caps
              :loading="loading"
              @click="fetch"
            />
          </div>
        </div>
      </q-card-section>

      <!-- CARDS RESUMEN -->
      <q-card-section class="q-pt-none">
        <div class="row q-col-gutter-sm">
          <div class="col-xs-12 col-md-4">
            <q-card flat bordered class="bg-green-1">
              <q-card-section class="row items-center">
                <q-icon name="trending_up" color="green" size="md" class="q-mr-sm" />
                <div>
                  <div class="text-caption">Compras (Bs)</div>
                  <div class="text-h6 text-weight-bold">{{ money(totalCompras) }}</div>
                </div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-xs-12 col-md-4">
            <q-card flat bordered class="bg-red-1">
              <q-card-section class="row items-center">
                <q-icon name="block" color="red" size="md" class="q-mr-sm" />
                <div>
                  <div class="text-caption">Anuladas (Bs)</div>
                  <div class="text-h6 text-weight-bold">{{ money(totalAnuladas) }}</div>
                </div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-xs-12 col-md-4">
            <q-card flat bordered class="bg-blue-1">
              <q-card-section class="row items-center">
                <q-icon name="check_circle" color="blue" size="md" class="q-mr-sm" />
                <div>
                  <div class="text-caption">Vigentes (Bs)</div>
                  <div class="text-h6 text-weight-bold">{{ money(totalVigentes) }}</div>
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </q-card-section>

      <!-- TABLA -->
      <q-card-section class="q-pt-none">
        <q-table
          :rows="rows"
          :columns="columns"
          row-key="id"
          flat
          bordered
          dense
          :rows-per-page-options="[0]"
          :loading="loading"
          hide-bottom
          binary-state-sort
        >
          <template #body-cell-estado="props">
            <q-td :props="props">
              <q-chip
                dense
                square
                :color="props.row.estado === 'ANULADO' ? 'negative' : 'primary'"
                text-color="white"
              >
                {{ props.row.estado }}
              </q-chip>
            </q-td>
          </template>

          <template #body-cell-opciones="props">
            <q-td :props="props">
<!--              <q-btn-->
<!--                flat dense round icon="visibility"-->
<!--                @click="ver(props.row.id)"-->
<!--                :title="'Ver compra #' + props.row.id"-->
<!--              />-->
<!--              <q-btn-->
<!--                flat dense round icon="picture_as_pdf"-->
<!--                color="amber-10"-->
<!--                @click="pdf(props.row.id)"-->
<!--                :title="'Ver PDF'"-->
<!--              />-->
<!--              <q-btn-->
<!--                flat dense round icon="block"-->
<!--                color="negative"-->
<!--                v-if="props.row.estado === 'ACTIVO'"-->
<!--                @click="anular(props.row.id)"-->
<!--                :title="'Anular compra'"-->
<!--              />-->
              <q-btn-dropdown dense icon="more_vert" :label="'Compra #' + props.row.id" no-caps size="10px" color="primary">
                <q-list>
                  <q-item clickable v-ripple @click="ver(props.row.id)" v-close-popup>
                    <q-item-section avatar>
                      <q-icon name="visibility" />
                    </q-item-section>
                    <q-item-section>Ver compra #{{ props.row.id }}</q-item-section>
                  </q-item>

                  <q-item clickable v-ripple @click="pdf(props.row.id)" v-close-popup>
                    <q-item-section avatar>
                      <q-icon name="picture_as_pdf" color="amber-10" />
                    </q-item-section>
                    <q-item-section>Ver PDF</q-item-section>
                  </q-item>

                  <q-item
                    v-if="props.row.estado === 'ACTIVO'"
                    clickable v-ripple
                    @click="anular(props.row.id)"
                     v-close-popup
                  >
                    <q-item-section avatar>
                      <q-icon name="block" color="negative" />
                    </q-item-section>
                    <q-item-section>Anular compra</q-item-section>
                  </q-item>
                </q-list>
              </q-btn-dropdown>
            </q-td>
          </template>

          <template #no-data>
            <div class="full-width row flex-center text-grey q-gutter-sm">
              <q-icon name="inbox" />
              <span>Sin compras registradas</span>
            </div>
          </template>
        </q-table>
      </q-card-section>
    </q-card>

    <!-- DIALOG VER COMPRA -->
    <q-dialog v-model="dlgVer">
      <q-card style="min-width: 720px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1">
            Compra almacén #{{ compra ? compra.id : '' }}
          </div>
          <q-space />
          <q-btn flat round dense icon="close" @click="dlgVer = false" />
        </q-card-section>

        <q-card-section class="q-pt-none">
          <div class="q-mb-sm">
            <div><b>Fecha:</b> {{ compra?.fecha }}</div>
            <div><b>Proveedor:</b> {{ compra?.proveedor || '—' }}</div>
            <div><b>Estado:</b> {{ compra?.estado }}</div>
            <div><b>Nota:</b> {{ compra?.nota || '—' }}</div>
          </div>

          <q-table
            :rows="compra?.detalles || []"
            :columns="colsDetalles"
            dense
            flat
            bordered
            :rows-per-page-options="[0]"
            hide-bottom
          />

          <div class="text-right text-subtitle1 q-mt-sm">
            <b>Total:</b> {{ money(compra?.total || 0) }} Bs
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'ComprasAlmacenIndex',
  data () {
    const today = new Date().toISOString().substr(0, 10)
    return {
      f: {
        ini: today,
        fin: today,
        q: ''
      },
      loading: false,
      rows: [],
      dlgVer: false,
      compra: null,
      columns: [
        { name: 'id', label: '#', field: 'id', align: 'left', sortable: true },
        { name: 'opciones', label: 'Opciones', field: 'opciones', align: 'center' },
        { name: 'fecha', label: 'Fecha', field: 'fecha', align: 'left', sortable: true },
        { name: 'proveedor', label: 'Proveedor', field: 'proveedor', align: 'left' },
        { name: 'detalles_count', label: 'Ítems', field: 'detalles_count', align: 'right' },
        {
          name: 'total',
          label: 'Total (Bs)',
          field: 'total',
          align: 'right',
          format: v => Number(v || 0).toFixed(2),
          sortable: true
        },
        { name: 'estado', label: 'Estado', field: 'estado', align: 'left', sortable: true },
      ],
      colsDetalles: [
        {
          name: 'almacen',
          label: 'Insumo almacén',
          align: 'left',
          field: r => (r.almacen && r.almacen.nombre) ? r.almacen.nombre : ''
        },
        {
          name: 'unidad',
          label: 'Unidad',
          align: 'left',
          field: r => (r.almacen && r.almacen.unidad) ? r.almacen.unidad : ''
        },
        { name: 'cantidad', label: 'Cantidad', align: 'right', field: 'cantidad' },
        {
          name: 'costo',
          label: 'Costo',
          align: 'right',
          field: r => Number(r.costo || 0).toFixed(2)
        },
        {
          name: 'subtotal',
          label: 'Subtotal',
          align: 'right',
          field: r => Number(r.subtotal || 0).toFixed(2)
        }
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
        const res = await this.$axios.post('compras-almacen/report', {
          fechaInicio: this.f.ini,
          fechaFin: this.f.fin,
          q: this.f.q
        })
        this.rows = res.data
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo cargar las compras')
      } finally {
        this.loading = false
      }
    },
    async ver (id) {
      try {
        const res = await this.$axios.get(`compras-almacen/${id}`)
        this.compra = res.data
        this.dlgVer = true
      } catch (e) {
        this.$alert?.error?.('No se pudo abrir la compra')
      }
    },
    anular (id) {
      this.$alert
        .dialog('¿Anular la compra de almacén? Esto revertirá el stock.')
        .onOk(async () => {
          this.loading = true
          try {
            await this.$axios.put(`compras-almacen/${id}/anular`)
            this.$alert?.success?.('Compra anulada')
            this.fetch()
          } catch (e) {
            this.$alert?.error?.(e.response?.data?.message || 'No se pudo anular')
          } finally {
            this.loading = false
          }
        })
    },
    pdf (id) {
      // Ajusta la ruta según cómo expongas tu API (si usas /api delante, etc.)
      // ulr de axios
      const url = this.$axios.defaults.baseURL || ''
      // window.open(`/api/compras-almacen/${id}/pdf`, '_blank')
      window.open(`${url}/compras-almacen/${id}/pdf`, '_blank')
    }
  }
}
</script>
