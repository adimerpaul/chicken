<template>
  <q-page class="bg-grey-3 q-pa-sm">
    <!-- HEADER + FILTROS -->
    <q-card flat bordered>
      <q-card-section class="q-pa-sm">
        <div class="row items-end q-col-gutter-sm">
          <div class="col-12 col-sm-2">
            <q-input v-model="filters.date_from" type="date" dense outlined label="Desde" />
          </div>
          <div class="col-12 col-sm-2">
            <q-input v-model="filters.date_to" type="date" dense outlined label="Hasta" />
          </div>
          <div class="col-12 col-sm-2">
            <q-select
              v-model="filters.type"
              dense
              outlined
              label="Tipo"
              :options="['', 'INGRESO', 'EGRESO', 'CAJA']"
              emit-value
              map-options
            />
          </div>
          <div class="col-12 col-sm-2">
            <q-select
              v-model="filters.status"
              dense
              outlined
              label="Estado"
              :options="['', 'ACTIVO', 'ANULADO']"
              emit-value
              map-options
            />
          </div>
          <div class="col-12 col-sm-2">
            <q-select
              v-model="filters.mesa"
              dense
              outlined
              label="Mesa"
              :options="['', 'MESA', 'LLEVAR', 'DELIVERY', 'PEDIDOS YA']"
              emit-value
              map-options
            />
          </div>

          <div class="col-12 col-sm-2">
            <q-select
              v-model="filters.user_id"
              dense
              outlined
              clearable
              label="Usuario"
              :options="users"
              option-value="id"
              option-label="name"
              emit-value
              map-options
            />
          </div>

          <div class="col-12 col-sm-4">
            <q-input
              v-model="filters.q"
              dense
              outlined
              debounce="400"
              label="Buscar (cliente, nro, comentario)"
            />
          </div>

          <div class="col-12 col-sm-8 text-right">
            <q-btn
              color="orange"
              icon="lock"
              @click="abrirCierreCaja"
              label="Cierre de Caja"
              no-caps
              class="q-mr-sm"
            />

            <q-btn-dropdown
              v-if="$store.user.role === 'Administrador'"
              color="purple"
              icon="print"
              label="Reportes"
              no-caps
              class="q-mr-sm"
            >
              <q-list>
                <q-item clickable @click="printResumenUsuarios" v-close-popup>
                  <q-item-section avatar><q-icon name="groups" /></q-item-section>
                  <q-item-section>Ventas por usuario</q-item-section>
                </q-item>

                <q-item clickable @click="printProductosUsuarios" v-close-popup>
                  <q-item-section avatar><q-icon name="restaurant" /></q-item-section>
                  <q-item-section>Productos por usuario</q-item-section>
                </q-item>

                <q-item clickable @click="printUltimoCierre" v-close-popup>
                  <q-item-section avatar><q-icon name="lock" /></q-item-section>
                  <q-item-section>Último cierre de caja</q-item-section>
                </q-item>

                <!-- NUEVOS REPORTES POR USUARIO SELECCIONADO -->
                <q-item clickable @click="abrirReporteUsuario('productos')" v-close-popup>
                  <q-item-section avatar><q-icon name="person" /></q-item-section>
                  <q-item-section>Productos del usuario seleccionado</q-item-section>
                </q-item>

                <q-item clickable @click="abrirReporteUsuario('ventas')" v-close-popup>
                  <q-item-section avatar><q-icon name="receipt_long" /></q-item-section>
                  <q-item-section>Ventas del usuario seleccionado</q-item-section>
                </q-item>
              </q-list>
            </q-btn-dropdown>

            <!-- crear venta -->
            <q-btn
              color="green"
              icon="add_shopping_cart"
              :to="{ path: '/ventas' }"
              label="Crear Venta"
              no-caps
              class="q-mr-sm"
            />
            <q-btn
              color="red"
              icon="payments"
              label="Gastos"
              no-caps
              class="q-mr-sm"
              @click="agregarGasto"
            />
            <q-btn
              color="blue"
              icon="query_stats"
              label="Ingresos del día"
              no-caps
              class="q-mr-sm"
              @click="verIngresosHoy"
            />


            <q-btn
              flat
              color="primary"
              icon="refresh"
              :loading="loading"
              @click="fetchSales"
              label="Actualizar"
              no-caps
              class="q-mr-sm"
            />
            <q-btn
              outline
              color="primary"
              icon="filter_alt_off"
              @click="resetFilters"
              label="Limpiar"
              no-caps
            />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- RESUMEN SOLO ADMIN -->
