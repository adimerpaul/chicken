<template>
  <q-page class="q-pa-md">
    <q-table
      :rows="rows"
      :columns="columns"
      dense
      wrap-cells
      flat
      bordered
      :rows-per-page-options="[0]"
      title="Insumos"
      :filter="filter"
    >
      <template v-slot:top-right>
        <q-btn
          color="positive"
          label="Nuevo"
          @click="onNew"
          no-caps
          icon="add_circle_outline"
          :loading="loading"
          class="q-mr-sm"
        />
        <q-btn
          color="primary"
          label="Actualizar"
          @click="fetchRows"
          no-caps
          icon="refresh"
          :loading="loading"
        />
        <q-input v-model="filter" label="Buscar" dense outlined class="q-ml-sm">
          <template v-slot:append><q-icon name="search"/></template>
        </q-input>
      </template>

      <template v-slot:body-cell-actions="props">
        <q-td :props="props">
          <q-btn-dropdown label="Opciones" no-caps size="10px" dense color="primary">
            <q-list>
              <q-item clickable @click="onEdit(props.row)" v-close-popup>
                <q-item-section avatar><q-icon name="edit"/></q-item-section>
                <q-item-section><q-item-label>Editar</q-item-label></q-item-section>
              </q-item>
              <q-item clickable @click="onDelete(props.row.id)" v-close-popup>
                <q-item-section avatar><q-icon name="delete"/></q-item-section>
                <q-item-section><q-item-label>Eliminar</q-item-label></q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </q-td>
      </template>

      <template v-slot:body-cell-costo="props">
        <q-td :props="props">
          {{ formatMoney(props.row.costo) }}
        </q-td>
      </template>

      <template v-slot:body-cell-stock="props">
        <q-td :props="props">
          <q-badge :color="badgeColor(props.row)" align="middle">
            {{ props.row.stock }} {{ props.row.unidad }}
          </q-badge>
        </q-td>
      </template>
    </q-table>

    <!-- Dialogo Crear/Editar -->
    <q-dialog v-model="dlg" persistent>
      <q-card style="width: 520px; max-width: 90vw">
        <q-card-section class="q-pb-none row items-center">
          <div>{{ item.id ? 'Editar' : 'Nuevo' }} insumo</div>
          <q-space/>
          <q-btn icon="close" flat round dense @click="dlg = false"/>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-form @submit.prevent="submit">
            <div class="row q-col-gutter-sm">
              <div class="col-12 col-sm-8">
                <q-input v-model="item.nombre" label="Nombre" dense outlined />
              </div>
              <div class="col-12 col-sm-4">
                <q-select v-model="item.unidad" label="Unidad" dense outlined
                          :options="unidades" emit-value map-options />
              </div>
              <div class="col-6">
                <q-input v-model.number="item.stock" type="number" step="0.01" label="Stock" dense outlined />
              </div>
              <div class="col-6">
                <q-input v-model.number="item.costo" type="number" step="0.01" label="Costo (Bs)" dense outlined />
              </div>
              <div class="col-6">
                <q-input v-model.number="item.min_stock" type="number" step="0.01" label="Stock mínimo" dense outlined />
              </div>
              <div class="col-12">
                <q-input v-model="item.descripcion" type="textarea" autogrow label="Descripción" dense outlined />
              </div>
            </div>

            <div class="text-right q-mt-md">
              <q-btn color="negative" label="Cancelar" no-caps @click="dlg=false" :loading="loading"/>
              <q-btn color="primary" label="Guardar" no-caps class="q-ml-sm" type="submit" :loading="loading"/>
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'InsumosPage',
  data () {
    return {
      rows: [],
      loading: false,
      filter: '',
      dlg: false,
      item: {},
      unidades: [
        { label: 'UND', value: 'UND' },
        { label: 'KG',  value: 'KG'  },
        { label: 'LT',  value: 'LT'  },
        { label: 'PAQ', value: 'PAQ' },
      ],
      columns: [
        { name: 'actions', label: 'Acciones', align: 'center' },
        { name: 'nombre',  label: 'Nombre',   align: 'left',  field: 'nombre', sortable: true },
        { name: 'unidad',  label: 'Unidad',   align: 'left',  field: 'unidad', sortable: true },
        { name: 'stock',   label: 'Stock',    align: 'left',  field: 'stock',  sortable: true },
        { name: 'costo',   label: 'Costo (Bs)', align: 'left', field: 'costo', sortable: true },
        { name: 'min_stock', label: 'Mínimo', align: 'left', field: 'min_stock' },
        { name: 'descripcion', label: 'Descripción', align: 'left', field: 'descripcion' },
      ]
    }
  },
  mounted () {
    this.fetchRows()
  },
  methods: {
    formatMoney (v) {
      v = Number(v || 0)
      return v.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    },
    badgeColor (row) {
      if (row.min_stock && Number(row.stock) <= Number(row.min_stock)) return 'negative'
      return 'primary'
    },
    async fetchRows () {
      this.loading = true
      try {
        const res = await this.$axios.get('insumos')
        this.rows = res.data
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo cargar')
      } finally {
        this.loading = false
      }
    },
    onNew () {
      this.item = {
        nombre: '',
        unidad: 'UND',
        stock: 0,
        costo: 0,
        min_stock: null,
        descripcion: ''
      }
      this.dlg = true
    },
    onEdit (row) {
      this.item = { ...row }
      this.dlg = true
    },
    async onDelete (id) {
      this.$alert.dialog('¿Eliminar el insumo?')
        .onOk(async () => {
          this.loading = true
          try {
            await this.$axios.delete(`insumos/${id}`)
            this.$alert?.success?.('Eliminado')
            this.fetchRows()
          } catch (e) {
            this.$alert?.error?.(e.response?.data?.message || 'Error al eliminar')
          } finally {
            this.loading = false
          }
        })
    },
    async submit () {
      this.loading = true
      try {
        if (this.item.id) {
          await this.$axios.put(`insumos/${this.item.id}`, this.item)
          this.$alert?.success?.('Actualizado')
        } else {
          await this.$axios.post('insumos', this.item)
          this.$alert?.success?.('Creado')
        }
        this.dlg = false
        this.fetchRows()
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo guardar')
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
