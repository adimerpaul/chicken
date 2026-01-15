<template>
  <q-page class="q-pa-md bg-grey-3">
    <!-- TOP BAR -->
    <div class="row items-center q-gutter-sm q-mb-sm">
      <q-btn flat round dense icon="menu" class="lt-md"/>

      <div class="text-h6 text-weight-bold">Productos</div>
      <q-space/>

      <q-btn
        flat round dense icon="refresh"
        @click="fetchProducts"
        title="Actualizar lista de productos"
        class="q-mr-sm"
      />

      <q-btn
        color="positive"
        icon="add_circle"
        label="Nuevo"
        no-caps
        @click="openNew"
        class="q-mr-sm"
      />

      <q-input
        v-model="search"
        dense outlined
        debounce="250"
        placeholder="Buscar producto..."
        style="width: 260px;"
        clearable
      >
        <template #prepend><q-icon name="search"/></template>
      </q-input>
    </div>

    <!-- CATEGORÍAS -->
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

    <!-- GRID -->
    <div class="row q-col-gutter-md">
      <div
        v-for="p in filtered"
        :key="p.id"
        class="col-12 col-sm-6 col-md-4 col-lg-3"
      >
        <q-card class="overflow-hidden hoverable">

          <!-- IMAGE -->
          <div class="relative-position" style="height:180px;background:#111;">
            <img
              v-if="p.image"
              :src="productImage(p)"
              alt="img"
              style="width:100%;height:100%;object-fit:cover"
              loading="lazy"
            >
            <div v-else class="full-width full-height flex flex-center text-white">
              <q-icon name="image" size="56px"/>
            </div>

            <!-- PRICE TAG -->
            <div class="absolute-bottom-right q-ma-sm bg-red text-white price-tag">
              <div class="text-right">
                <div class="text-caption">Venta</div>
                <div class="text-subtitle2">{{ toMoney(p.price) }} Bs</div>
                <div class="text-caption">
                  Costo: {{ toMoney(p.costo_insumos || 0) }} Bs
                </div>
              </div>
            </div>

            <div class="absolute-bottom bg-gradient text-white q-pa-sm">
              <div class="text-subtitle2 text-weight-bold ellipsis-2-lines">
                {{ p.name }}
              </div>
            </div>
          </div>

          <q-card-section class="q-py-xs">
            <div class="row items-center">
              <q-badge outline color="grey-7" class="q-mr-sm">{{ p.unit || 'UND' }}</q-badge>
              <div class="text-caption text-grey">{{ p.categoria || '—' }}</div>
              <q-space/>
              <q-btn dense round flat icon="edit" @click.stop="openEdit(p)" />
              <q-btn
                dense round flat
                icon="inventory_2"
                class="q-ml-xs"
                @click.stop="openInsumos(p)"
                title="Insumos del producto"
              />
            </div>

            <!-- COSTO / UTILIDAD -->
            <div class="row items-center q-mt-xs">
              <div>
                <div class="text-caption text-grey-7">Costo aprox.</div>
                <div class="text-caption">
                  {{ toMoney(p.costo_insumos || 0) }} Bs
                </div>
              </div>
              <q-space/>
              <div class="text-right">
                <div class="text-caption text-grey-7">Utilidad aprox.</div>
                <div
                  class="text-caption"
                  :class="utilidad(p) >= 0 ? 'text-positive' : 'text-negative'"
                >
                  {{ toMoney(utilidad(p)) }} Bs
                </div>
              </div>
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

    <q-inner-loading :showing="loading">
      <q-spinner size="50px"/>
    </q-inner-loading>

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
                <q-input
                  v-model="form.name"
                  label="Nombre"
                  dense outlined
                  :rules="[v=>!!v || 'Requerido']"
                />
              </div>

              <div class="col-12 col-md-4">
                <q-select
                  v-model="form.categoria"
                  dense outlined
                  label="Categoría"
                  :options="categories.map(c=>c.id)"
                  :rules="[v=>!!v || 'Requerido']"
                />
              </div>

              <div class="col-6">
                <q-input
                  v-model.number="form.price"
                  type="number"
                  step="0.01"
                  label="Precio (Bs)"
                  dense outlined
                  :rules="[v => toNum(v) >= 0 || '≥ 0']"
                />
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

    <!-- DIALOG INSUMOS (QMarkupTable) -->
    <q-dialog v-model="dlgInsumos" persistent>
      <q-card style="width: 900px; max-width: 95vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-subtitle1">
            Insumos de: <b>{{ manageProduct?.name || '' }}</b>
          </div>
          <q-space/>

          <!-- Costo real segun dialog (para comparar con costo_insumos backend) -->
          <q-chip
            dense
            color="primary"
            text-color="white"
            icon="summarize"
            class="q-mr-sm"
          >
            Subtotal: {{ toMoney(subtotalInsumos) }} Bs
          </q-chip>

          <q-btn flat round dense icon="close" @click="dlgInsumos = false"/>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <!-- TABLE -->
          <q-markup-table dense flat bordered separator="horizontal">
            <thead>
            <tr>
              <th class="text-left">Insumo</th>
              <th class="text-left" style="width: 90px;">Unidad</th>
              <th class="text-right" style="width: 140px;">Cantidad</th>
              <th class="text-right" style="width: 120px;">Costo</th>
              <th class="text-right" style="width: 140px;">Total</th>
              <th class="text-right" style="width: 70px;"></th>
            </tr>
            </thead>

            <tbody>
            <tr v-for="row in insumosProducto" :key="row.id">
              <!-- INSUMO + TAGS -->
              <td class="text-left">
                <div class="text-weight-medium">
                  {{ row.insumo?.nombre || '' }}
                </div>

                <div class="row q-gutter-xs q-mt-xs">
                  <q-chip
                    v-if="Number(row.insumo?.no_contar) === 1"
                    dense color="grey-8" text-color="white"
                    icon="do_not_disturb_on"
                  >
                    No contar
                  </q-chip>

                  <q-chip
                    v-if="Number(row.insumo?.es_mesa) === 1"
                    dense color="indigo" text-color="white"
                    icon="table_restaurant"
                  >
                    MESA
                  </q-chip>

                  <q-chip
                    v-if="Number(row.insumo?.es_llevar) === 1"
                    dense color="deep-orange" text-color="white"
                    icon="takeout_dining"
                  >
                    LLEVAR
                  </q-chip>
                </div>
              </td>

              <td class="text-left">
                <q-badge outline color="grey-7">
                  {{ row.insumo?.unidad || '—' }}
                </q-badge>
              </td>

              <td class="text-right">
                <q-input
                  v-model.number="row.cantidad"
                  type="number"
                  step="0.01"
                  dense outlined
                  style="max-width: 120px; margin-left:auto;"
                  @blur="updateInsumoProducto(row)"
                />
              </td>

              <td class="text-right">
                {{ toMoney(row.insumo?.costo || 0) }}
              </td>

              <td class="text-right text-weight-bold">
                {{ toMoney(rowTotal(row)) }}
              </td>

              <td class="text-right">
                <q-btn
                  dense round flat
                  icon="delete"
                  color="negative"
                  @click="deleteInsumoProducto(row)"
                />
              </td>
            </tr>

            <tr v-if="!insumosLoading && insumosProducto.length === 0">
              <td colspan="6" class="text-center text-grey q-pa-md">
                Este producto no tiene insumos.
              </td>
            </tr>
            </tbody>

            <tfoot>
            <tr>
              <td colspan="4" class="text-right text-grey-7">
                SUBTOTAL
              </td>
              <td class="text-right text-weight-bold text-primary">
                {{ toMoney(subtotalInsumos) }} Bs
              </td>
              <td></td>
            </tr>
            </tfoot>
          </q-markup-table>

          <q-inner-loading :showing="insumosLoading">
            <q-spinner size="40px"/>
          </q-inner-loading>

          <!-- ADD INSUMO -->
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
                  use-input
                  fill-input
                  input-debounce="200"
                  behavior="menu"
                >
                  <template #option="scope">
                    <q-item v-bind="scope.itemProps">
                      <q-item-section>
                        <q-item-label class="text-weight-medium">
                          {{ scope.opt.label }}
                        </q-item-label>

                        <div class="row q-gutter-xs q-mt-xs">
                          <q-chip v-if="Number(scope.opt.no_contar)===1" dense color="grey-8" text-color="white">No contar</q-chip>
                          <q-chip v-if="Number(scope.opt.es_mesa)===1" dense color="indigo" text-color="white">MESA</q-chip>
                          <q-chip v-if="Number(scope.opt.es_llevar)===1" dense color="deep-orange" text-color="white">LLEVAR</q-chip>
                        </div>
                      </q-item-section>

                      <q-item-section side top class="text-right">
                        <div class="text-caption text-grey-7">Costo</div>
                        <div class="text-weight-bold">{{ toMoney(scope.opt.costo || 0) }}</div>
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>
              </div>

              <div class="col-6 col-md-3">
                <q-input
                  v-model.number="nuevoInsumo.cantidad"
                  type="number"
                  dense outlined
                  step="0.01"
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

          <!-- AYUDA -->
          <div class="text-caption text-grey-7 q-mt-sm">
            * Si tu costo te sale gigante (ej 2.460 Bs), revisa: <b>insumo.costo</b> (que sea costo por unidad) y
            <b>cantidad</b> (que sea la porción real: 0.20, 0.05, etc.).
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

      categories: [
        { id: 'Refrescos y Bebidas', name: 'Refrescos y Bebidas', icon: 'local_drink', color: 'blue' },
        { id: 'Acompañamientos', name: 'Acompañamientos', icon: 'restaurant', color: 'teal' },
        { id: 'Pollos', name: 'Pollos', icon: 'lunch_dining', color: 'brown' },
      ],

      products: [],

      // dialog/producto
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

      // dialog insumos
      dlgInsumos: false,
      manageProduct: null,
      insumosProducto: [],
      insumosOptions: [],
      nuevoInsumo: { insumo_id: null, cantidad: 1 },
      insumosLoading: false,
      insumosSaving: false
    }
  },

  computed: {
    filtered () {
      const q = (this.search || '').toLowerCase().trim()
      return (this.products || []).filter(p => {
        const byCat = this.categoryId === 'all' || p.categoria === this.categoryId
        const byText = !q || (
          String(p.name || '').toLowerCase().includes(q) ||
          String(p.description || '').toLowerCase().includes(q)
        )
        return byCat && byText
      })
    },

    subtotalInsumos () {
      return (this.insumosProducto || []).reduce((acc, r) => {
        const qty = this.toNum(r.cantidad)
        const costo = this.toNum(r.insumo?.costo)
        return acc + (qty * costo)
      }, 0)
    }
  },

  mounted () {
    this.fetchProducts()
  },

  methods: {
    // ----- helpers -----
    toNum (v) {
      // soporta "2,460.39" o "2460.39" o null
      if (v === null || v === undefined) return 0
      if (typeof v === 'number') return isFinite(v) ? v : 0
      const s = String(v).trim()
        .replace(/\s/g, '')
        .replace(/,/g, '') // quita separador miles tipo "2,460.39"
      const n = Number(s)
      return isFinite(n) ? n : 0
    },

    toMoney (v) {
      const n = this.toNum(v)
      return n.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    },

    utilidad (p) {
      return this.toNum(p?.price) - this.toNum(p?.costo_insumos)
    },

    rowTotal (row) {
      const qty = this.toNum(row?.cantidad)
      const costo = this.toNum(row?.insumo?.costo)
      return qty * costo
    },

    productImage (p) {
      // ajusta según tu backend
      return `${this.$url}/../storage/${p.image}`
    },

    // ----- category -----
    selectCategory (id) {
      this.categoryId = id
      // no re-fetch necesario si ya trajiste todo, pero si quieres server-side, descomenta:
      // this.fetchProducts()
    },

    // ----- products -----
    async fetchProducts () {
      this.loading = true
      try {
        // Mejor: trae todos y filtra local (sin pegarle al server en cada tecla)
        const { data } = await this.$axios.get('productos')
        this.products = Array.isArray(data) ? data : []
      } catch (e) {
        this.products = []
        this.$q.notify({ type: 'negative', message: 'No se pudieron cargar los productos' })
      } finally {
        this.loading = false
      }
    },

    openNew () {
      this.form = {
        id: null,
        name: '',
        categoria: null,
        description: '',
        price: 0,
        unit: 'UND',
        active: true,
        ord: 0,
        image_url: null
      }
      this.file = null
      this.dlg = true
    },

    openEdit (p) {
      this.form = {
        id: p.id,
        name: p.name,
        categoria: p.categoria,
        description: p.description,
        price: this.toNum(p.price),
        unit: p.unit || 'UND',
        active: !!p.active,
        ord: this.toNum(p.ord),
        image_url: p.image ? this.productImage(p) : null
      }
      this.file = null
      this.dlg = true
    },

    async save () {
      this.saving = true
      try {
        const fd = new FormData()
        fd.append('name', this.form.name)
        fd.append('categoria', this.form.categoria || '')
        fd.append('description', this.form.description || '')
        fd.append('price', this.toNum(this.form.price))
        fd.append('unit', this.form.unit || 'UND')
        fd.append('active', this.form.active ? 1 : 0)
        fd.append('ord', this.toNum(this.form.ord))
        if (this.file) fd.append('image', this.file)

        if (this.form.id) {
          fd.append('_method', 'PUT')
          await this.$axios.post(`productos/${this.form.id}`, fd, {
            headers: { 'Content-Type': 'multipart/form-data' }
          })
          this.$q.notify({ type: 'positive', message: 'Producto actualizado' })
        } else {
          await this.$axios.post('productos', fd, {
            headers: { 'Content-Type': 'multipart/form-data' }
          })
          this.$q.notify({ type: 'positive', message: 'Producto creado' })
        }

        this.dlg = false
        this.fetchProducts()
      } catch (e) {
        this.$q.notify({ type: 'negative', message: e.response?.data?.message || 'No se pudo guardar' })
      } finally {
        this.saving = false
      }
    },

    // ----- insumos dialog -----
    openInsumos (p) {
      this.manageProduct = p
      this.dlgInsumos = true
      this.nuevoInsumo = { insumo_id: null, cantidad: 1 }
      this.fetchInsumosOptions()
      this.fetchInsumosProducto()
    },

    async fetchInsumosOptions () {
      try {
        const { data } = await this.$axios.get('insumos')
        this.insumosOptions = (data || []).map(i => ({
          label: `${i.nombre} (${i.unidad || ''})`,
          value: i.id,
          costo: this.toNum(i.costo),
          no_contar: this.toNum(i.no_contar),
          es_mesa: this.toNum(i.es_mesa),
          es_llevar: this.toNum(i.es_llevar),
        }))
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'No se pudieron cargar los insumos' })
      }
    },

    async fetchInsumosProducto () {
      if (!this.manageProduct) return
      this.insumosLoading = true
      try {
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
        const payload = {
          insumo_id: this.nuevoInsumo.insumo_id,
          cantidad: this.toNum(this.nuevoInsumo.cantidad)
        }
        await this.$axios.post(`productos/${this.manageProduct.id}/insumos`, payload)

        // refresca para traer relación con insumo completa
        await this.fetchInsumosProducto()

        this.nuevoInsumo = { insumo_id: null, cantidad: 1 }
        this.$q.notify({ type: 'positive', message: 'Insumo agregado' })
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'No se pudo agregar el insumo' })
      } finally {
        this.insumosSaving = false
      }
    },

    async updateInsumoProducto (row) {
      if (!this.manageProduct || !row?.id) return
      try {
        await this.$axios.put(
          `productos/${this.manageProduct.id}/insumos/${row.id}`,
          { cantidad: this.toNum(row.cantidad) }
        )
        // no spamear notificaciones por cada blur, pero si quieres:
        // this.$q.notify({ type: 'positive', message: 'Cantidad actualizada' })
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'No se pudo actualizar' })
      }
    },

    async deleteInsumoProducto (row) {
      if (!this.manageProduct || !row?.id) return
      this.$q.dialog({
        title: 'Eliminar',
        message: '¿Quitar este insumo del producto?',
        cancel: true,
        persistent: true
      }).onOk(async () => {
        try {
          await this.$axios.delete(`productos/${this.manageProduct.id}/insumos/${row.id}`)
          this.insumosProducto = this.insumosProducto.filter(r => r.id !== row.id)
          this.$q.notify({ type: 'positive', message: 'Insumo eliminado' })
        } catch (e) {
          this.$q.notify({ type: 'negative', message: 'No se pudo eliminar' })
        }
      })
    }
  }
}
</script>

<style scoped>
.bg-gradient {
  background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,.85) 100%);
}
.hoverable:hover {
  transform: translateY(-2px);
  transition: .15s;
}
.ellipsis-2-lines {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.price-tag{
  padding:4px 10px;
  border-radius:12px;
  font-weight:700;
  min-width:120px;
}
</style>