<!--    <template >-->
      bb
      <div class="row q-col-gutter-sm q-mt-sm" v-if="$store.user.role === 'Administrador'">
        <div class="col-12 col-sm-3">
          <q-card flat bordered>
            <q-card-section class="q-pa-sm">
              <div class="text-caption text-grey">Total</div>
              <div class="text-h6 text-bold">{{ total }} Bs</div>
            </q-card-section>
          </q-card>
        </div>
        <div class="col-12 col-sm-3">
          <q-card flat bordered>
            <q-card-section class="q-pa-sm">
              <div class="text-caption text-grey"># Ventas</div>
              <div class="text-h6 text-bold">{{ countIngreso }}</div>
            </q-card-section>
          </q-card>
        </div>
        <div class="col-12 col-sm-3">
          <q-card flat bordered>
            <q-card-section class="q-pa-sm">
              <div class="text-caption text-grey">Ingreso</div>
              <div class="text-h6 text-positive text-bold">{{ ingresoTotal }} Bs</div>
              <div class="text-caption">
                Efectivo: <b>{{ ingresoEfectivo }}</b> Bs
              </div>
              <div class="text-caption">
                QR: <b>{{ ingresoQr }}</b> Bs
              </div>
            </q-card-section>
          </q-card>
        </div>
        <div class="col-12 col-sm-3">
          <q-card flat bordered>
            <q-card-section class="q-pa-sm">
              <div class="text-caption text-grey">Egreso/Caja</div>
              <div class="text-h6 text-bold text-red">
                {{ egresoCaja }} Bs
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- TABLA -->
      <q-card flat bordered class="q-mt-sm">
        <q-table
          :rows="rows"
          :columns="columns"
          row-key="id"
          flat
          bordered
          dense
          :loading="loading"
          :rows-per-page-options="[10, 20, 50, 0]"
          :pagination.sync="pagination"
          @request="onRequest"
        >
          <template #body-cell-status="props">
            <q-td :props="props">
              <q-chip
                dense
                :color="props.row.status === 'ACTIVO' ? 'green' : 'red'"
                text-color="white"
              >
                {{ props.row.status }}
              </q-chip>
            </q-td>
          </template>

          <template #body-cell-type="props">
            <q-td :props="props">
              <q-chip dense :color="typeColor(props.row.type)" text-color="white">
                {{ props.row.type }}
              </q-chip>
            </q-td>
          </template>

          <template #body-cell-total="props">
            <q-td :props="props" class="text-right">
              {{ money(props.row.total) }}
            </q-td>
          </template>

          <template #body-cell-actions="props">
            <q-td :props="props">
              <q-btn-dropdown dense label="Opciones" no-caps size="xs" color="primary">
                <q-list>
                  <q-item clickable v-if="props.row.type === 'INGRESO'" @click="openDetail(props.row)" v-close-popup>
                  <q-item-section avatar><q-icon name="visibility" /></q-item-section>
                    <q-item-section>Ver Detalle</q-item-section>
                  </q-item>

                  <q-item clickable @click="printTicket(props.row)" v-close-popup>
                    <q-item-section avatar><q-icon name="print" /></q-item-section>
                    <q-item-section>Imprimir ticket</q-item-section>
                  </q-item>

                  <q-item
                    clickable
                    v-if="props.row.status === 'ACTIVO'"
                    @click="anularVenta(props.row)"
                    v-close-popup
                  >
                    <q-item-section avatar><q-icon name="cancel" /></q-item-section>
                    <q-item-section>Anular Venta</q-item-section>
                  </q-item>
                </q-list>
              </q-btn-dropdown>
            </q-td>
          </template>

          <template #no-data>
            <div class="full-width row flex-center q-pa-lg text-grey">
              Sin resultados
            </div>
          </template>
        </q-table>

        <!-- Paginación (Laravel) -->
        <div class="row items-center q-pa-sm">
          <div class="col">
            <span class="text-caption text-grey-7">
              Mostrando {{ meta.from || 0 }}–{{ meta.to || 0 }} de {{ meta.total || 0 }}
            </span>
          </div>
          <div class="col-auto">
            <q-pagination
              v-model="meta.current_page"
              :max="meta.last_page || 1"
              max-pages="8"
              direction-links
              boundary-links
              @update:model-value="pageChange"
            />
          </div>
        </div>
      </q-card>
