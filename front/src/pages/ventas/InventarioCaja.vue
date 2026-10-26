<template>
  <q-page class="bg-grey-3 q-pa-sm">
    <q-card flat bordered>
      <q-card-section class="q-pa-sm">
        <div class="row items-center q-col-gutter-sm">
          <div class="col-12 col-sm-3">
            <q-input v-model="date" type="date" dense outlined label="Fecha" :readonly="!esAdmin" @update:model-value="fetch" />
          </div>
          <div v-if="esAdmin" class="col-12 col-sm-3">
            <q-select
              v-model="userId" :options="users" option-value="id" option-label="name"
              emit-value map-options dense outlined label="Vendedor" @update:model-value="fetch"
            />
          </div>
          <q-space />
          <div class="col-auto">
            <q-btn flat color="primary" icon="refresh" :loading="loading" @click="fetch" />
            <q-btn color="indigo" icon="print" label="Imprimir" no-caps class="q-ml-sm" @click="imprimir" />
          </div>
        </div>
      </q-card-section>

      <q-banner v-if="!inv.registrado" dense class="bg-orange-1 text-orange-10 q-mx-sm">
        <template #avatar><q-icon name="info" /></template>
        Cuente los refrescos que tiene al abrir su caja y presione <b>Guardar inventario inicial</b>.
        Las ventas se descuentan solas.
      </q-banner>

      <q-card-section class="q-pa-sm">
        <q-markup-table flat bordered dense separator="cell">
          <thead>
            <tr class="bg-grey-2">
              <th class="text-left">Producto</th>
              <th style="width:110px">Inicial</th>
              <th style="width:110px">Aumentos</th>
              <th style="width:80px">Vendido</th>
              <th style="width:80px">Queda</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in inv.items" :key="r.producto_id">
              <td class="text-left text-weight-medium">{{ r.name }}</td>
              <td>
                <q-input v-model.number="iniciales[r.producto_id]" type="number" min="0" dense outlined input-class="text-center" />
              </td>
              <td class="text-center">
                <span class="text-weight-bold q-mr-xs">{{ n(r.aumento) }}</span>
                <q-btn dense round flat size="sm" color="green" icon="add_circle" @click="abrirAumento(r)">
                  <q-tooltip>Registrar aumento</q-tooltip>
                </q-btn>
                <q-btn v-if="r.aumentos.length" dense round flat size="sm" color="grey-7" icon="list">
                  <q-menu>
                    <q-list dense style="min-width:220px">
                      <q-item v-for="a in r.aumentos" :key="a.id">
                        <q-item-section>
                          <q-item-label>+{{ n(a.cantidad) }}</q-item-label>
                          <q-item-label caption>{{ $filters.dateDmYHis(a.created_at) }} {{ a.comment || '' }}</q-item-label>
                        </q-item-section>
                        <q-item-section side>
                          <q-btn dense flat round size="sm" color="red" icon="delete" @click="eliminarAumento(a)" />
                        </q-item-section>
                      </q-item>
                    </q-list>
                  </q-menu>
                </q-btn>
              </td>
              <td class="text-center">{{ n(r.vendido) }}</td>
              <td class="text-center text-weight-bold" :class="r.queda < 0 ? 'text-red' : 'text-green-8'">{{ n(r.queda) }}</td>
            </tr>
            <tr v-if="!inv.items.length">
              <td colspan="5" class="text-center text-grey">No hay productos en la categoría Refrescos y Bebidas</td>
            </tr>
          </tbody>
        </q-markup-table>

        <div class="row q-mt-sm">
          <q-space />
          <q-btn color="primary" icon="save" no-caps :loading="loading" label="Guardar inventario inicial" @click="guardarInicial" />
        </div>
      </q-card-section>
    </q-card>

    <q-dialog v-model="dlgAumento">
      <q-card style="width:340px">
        <q-card-section class="text-subtitle1 text-weight-bold">Aumento: {{ aumento.name }}</q-card-section>
        <q-card-section class="q-gutter-sm">
          <q-input v-model.number="aumento.cantidad" type="number" dense outlined label="Cantidad" autofocus />
          <q-input v-model="aumento.comment" dense outlined label="Comentario (opcional)" />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cancelar" no-caps v-close-popup />
          <q-btn color="green" label="Registrar" no-caps :loading="loading" @click="guardarAumento" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import moment from 'moment'
import { Imprimir } from 'src/utils/ImprimirTicket'

export default {
  name: 'InventarioCajaPage',
  data () {
    return {
      loading: false,
      date: moment().format('YYYY-MM-DD'),
      userId: null,
      users: [],
      inv: { registrado: false, items: [] },
      iniciales: {},
      dlgAumento: false,
      aumento: { producto_id: null, name: '', cantidad: null, comment: '' }
    }
  },
  computed: {
    esAdmin () {
      return this.$store.user?.role === 'Administrador'
    },
    userName () {
      if (!this.esAdmin) return this.$store.user?.name || ''
      return this.users.find(u => u.id === this.userId)?.name || ''
    }
  },
  mounted () {
    this.userId = this.$store.user?.id || null
    if (this.esAdmin) {
      this.$axios.get('users').then(({ data }) => { this.users = data })
    }
    this.fetch()
  },
  methods: {
    n (v) {
      return Number(v || 0).toLocaleString('es-BO', { maximumFractionDigits: 2 })
    },
    params () {
      return { date: this.date, user_id: this.userId }
    },
    setInv (data) {
      this.inv = data
      const ini = {}
      data.items.forEach(r => { ini[r.producto_id] = r.inicial })
      this.iniciales = ini
    },
    async fetch () {
      this.loading = true
      try {
        const { data } = await this.$axios.get('inventario-caja', { params: this.params() })
        this.setInv(data)
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo cargar el inventario')
      } finally {
        this.loading = false
      }
    },
    async guardarInicial () {
      const items = this.inv.items.map(r => ({
        producto_id: r.producto_id,
        cantidad: Number(this.iniciales[r.producto_id] || 0)
      }))
      this.loading = true
      try {
        const { data } = await this.$axios.post('inventario-caja/inicial', { ...this.params(), items })
        this.setInv(data)
        this.$alert.success('Inventario inicial guardado')
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo guardar')
      } finally {
        this.loading = false
      }
    },
    abrirAumento (r) {
      this.aumento = { producto_id: r.producto_id, name: r.name, cantidad: null, comment: '' }
      this.dlgAumento = true
    },
    async guardarAumento () {
      if (!Number(this.aumento.cantidad)) {
        this.$alert.error('Ingrese la cantidad')
        return
      }
      this.loading = true
      try {
        const { data } = await this.$axios.post('inventario-caja/aumento', {
          ...this.params(),
          producto_id: this.aumento.producto_id,
          cantidad: this.aumento.cantidad,
          comment: this.aumento.comment
        })
        this.setInv(data)
        this.dlgAumento = false
        this.$alert.success('Aumento registrado')
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo registrar')
      } finally {
        this.loading = false
      }
    },
    eliminarAumento (a) {
      this.$q.dialog({
        title: 'Eliminar aumento',
        message: `¿Eliminar el aumento de ${this.n(a.cantidad)}?`,
        cancel: true
      }).onOk(async () => {
        try {
          const { data } = await this.$axios.delete(`inventario-caja/${a.id}`)
          this.setInv(data)
        } catch (e) {
          this.$alert.error(e.response?.data?.message || 'No se pudo eliminar')
        }
      })
    },
    imprimir () {
      Imprimir.inventarioCaja(this.inv, this.userName)
    }
  }
}
</script>
