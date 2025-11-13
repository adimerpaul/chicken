<template>
  <q-page class="q-pa-md bg-grey-3">
    <div class="row items-center q-gutter-sm q-mb-sm">
      <q-btn flat round dense icon="menu" class="lt-md"/>
      <div class="text-h6 text-weight-bold">Productos</div>
      <q-space/>
      <q-btn
        color="positive" icon="add_circle" label="Nuevo" no-caps
        @click="openNew"
        class="q-mr-sm"
      />
      <q-input v-model="search" dense outlined debounce="300" placeholder="Buscar producto...">
        <template #prepend><q-icon name="search"/></template>
      </q-input>
    </div>

    <!-- Chips categorías (hardcode) -->
    <div class="row q-col-gutter-sm q-mb-md">
      <div class="col-12">
        <div class="row items-center q-gutter-sm">
          <q-chip
            clickable
            :color="categoryId === 'all' ? 'indigo' : 'grey-4'"
            :text-color="categoryId === 'all' ? 'white' : 'black'"
            @click="selectCategory('all')"
            icon="public"
          >Todo</q-chip>

          <q-chip
            v-for="c in categories"
            :key="c.id"
            clickable
            :color="categoryId === c.id ? (c.color || 'indigo') : 'grey-4'"
            :text-color="categoryId === c.id ? 'white' : 'black'"
            @click="selectCategory(c.id)"
            :icon="c.icon || 'category'"
          >{{ c.name }}</q-chip>
        </div>
      </div>
    </div>

    <!-- Grilla -->
    <div class="row q-col-gutter-md">
      <div v-for="p in filtered" :key="p.id" class="col-12 col-sm-6 col-md-4 col-lg-3">
<!--        <pre>{{p}}</pre>-->
<!--        <a :href="`${$url}/../storage/${p.image}`" target="_blank">Ver en nueva pestaña</a>-->
        <q-card class="overflow-hidden hoverable" >
          <div class="relative-position" style="height:180px;background:#111;">
            <img v-if="p.image" :src="`${$url}/../storage/${p.image}`" alt="img"
                 style="width:100%;height:100%;object-fit:cover" loading="lazy">
            <div v-else class="full-width full-height flex flex-center text-white">
              <q-icon name="image" size="56px"/>
            </div>

            <div class="absolute-bottom-right q-ma-sm bg-red text-white"
                 style="padding:4px 10px;border-radius:999px;font-weight:700;">
              {{ toMoney(p.price) }} Bs
            </div>

            <div class="absolute-bottom bg-gradient text-white q-pa-sm">
              <div class="text-subtitle2 text-weight-bold ellipsis-2-lines">{{ p.name }}</div>
            </div>
          </div>

          <q-card-section class="q-py-xs">
            <div class="row items-center">
              <q-badge outline color="grey-7" class="q-mr-sm">{{ p.unit }}</q-badge>
              <div class="text-caption text-grey">{{ p.categoria || '—' }}</div>
              <q-space/>
              <q-btn dense round flat icon="edit" @click.stop="openEdit(p)" />
              <q-btn
                dense round flat
                icon="inventory_2"
                class="q-ml-xs"
                @click.stop="openInsumos(p)"
                :title="'Insumos del producto'"
              />