<!--    </template>-->

    <!-- DETALLE VENTA -->
    <q-dialog v-model="dlg" persistent>
      <q-card style="width: 800px; max-width: 95vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1">
            Venta #{{ current?.numero }} — {{ current?.date }} {{ current?.time?.substring(0, 8) }}
          </div>
          <q-space />
          <q-btn flat round dense icon="close" @click="dlg = false" />
        </q-card-section>
        <q-card-section class="q-pt-none">
          <div class="row q-col-gutter-sm q-mb-sm">
            <div class="col-6"><b>Cliente:</b> {{ current?.name }}</div>
            <div class="col-3"><b>Mesa:</b> {{ current?.mesa }}</div>
            <div class="col-3"><b>Pago:</b> {{ current?.pago }}</div>
            <div class="col-6"><b>Tipo:</b> {{ current?.type }}</div>
            <div class="col-6"><b>Estado:</b> {{ current?.status }}</div>
            <div class="col-12" v-if="current?.comment">
              <b>Comentario:</b> {{ current?.comment }}
            </div>
          </div>

          <q-table
            :rows="current?.detalles || []"
            :columns="detailCols"
            flat
            bordered
            dense
            row-key="id"
          >
            <template #body-cell-subtotal="p">
              <q-td :props="p" class="text-right">{{ money(p.row.subtotal) }}</q-td>
            </template>
            <template #body-cell-price="p">
              <q-td :props="p" class="text-right">{{ money(p.row.price) }}</q-td>
            </template>
          </q-table>

          <div class="text-right q-mt-sm text-h6">
            Total: {{ money(current?.total || 0) }} Bs
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- DIALOG: AGREGAR GASTO -->
    <q-dialog v-model="dialogGasto" persistent>
      <q-card style="width: 400px; max-width: 95vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1">
            Agregar Gasto
          </div>
          <q-space />
          <q-btn flat round dense icon="close" @click="dialogGasto = false" />
        </q-card-section>
        <q-card-section>
          <div class="q-pa-sm">
            <q-input
              v-model="venta.name"
              label="Descripción"
              outlined
              dense
              class="q-mb-sm"
            />
            <q-input
              v-model.number="venta.total"
              label="Monto (Bs)"
              type="number"
              outlined
              dense
              class="q-mb-sm"
            />
            <q-btn label="Guardar" color="primary" @click="guardarGasto" :loading="loading" />
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- DIALOG: CIERRE DE CAJA (GENERAL, EFECTIVO + QR) -->
    <q-dialog v-model="dialogCierre" persistent>
      <q-card style="width: 420px; max-width: 95vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1">
            Cierre de Caja
          </div>
          <q-space />
          <q-btn flat round dense icon="close" @click="dialogCierre = false" />
        </q-card-section>

        <q-card-section>
          <div class="q-pa-sm">
            <q-input
              v-model="cierre.date"
              type="date"
              label="Fecha"
              outlined
              dense
              class="q-mb-sm"
            />

            <q-input
              v-model.number="cierre.monto_efectivo"
              type="number"
              label="Efectivo contado (Bs)"
              outlined
              dense
              class="q-mb-sm"
            />

            <q-input
              v-model.number="cierre.monto_qr"
              type="number"
              label="Monto QR contado (Bs)"
              outlined
              dense
              class="q-mb-sm"
            />

            <q-input
              v-model="cierre.observacion"
              type="textarea"
              label="Observación"
              outlined
              dense
              class="q-mb-sm"
            />

            <q-btn
              label="Guardar y imprimir"
              color="primary"
              @click="guardarCierreCaja"
              :loading="loading"
            />
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- DIALOG: REPORTE POR USUARIO SELECCIONADO -->
    <q-dialog v-model="dialogReporteUsuario" persistent>
      <q-card style="width: 400px; max-width: 95vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1">
            {{ reporteUsuarioModo === 'productos'
            ? 'Reporte de productos por usuario'
            : 'Reporte de ventas por usuario' }}
          </div>
          <q-space />
          <q-btn flat round dense icon="close" @click="dialogReporteUsuario = false" />
        </q-card-section>

        <q-card-section>
          <q-select
            v-model="selectedUserId"
            :options="users"
            option-value="id"
            option-label="name"
            emit-value
            map-options
            dense
            outlined
            label="Usuario"
            class="q-mb-sm"
          />

          <div class="row q-col-gutter-sm">
            <div class="col-6">
              <q-input
                v-model="reporteUsuarioFechaDesde"
                type="date"
                dense
                outlined
                label="Desde"
              />
            </div>
            <div class="col-6">
              <q-input
                v-model="reporteUsuarioFechaHasta"
                type="date"
                dense
                outlined
                label="Hasta"
              />
            </div>
          </div>

          <div class="q-mt-md text-right">
            <q-btn
              flat
              label="Cancelar"
              color="primary"
              class="q-mr-sm"
              @click="dialogReporteUsuario = false"
            />
            <q-btn
              label="Imprimir"
              color="primary"
              :loading="loading"
              @click="confirmarReporteUsuario"
            />
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
    <q-dialog v-model="dialogGasto" persistent>
      <q-card style="width: 420px; max-width: 95vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1 text-bold">Agregar Gasto</div>
          <q-space />
          <q-btn flat round dense icon="close" @click="dialogGasto = false" />
        </q-card-section>

        <q-card-section class="q-pt-sm">
          <q-input
            v-model="venta.name"
            label="Descripción"
            outlined
            dense
            class="q-mb-sm"
          />

          <q-input
            v-model.number="venta.total"
            label="Monto (Bs)"
            type="number"
            step="0.01"
            outlined
            dense
            class="q-mb-sm"
          />

          <q-select
            v-model="venta.pago"
            label="Pago"
            outlined
            dense
            :options="['EFECTIVO','QR']"
            class="q-mb-sm"
          />

          <q-input
            v-model="venta.comment"
            label="Comentario (opcional)"
            outlined
            dense
            type="textarea"
            autogrow
            class="q-mb-sm"
          />

          <div class="text-right">
            <q-btn flat label="Cancelar" color="grey-8" no-caps class="q-mr-sm" @click="dialogGasto=false"/>
            <q-btn label="Guardar e imprimir" color="primary" no-caps @click="guardarGasto" :loading="loading" />
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>


    <div id="myelement" class="hidden"></div>
  </q-page>
