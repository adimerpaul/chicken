<template>
  <q-page class="q-pa-md bg-grey-3">
    <q-card flat bordered class="q-pa-sm">
      <q-card-section class="row items-center q-pa-sm">
        <q-input v-model="filtro" label="Buscar" dense outlined clearable style="width: 260px">
          <template #append><q-icon name="search" /></template>
        </q-input>
        <q-space />
        <q-btn color="primary" icon="add" label="Nuevo producto" no-caps @click="nuevo" />
      </q-card-section>

      <q-card-section class="q-pt-none">
        <q-table
          :rows="rows"
          :columns="columns"
          :filter="filtro"
          row-key="id"
          flat bordered dense
          :rows-per-page-options="[0]"
          :loading="loading"
          hide-bottom
        >
          <template #body-cell-opciones="props">
            <q-td :props="props">
              <q-btn flat dense round size="sm" icon="edit" color="primary" @click="editar(props.row)" />
              <q-btn flat dense round size="sm" icon="delete" color="negative" @click="eliminar(props.row)" />
            </q-td>
          </template>
          <template #body-cell-activo="props">
            <q-td :props="props">
              <q-chip dense square :color="props.row.activo ? 'positive' : 'grey'" text-color="white">
                {{ props.row.activo ? 'Activo' : 'Inactivo' }}
              </q-chip>
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>

    <q-dialog v-model="dlg">
      <q-card style="width: 420px; max-width: 95vw">
        <q-card-section class="text-subtitle1 text-weight-bold">
          {{ form.id ? 'Editar producto' : 'Nuevo producto' }}
        </q-card-section>
        <q-form @submit="guardar">
          <q-card-section class="q-pt-none q-gutter-sm">
            <q-input v-model="form.nombre" label="Nombre (ej. POLLO BRASA LIMPIO S/C)" dense outlined
                     :rules="[v => !!v || 'Requerido']" />
            <q-select v-model="form.unidad" :options="['KGS', 'UND']" label="Unidad" dense outlined
                      hint="KGS: se cobra por peso neto · UND: se cobra por cantidad" />
            <q-input v-model.number="form.precio" type="number" step="0.0001" min="0" label="Precio referencial (Bs)" dense outlined />
            <q-toggle v-model="form.activo" label="Activo" />
          </q-card-section>
          <q-card-actions align="right">
            <q-btn flat no-caps label="Cancelar" v-close-popup />
            <q-btn type="submit" color="primary" no-caps icon="save" label="Guardar" :loading="saving" />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'DespachoProductosPage',
  data () {
    return {
      rows: [],
      filtro: '',
      loading: false,
      saving: false,
      dlg: false,
      form: {},
      columns: [
        { name: 'opciones', label: 'Opciones', field: 'opciones', align: 'left' },
        { name: 'nombre', label: 'Nombre', field: 'nombre', align: 'left', sortable: true },
        { name: 'unidad', label: 'Unidad', field: 'unidad', align: 'left' },
        { name: 'precio', label: 'Precio ref. (Bs)', field: 'precio', align: 'right', format: v => Number(v || 0).toFixed(2) },
        { name: 'activo', label: 'Estado', field: 'activo', align: 'left' }
      ]
    }
  },
  mounted () {
    this.fetch()
  },
  methods: {
    async fetch () {
      this.loading = true
      try {
        const res = await this.$axios.get('despacho-productos')
        this.rows = res.data
      } catch (e) {
        this.$alert.error('No se pudo cargar los productos')
      } finally {
        this.loading = false
      }
    },
    nuevo () {
      this.form = { id: null, nombre: '', unidad: 'KGS', precio: 0, activo: true }
      this.dlg = true
    },
    editar (row) {
      this.form = { ...row, precio: Number(row.precio) }
      this.dlg = true
    },
    async guardar () {
      this.saving = true
      try {
        if (this.form.id) {
          await this.$axios.put(`despacho-productos/${this.form.id}`, this.form)
        } else {
          await this.$axios.post('despacho-productos', this.form)
        }
        this.$alert.success('Producto guardado')
        this.dlg = false
        this.fetch()
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo guardar')
      } finally {
        this.saving = false
      }
    },
    eliminar (row) {
      this.$alert.dialog(`¿Eliminar "${row.nombre}"?`).onOk(async () => {
        try {
          await this.$axios.delete(`despacho-productos/${row.id}`)
          this.$alert.success('Producto eliminado')
          this.fetch()
        } catch (e) {
          this.$alert.error(e.response?.data?.message || 'No se pudo eliminar')
        }
      })
    }
  }
}
</script>
