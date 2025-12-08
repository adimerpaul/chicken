<template>
  <q-page class="q-pa-sm bg-grey-3">
    <!-- HEADER -->
    <q-card flat bordered class="q-mb-sm">
      <q-card-section class="row items-end q-col-gutter-sm">
        <div class="col-12 col-sm-3">
          <div class="text-h6 text-weight-bold">Resumen de Ventas</div>
          <div class="text-caption text-grey-7">
            Ventas, costos y ganancias por rango de fechas
          </div>
        </div>

        <div class="col-6 col-sm-3">
          <q-input
            v-model="filters.date_from"
            type="date"
            dense
            outlined
            label="Desde"
          />
        </div>
        <div class="col-6 col-sm-3">
          <q-input
            v-model="filters.date_to"
            type="date"
            dense
            outlined
            label="Hasta"
          />
        </div>

        <div class="col-12 col-sm-3 text-right">
          <q-btn
            color="primary"
            icon="search"
            label="Actualizar"
            no-caps
            :loading="loading"
            @click="fetchResumen"
            class="q-mr-sm"
          />
          <q-btn
            outline
            color="primary"
            icon="filter_alt_off"
            label="Hoy"
            no-caps
            @click="setToday"
          />
        </div>
      </q-card-section>
    </q-card>

    <!-- TARJETAS -->
    <div class="row q-col-gutter-sm">
      <div class="col-12 col-sm-3">
        <q-card flat bordered>
          <q-card-section class="q-pa-sm">
            <div class="text-caption text-grey-7">Vendido (INGRESO)</div>
            <div class="text-h6 text-bold">
              {{ money(resumen.ventas_brutas) }} Bs
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-3">
        <q-card flat bordered>
          <q-card-section class="q-pa-sm">
            <div class="text-caption text-grey-7">
              Costo insumos vendidos
            </div>
            <div class="text-h6 text-negative text-bold">
              {{ money(resumen.costo_insumos_vendidos) }} Bs
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-3">
        <q-card flat bordered>
          <q-card-section class="q-pa-sm">
            <div class="text-caption text-grey-7">
              Gastos / Egresos
            </div>
            <div class="text-h6 text-negative text-bold">
              {{ money(resumen.gastos) }} Bs
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-3">
        <q-card flat bordered>
          <q-card-section class="q-pa-sm">
            <div class="text-caption text-grey-7">Ganancia neta</div>
            <div class="text-h6 text-positive text-bold">
              {{ money(resumen.utilidad_neta) }} Bs
            </div>
            <div class="text-caption text-grey-7">
              (Utilidad bruta:
              <b>{{ money(resumen.utilidad_bruta) }} Bs</b>)
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- OPCIONAL: TEXTO RESUMEN -->
    <q-card flat bordered class="q-mt-sm">
      <q-card-section class="q-pa-sm">
        <div class="text-body2">
          Entre
          <b>{{ resumen.date_from || '-' }}</b> y
          <b>{{ resumen.date_to || '-' }}</b> vendiste
          <b>{{ money(resumen.ventas_brutas) }} Bs</b>,
          gastaste
          <b>{{ money(resumen.costo_insumos_vendidos + resumen.gastos) }} Bs</b>
          (insumos + egresos) y tu ganancia neta fue de
          <b>{{ money(resumen.utilidad_neta) }} Bs</b>.
        </div>
      </q-card-section>
    </q-card>

    <q-inner-loading :showing="loading">
      <q-spinner size="40px" />
    </q-inner-loading>
  </q-page>
</template>

<script>
import moment from 'moment'

export default {
  name: 'VentasResumen',
  data () {
    const today = moment().format('YYYY-MM-DD')
    return {
      loading: false,
      filters: {
        date_from: today,
        date_to: today
      },
      resumen: {
        date_from: null,
        date_to: null,
        ventas_brutas: 0,
        costo_insumos_vendidos: 0,
        gastos: 0,
        utilidad_bruta: 0,
        utilidad_neta: 0
      }
    }
  },
  mounted () {
    this.fetchResumen()
  },
  methods: {
    money (v) {
      return Number(v || 0).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    },
    setToday () {
      const today = moment().format('YYYY-MM-DD')
      this.filters.date_from = today
      this.filters.date_to = today
      this.fetchResumen()
    },
    async fetchResumen () {
      this.loading = true
      try {
        const params = {
          date_from: this.filters.date_from,
          date_to: this.filters.date_to
        }
        const { data } = await this.$axios.get('salesResumen', { params })
        this.resumen = {
          date_from: data.date_from,
          date_to: data.date_to,
          ventas_brutas: data.ventas_brutas || 0,
          costo_insumos_vendidos: data.costo_insumos_vendidos || 0,
          gastos: data.gastos || 0,
          utilidad_bruta: data.utilidad_bruta || 0,
          utilidad_neta: data.utilidad_neta || 0
        }
      } catch (e) {
        this.$q.notify?.({
          type: 'negative',
          message: 'No se pudo cargar el resumen de ventas'
        })
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
