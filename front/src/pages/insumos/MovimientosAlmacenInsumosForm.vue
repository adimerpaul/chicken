<template>
  <q-page class="bg-grey-3 q-pa-sm">
    <div class="row q-col-gutter-sm">

      <!-- IZQUIERDA: lista de almacenes -->
      <div class="col-12 col-md-8">
        <q-card flat bordered>
          <q-card-section class="q-pa-sm bg-blue-1 row items-center">
            <div class="text-subtitle1 text-weight-bold">Almacén</div>
            <q-space />
            <q-btn
              flat
              dense
              color="primary"
              icon="arrow_back"
              label="Volver a movimientos"
              no-caps
              :to="{ path: '/movimientos-almacen-insumos' }"
            />
          </q-card-section>

          <q-card-section class="q-pa-sm">
            <div class="row items-center q-col-gutter-sm">
              <div class="col-12 col-md-6">
                <q-input
                  v-model="search"
                  dense
                  outlined
                  placeholder="Buscar en almacén (nombre, unidad, descripción)"
                >
                  <template #prepend><q-icon name="search" /></template>
                </q-input>
              </div>
              <div class="col-12 col-md-6 text-right">
                <q-btn
                  flat
                  color="primary"
                  icon="refresh"
                  :loading="loading"
                  @click="almacenesGet"
                  label="Actualizar"
                  no-caps
                />
              </div>
            </div>

            <div class="q-mt-sm">
              <div class="row text-grey-7 q-pb-xs">
                <div class="col-8 text-caption text-weight-bold">Insumo almacén</div>
                <div class="col-4 text-right text-caption text-weight-bold">Stock</div>
              </div>

              <div
                v-for="a in filteredAlmacenes"
                :key="a.id"
                class="q-py-xs q-mb-xs bg-grey-1 rounded-borders cursor-pointer"
                @click="openQtyDialog(a)"
              >
                <div class="row items-center q-px-sm q-py-xs">
                  <div class="col-8">
                    <div class="text-body1 text-weight-medium">{{ a.nombre }}</div>
                    <div class="text-caption text-grey">
                      {{ a.unidad }} — {{ a.descripcion || '—' }}
                    </div>
                  </div>
                  <div class="col-4 text-right">
                    <q-badge color="primary" text-color="white" align="middle">
                      {{ to2(a.stock) }} {{ a.unidad }}
                    </q-badge>
                  </div>
                </div>
              </div>

              <div v-if="!filteredAlmacenes.length" class="q-mt-md text-center text-grey">
                <q-icon name="inventory_2" />
                <div class="text-caption">Sin insumos de almacén para mostrar</div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- DERECHA: carrito -->
      <div class="col-12 col-md-4">
        <q-card flat bordered>
          <q-card-section class="q-pa-sm bg-amber-1 row items-center">
            <div class="text-subtitle1 text-bold">Salida hacia Insumos</div>
            <q-space />
            <q-btn
              dense
              flat
              color="red"
              icon="delete_sweep"
              label="Limpiar"
              no-caps
              @click="clearCart"
            />
          </q-card-section>

          <q-card-section class="q-pa-sm">
            <q-list bordered dense class="rounded-borders">
              <q-item v-for="it in carrito" :key="it.key">
                <q-item-section>
                  <q-item-label class="text-bold">
                    {{ it.almacen_nombre }}
                    <q-icon name="arrow_forward" size="14px" class="q-ml-xs" />
                    {{ it.insumo_nombre }}
                  </q-item-label>
                  <q-item-label caption>
                    <div class="row q-col-gutter-xs q-mt-xs">
                      <div class="col-6">
                        <q-input
                          v-model.number="it.cantidad"
                          type="number"
                          step="0.01"
                          dense
                          outlined
                          label="Cantidad"
                        />
                      </div>
                      <div class="col-6">
                        <q-input
                          v-model.number="it.costo"
                          type="number"
                          step="0.01"
                          dense
                          outlined
                          label="Costo (Bs)"
                        />
                      </div>
                    </div>
                  </q-item-label>
                </q-item-section>
                <q-item-section side class="text-right">
                  <div class="text-bold text-primary">
                    {{ money(it.cantidad * it.costo) }}
                  </div>
                  <q-btn
                    dense
                    flat
                    round
                    icon="delete"
                    color="red"
                    @click="removeFromCart(it.key)"
                  />
                </q-item-section>
              </q-item>

              <q-item v-if="!carrito.length">
                <q-item-section>
                  <q-item-label caption>Sin ítems en la salida</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>

            <div class="q-mt-sm text-right text-subtitle1">
              <b>Total:</b> {{ money(total) }} Bs
            </div>

            <q-btn
              class="q-mt-md full-width"
              color="indigo"
              icon="sync_alt"
              label="Registrar movimiento"
              :disable="!carrito.length"
              :loading="loading"
              no-caps
              @click="openPayDialog"
            />
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- DIALOG: seleccionar insumo destino + cantidad/costo -->
    <q-dialog v-model="qtyDlg">
      <q-card style="width: 480px; max-width: 90vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1">Desde almacén: {{ current?.nombre }}</div>
          <q-space />
          <q-btn flat round dense icon="close" @click="qtyDlg = false" />
        </q-card-section>
        <q-card-section class="q-pt-none">
          <div class="text-caption q-mb-xs">
            Unidad: {{ current?.unidad }} |
            Stock actual: {{ to2(current?.stock) }}
          </div>

          <q-select
            v-model="form.insumo_id"
            :options="insumosOptions"
            dense outlined
            label="Insumo destino (despensa)"
            option-value="id"
            option-label="label"
            emit-value
            map-options
            class="q-mb-sm"
          />

          <q-input
            v-model.number="form.cantidad"
            type="number"
            step="0.01"
            dense
            outlined
            label="Cantidad"
            class="q-mb-sm"
          />
          <q-input
            v-model.number="form.costo"
            type="number"
            step="0.01"
            dense
            outlined
            label="Costo (Bs)"
          />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cancelar" color="negative" no-caps @click="qtyDlg = false" />
          <q-btn label="Agregar" color="primary" no-caps @click="addToCart" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- DIALOG: datos generales del movimiento -->
    <q-dialog v-model="payDlg">
      <q-card style="width: 540px; max-width: 95vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1">Confirmar movimiento Almacén → Insumos</div>
          <q-space />
          <q-btn flat round dense icon="close" @click="payDlg = false" />
        </q-card-section>
        <q-card-section class="q-pt-none">
          <div class="row q-col-gutter-sm">
            <div class="col-12 col-sm-6">
              <q-input v-model="movimiento.fecha" label="Fecha" type="date" dense outlined />
            </div>
            <div class="col-12">
              <q-input
                v-model="movimiento.nota"
                label="Nota"
                type="textarea"
                autogrow
                dense
                outlined
              />
            </div>
          </div>
          <div class="q-mt-sm text-right text-subtitle1">
            <b>Total:</b> {{ money(total) }} Bs
          </div>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat color="negative" label="Cancelar" no-caps @click="payDlg = false" />
          <q-btn
            color="positive"
            label="Guardar"
            no-caps
            :loading="loading"
            @click="saveMovimiento"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'MovimientosAlmacenInsumosForm',
  data () {
    const today = new Date().toISOString().substr(0, 10)
    return {
      loading: false,
      search: '',
      almacenes: [],
      insumos: [],
      carrito: [],
      // dialog selección
      qtyDlg: false,
      current: null,
      form: { insumo_id: null, cantidad: null, costo: null },
      // dialog confirmación
      payDlg: false,
      movimiento: { fecha: today, nota: '' },
      keyCounter: 1
    }
  },
  computed: {
    filteredAlmacenes () {
      const q = (this.search || '').toLowerCase()
      if (!q) return this.almacenes
      return this.almacenes.filter(a =>
        String(a.nombre || '').toLowerCase().includes(q) ||
        String(a.unidad || '').toLowerCase().includes(q) ||
        String(a.descripcion || '').toLowerCase().includes(q)
      )
    },
    insumosOptions () {
      return this.insumos.map(i => ({
        id: i.id,
        label: `${i.nombre} (${i.unidad})`
      }))
    },
    total () {
      return this.carrito.reduce((a, it) => {
        const c = Number(it.cantidad || 0)
        const p = Number(it.costo || 0)
        return a + (c * p)
      }, 0)
    }
  },
  mounted () {
    this.almacenesGet()
    this.insumosGet()
  },
  methods: {
    to2 (v) {
      const n = Number(v || 0)
      return n.toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    },
    money (v) {
      return this.to2(v)
    },
    async almacenesGet () {
      this.loading = true
      try {
        const res = await this.$axios.get('almacenes')
        this.almacenes = Array.isArray(res.data) ? res.data : []
      } catch (e) {
        this.$alert?.error?.('No se pudo cargar los insumos de almacén')
      } finally {
        this.loading = false
      }
    },
    async insumosGet () {
      try {
        const res = await this.$axios.get('insumos')
        this.insumos = Array.isArray(res.data) ? res.data : []
      } catch (e) {
        this.$alert?.error?.('No se pudo cargar los insumos de despensa')
      }
    },
    openQtyDialog (almacen) {
      this.current = almacen
      this.form = {
        insumo_id: null,
        cantidad: 1,
        costo: Number(almacen.costo || 0)
      }
      this.qtyDlg = true
    },
    addToCart () {
      const cant = Number(this.form.cantidad || 0)
      const cost = Number(this.form.costo || 0)
      const insumoId = this.form.insumo_id

      if (!insumoId || cant <= 0) {
        this.$alert?.error?.('Seleccione insumo destino y cantidad válida')
        return
      }

      const insumo = this.insumos.find(i => i.id === insumoId)
      if (!insumo) {
        this.$alert?.error?.('Insumo destino inválido')
        return
      }

      const key = this.keyCounter++

      this.carrito.push({
        key,
        almacen_id: this.current.id,
        almacen_nombre: this.current.nombre,
        insumo_id: insumo.id,
        insumo_nombre: insumo.nombre,
        cantidad: cant,
        costo: cost
      })

      this.qtyDlg = false
    },
    removeFromCart (key) {
      this.carrito = this.carrito.filter(x => x.key !== key)
    },
    clearCart () {
      this.carrito = []
    },
    openPayDialog () {
      if (!this.carrito.length) {
        this.$alert?.error?.('No hay ítems en el movimiento')
        return
      }
      this.payDlg = true
    },
    async saveMovimiento () {
      const payload = {
        fecha: this.movimiento.fecha,
        nota: this.movimiento.nota || null,
        detalles: this.carrito.map(it => ({
          almacen_id: it.almacen_id,
          insumo_id: it.insumo_id,
          cantidad: Number(it.cantidad || 0),
          costo: Number(it.costo || 0)
        }))
      }

      this.loading = true
      try {
        await this.$axios.post('movimientos-almacen-insumos', payload)
        this.$alert?.success?.('Movimiento registrado')
        this.payDlg = false
        this.carrito = []
        this.movimiento.nota = ''
        // refrescar stocks
        this.almacenesGet()
      } catch (e) {
        const msg = e.response?.data?.message || 'No se pudo registrar el movimiento'
        this.$alert?.error?.(msg)
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