<!--              <q-btn dense round flat icon="add_shopping_cart" @click.stop="emitAdd(p)"/>-->
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div v-if="!loading && !filtered.length" class="col-12">
        <q-card flat bordered>
          <q-card-section class="text-center text-grey">Sin resultados</q-card-section>
        </q-card>
      </div>
    </div>

    <q-inner-loading :showing="loading"><q-spinner size="50px"/></q-inner-loading>

    <!-- DIALOG NUEVO/EDITAR -->
    <q-dialog v-model="dlg" persistent>
      <q-card style="width: 680px; max-width: 95vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1">
            {{ form.id ? 'Editar producto' : 'Nuevo producto' }}
          </div>
          <q-space/>
          <q-btn flat round dense icon="close" @click="dlg=false"/>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-form @submit.prevent="save">
            <div class="row q-col-gutter-sm">
              <div class="col-12 col-md-8">
                <q-input v-model="form.name" label="Nombre" dense outlined :rules="[v=>!!v || 'Requerido']"/>
              </div>
              <div class="col-12 col-md-4">
                <q-select
                  v-model="form.categoria" dense outlined label="Categoría"
                  :options="categories.map(c=>c.id)" emit-value map-options
                  :rules="[v=>!!v || 'Requerido']"
                />
              </div>

              <div class="col-6">
                <q-input v-model.number="form.price" type="number" step="0.01" label="Precio (Bs)" dense outlined :rules="[v=>v>=0 || '≥ 0']"/>
              </div>
              <div class="col-6">
                <q-input v-model="form.unit" label="Unidad (ej: UND, KG, LT)" dense outlined/>
              </div>

              <div class="col-12">
                <q-input v-model="form.description" type="textarea" autogrow label="Descripción" dense outlined/>
              </div>

              <div class="col-12">
                <q-file v-model="file" label="Imagen" dense outlined accept=".png,.jpg,.jpeg,.webp" counter>
                  <template #prepend><q-icon name="image"/></template>
                </q-file>
                <div v-if="form.image_url" class="q-mt-sm">
                  <img :src="form.image_url" alt="preview" style="max-width: 160px; border-radius:8px;">
                </div>
              </div>

              <div class="col-6">
                <q-toggle v-model="form.active" label="Activo"/>
              </div>
              <div class="col-6">
                <q-input v-model.number="form.ord" type="number" label="Orden" dense outlined/>
              </div>
            </div>

            <div class="text-right q-mt-md">
              <q-btn flat color="negative" label="Cancelar" no-caps @click="dlg=false"/>
              <q-btn color="primary" label="Guardar" no-caps class="q-ml-sm" type="submit" :loading="saving"/>
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
    <!-- DIALOG INSUMOS DEL PRODUCTO -->
    <q-dialog v-model="dlgInsumos" persistent>
      <q-card style="width: 720px; max-width: 95vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1">
            Insumos de: {{ manageProduct?.name || '' }}
          </div>
          <q-space/>
          <q-btn flat round dense icon="close" @click="dlgInsumos = false" />
        </q-card-section>

        <q-card-section class="q-pt-none">
          <!-- lista actual -->
          <q-table
            title="Insumos"
            :rows="insumosProducto"
            :columns="[
              { name:'insumo', label:'Insumo', field: row => row.insumo?.nombre || '', align:'left' },
              { name:'unidad', label:'Unidad', field: row => row.insumo?.unidad || '', align:'left' },
              { name:'cantidad', label:'Cantidad', field:'cantidad', align:'right' },
              { name:'actions', label:'', field:'id', align:'right' }
            ]"
            row-key="id"
            dense flat bordered
            :rows-per-page-options='[0]'
          >
            <template #body-cell-cantidad="props">
              <q-td :props="props">
                <q-input
                  v-model.number="props.row.cantidad"
                  type="number"
                  dense outlined
                  style="max-width:90px"
                  @blur="updateInsumoProducto(props.row)"
                />
              </q-td>
            </template>

            <template #body-cell-actions="props">
              <q-td :props="props">
                <q-btn
                  dense round flat
                  icon="delete"
                  color="negative"
                  @click="deleteInsumoProducto(props.row)"
                />
              </q-td>
            </template>
          </q-table>

          <q-inner-loading :showing="insumosLoading">
            <q-spinner size="40px" />
          </q-inner-loading>

          <!-- agregar nuevo -->
          <div class="q-mt-md">
            <div class="text-subtitle2 q-mb-xs">Agregar insumo</div>
            <div class="row q-col-gutter-sm items-center">
              <div class="col-12 col-md-6">
                <q-select
                  v-model="nuevoInsumo.insumo_id"
                  :options="insumosOptions"
                  label="Insumo"
                  dense outlined
                  emit-value map-options
                />
              </div>
              <div class="col-6 col-md-3">
                <q-input
                  v-model.number="nuevoInsumo.cantidad"
                  type="number"
                  dense outlined
                  label="Cantidad"
                />
              </div>
              <div class="col-6 col-md-3">
                <q-btn
                  color="primary"
                  label="Agregar"
                  no-caps
                  class="full-width"
                  :loading="insumosSaving"
                  @click="addInsumoProducto"
                />
              </div>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script>