</template>

<script>
import { Imprimir } from 'src/utils/ImprimirTicket'
import moment from 'moment'

export default {
  name: 'VentasListado',
  data () {
    return {
      dialogCierre: false,
      cierre: {
        date: moment().format('YYYY-MM-DD'),
        // 👇 ya NO usamos user_id en el front
        monto_efectivo: 0,
        monto_qr: 0,
        observacion: ''
      },
      dialogCaja: false,
      caja: {
        name: 'Inicio de Caja',
        total: 0
      },
      loading: false,
      rows: [],
      venta: { name: '', total: null, pago: 'EFECTIVO', comment: '' },
      dialogGasto: false,
      meta: { current_page: 1, last_page: 1, total: 0, from: 0, to: 0, per_page: 20 },
      pagination: { page: 1, rowsPerPage: 20, sortBy: 'date', descending: true },
      filters: {
        date_from: moment().format('YYYY-MM-DD'),
        date_to: moment().format('YYYY-MM-DD'),
        type: '',
        status: '',
        mesa: '',
        pago: '',
        user_id: '',
        q: ''
      },
      summary: { total: 0, count: 0, by_type: [] },
      dlg: false,
      current: null,

      // columns: [
      //   // { name: 'numero', label: '#', field: 'numero', align: 'left', sortable: true },
      //   // { name: 'actions', label: 'Opciones', field: 'id', align: 'right' },
      //   // { name: 'date', label: 'Fecha', field: 'date', align: 'left', sortable: true },
      //   // { name: 'time', label: 'Hora', field: row => String(row.time).substring(0, 8), align: 'left' },
      //   // { name: 'name', label: 'Cliente', field: 'name', align: 'left' },
      //   // { name: 'mesa', label: 'Mesa', field: 'mesa', align: 'left' },
      //   // { name: 'pago', label: 'Pago', field: 'pago', align: 'left' },
      //   // { name: 'type', label: 'Tipo', field: 'type', align: 'left' },
      //   // { name: 'status', label: 'Estado', field: 'status', align: 'left' },
      //   // { name: 'total', label: 'Total (Bs)', field: 'total', align: 'right', sortable: true },
      //   // { name: 'user', label: 'Usuario', field: row => row.user?.name || 'N/A', align: 'left' }
      // ],
      detailCols: [
        { name: 'name', label: 'Producto', field: 'name', align: 'left' },
        { name: 'qty', label: 'Cant.', field: 'qty', align: 'right' },
        { name: 'price', label: 'Precio', field: 'price', align: 'right' },
        { name: 'subtotal', label: 'Subtotal', field: 'subtotal', align: 'right' }
      ],

      users: [],
      dialogReporteUsuario: false,
      selectedUserId: null,
      reporteUsuarioModo: 'productos',
      reporteUsuarioFechaDesde: moment().format('YYYY-MM-DD'),
      reporteUsuarioFechaHasta: moment().format('YYYY-MM-DD')
    }
  },
  mounted () {
    this.fetchSales()
    this.fetchUsers()
  },
  computed: {
    columns () {
      // verificar si es admin
      console.log('ROL USUARIO:', this.$store.user.role)
      const isAdmin = this.$store.user.role === 'Administrador'
      const baseColumns = [
        { name: 'numero', label: '#', field: 'numero', align: 'left', sortable: true },
        // { name: 'actions', label: 'Opciones', field: 'id', align: 'right' },
        { name: 'date', label: 'Fecha', field: 'date', align: 'left', sortable: true },
        { name: 'time', label: 'Hora', field: row => String(row.time).substring(0, 8), align: 'left' },
        { name: 'name', label: 'Cliente', field: 'name', align: 'left' },
        { name: 'mesa', label: 'Mesa', field: 'mesa', align: 'left' },
        // { name: 'pago', label: 'Pago', field: 'pago', align: 'left' },
        // { name: 'type', label: 'Tipo', field: 'type', align: 'left' },
        // { name: 'status', label: 'Estado', field: 'status', align: 'left' },
        // { name: 'total', label: 'Total (Bs)', field: 'total', align: 'right', sortable: true }
      ]
      if (isAdmin) {
        // baseColumns.push({ name: 'user', label: 'Usuario', field: row => row.user?.name || 'N/A', align: 'left' })
        baseColumns.push(
          { name: 'actions', label: 'Opciones', field: 'id', align: 'right' },
          { name: 'pago', label: 'Pago', field: 'pago', align: 'left' },
          { name: 'type', label: 'Tipo', field: 'type', align: 'left' },
          { name: 'status', label: 'Estado', field: 'status', align: 'left' },
          { name: 'total', label: 'Total (Bs)', field: 'total', align: 'right', sortable: true },
          // user
          { name: 'user', label: 'Usuario', field: row => row.user?.name || 'N/A', align: 'left' }
        )
      }
      return baseColumns
    },
    total () {
      const ingresosActivos = this.rows.filter(r => r.type === 'INGRESO' && r.status === 'ACTIVO')
      const egresosActivos = this.rows.filter(r => r.type === 'EGRESO' && r.status === 'ACTIVO')
      return this.money(
        ingresosActivos.reduce((a, b) => a + Number(b.total || 0), 0) -
        egresosActivos.reduce((a, b) => a + Number(b.total || 0), 0)
      )
    },
    ingresoTotal () {
      let sum = 0
      this.rows.forEach(item => {
        if (
          item.type === 'INGRESO' &&
          item.status === 'ACTIVO'
        ) sum += Number(item.total || 0)
      })
      return this.money(sum)
    },
    ingresoEfectivo () {
      let sum = 0
      this.rows.forEach(item => {
        if (
          item.type === 'INGRESO' &&
          item.status === 'ACTIVO' &&
          item.pago === 'EFECTIVO'
        ) {
          sum += Number(item.total || 0)
        }
      })
      return this.money(sum)
    },
    ingresoQr () {
      let sum = 0
      this.rows.forEach(item => {
        if (
          item.type === 'INGRESO' &&
          item.status === 'ACTIVO' &&
          item.pago === 'QR'
        ) {
          sum += Number(item.total || 0)
        }
      })
      return this.money(sum)
    },
    countIngreso () {
      let count = 0
      ;(this.rows || []).forEach(item => {
        if (
          item.type === 'INGRESO' &&
          item.status === 'ACTIVO'
        ) count += 1
      })
      return count
    },
    egresoCaja () {
      let sum = 0
      this.rows.forEach(item => {
        if (item.type === 'EGRESO' &&
          item.status === 'ACTIVO'
        ) sum += Number(item.total || 0)
      })
      return this.money(sum)
    }
  },
  methods: {
    agregarGasto () {
      this.venta = { name: '', total: null, pago: 'EFECTIVO', comment: '' }
      this.dialogGasto = true
    },
    async guardarGasto () {
      if (!this.venta?.name || !this.venta?.total || Number(this.venta.total) <= 0) {
        this.$q.notify?.({ type: 'negative', message: 'Ingrese descripción y monto válido' })
        return
      }

      this.loading = true
      // try {
        const payload = {
          name: this.venta.name,
          total: Number(this.venta.total),
          pago: this.venta.pago || 'EFECTIVO',
          comment: this.venta.comment || null
        }

        const { data } = await this.$axios.post('gastos', payload)

        this.$q.notify?.({ type: 'positive', message: 'Gasto registrado' })
        this.dialogGasto = false

        // ✅ imprimir
        Imprimir.gasto(data) // <-- lo añadimos abajo en tu ImprimirTicket

        // limpiar
        this.venta = { name: '', total: null, pago: 'EFECTIVO', comment: '' }

        // refrescar lista
        this.fetchSales()
      // }
      // catch (e) {
      //   this.$q.notify?.({
      //     type: 'negative',
      //     message: e?.response?.data?.message || 'Error al guardar gasto'
      //   })
      // } finally {
      //   this.loading = false
      // }
    },
    async fetchUsers () {
      try {
        const { data } = await this.$axios.get('users')
        this.users = data
      } catch (e) {
        this.$q.notify?.({
          type: 'negative',
          message: 'No se pudo cargar la lista de usuarios'
        })
      }
    },

    abrirReporteUsuario (modo) {
      this.reporteUsuarioModo = modo
      this.selectedUserId = null
      this.reporteUsuarioFechaDesde = this.filters.date_from
      this.reporteUsuarioFechaHasta = this.filters.date_to
      this.dialogReporteUsuario = true
    },

    async confirmarReporteUsuario () {
      if (!this.selectedUserId) {
        this.$q.notify?.({
          type: 'warning',
          message: 'Seleccione un usuario'
        })
        return
      }

      this.loading = true
      try {
        const params = {
          date_from: this.reporteUsuarioFechaDesde || this.filters.date_from,
          date_to: this.reporteUsuarioFechaHasta || this.filters.date_to,
          user_id: this.selectedUserId
        }

        const { data } = await this.$axios.get('sales/report/by-user', { params })
        data.date_from = params.date_from
        data.date_to = params.date_to

        if (this.reporteUsuarioModo === 'productos') {
          Imprimir.reporteProductosPorUsuario(data)
        } else {
          Imprimir.reporteVentasPorUsuario(data)
        }

        this.dialogReporteUsuario = false
      } catch (e) {
        this.$q.notify?.({
          type: 'negative',
          message: 'No se pudo generar el reporte'
        })
      } finally {
        this.loading = false
      }
    },

    abrirCierreCaja () {
      const today = new Date().toISOString().slice(0, 10)
      this.cierre = {
        date: this.filters.date_from || today,
        monto_efectivo: 0,
        monto_qr: 0,
        observacion: ''
      }
      this.dialogCierre = true
    },

    async guardarCierreCaja () {
      if (this.cierre.monto_efectivo === null || this.cierre.monto_efectivo === '' || isNaN(this.cierre.monto_efectivo)) {
        this.$q.notify?.({ type: 'negative', message: 'Ingrese el efectivo contado' })
        return
      }
      if (this.cierre.monto_qr === null || this.cierre.monto_qr === '' || isNaN(this.cierre.monto_qr)) {
        this.$q.notify?.({ type: 'negative', message: 'Ingrese el monto QR contado (puede ser 0)' })
        return
      }

      this.loading = true
      // try {
        const { data } = await this.$axios.post('cierres-caja', this.cierre)

        const cierre = data?.cierre
        const already = !!data?.already_exists

        // ✅ Si ya existe: NO decir "guardado"
        if (already) {
          this.$q.notify?.({ type: 'info', message: 'Cierre ya registrado. Imprimiendo…' })
        } else {
          this.$q.notify?.({ type: 'positive', message: 'Cierre registrado' })
        }

        this.dialogCierre = false

        // ✅ imprimir SOLO usuario
        Imprimir.cierreCajaUsuario(cierre)

        // refrescar lista
        this.fetchSales()
      // } catch (e) {
      //   this.$q.notify?.({
      //     type: 'negative',
      //     message: e.response?.data?.message || 'No se pudo guardar / imprimir el cierre'
      //   })
      // } finally {
      //   this.loading = false
      // }
    },

    async printResumenUsuarios () {
      try {
        const params = { ...this.filters }
        const { data } = await this.$axios.get('sales/report/by-user', { params })
        data.date_from = this.filters.date_from
        data.date_to = this.filters.date_to
        Imprimir.reporteUsuarios(data)
      } catch (e) {
        this.$q.notify?.({
          type: 'negative',
          message: 'No se pudo generar el reporte de usuarios'
        })
      }
    },

    async printProductosUsuarios () {
      try {
        const params = { ...this.filters }
        const { data } = await this.$axios.get('sales/report/by-user', { params })
        Imprimir.reporteProductosPorUsuario(data)
      } catch (e) {
        this.$q.notify?.({
          type: 'negative',
          message: 'No se pudo generar el reporte de productos'
        })
      }
    },

    async printUltimoCierre () {
      try {
        const { data } = await this.$axios.get('cierres-caja-ultimo')
        Imprimir.cierreCaja(data)
      } catch (e) {
        this.$q.notify?.({
          type: 'negative',
          message: 'No se encontró un cierre de caja'
        })
      }
    },

    anularVenta (row) {
      this.$q.dialog({
        title: 'Confirmar Anulación',
        message: `¿Está seguro de anular la venta #${row.numero}? Esta acción no se puede deshacer.`,
        cancel: true,
        persistent: true
      }).onOk(() => {
        this.loading = true
        this.$axios.post(`sales/${row.id}/anular`).then(() => {
          this.$q.notify?.({ type: 'positive', message: 'Venta anulada' })
          this.fetchSales()
        }).catch(() => {
          this.$q.notify?.({ type: 'negative', message: 'Error al anular venta' })
        }).finally(() => {
          this.loading = false
        })
      })
    },

    money (v) {
      return Number(v || 0).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    },

    typeColor (t) {
      if (t === 'INGRESO') return 'green'
      if (t === 'EGRESO') return 'orange'
      if (t === 'CAJA') return 'blue'
      return 'grey'
    },

    resetFilters () {
      Object.assign(this.filters, {
        date_from: '',
        date_to: '',
        type: '',
        status: '',
        mesa: '',
        pago: '',
        user_id: '',
        q: ''
      })
      this.pagination.page = 1
      this.fetchSales()
    },

    pageChange () {
      this.pagination.page = this.meta.current_page
      this.fetchSales()
    },

    onRequest () {
      // manejamos paginación con meta del backend
    },

    agregarGasto () {
      this.dialogGasto = true
    },

    async verIngresosHoy () {
      try {
        this.loading = true
        const { data } = await this.$axios.get('cierres-caja/reporte/ultimo')
        Imprimir.reporteUltimoCierreUsuarios(data)
        this.loading = false
      } catch (e) {
        this.loading = false
        this.$q.notify?.({
          type: 'negative',
          message: 'No hay cierres de caja registrados'
        })
      }
    },

    async fetchSales () {
      this.loading = true
      try {
        const params = {
          page: this.pagination.page,
          per_page: this.pagination.rowsPerPage,
          ...this.filters
        }
        const { data } = await this.$axios.get('sales', { params })

        if (Array.isArray(data?.data) && data?.meta) {
          this.rows = data.data
          this.meta = {
            current_page: data.meta.current_page,
            last_page: data.meta.last_page,
            total: data.meta.total,
            from: data.meta.from,
            to: data.meta.to,
            per_page: data.meta.per_page
          }
          this.summary = data.summary || this.summary
        } else {
          this.rows = data.data || []
          this.meta = {
            current_page: data.current_page || 1,
            last_page: data.last_page || 1,
            total: data.total || 0,
            from: data.from || 0,
            to: data.to || 0,
            per_page: data.per_page || this.pagination.rowsPerPage
          }
          this.summary = {
            total: this.rows.reduce((a, b) => a + Number(b.total || 0), 0),
            count: this.rows.length,
            by_type: [
              { type: 'INGRESO', total: this.rows.filter(r => r.type === 'INGRESO').reduce((a, b) => a + Number(b.total || 0), 0) },
              { type: 'EGRESO', total: this.rows.filter(r => r.type === 'EGRESO').reduce((a, b) => a + Number(b.total || 0), 0) },
              { type: 'CAJA', total: this.rows.filter(r => r.type === 'CAJA').reduce((a, b) => a + Number(b.total || 0), 0) }
            ]
          }
        }
      } catch (e) {
        this.$q.notify?.({ type: 'negative', message: 'No se pudo cargar ventas' })
      } finally {
        this.loading = false
      }
    },

    openDetail (row) {
      this.current = row
      this.dlg = true
    },

    async printTicket (row) {
      try {
        const { data } = await this.$axios.get(`sales/${row.id}`)
        Imprimir.ticket(data)
      } catch (e) {
        this.$q.notify?.({
          type: 'negative',
          message: 'No se pudo imprimir el ticket'
        })
      }
    }
  },
  watch: {
    'filters.type': 'fetchSales',
    'filters.status': 'fetchSales',
    'filters.mesa': 'fetchSales',
    'filters.pago': 'fetchSales',
    'filters.user_id': 'fetchSales',
    'filters.q': function () {
      this.pagination.page = 1
      this.fetchSales()
    },
    'filters.date_from': 'fetchSales',
    'filters.date_to': 'fetchSales',
    'pagination.rowsPerPage': 'fetchSales'
  }
}
</script>

<style scoped>
.text-positive {
  color: #21ba45;
}
</style>
