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
              label="Buscar (nota / estado)"
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
              label="Nuevo movimiento"
              icon="call_made"
              no-caps
              class="q-mr-sm"
              :to="{ path: '/movimientos-almacen-insumos/nuevo' }"
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
            <q-card flat bordered class="bg-blue-1">
              <q-card-section class="row items-center">
                <q-icon name="sync_alt" color="blue" size="md" class="q-mr-sm" />
                <div>
                  <div class="text-caption">Total Movimientos (Bs)</div>
                  <div class="text-h6 text-weight-bold">{{ money(totalMovimientos) }}</div>
                </div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-xs-12 col-md-4">
            <q-card flat bordered class="bg-red-1">
              <q-card-section class="row items-center">
                <q-icon name="block" color="red" size="md" class="q-mr-sm" />
                <div>
                  <div class="text-caption">Anulados (Bs)</div>
                  <div class="text-h6 text-weight-bold">{{ money(totalAnulados) }}</div>
                </div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-xs-12 col-md-4">
            <q-card flat bordered class="bg-green-1">
              <q-card-section class="row items-center">
                <q-icon name="check_circle" color="green" size="md" class="q-mr-sm" />
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
              <q-btn
                flat dense round icon="visibility"
                @click="ver(props.row.id)"
                :title="'Ver movimiento #' + props.row.id"
              />
              <q-btn
                flat dense round icon="block"
                color="negative"
                v-if="props.row.estado === 'ACTIVO'"
                @click="anular(props.row.id)"
                :title="'Anular movimiento'"
              />
            </q-td>
          </template>

          <template #no-data>
            <div class="full-width row flex-center text-grey q-gutter-sm">
              <q-icon name="inbox" />
              <span>Sin movimientos registrados</span>
            </div>
          </template>
        </q-table>
      </q-card-section>
    </q-card>

    <!-- DIALOG VER MOVIMIENTO -->
    <q-dialog v-model="dlgVer">
      <q-card style="min-width: 720px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1">
            Movimiento #{{ mov ? mov.id : '' }}
          </div>
          <q-space />
          <q-btn flat round dense icon="close" @click="dlgVer = false" />
        </q-card-section>

        <q-card-section class="q-pt-none">
          <div class="q-mb-sm">
            <div><b>Fecha:</b> {{ mov?.fecha }}</div>
            <div><b>Estado:</b> {{ mov?.estado }}</div>
            <div><b>Nota:</b> {{ mov?.nota || '—' }}</div>
          </div>

          <q-table
            :rows="mov?.detalles || []"
            :columns="colsDetalles"
            dense
            flat
            bordered
            :rows-per-page-options="[0]"
            hide-bottom
          />

          <div class="text-right text-subtitle1 q-mt-sm">
            <b>Total:</b> {{ money(mov?.total || 0) }} Bs
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'MovimientosAlmacenInsumosIndex',
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
      mov: null,
      columns: [
        { name: 'id', label: '#', field: 'id', align: 'left', sortable: true },
        { name: 'fecha', label: 'Fecha', field: 'fecha', align: 'left', sortable: true },
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
        { name: 'opciones', label: 'Opciones', field: 'opciones', align: 'center' }
      ],
      colsDetalles: [
        {
          name: 'almacen',
          label: 'Desde almacén',
          align: 'left',
          field: r => (r.almacen && r.almacen.nombre) ? r.almacen.nombre : ''
        },
        {
          name: 'insumo',
          label: 'Hacia insumo',
          align: 'left',
          field: r => (r.insumo && r.insumo.nombre) ? r.insumo.nombre : ''
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
    totalMovimientos () {
      return this.rows.reduce((a, r) => a + Number(r.total || 0), 0)
    },
    totalAnulados () {
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
        const res = await this.$axios.post('movimientos-almacen-insumos/report', {
          fechaInicio: this.f.ini,
          fechaFin: this.f.fin,
          q: this.f.q
        })
        this.rows = res.data
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo cargar los movimientos')
      } finally {
        this.loading = false
      }
    },
    async ver (id) {
      try {
        const res = await this.$axios.get(`movimientos-almacen-insumos/${id}`)
        this.mov = res.data
        this.dlgVer = true
      } catch (e) {
        this.$alert?.error?.('No se pudo abrir el movimiento')
      }
    },
    anular (id) {
      this.$alert
        .dialog('¿Anular el movimiento? Esto restaurará los stocks.')
        .onOk(async () => {
          this.loading = true
          try {
            await this.$axios.put(`movimientos-almacen-insumos/${id}/anular`)
            this.$alert?.success?.('Movimiento anulado')
            this.fetch()
          } catch (e) {
            this.$alert?.error?.(e.response?.data?.message || 'No se pudo anular')
          } finally {
            this.loading = false
          }
        })
    }
  }
}
</script>