export default {
  name: 'ProductsGrid',
  data () {
    return {
      loading: false,
      search: '',
      categoryId: 'all',
      // categorías HARD-CODE
      categories: [
        { id: 'Refrescos y Bebidas', name: 'Refrescos y Bebidas', icon: 'local_drink',  color: 'blue'   },
        { id: 'Acompañamientos',     name: 'Acompañamientos',     icon: 'restaurant',    color: 'teal'   },
        { id: 'Pollos',              name: 'Pollos',              icon: 'lunch_dining',  color: 'brown'  },
        // agrega las demás si quieres…
      ],
      products: [],

      // dialog/form
      dlg: false,
      form: {
        id: null,
        name: '',
        categoria: null,
        description: '',
        price: 0,
        unit: 'UND',
        active: true,
        ord: 0,
        image_url: null
      },
      file: null,
      saving: false,
      dlgInsumos: false,
      manageProduct: null,
      insumosProducto: [],   // relaciones insumo_producto
      insumosOptions: [],    // lista de insumos para el select
      nuevoInsumo: {
        insumo_id: null,
        cantidad: 1
      },
      insumosLoading: false,
      insumosSaving: false,
    }
  },
  computed: {
    filtered () {
      const q = (this.search || '').toLowerCase()
      return this.products.filter(p => {
        const byCat  = this.categoryId === 'all' || p.categoria === this.categoryId
        const byText = !q || (String(p.name || '').toLowerCase().includes(q) ||
          String(p.description || '').toLowerCase().includes(q))
        return byCat && byText
      })
    }
  },
  mounted () { this.fetchProducts() },
  methods: {
    openInsumos (p) {
      this.manageProduct = p
      this.dlgInsumos = true
      this.nuevoInsumo = { insumo_id: null, cantidad: 1 }
      this.fetchInsumosOptions()
      this.fetchInsumosProducto()
    },

    async fetchInsumosOptions () {
      // usa tu endpoint /insumos que ya existe
      try {
        const { data } = await this.$axios.get('insumos')
        this.insumosOptions = (data || []).map(i => ({
          label: `${i.nombre} (${i.unidad || ''})`,
          value: i.id
        }))
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'No se pudieron cargar los insumos' })
      }
    },

    async fetchInsumosProducto () {
      if (!this.manageProduct) return
      this.insumosLoading = true
      try {
        // asumo endpoint: GET /productos/{id}/insumos
        const { data } = await this.$axios.get(`productos/${this.manageProduct.id}/insumos`)
        this.insumosProducto = Array.isArray(data) ? data : []
      } catch (e) {
        this.insumosProducto = []
      } finally {
        this.insumosLoading = false
      }
    },

    async addInsumoProducto () {
      if (!this.manageProduct || !this.nuevoInsumo.insumo_id) return
      this.insumosSaving = true
      try {
        // POST /productos/{id}/insumos
        const { data } = await this.$axios.post(
          `productos/${this.manageProduct.id}/insumos`,
          this.nuevoInsumo
        )
        this.insumosProducto.push(data)
        this.nuevoInsumo = { insumo_id: null, cantidad: 1 }
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'No se pudo agregar el insumo' })
      } finally {
        this.insumosSaving = false
      }
    },

    async updateInsumoProducto (row) {
      if (!this.manageProduct || !row.id) return
      // PUT /productos/{id}/insumos/{insumoProducto}
      try {
        await this.$axios.put(
          `productos/${this.manageProduct.id}/insumos/${row.id}`,
          { cantidad: row.cantidad }
        )
        this.$q.notify({ type: 'positive', message: 'Cantidad actualizada' })
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'No se pudo actualizar' })
      }
    },

    async deleteInsumoProducto (row) {
      if (!this.manageProduct || !row.id) return
      this.$q.dialog({
        title: 'Eliminar',
        message: '¿Quitar este insumo del producto?',
        cancel: true,
        persistent: true
      }).onOk(async () => {
        try {
          await this.$axios.delete(
            `productos/${this.manageProduct.id}/insumos/${row.id}`
          )
          this.insumosProducto = this.insumosProducto.filter(r => r.id !== row.id)
          this.$q.notify({ type: 'positive', message: 'Insumo eliminado' })
        } catch (e) {
          this.$q.notify({ type: 'negative', message: 'No se pudo eliminar' })
        }
      })
    },
    toMoney (v) { return Number(v || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) },
    selectCategory (id) { this.categoryId = id; this.fetchProducts() },

    async fetchProducts () {
      this.loading = true
      try {
        const params = {}
        if (this.categoryId !== 'all') params.categoria = this.categoryId
        if (this.search) params.q = this.search
        const { data } = await this.$axios.get('productos', { params })
        this.products = Array.isArray(data) ? data : []
      } finally { this.loading = false }
    },

    // NUEVO
    openNew () {
      this.form = { id:null, name:'', categoria:null, description:'', price:0, unit:'UND', active:true, ord:0, image_url:null }
      this.file = null
      this.dlg = true
    },

    // EDITAR
    openEdit (p) {
      this.form = { ...p, id: p.id, image_url: p.image_url }
      this.file = null
      this.dlg = true
    },

    async save () {
      this.saving = true
      try {
        const fd = new FormData()
        fd.append('name',        this.form.name)
        fd.append('categoria',   this.form.categoria || '')
        fd.append('description', this.form.description || '')
        fd.append('price',       this.form.price ?? 0)
        fd.append('unit',        this.form.unit || 'UND')
        fd.append('active',      this.form.active ? 1 : 0)
        fd.append('ord',         this.form.ord ?? 0)
        if (this.file) fd.append('image', this.file)

        if (this.form.id) {
          // PUT con multipart: algunos back requieren method spoof
          fd.append('_method', 'PUT')
          await this.$axios.post(`productos/${this.form.id}`, fd, {
            headers: { 'Content-Type': 'multipart/form-data' }
          })
          this.$q.notify({ type:'positive', message:'Producto actualizado' })
        } else {
          await this.$axios.post('productos', fd, {
            headers: { 'Content-Type': 'multipart/form-data' }
          })
          this.$q.notify({ type:'positive', message:'Producto creado' })
        }

        this.dlg = false
        this.fetchProducts()
      } catch (e) {
        this.$q.notify({ type:'negative', message: e.response?.data?.message || 'No se pudo guardar' })
      } finally {
        this.saving = false
      }
    },

    emitAdd (p) {
      this.$emit('add', p)
      this.$q.notify({ type: 'positive', message: `${p.name} agregado` })
    }
  },
  watch: { search () { this.fetchProducts() } }
}
</script>

<style scoped>
.bg-gradient{ background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,.85) 100%); }
.hoverable:hover{ transform: translateY(-2px); transition: .15s; }
.ellipsis-2-lines{ display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
</style>
