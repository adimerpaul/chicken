<template>
  <q-page class="bg-grey-3 q-pa-sm">
    <div class="row">
      <!-- IZQUIERDA: productos -->
      <div class="col-12 col-md-8">
        <q-card flat bordered>
          <q-card-section class="q-pa-sm">
            <div class="row items-center q-col-gutter-sm">
              <div class="col-12 col-sm-4">
                <q-input v-model="search" dense outlined debounce="300" placeholder="Buscar producto..." />
              </div>
              <div class="col-12 col-sm-8">
                <div class="row items-center q-gutter-xs">
                  <q-chip
                    clickable
                    :color="categoryId === '' ? 'indigo' : 'grey-4'"
                    :text-color="categoryId === '' ? 'white' : 'black'"
                    icon="public"
                    @click="selectCategory('')"
                  >Todo</q-chip>

                  <q-chip
                    v-for="c in categories" :key="c.id"
                    clickable
                    :color="categoryId === c.id ? (c.color || 'indigo') : 'grey-4'"
                    :text-color="categoryId === c.id ? 'white' : 'black'"
                    :icon="c.icon || 'category'"
                    @click="selectCategory(c.id)"
                  >{{ c.name }}</q-chip>

                  <q-space/>
                  <q-btn flat color="primary" icon="refresh" :loading="loading" @click="fetchAll"/>
                </div>
              </div>
            </div>

            <div class="row q-col-gutter-md q-mt-sm">
              <div
                v-for="p in filteredProducts"
                :key="p.id"
                class="col-12 col-sm-6 col-md-4 col-lg-3"
              >
                <q-card class="hoverable cursor-pointer" @click="addToCart(p)">
                  <div class="relative-position" style="height:160px;background:#111;">
                    <img v-if="p.image" :src="imageUrl(p)" alt="" style="width:100%;height:100%;object-fit:cover" loading="lazy">
                    <div v-else class="full-width full-height flex flex-center text-white">
                      <q-icon name="image" size="48px"/>
                    </div>

                    <div class="absolute-bottom-right q-ma-sm bg-red text-white"
                         style="padding:4px 10px;border-radius:999px;font-weight:700;">
                      {{ money(p.price) }} Bs
                    </div>

                    <div class="absolute-bottom bg-gradient text-white q-pa-sm">
                      <div class="text-subtitle2 text-weight-bold ellipsis-2-lines">{{ p.name }}</div>
                    </div>
                  </div>

                  <q-card-section class="q-py-xs">
                    <div class="row items-center">
                      <q-badge outline color="grey-7" class="q-mr-sm">{{ p.unit || 'UND' }}</q-badge>
                      <div class="text-caption text-grey">{{ p.categoria || '—' }}</div>
                      <q-space/>
                      <q-btn dense round flat icon="add_shopping_cart" @click.stop="addToCart(p)" />
                    </div>
                  </q-card-section>
                </q-card>
              </div>

              <div v-if="!loading && !filteredProducts.length" class="col-12">
                <q-card flat bordered>
                  <q-card-section class="text-center text-grey">No hay productos</q-card-section>
                </q-card>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- DERECHA: carrito -->
      <div class="col-12 col-md-4">
        <q-card flat bordered>
          <q-card-section class="q-pa-sm row items-center bg-yellow-3">
            <div class="text-subtitle1 text-bold">Carrito</div>
            <q-space/>
            <q-btn dense color="red" icon="delete_sweep" label="Limpiar" no-caps @click="clearCart" />
          </q-card-section>

          <q-card-section class="q-pa-sm">
            <q-list bordered dense>
              <q-item v-for="it in cart" :key="it.id">
                <q-item-section>
                  <q-item-label class="text-bold">{{ it.name }}</q-item-label>
                  <q-item-label caption>
                    <div class="row q-col-gutter-xs">
                      <div class="col-5">
                        <q-input v-model.number="it.price" type="number" step="0.01" dense outlined>
