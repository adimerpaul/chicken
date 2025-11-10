<template>
  <q-page class="bg-grey-3 q-pa-sm">
    <div class="row">
      <!-- IZQUIERDA: lista de insumos -->
      <div class="col-12 col-md-8">
        <q-card>
          <q-card-section class="q-pa-sm">
            <div class="row items-center">
              <div class="col-12">
                <q-btn flat color="primary" icon="shopping_cart" label="Ir a Compras" no-caps href="/compras" />
              </div>
              <div class="col-12 col-md-6 q-pa-xs">
                <q-input v-model="search" dense outlined placeholder="Buscar insumo (nombre, unidad, descripción)"/>
              </div>
              <div class="col-12 col-md-6 q-pa-xs text-right">
                <q-btn flat color="primary" icon="refresh" :loading="loading" @click="insumosGet"/>
              </div>
            </div>

            <div class="row q-mt-sm">
              <div class="col-8">
                <div class="text-subtitle1 text-weight-bold">Insumo</div>
              </div>
              <div class="col-4 text-right">
                <div class="text-subtitle1 text-weight-bold">Stock</div>
              </div>

              <div
                v-for="insumo in filteredInsumos"
                :key="insumo.id"
                class="col-12 q-mt-sm"
              >
                <div class="row cursor-pointer" @click="openQtyDialog(insumo)">
                  <div class="col-8">
                    <div class="text-body1">{{ insumo.nombre }}</div>
                    <div class="text-caption text-grey">
                      {{ insumo.unidad }} — {{ insumo.descripcion || '—' }}
                    </div>
                  </div>
                  <div class="col-4 text-right">
                    <div class="text-body1">{{ to2(insumo.stock) }}</div>
                  </div>
                </div>
                <q-separator spaced inset/>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- DERECHA: carrito -->
      <div class="col-12 col-md-4">
        <q-card>
          <q-card-section class="q-pa-sm row items-center bg-yellow-3">
            <div class="text-subtitle1 text-bold">Carrito</div>
            <q-space/>
            <q-btn dense color="red" icon="delete_sweep" label="Limpiar" no-caps @click="clearCart" />
          </q-card-section>

          <q-card-section class="q-pa-sm">
            <q-list bordered dense>
              <q-item v-for="it in carrito" :key="it.id">
                <q-item-section>
                  <q-item-label class="text-bold">{{ it.nombre }}</q-item-label>
                  <q-item-label caption>
                    <div class="row q-col-gutter-xs">
                      <div class="col-6">
                        <q-input v-model.number="it.cantidad" type="number" step="0.01" dense outlined label="Cantidad"/>
                      </div>
                      <div class="col-6">
                        <q-input v-model.number="it.costo" type="number" step="0.01" dense outlined label="Costo (Bs)"/>
                      </div>
                    </div>
                  </q-item-label>
                </q-item-section>
                <q-item-section side class="text-right">
                  <div class="text-bold">{{ money(it.cantidad * it.costo) }}</div>
                  <q-btn dense flat round icon="delete" color="red" @click="removeFromCart(it.id)"/>
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
              class="q-mt-md"
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
          <q-space/>
          <q-btn flat round dense icon="close" @click="qtyDlg=false"/>
        </q-card-section>
        <q-card-section class="q-pt-none">
          <div class="text-caption q-mb-xs">Unidad: {{ current?.unidad }} | Stock: {{ to2(current?.stock) }}</div>
          <q-input v-model.number="form.cantidad" type="number" step="0.01" dense outlined label="Cantidad" class="q-mb-sm"/>
          <q-input v-model.number="form.costo"    type="number" step="0.01" dense outlined label="Costo (Bs)"/>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cancelar" color="negative" no-caps @click="qtyDlg=false"/>
          <q-btn label="Agregar" color="primary" no-caps @click="addToCart"/>
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- DIALOG: datos de compra -->
    <q-dialog v-model="payDlg">
      <q-card style="width: 540px; max-width: 95vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1">Confirmar compra</div>
          <q-space/><q-btn flat round dense icon="close" @click="payDlg=false"/>
        </q-card-section>
        <q-card-section class="q-pt-none">
          <div class="row q-col-gutter-sm">
            <div class="col-12 col-sm-6">
              <q-input v-model="compra.fecha" label="Fecha" type="date" dense outlined/>
            </div>
            <div class="col-12 col-sm-6">
              <q-input v-model="compra.proveedor" label="Proveedor" dense outlined/>
            </div>
            <div class="col-12">
              <q-input v-model="compra.nota" label="Nota" type="textarea" autogrow dense outlined/>
            </div>
          </div>
          <div class="q-mt-sm text-right text-subtitle1">
            <b>Total:</b> {{ money(total) }} Bs
          </div>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat color="negative" label="Cancelar" no-caps @click="payDlg=false"/>
          <q-btn color="positive" label="Guardar" no-caps :loading="loading" @click="savePurchase"/>
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'InsumosResumen',
  data () {
    const today = new Date().toISOString().substr(0,10)
    return {
      loading: false,
      search: '',
      insumos: [],
      carrito: [],
      // dialog agregar
      qtyDlg: false,
      current: null,
      form: { cantidad: null, costo: null },
      // dialog pagar
      payDlg: false,
      compra: { fecha: today, proveedor: '', nota: '' },
    }
  },
  computed: {
    filteredInsumos () {
      const q = (this.search || '').toLowerCase()
      if (!q) return this.insumos
      return this.insumos.filter(i =>
        String(i.nombre || '').toLowerCase().includes(q) ||
        String(i.unidad || '').toLowerCase().includes(q) ||
        String(i.descripcion || '').toLowerCase().includes(q)
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
    this.insumosGet()
  },
  methods: {
    to2 (v) {
      const n = Number(v || 0)
      return n.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    },
    money (v) { return this.to2(v) },

    async insumosGet () {
      this.loading = true
      try {
        const res = await this.$axios.get('insumos')
        this.insumos = Array.isArray(res.data) ? res.data : []
      } catch (e) {
        if (this.$alert && this.$alert.error) this.$alert.error('No se pudo cargar los insumos')
      } finally { this.loading = false }
    },

    openQtyDialog (insumo) {
      this.current = insumo
      this.form = {
        cantidad: 1,
        costo: Number(insumo.costo || 0)
      }
      // this.qtyDlg = true
      this.addToCart()
    },

    addToCart () {
      const cant = Number(this.form.cantidad || 0)
      const cost = Number(this.form.costo || 0)
      // if (cant <= 0 || cost <= 0) {
      //   if (this.$alert && this.$alert.error) this.$alert.error('Ingrese cantidad y costo válidos')
      //   return
      // }
      // merge
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
        if (this.$alert && this.$alert.error) this.$alert.error('Carrito vacío')
        return
      }
      this.payDlg = true
    },

    async savePurchase () {
      // arma payload para /compras
      const payload = {
        fecha: this.compra.fecha,
        proveedor: this.compra.proveedor || null,
        nota: this.compra.nota || null,
        detalles: this.carrito.map(it => ({
          insumo_id: it.id,
          cantidad: Number(it.cantidad || 0),
          costo: Number(it.costo || 0)
        }))
      }

      this.loading = true
      try {
        await this.$axios.post('compras', payload)
        if (this.$alert && this.$alert.success) this.$alert.success('Compra registrada')
        // reset
        this.payDlg = false
        this.carrito = []
        this.compra.proveedor = ''
        this.compra.nota = ''
        // refrescar stocks si quieres
        this.insumosGet()
      } catch (e) {
        const msg = e && e.response && e.response.data && e.response.data.message
          ? e.response.data.message
          : 'No se pudo registrar la compra'
        if (this.$alert && this.$alert.error) this.$alert.error(msg)
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
