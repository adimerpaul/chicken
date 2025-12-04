<template>
  <q-page class="bg-grey-3 q-pa-sm">
    <div class="row q-col-gutter-sm">

      <!-- IZQUIERDA: lista de almacén -->
      <div class="col-12 col-md-8">
        <q-card flat bordered>
          <q-card-section class="q-pa-sm bg-blue-1 row items-center">
            <div class="text-subtitle1 text-weight-bold">Insumos de almacén</div>
            <q-space />
            <q-btn
              flat
              dense
              color="primary"
              icon="arrow_back"
              label="Volver a listado"
              no-caps
              :to="{ path: '/compras-almacen' }"
            />
          </q-card-section>

          <q-card-section class="q-pa-sm">
            <div class="row items-center q-col-gutter-sm">
              <div class="col-12 col-md-6">
                <q-input
                  v-model="search"
                  dense
                  outlined
                  placeholder="Buscar (nombre, unidad, descripción)"
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
                <div class="col-8 text-caption text-weight-bold">Insumo</div>
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
            <div class="text-subtitle1 text-bold">Carrito de compra</div>
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
              <q-item v-for="it in carrito" :key="it.id">
                <q-item-section>
                  <q-item-label class="text-bold">{{ it.nombre }}</q-item-label>
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
                    @click="removeFromCart(it.id)"
                  />
                </q-item-section>
              </q-item>

              <q-item v-if="!carrito.length">
                <q-item-section>
                  <q-item-label caption>Carrito vacío</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>

            <div class="q-mt-sm text-right text-subtitle1">
              <b>Total:</b> {{ money(total) }} Bs
            </div>

            <q-btn
              class="q-mt-md full-width"
              color="indigo"
              icon="shopping_cart"
              label="Registrar compra"
              :disable="!carrito.length"
              :loading="loading"
              no-caps
              @click="openPayDialog"
            />
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- DIALOG: cantidad/costo al agregar -->
    <q-dialog v-model="qtyDlg">
      <q-card style="width: 420px; max-width: 90vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1">Agregar: {{ current?.nombre }}</div>
          <q-space />
          <q-btn flat round dense icon="close" @click="qtyDlg = false" />
        </q-card-section>
        <q-card-section class="q-pt-none">
          <div class="text-caption q-mb-xs">
            Unidad: {{ current?.unidad }} |
            Stock actual: {{ to2(current?.stock) }}
          </div>
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

    <!-- DIALOG: datos de compra -->
    <q-dialog v-model="payDlg">
      <q-card style="width: 540px; max-width: 95vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1">Confirmar compra de almacén</div>
          <q-space />
          <q-btn flat round dense icon="close" @click="payDlg = false" />
        </q-card-section>
        <q-card-section class="q-pt-none">
          <div class="row q-col-gutter-sm">
            <div class="col-12 col-sm-6">
              <q-input v-model="compra.fecha" label="Fecha" type="date" dense outlined />
            </div>
            <div class="col-12 col-sm-6">
              <q-input v-model="compra.proveedor" label="Proveedor" dense outlined />
            </div>
            <div class="col-12">
              <q-input
                v-model="compra.nota"
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
            @click="savePurchase"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'ComprasAlmacenForm',
  data () {
    const today = new Date().toISOString().substr(0, 10)
    return {
      loading: false,
      search: '',
      almacenes: [],
      carrito: [],
      qtyDlg: false,
      current: null,
      form: { cantidad: null, costo: null },
      payDlg: false,
      compra: { fecha: today, proveedor: '', nota: '' }
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
    openQtyDialog (almacen) {
      this.current = almacen
      this.form = {
        cantidad: 1,
        costo: Number(almacen.costo || 0)
      }
      // si quieres preguntar siempre, descomenta:
      // this.qtyDlg = true
      this.addToCart()
    },
    addToCart () {
      const cant = Number(this.form.cantidad || 0)
      const cost = Number(this.form.costo || 0)

      const idx = this.carrito.findIndex(x => x.id === this.current.id)
      if (idx >= 0) {
        this.carrito[idx].cantidad += cant
        this.carrito[idx].costo = cost
      } else {
        this.carrito.push({
          id: this.current.id,
          nombre: this.current.nombre,
          unidad: this.current.unidad,
          cantidad: cant,
          costo: cost
        })
      }
      this.qtyDlg = false
    },
    removeFromCart (id) {
      this.carrito = this.carrito.filter(x => x.id !== id)
    },
    clearCart () {
      this.carrito = []
    },
    openPayDialog () {
      if (!this.carrito.length) {
        this.$alert?.error?.('Carrito vacío')
        return
      }
      this.payDlg = true
    },
    async savePurchase () {
      const payload = {
        fecha: this.compra.fecha,
        proveedor: this.compra.proveedor || null,
        nota: this.compra.nota || null,
        detalles: this.carrito.map(it => ({
          almacen_id: it.id,
          cantidad: Number(it.cantidad || 0),
          costo: Number(it.costo || 0)
        }))
      }

      this.loading = true
      try {
        await this.$axios.post('compras-almacen', payload)
        this.$alert?.success?.('Compra de almacén registrada')
        this.payDlg = false
        this.carrito = []
        this.compra.proveedor = ''
        this.compra.nota = ''
        this.almacenesGet()
      } catch (e) {
        const msg = e.response?.data?.message || 'No se pudo registrar la compra'
        this.$alert?.error?.(msg)
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