<!--                          <template #prepend><q-icon name="o_attach_money" size="14px" /></template>-->
                        </q-input>
                      </div>
                      <div class="col-5">
                        <q-input v-model.number="it.cantidadSale" type="number" step="1" min="1" dense outlined>
                          <template #prepend><q-icon name="shopping_cart" size="14px" /></template>
                        </q-input>
                      </div>
                      <div class="col-2 flex flex-center">
                        <q-btn dense flat round icon="delete" color="red" @click="removeFromCart(it.id)"/>
                      </div>
                    </div>
                  </q-item-label>
                </q-item-section>
                <q-item-section side class="text-right">
                  <div class="text-bold">{{ money(it.price * it.cantidadSale) }}</div>
                </q-item-section>
              </q-item>

              <q-item v-if="!cart.length">
                <q-item-section><q-item-label caption>Carrito vacío</q-item-label></q-item-section>
              </q-item>
            </q-list>

            <div class="q-mt-sm text-right text-subtitle1">
              <b>Total:</b> {{ money(total) }} Bs
            </div>

            <q-btn
              class="q-mt-md"
              color="indigo"
              icon="shopping_cart"
              label="Pagar"
              :disable="!cart.length"
              :loading="loading"
              no-caps
              @click="openPayDialog"
            />
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- DIALOG: pago -->
    <q-dialog v-model="payDlg" persistent>
      <q-card style="width: 860px; max-width: 95vw">
        <q-card-section class="q-pa-sm row items-center">
          <div class="text-h6 text-bold">Pago</div>
          <q-space/>
          <q-btn flat dense icon="close" @click="payDlg=false"/>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <div class="row q-col-gutter-sm">
            <div class="col-12 col-md-3">
              <q-input v-model="client.ci" label="CI/NIT" outlined dense clearable debounce="300" @update:model-value="searchClient"/>
            </div>
            <div class="col-12 col-md-5">
              <q-input v-model="client.name" label="Nombre" outlined dense @update:model-value="toUpperName"/>
            </div>
            <div class="col-6 col-md-2">
              <q-select v-model="client.mesa" label="Mesa" outlined dense :options="['MESA','LLEVAR','DELIVERY','PEDIDOS YA']"/>
            </div>
            <div class="col-6 col-md-2">
              <q-select v-model="client.pago" label="Pago" outlined dense :options="['EFECTIVO','TARJETA','ONLINE','QR']"/>
            </div>

            <div class="col-6 col-md-2">
              <q-select v-model="client.llamada" label="Llamada" outlined dense :options="cantidades"/>
            </div>
            <div class="col-12 col-md-10">
              <q-input v-model="client.comment" label="Comentario" outlined dense />
            </div>
          </div>

          <q-separator class="q-my-sm"/>

          <div class="row items-center q-col-gutter-sm">
            <div class="col-12 col-md-4">
              <q-input v-model.number="recibido" type="number" step="0.01" label="Monto recibido" outlined dense/>
            </div>
            <div class="col-12 col-md-4 text-red text-subtitle1 text-bold">
              Total: {{ money(total) }} Bs
            </div>
            <div class="col-12 col-md-4 text-blue text-subtitle1 text-bold">
              <span v-if="recibido !== ''">Cambio: {{ money(recibido - total) }} Bs</span>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat color="negative" label="Cancelar" no-caps @click="payDlg=false"/>
          <q-btn color="positive" label="Confirmar y cobrar" no-caps :loading="loading" @click="submitSale"/>
        </q-card-actions>
      </q-card>
    </q-dialog>
    <div id="myelement" class="hidden"></div>
  </q-page>
</template>

<script>
import { Imprimir } from 'src/utils/ImprimirTicket' // ajusta la ruta

