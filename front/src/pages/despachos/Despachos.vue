<template>
  <q-page class="q-pa-md bg-grey-3">
    <q-card flat bordered class="q-pa-sm">

      <!-- FILTROS -->
      <q-card-section class="q-pa-sm">
        <div class="row items-end q-col-gutter-sm">
          <div class="col-6 col-md-2">
            <q-input v-model="f.ini" label="Fecha inicio" type="date" dense outlined />
          </div>
          <div class="col-6 col-md-2">
            <q-input v-model="f.fin" label="Fecha fin" type="date" dense outlined />
          </div>
          <div class="col-6 col-md-2">
            <q-select
              v-model="f.estado"
              :options="['PENDIENTE', 'COMPLETADO', 'ANULADO']"
              label="Estado"
              dense outlined clearable
            />
          </div>
          <div class="col-6 col-md-3">
            <q-input v-model="f.q" label="Buscar (nota / proveedor / comentario)" dense outlined clearable @keyup.enter="fetch">
              <template #append><q-icon name="search" /></template>
            </q-input>
          </div>
          <div class="col-12 col-md-3 text-right">
            <q-btn v-if="esAdmin" flat color="primary" label="Productos" icon="egg" no-caps class="q-mr-sm" to="/despachos/productos" />
            <q-btn color="primary" label="Registrar despacho" icon="add" no-caps class="q-mr-sm" @click="nuevo" />
            <q-btn color="indigo" label="Buscar" icon="search" no-caps :loading="loading" @click="fetch" />
          </div>
        </div>
      </q-card-section>

      <!-- RESUMEN -->
      <q-card-section class="q-pt-none">
        <div class="row q-col-gutter-sm">
          <div class="col-6 col-md-3">
            <q-card flat bordered class="bg-orange-1">
              <q-card-section class="row items-center no-wrap">
                <q-icon name="pending_actions" color="orange" size="md" class="q-mr-sm" />
                <div>
                  <div class="text-caption">Pendientes</div>
                  <div class="text-h6 text-weight-bold">{{ pendientes }}</div>
                </div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-6 col-md-3">
            <q-card flat bordered class="bg-blue-1">
              <q-card-section class="row items-center no-wrap">
                <q-icon name="scale" color="blue" size="md" class="q-mr-sm" />
                <div>
                  <div class="text-caption">Kg nota / Kg recibido</div>
                  <div class="text-h6 text-weight-bold">{{ num(kgNota) }} / {{ num(kgRecibido) }}</div>
                </div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-6 col-md-3">
            <q-card flat bordered :class="kgFaltante > 0 ? 'bg-red-1' : 'bg-green-1'">
              <q-card-section class="row items-center no-wrap">
                <q-icon name="warning" :color="kgFaltante > 0 ? 'red' : 'green'" size="md" class="q-mr-sm" />
                <div>
                  <div class="text-caption">Kg faltantes (completados)</div>
                  <div class="text-h6 text-weight-bold">{{ num(kgFaltante) }}</div>
                </div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-6 col-md-3">
            <q-card flat bordered class="bg-green-1">
              <q-card-section class="row items-center no-wrap">
                <q-icon name="payments" color="green" size="md" class="q-mr-sm" />
                <div>
                  <div class="text-caption">Total (Bs)</div>
                  <div class="text-h6 text-weight-bold">{{ money(totalBs) }}</div>
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
          flat bordered dense
          :rows-per-page-options="[0]"
          :loading="loading"
          hide-bottom
        >
          <template #body-cell-opciones="props">
            <q-td :props="props">
              <q-btn-dropdown dense icon="more_vert" :label="'Despacho #' + props.row.id" no-caps size="10px" color="primary">
                <q-list>
                  <q-item clickable v-ripple v-close-popup @click="abrir(props.row.id, 'ver')">
                    <q-item-section avatar><q-icon name="visibility" /></q-item-section>
                    <q-item-section>Ver</q-item-section>
                  </q-item>
                  <q-item
                    v-if="props.row.estado === 'PENDIENTE' && (esAdmin || props.row.user_id === $store.user.id)"
                    clickable v-ripple v-close-popup @click="abrir(props.row.id, 'recepcion')"
                  >
                    <q-item-section avatar><q-icon name="edit" color="primary" /></q-item-section>
                    <q-item-section>Editar recepción</q-item-section>
                  </q-item>
                  <q-item
                    v-if="esAdmin && props.row.estado !== 'ANULADO'"
                    clickable v-ripple v-close-popup @click="abrir(props.row.id, 'admin')"
                  >
                    <q-item-section avatar><q-icon name="fact_check" color="green" /></q-item-section>
                    <q-item-section>{{ props.row.estado === 'COMPLETADO' ? 'Editar datos de nota' : 'Completar datos de nota' }}</q-item-section>
                  </q-item>
                  <q-item clickable v-ripple v-close-popup @click="pdf(props.row.id)">
                    <q-item-section avatar><q-icon name="print" color="amber-10" /></q-item-section>
                    <q-item-section>Imprimir</q-item-section>
                  </q-item>
                  <q-item
                    v-if="esAdmin && props.row.estado !== 'ANULADO'"
                    clickable v-ripple v-close-popup @click="anular(props.row.id)"
                  >
                    <q-item-section avatar><q-icon name="block" color="negative" /></q-item-section>
                    <q-item-section>Anular</q-item-section>
                  </q-item>
                </q-list>
              </q-btn-dropdown>
            </q-td>
          </template>

          <template #body-cell-diferencia="props">
            <q-td :props="props">
              <span
                v-if="props.row.estado === 'COMPLETADO'"
                :class="diferencia(props.row) > 0 ? 'text-negative text-weight-bold' : 'text-positive'"
              >
                {{ num(diferencia(props.row)) }}
              </span>
              <span v-else class="text-grey">—</span>
            </q-td>
          </template>

          <template #body-cell-estado="props">
            <q-td :props="props">
              <q-chip dense square :color="colorEstado(props.row.estado)" text-color="white">
                {{ props.row.estado }}
              </q-chip>
            </q-td>
          </template>

          <template #no-data>
            <div class="full-width row flex-center text-grey q-gutter-sm">
              <q-icon name="inbox" />
              <span>Sin despachos registrados</span>
            </div>
          </template>
        </q-table>
      </q-card-section>
    </q-card>

    <!-- DIALOG FORMULARIO -->
    <q-dialog v-model="dlg" :maximized="modo === 'admin' || $q.screen.lt.md">
      <q-card :style="modo === 'admin' ? '' : 'width: 900px; max-width: 95vw'">
        <q-card-section class="row items-center q-pb-sm">
          <div class="text-subtitle1 text-weight-bold">
            {{ tituloDialog }}
          </div>
          <q-chip v-if="form.estado" dense square :color="colorEstado(form.estado)" text-color="white" class="q-ml-sm">
            {{ form.estado }}
          </q-chip>
          <q-space />
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>

        <q-form @submit="guardar">
          <q-card-section class="q-pt-none">
            <q-banner v-if="!productosOptions.length && !soloLectura" dense rounded class="bg-orange-1 text-orange-10 q-mb-sm">
              No hay productos de despacho registrados.
              Use el botón <q-icon name="add_circle" color="green" /> junto al producto para crear uno.
            </q-banner>
            <!-- cabecera -->
            <div class="row q-col-gutter-sm q-mb-sm">
              <div class="col-6 col-md-2">
                <q-input v-model="form.fecha" type="date" label="Fecha" dense outlined :readonly="soloLectura"
                         :rules="[v => !!v || 'Requerido']" hide-bottom-space />
              </div>
              <div class="col-6 col-md-2">
                <q-input v-model="form.numero_nota" label="N° nota proveedor" dense outlined :readonly="soloLectura" />
              </div>
              <div class="col-12 col-md-3">
                <q-input v-model="form.proveedor" label="Proveedor" dense outlined :readonly="soloLectura" />
              </div>
              <div v-if="modo !== 'recepcion'" class="col-12 col-md-3">
                <q-input v-model="form.cobrador" label="Cobrador" dense outlined :readonly="soloLectura" />
              </div>
            </div>

            <!-- detalles: recepción -->
            <q-markup-table v-if="modo === 'recepcion'" dense flat bordered separator="cell">
              <thead>
              <tr class="bg-grey-2">
                <th class="text-left" style="min-width: 200px">Producto</th>
                <th class="text-right" style="width: 110px">Cantidad</th>
                <th class="text-right" style="width: 130px">Peso recibido (kg)</th>
                <th class="text-left">Comentario</th>
                <th style="width: 40px"></th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="(d, i) in form.detalles" :key="i">
                <td>
                  <q-select v-model="d.despacho_producto_id" :options="productosOptions" emit-value map-options
                            dense borderless options-dense placeholder="Seleccione">
                    <template #after>
                      <q-btn flat dense round size="sm" icon="add_circle" color="green" @click="nuevoProducto(d)">
                        <q-tooltip>Crear producto</q-tooltip>
                      </q-btn>
                    </template>
                  </q-select>
                </td>
                <td><q-input v-model.number="d.cantidad" type="number" step="0.01" min="0" dense borderless input-class="text-right" /></td>
                <td><q-input v-model.number="d.peso_recibido" type="number" step="0.01" min="0" dense borderless input-class="text-right" /></td>
                <td><q-input v-model="d.comentario" dense borderless placeholder="Ej: llegó mojado, faltan 2 pollos..." /></td>
                <td><q-btn flat dense round size="sm" icon="delete" color="negative" @click="form.detalles.splice(i, 1)" /></td>
              </tr>
              </tbody>
              <tfoot>
              <tr class="bg-grey-1 text-weight-bold">
                <td>
                  <q-btn flat dense no-caps color="primary" icon="add" label="Agregar producto" @click="agregar" />
                </td>
                <td class="text-right">{{ num(suma('cantidad')) }}</td>
                <td class="text-right">{{ num(suma('peso_recibido')) }}</td>
                <td colspan="2"></td>
              </tr>
              </tfoot>
            </q-markup-table>

            <!-- detalles: admin / ver -->
            <div v-else style="overflow-x: auto">
              <q-markup-table dense flat bordered separator="cell" class="tabla-nota">
                <thead>
                <tr class="bg-grey-2">
                  <th class="text-left" style="min-width: 180px">Descripción</th>
                  <th style="width: 100px">Env.</th>
                  <th class="text-right" style="width: 80px">Peso</th>
                  <th class="text-right" style="width: 80px">Tara</th>
                  <th class="text-right" style="width: 95px">P-Bruto</th>
                  <th class="text-right" style="width: 95px">P-Neto</th>
                  <th class="text-right" style="width: 80px">Cantidad</th>
                  <th class="text-right bg-blue-1" style="width: 95px">P. recibido</th>
                  <th class="text-right bg-red-1" style="width: 85px">Diferencia</th>
                  <th class="text-right" style="width: 90px">Precio</th>
                  <th class="text-right" style="width: 100px">Importe</th>
                  <th class="text-left" style="min-width: 160px">Comentario</th>
                  <th v-if="!soloLectura" style="width: 40px"></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(d, i) in form.detalles" :key="i">
                  <td>
                    <q-select v-model="d.despacho_producto_id" :options="productosOptions" emit-value map-options
                              dense borderless options-dense :readonly="soloLectura" @update:model-value="precioSugerido(d)">
                      <template v-if="!soloLectura" #after>
                        <q-btn flat dense round size="sm" icon="add_circle" color="green" @click="nuevoProducto(d)">
                          <q-tooltip>Crear producto</q-tooltip>
                        </q-btn>
                      </template>
                    </q-select>
                  </td>
                  <td><q-input v-model="d.envase" dense borderless :readonly="soloLectura" /></td>
                  <td><q-input v-model.number="d.peso_envase" type="number" step="0.01" min="0" dense borderless input-class="text-right" :readonly="soloLectura" /></td>
                  <td><q-input v-model.number="d.tara" type="number" step="0.01" min="0" dense borderless input-class="text-right" :readonly="soloLectura" @update:model-value="calcularNeto(d)" /></td>
                  <td><q-input v-model.number="d.peso_bruto" type="number" step="0.0001" min="0" dense borderless input-class="text-right" :readonly="soloLectura" @update:model-value="calcularNeto(d)" /></td>
                  <td><q-input v-model.number="d.peso_neto" type="number" step="0.0001" min="0" dense borderless input-class="text-right text-weight-bold" :readonly="soloLectura" /></td>
                  <td><q-input v-model.number="d.cantidad" type="number" step="0.01" min="0" dense borderless input-class="text-right" :readonly="soloLectura" /></td>
                  <td class="bg-blue-1"><q-input v-model.number="d.peso_recibido" type="number" step="0.01" min="0" dense borderless input-class="text-right" :readonly="soloLectura" /></td>
                  <td class="text-right bg-red-1" :class="difDetalle(d) > 0 ? 'text-negative text-weight-bold' : 'text-positive'">
                    {{ num(difDetalle(d)) }}
                  </td>
                  <td><q-input v-model.number="d.precio" type="number" step="0.0001" min="0" dense borderless input-class="text-right" :readonly="soloLectura" /></td>
                  <td class="text-right">{{ money(importe(d)) }}</td>
                  <td><q-input v-model="d.comentario" dense borderless autogrow :readonly="soloLectura" /></td>
                  <td v-if="!soloLectura"><q-btn flat dense round size="sm" icon="delete" color="negative" @click="form.detalles.splice(i, 1)" /></td>
                </tr>
                </tbody>
                <tfoot>
                <tr class="bg-grey-1 text-weight-bold">
                  <td>
                    <q-btn v-if="!soloLectura" flat dense no-caps color="primary" icon="add" label="Agregar" @click="agregar" />
                  </td>
                  <td></td>
                  <td></td>
                  <td class="text-right">{{ num(suma('tara')) }}</td>
                  <td class="text-right">{{ num(suma('peso_bruto')) }}</td>
                  <td class="text-right">{{ num(suma('peso_neto')) }}</td>
                  <td class="text-right">{{ num(suma('cantidad')) }}</td>
                  <td class="text-right bg-blue-1">{{ num(suma('peso_recibido')) }}</td>
                  <td class="text-right bg-red-1" :class="difTotal > 0 ? 'text-negative' : 'text-positive'">{{ num(difTotal) }}</td>
                  <td class="text-right">TOTAL</td>
                  <td class="text-right">{{ money(totalForm) }}</td>
                  <td :colspan="soloLectura ? 1 : 2"></td>
                </tr>
                </tfoot>
              </q-markup-table>
              <div class="text-caption text-grey-7 q-mt-xs">
                P-Neto = P-Bruto − Tara (se calcula solo, se puede corregir).
                Diferencia = P-Neto de la nota − peso recibido (positivo = falta pollo).
              </div>
            </div>

            <!-- comentarios -->
            <div class="row q-col-gutter-sm q-mt-sm">
              <div class="col-12" :class="modo === 'recepcion' ? '' : 'col-md-6'">
                <q-input v-model="form.comentario" type="textarea" autogrow label="Comentario de recepción" dense outlined
                         :readonly="soloLectura" />
              </div>
              <div v-if="modo !== 'recepcion'" class="col-12 col-md-6">
                <q-input v-model="form.comentario_admin" type="textarea" autogrow label="Comentario del administrador" dense outlined
                         :readonly="soloLectura" />
              </div>
            </div>

            <div v-if="form.id" class="text-caption text-grey-7 q-mt-sm">
              Registrado por <b>{{ form.user?.name || '—' }}</b>
              <span v-if="form.admin"> · Completado por <b>{{ form.admin.name }}</b></span>
            </div>
          </q-card-section>

          <q-card-actions align="right">
            <q-btn v-if="form.id" flat no-caps color="amber-10" icon="print" label="Imprimir" @click="pdf(form.id)" />
            <q-btn flat no-caps label="Cerrar" v-close-popup />
            <q-btn v-if="!soloLectura" type="submit" color="primary" no-caps icon="save"
                   :label="modo === 'admin' ? 'Guardar y completar' : 'Guardar'" :loading="saving" />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>

    <!-- DIALOG CREAR PRODUCTO RÁPIDO -->
    <q-dialog v-model="dlgProd">
      <q-card style="width: 380px; max-width: 95vw">
        <q-card-section class="text-subtitle1 text-weight-bold q-pb-sm">Nuevo producto de despacho</q-card-section>
        <q-form @submit="guardarProducto">
          <q-card-section class="q-pt-none q-gutter-sm">
            <q-input v-model="prod.nombre" label="Nombre (ej. PECHUGA DE POLLO)" dense outlined autofocus
                     :rules="[v => !!v || 'Requerido']" hide-bottom-space />
            <q-select v-model="prod.unidad" :options="['KGS', 'UND']" label="Unidad" dense outlined />
            <q-input v-model.number="prod.precio" type="number" step="0.0001" min="0" label="Precio referencial (Bs)" dense outlined />
          </q-card-section>
          <q-card-actions align="right">
            <q-btn flat no-caps label="Cancelar" v-close-popup />
            <q-btn type="submit" color="primary" no-caps icon="save" label="Crear" :loading="savingProd" />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'DespachosPage',
  data () {
    const today = new Date().toISOString().substr(0, 10)
    return {
      f: { ini: today, fin: today, q: '', estado: null },
      loading: false,
      saving: false,
      rows: [],
      productos: [],
      dlg: false,
      modo: 'recepcion', // recepcion | admin | ver
      dlgProd: false,
      savingProd: false,
      prod: {},
      prodDetalle: null, // fila del detalle donde se asigna el producto creado
      form: this.formVacio(),
      columns: [
        { name: 'opciones', label: 'Opciones', field: 'opciones', align: 'left' },
        { name: 'fecha', label: 'Fecha', field: 'fecha', align: 'left', sortable: true },
        { name: 'numero_nota', label: 'N° nota', field: 'numero_nota', align: 'left' },
        { name: 'proveedor', label: 'Proveedor', field: 'proveedor', align: 'left' },
        { name: 'detalles_count', label: 'Ítems', field: 'detalles_count', align: 'right' },
        { name: 'total_peso_neto', label: 'Kg nota', field: 'total_peso_neto', align: 'right', format: v => this.num(v) },
        { name: 'total_peso_recibido', label: 'Kg recibido', field: 'total_peso_recibido', align: 'right', format: v => this.num(v) },
        { name: 'diferencia', label: 'Diferencia kg', field: 'diferencia', align: 'right' },
        { name: 'total', label: 'Total (Bs)', field: 'total', align: 'right', format: v => this.money(v), sortable: true },
        { name: 'user', label: 'Registró', field: r => r.user?.name || '', align: 'left' },
        { name: 'comentario', label: 'Comentario', field: 'comentario', align: 'left', classes: 'ellipsis', style: 'max-width: 200px' },
        { name: 'estado', label: 'Estado', field: 'estado', align: 'left', sortable: true }
      ]
    }
  },
  computed: {
    esAdmin () {
      return this.$store.permissions.includes('Despachos admin')
    },
    soloLectura () {
      return this.modo === 'ver'
    },
    tituloDialog () {
      const id = this.form.id ? ` #${this.form.id}` : ''
      if (this.modo === 'admin') return `Completar datos de la nota${id}`
      if (this.modo === 'ver') return `Despacho${id}`
      return this.form.id ? `Editar recepción${id}` : 'Registrar despacho (recepción)'
    },
    productosOptions () {
      return this.productos
        .filter(p => p.activo)
        .map(p => ({ label: `${p.nombre} (${p.unidad})`, value: p.id }))
    },
    vigentes () {
      return this.rows.filter(r => r.estado !== 'ANULADO')
    },
    pendientes () {
      return this.rows.filter(r => r.estado === 'PENDIENTE').length
    },
    kgNota () {
      return this.vigentes.reduce((a, r) => a + Number(r.total_peso_neto || 0), 0)
    },
    kgRecibido () {
      return this.vigentes.reduce((a, r) => a + Number(r.total_peso_recibido || 0), 0)
    },
    kgFaltante () {
      return this.rows
        .filter(r => r.estado === 'COMPLETADO')
        .reduce((a, r) => a + this.diferencia(r), 0)
    },
    totalBs () {
      return this.vigentes.reduce((a, r) => a + Number(r.total || 0), 0)
    },
    difTotal () {
      return this.form.detalles.reduce((a, d) => a + this.difDetalle(d), 0)
    },
    totalForm () {
      return this.form.detalles.reduce((a, d) => a + this.importe(d), 0)
    }
  },
  mounted () {
    this.fetch()
    this.fetchProductos()
  },
  methods: {
    formVacio () {
      return {
        id: null,
        fecha: new Date().toISOString().substr(0, 10),
        numero_nota: '',
        proveedor: '',
        cobrador: '',
        comentario: '',
        comentario_admin: '',
        estado: null,
        detalles: []
      }
    },
    detalleVacio () {
      return {
        despacho_producto_id: null,
        cantidad: 0,
        peso_recibido: 0,
        comentario: '',
        envase: '',
        peso_envase: 0,
        tara: 0,
        peso_bruto: 0,
        peso_neto: 0,
        precio: 0
      }
    },
    money (v) {
      return Number(v || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    },
    num (v) {
      return Number(v || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    },
    colorEstado (estado) {
      if (estado === 'ANULADO') return 'negative'
      if (estado === 'COMPLETADO') return 'positive'
      return 'orange'
    },
    diferencia (row) {
      return Number(row.total_peso_neto || 0) - Number(row.total_peso_recibido || 0)
    },
    difDetalle (d) {
      if (!Number(d.peso_neto)) return 0
      return Number(d.peso_neto || 0) - Number(d.peso_recibido || 0)
    },
    importe (d) {
      const p = this.productos.find(x => x.id === d.despacho_producto_id)
      const base = p && String(p.unidad).toUpperCase() === 'UND' ? d.cantidad : d.peso_neto
      return Number(base || 0) * Number(d.precio || 0)
    },
    suma (campo) {
      return this.form.detalles.reduce((a, d) => a + Number(d[campo] || 0), 0)
    },
    calcularNeto (d) {
      d.peso_neto = Math.max(0, Number((Number(d.peso_bruto || 0) - Number(d.tara || 0)).toFixed(4)))
    },
    precioSugerido (d) {
      const p = this.productos.find(x => x.id === d.despacho_producto_id)
      if (p && !Number(d.precio)) d.precio = Number(p.precio || 0)
    },
    agregar () {
      this.form.detalles.push(this.detalleVacio())
    },
    nuevoProducto (d) {
      this.prodDetalle = d
      this.prod = { nombre: '', unidad: 'KGS', precio: 0, activo: true }
      this.dlgProd = true
    },
    async guardarProducto () {
      this.savingProd = true
      try {
        const res = await this.$axios.post('despacho-productos', {
          ...this.prod,
          nombre: this.prod.nombre.trim().toUpperCase()
        })
        this.productos.push(res.data)
        if (this.prodDetalle) {
          this.prodDetalle.despacho_producto_id = res.data.id
          this.precioSugerido(this.prodDetalle)
        }
        this.$alert.success('Producto creado')
        this.dlgProd = false
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo crear el producto')
      } finally {
        this.savingProd = false
      }
    },
    async fetch () {
      this.loading = true
      try {
        const res = await this.$axios.post('despachos/report', {
          fechaInicio: this.f.ini,
          fechaFin: this.f.fin,
          q: this.f.q,
          estado: this.f.estado
        })
        this.rows = res.data
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo cargar los despachos')
      } finally {
        this.loading = false
      }
    },
    async fetchProductos () {
      try {
        const res = await this.$axios.get('despacho-productos')
        this.productos = res.data
      } catch (e) {
        this.$alert.error('No se pudo cargar los productos de despacho')
      }
    },
    nuevo () {
      this.modo = 'recepcion'
      this.form = this.formVacio()
      this.agregar()
      this.dlg = true
    },
    async abrir (id, modo) {
      try {
        const res = await this.$axios.get(`despachos/${id}`)
        const d = res.data
        this.form = {
          ...this.formVacio(),
          ...d,
          detalles: d.detalles.map(x => ({
            ...this.detalleVacio(),
            ...x,
            cantidad: Number(x.cantidad),
            peso_recibido: Number(x.peso_recibido),
            peso_envase: Number(x.peso_envase),
            tara: Number(x.tara),
            peso_bruto: Number(x.peso_bruto),
            peso_neto: Number(x.peso_neto),
            precio: Number(x.precio)
          }))
        }
        if (modo === 'admin') this.form.detalles.forEach(x => this.precioSugerido(x))
        this.modo = modo
        this.dlg = true
      } catch (e) {
        this.$alert.error('No se pudo abrir el despacho')
      }
    },
    async guardar () {
      const detalles = this.form.detalles.filter(d => d.despacho_producto_id)
      if (!detalles.length) {
        this.$alert.warning('Agregue al menos un producto')
        return
      }
      const payload = { ...this.form, detalles }
      this.saving = true
      try {
        if (this.modo === 'admin') {
          let id = this.form.id
          if (!id) {
            const res = await this.$axios.post('despachos', payload)
            id = res.data.id
          }
          await this.$axios.put(`despachos/${id}/completar`, payload)
          this.$alert.success('Despacho completado')
        } else if (this.form.id) {
          await this.$axios.put(`despachos/${this.form.id}`, payload)
          this.$alert.success('Despacho actualizado')
        } else {
          const res = await this.$axios.post('despachos', payload)
          this.$alert.success('Despacho registrado')
          this.$alert.dialog('¿Desea imprimir el despacho?').onOk(() => this.pdf(res.data.id))
        }
        this.dlg = false
        this.fetch()
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo guardar')
      } finally {
        this.saving = false
      }
    },
    anular (id) {
      this.$alert.dialog('¿Anular el despacho?').onOk(async () => {
        try {
          await this.$axios.put(`despachos/${id}/anular`)
          this.$alert.success('Despacho anulado')
          this.fetch()
        } catch (e) {
          this.$alert.error(e.response?.data?.message || 'No se pudo anular')
        }
      })
    },
    pdf (id) {
      const url = this.$axios.defaults.baseURL || ''
      window.open(`${url}/despachos/${id}/pdf`, '_blank')
    }
  }
}
</script>

<style scoped>
.tabla-nota :deep(td) {
  padding: 0 6px;
}
</style>