export default {
  name: 'VentasPage',
  data () {
    return {
      loading: false,
      products: [],
      categories: [
        { id: 'Refrescos y Bebidas', name: 'Refrescos y Bebidas', icon: 'local_drink',  color: 'blue'   },
        { id: 'Acompañamientos',     name: 'Acompañamientos',     icon: 'restaurant',    color: 'teal'   },
        { id: 'Pollos',              name: 'Pollos',              icon: 'lunch_dining',  color: 'brown'  },
        // agrega las demás si quieres…
      ],
      search: '',
      categoryId: '',
      cart: [],

      // pago
      payDlg: false,
      client: { ci: '', name: 'SN', mesa: 'MESA', pago: 'EFECTIVO', llamada: 0, comment: '' },
      recibido: '',

      cantidades: Array.from({length: 100}, (_,i)=> i+1),
    }
  },
  computed: {
    filteredProducts () {
      const q = (this.search || '').toLowerCase()
      return this.products.filter(p => {
        const byCat  = !this.categoryId || p.category_id === this.categoryId
        const byText = !q || (String(p.name||'').toLowerCase().includes(q) ||
          String(p.description||'').toLowerCase().includes(q))
        return byCat && byText
      })
    },
    total () {
      return this.cart.reduce((acc, it) => acc + (Number(it.price||0) * Number(it.cantidadSale||0)), 0)
    }
  },
  mounted () {
    this.fetchAll()
  },
  methods: {
    money (v) {
      return Number(v || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    },
    imageUrl (p) {
      // ajusta si tus productos ya traen image_url
      return p.image_url || `${this.$url}/../storage/${p.image}`
    },

    async fetchAll () {
      this.loading = true
      // try {
      //   const [cats, prods] = await Promise.all([
      //     // this.$axios.get('categories'),
      //     this.$axios.get('productos'),
      //   ])
      //   this.categories = Array.isArray(cats.data) ? cats.data : []
      //   const arr = Array.isArray(prods.data) ? prods.data : []
      //   // Normaliza precios/qty
      //   this.products = arr.map(p => ({ ...p, price: Number(p.price || 0) }))
      // } finally { this.loading = false }
      // productos
      this.$axios.get('productos')
        .then(({ data }) => {
          const arr = Array.isArray(data) ? data : []
          // Normaliza precios/qty
          this.products = arr.map(p => ({ ...p, price: Number(p.price || 0) }))
        })
        .catch(() => {
          this.$q.notify?.({ type:'negative', message:'No se pudieron cargar los productos' })
        })
        .finally(() => { this.loading = false })
    },

    selectCategory (id) { this.categoryId = id },

    addToCart (p) {
      const idx = this.cart.findIndex(x => x.id === p.id)
      if (idx >= 0) {
        this.cart[idx].cantidadSale += 1
      } else {
        this.cart.push({
          id: p.id,
          name: p.name,
          price: Number(p.price || 0),
          cantidadSale: 1
        })
      }
      this.$q.notify?.({ type:'positive', message:`${p.name} agregado` })
    },

    removeFromCart (id) {
      this.cart = this.cart.filter(x => x.id !== id)
    },

    clearCart () { this.cart = [] },

    openPayDialog () {
      if (!this.cart.length) {
        this.$q.notify?.({ type:'warning', message:'Carrito vacío' })
        return
      }
      this.recibido = ''
      this.payDlg = true
    },

    toUpperName () { this.client.name = (this.client.name || '').toUpperCase() },

    async searchClient () {
      if (!this.client.ci) { this.client.name = 'SN'; return }
      try {
        const { data } = await this.$axios.get(`searchClient/${this.client.ci}`)
        if (data && data.name) this.client.name = data.name
      } catch (_) {}
    },

    async submitSale () {
      if (!this.cart.length) { this.$q.notify?.({ type:'negative', message:'Carrito vacío' }); return }
      this.loading = true
      try {
        const payload = {
          client: this.client,
          products: this.cart,        // [{ id, name, price, cantidadSale }]
          mesa: this.client.mesa,
          llamada: this.client.llamada,
          pago: this.client.pago,
          comment: this.client.comment || ''
        }
        const { data } = await this.$axios.post('sales', payload)
        this.$q.notify?.({ type:'positive', message:'Venta realizada' })
        // limpia y cierra
        Imprimir.ticket(data)
        this.cart = []
        this.payDlg = false
        this.client = { ci: '', name: 'SN', mesa: 'MESA', pago: 'EFECTIVO', llamada: 0, comment: '' }
        this.recibido = ''
        // si imprimes recibo:
        // Imprimir.recibo(data)
      } catch (e) {
        const msg = e?.response?.data?.message || 'No se pudo registrar la venta'
        this.$q.notify?.({ type:'negative', message: msg })
      } finally { this.loading = false }
    }
  }
}
</script>

<style scoped>
.bg-gradient{ background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,.85) 100%); }
.hoverable{ transition: .15s; }
.hoverable:hover{ transform: translateY(-2px); }
.ellipsis-2-lines{
  display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
}
</style>
