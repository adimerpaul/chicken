<template>
  <q-layout view="lHh Lpr lFf">
    <!-- HEADER -->
    <q-header class="bg-white text-black" bordered>
      <q-toolbar>
        <q-btn
          flat
          color="primary"
          :icon="leftDrawerOpen ? 'keyboard_double_arrow_left' : 'keyboard_double_arrow_right'"
          aria-label="Menu"
          @click="toggleLeftDrawer"
          unelevated
          dense
        />
        <div class="row items-center q-gutter-sm">
          <!--          <q-badge color="green-8" text-color="white" class="text-bold">EBA</q-badge>-->
          <div class="text-subtitle1 text-weight-medium" style="line-height: 0.9">
            Dashboard de Gestión
          </div>
        </div>

        <q-space />

        <div class="row items-center q-gutter-sm">

          <q-btn-dropdown flat unelevated no-caps dropdown-icon="expand_more">
            <template v-slot:label>
              <div class="row items-center no-wrap q-gutter-sm">
                <q-avatar rounded>
                  <q-img :src="`${$url}/../images/${$store.user.avatar}`" width="40px" height="40px" v-if="$store.user.avatar"/>
                  <q-icon name="person" v-else />
                </q-avatar>
                <div class="text-left" style="line-height: 1">
                  <div class="ellipsis" style="max-width: 130px;">
                    {{ $store.user.username }}
                  </div>
                  <!--                  <q-chip dense size="10px" :color="$filters.color($store.user.role)" text-color="white">-->
                  <!--                    {{ $store.user.role }}-->
                  <!--                  </q-chip>-->
                </div>
              </div>
            </template>

            <q-item clickable v-close-popup>
              <q-item-section>
                <q-item-label class="text-grey-7">
                  Permisos asignados
                </q-item-label>
                <q-item-label caption class="q-mt-xs">
                  <div class="row q-col-gutter-xs" style="min-width: 150px; max-width: 150px;">
                    <q-chip
                      v-for="(p, i) in $store.permissions"
                      :key="i"
                      dense
                      color="grey-3"
                      text-color="black"
                      size="12px"
                      class="q-mr-xs q-mb-xs"
                    >
                      {{ p }}
                    </q-chip>
                    <q-badge v-if="!$store.permissions?.length" color="grey-5" outline>Sin permisos</q-badge>
                  </div>
                </q-item-label>
              </q-item-section>
            </q-item>

            <q-separator />

            <q-item clickable v-ripple @click="logout" v-close-popup>
              <q-item-section avatar>
                <q-icon name="logout" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Salir</q-item-label>
              </q-item-section>
            </q-item>
          </q-btn-dropdown>
        </div>
      </q-toolbar>
    </q-header>

    <!-- DRAWER -->
    <q-drawer
      v-model="leftDrawerOpen"
      bordered
      show-if-above
      :width="200"
      :breakpoint="500"
      class="bg-primary text-white"
    >
      <q-list class="q-pb-none">
        <q-item-label header class="text-center q-pa-none q-pt-md">
          <q-avatar size="64px" class="q-mb-sm bg-white" rounded>
            <q-img src="/logo.png" width="90px" />
          </q-avatar>
          <div class="text-weight-bold text-white">Chicken</div>
          <div class="text-caption text-white">Garden</div>
        </q-item-label>

<!--        <q-separator color="green-8" />-->

        <q-item-label header class="q-px-md text-grey-3 q-mt-sm">
          Módulos del Sistema
        </q-item-label>
        <q-item dense to="/" exact clickable class="menu-item" active-class="menu-active" v-close-popup>
          <q-item-section avatar>
            <q-icon name="dashboard" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Dashboard</q-item-label>
          </q-item-section>
        </q-item>

<!--        'Usuarios',-->
<!--        'Insumos',-->
<!--        'Productos',-->
<!--        'Clientes',-->
<!--        'Ventas',-->
<!--        'Compras',-->
<!--        'Reportes',-->
        <q-item dense to="/usuarios" exact clickable class="menu-item" active-class="menu-active" v-close-popup v-if="canPermission('Usuarios')">
          <q-item-section avatar>
            <q-icon name="people" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Usuarios</q-item-label>
          </q-item-section>
        </q-item>
<!--        almacenes-->
        <q-item dense to="/almacenes" exact clickable class="menu-item" active-class="menu-active" v-close-popup v-if="canPermission('Insumos')">
          <q-item-section avatar>
            <q-icon name="warehouse" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Almacenes</q-item-label>
          </q-item-section>
        </q-item>
        <q-item dense to="/insumos" exact clickable class="menu-item" active-class="menu-active" v-close-popup v-if="canPermission('Insumos')">
          <q-item-section avatar>
            <q-icon name="inventory_2" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Insumos</q-item-label>
          </q-item-section>
        </q-item>
        <q-item dense to="/productos" exact clickable class="menu-item" active-class="menu-active" v-close-popup v-if="canPermission('Productos')">
          <q-item-section avatar>
            <q-icon name="shopping_bag" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Productos</q-item-label>
          </q-item-section>
        </q-item>
<!--        <q-item dense to="/clientes" exact clickable class="menu-item" active-class="menu-active" v-close-popup>-->
<!--          <q-item-section avatar>-->
<!--            <q-icon name="storefront" class="text-white"/>-->
<!--          </q-item-section>-->
<!--          <q-item-section>-->
<!--            <q-item-label class="text-white">Clientes</q-item-label>-->
<!--          </q-item-section>-->
<!--        </q-item>-->
        <q-item dense to="/ventas" exact clickable class="menu-item" active-class="menu-active" v-close-popup v-if="canPermission('Ventas')">
          <q-item-section avatar>
            <q-icon name="point_of_sale" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Ventas</q-item-label>
          </q-item-section>
        </q-item>
<!--        ventas list-->
        <q-item dense to="/ventas/lista" exact clickable class="menu-item" active-class="menu-active" v-close-popup v-if="canPermission('Ventas')">
          <q-item-section avatar>
            <q-icon name="receipt_long" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Lista de Ventas</q-item-label>
          </q-item-section>
        </q-item>
<!--        compras routes-->
<!--        {-->
<!--        path: '/compras',-->
<!--        component: () => import('pages/compras/ComprasIndex.vue'),-->
<!--        meta: { requiresAuth: true }-->
<!--        },-->
<!--        {-->
<!--        path: '/compras/insumos',-->
<!--        component: () => import('pages/compras/InsumosResumen.vue'),-->
<!--        meta: { requiresAuth: true }-->
<!--        }-->
        <q-expansion-item dense expand-separator icon="shopping_cart" label="Módulo Compras" active-class="menu-active"  v-if="canPermission('Compras')">
          <q-list>
            <q-item :inset-level="0.3" dense to="/compras/insumos" clickable class="menu-item" active-class="menu-active" v-close-popup >
              <q-item-section avatar>
                <q-icon name="add_shopping_cart" class="text-white"/>
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-white">Compras de Insumos</q-item-label>
              </q-item-section>
            </q-item>
            <q-item :inset-level="0.3" dense to="/compras" clickable class="menu-item" active-class="menu-active" v-close-popup >
              <q-item-section avatar>
                <q-icon name="shopping_cart" class="text-white"/>
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-white">Gestión de Compras</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-expansion-item>
<!--&lt;!&ndash;        compras&ndash;&gt;-->
<!--        <q-item dense to="/compras" exact clickable class="menu-item" active-class="menu-active" v-close-popup>-->
<!--          <q-item-section avatar>-->
<!--            <q-icon name="shopping_cart" class="text-white"/>-->
<!--          </q-item-section>-->
<!--          <q-item-section>-->
<!--            <q-item-label class="text-white">Compras</q-item-label>-->
<!--          </q-item-section>-->
<!--        </q-item>-->
<!--&lt;!&ndash;        comprasInsumos&ndash;&gt;-->
<!--        <q-item dense to="/compras-insumos" exact clickable class="menu-item" active-class="menu-active" v-close-popup>-->
<!--          <q-item-section avatar>-->
<!--            <q-icon name="add_shopping_cart" class="text-white"/>-->
<!--          </q-item-section>-->
<!--          <q-item-section>-->
<!--            <q-item-label class="text-white">Compras de Insumos</q-item-label>-->
<!--          </q-item-section>-->
<!--        </q-item>-->
        <q-item dense to="/reportes" exact clickable class="menu-item" active-class="menu-active" v-close-popup v-if="canPermission('Reportes')">
          <q-item-section avatar>
            <q-icon name="bar_chart" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Reportes</q-item-label>
          </q-item-section>
        </q-item>

<!--        <q-expansion-item dense expand-separator icon="gavel" label="Modulo Producción Primaria" active-class="menu-active" >-->
<!--          <q-list>-->
<!--            <q-item :inset-level="0.3" dense to="/productores/crear" clickable class="menu-item" active-class="menu-active" v-close-popup >-->
<!--              <q-item-section avatar>-->
<!--                <q-icon name="person_add" class="text-white"/>-->
<!--              </q-item-section>-->
<!--              <q-item-section>-->
<!--                <q-item-label class="text-white">Crear Productor</q-item-label>-->
<!--              </q-item-section>-->
<!--            </q-item>-->
<!--            <q-item :inset-level="0.3" dense to="/productores" clickable class="menu-item" active-class="menu-active" v-close-popup >-->
<!--              <q-item-section avatar>-->
<!--                <q-icon name="agriculture" class="text-white"/>-->
<!--              </q-item-section>-->
<!--              <q-item-section>-->
<!--                <q-item-label class="text-white">Gestion de Productores</q-item-label>-->
<!--              </q-item-section>-->
<!--            </q-item>-->
<!--            <q-item :inset-level="0.3" dense to="/geocrud" clickable class="menu-item" active-class="menu-active" v-close-popup >-->
<!--              <q-item-section avatar>-->
<!--                <q-icon name="location_city" class="text-white"/>-->
<!--              </q-item-section>-->
<!--              <q-item-section>-->
<!--                <q-item-label class="text-white">Departamento / Provincia </q-item-label>-->
<!--              </q-item-section>-->
<!--            </q-item>-->
<!--            <q-item :inset-level="0.3" dense to="/organizaciones" clickable class="menu-item" active-class="menu-active" v-close-popup >-->
<!--              <q-item-section avatar>-->
<!--                <q-icon name="apartment" class="text-white"/>-->
<!--              </q-item-section>-->
<!--              <q-item-section>-->
<!--                <q-item-label class="text-white">Módulo de convenios</q-item-label>-->
<!--              </q-item-section>-->
<!--            </q-item>-->
<!--          </q-list>-->
<!--        </q-expansion-item>-->
<!--        <q-expansion-item dense expand-separator icon="inbox" label="Módulo Acopio" active-class="menu-active" >-->
<!--          <q-list>-->
<!--            <q-item :inset-level="0.3" dense to="/recoleccion" clickable class="menu-item" active-class="menu-active" v-close-popup >-->
<!--              <q-item-section avatar>-->
<!--                <q-icon name="yard" class="text-white"/>-->
<!--              </q-item-section>-->
<!--              <q-item-section>-->
<!--                <q-item-label class="text-white">Recolección</q-item-label>-->
<!--              </q-item-section>-->
<!--            </q-item>-->
<!--            <q-item :inset-level="0.3" dense to="/acopios" clickable class="menu-item" active-class="menu-active" v-close-popup >-->
<!--              <q-item-section avatar>-->
<!--                <q-icon name="inbox" class="text-white"/>-->
<!--              </q-item-section>-->
<!--              <q-item-section>-->
<!--                <q-item-label class="text-white">Acopios</q-item-label>-->
<!--              </q-item-section>-->
<!--            </q-item>-->
<!--          </q-list>-->
<!--        </q-expansion-item>-->
<!--        <q-item dense to="/usuarios" exact clickable class="menu-item" active-class="menu-active" v-close-popup>-->
<!--          <q-item-section avatar>-->
<!--            <q-icon name="people" class="text-white"/>-->
<!--          </q-item-section>-->
<!--          <q-item-section>-->
<!--            <q-item-label class="text-white">Usuarios</q-item-label>-->
<!--          </q-item-section>-->
<!--        </q-item>-->
<!--        &lt;!&ndash;        productos cleintes transporte&ndash;&gt;-->
<!--        <q-item dense to="/productos" exact clickable class="menu-item" active-class="menu-active" v-close-popup>-->
<!--          <q-item-section avatar>-->
<!--            <q-icon name="inventory" class="text-white"/>-->
<!--          </q-item-section>-->
<!--          <q-item-section>-->
<!--            <q-item-label class="text-white">Productos</q-item-label>-->
<!--          </q-item-section>-->
<!--        </q-item>-->
<!--        <q-item dense to="/clientes" exact clickable class="menu-item" active-class="menu-active" v-close-popup>-->
<!--          <q-item-section avatar>-->
<!--            <q-icon name="storefront" class="text-white"/>-->
<!--          </q-item-section>-->
<!--          <q-item-section>-->
<!--            <q-item-label class="text-white">Clientes</q-item-label>-->
<!--          </q-item-section>-->
<!--        </q-item>-->
<!--        <q-item dense to="/transportes" exact clickable class="menu-item" active-class="menu-active" v-close-popup>-->
<!--          <q-item-section avatar>-->
<!--            <q-icon name="local_shipping" class="text-white"/>-->
<!--          </q-item-section>-->
<!--          <q-item-section>-->
<!--            <q-item-label class="text-white">Transporte</q-item-label>-->
<!--          </q-item-section>-->
<!--        </q-item>-->
<!--        &lt;!&ndash;        plantas&ndash;&gt;-->
<!--        <q-item dense to="/plantas" exact clickable class="menu-item" active-class="menu-active" v-close-popup>-->
<!--          <q-item-section avatar>-->
<!--            <q-icon name="factory" class="text-white"/>-->
<!--          </q-item-section>-->
<!--          <q-item-section>-->
<!--            <q-item-label class="text-white">Plantas de Procesamiento</q-item-label>-->
<!--          </q-item-section>-->
<!--        </q-item>-->
<!--        &lt;!&ndash;        COMERZILIZACION&ndash;&gt;-->
<!--        <q-expansion-item dense expand-separator icon="store" label="Módulo Comercialización" active-class="menu-active" >-->
<!--          <q-list>-->
<!--            &lt;!&ndash;            crear ventas&ndash;&gt;-->
<!--            <q-item :inset-level="0.3" dense to="/ventas/crear" clickable class="menu-item" active-class="menu-active" v-close-popup >-->
<!--              <q-item-section avatar>-->
<!--                <q-icon name="point_of_sale" class="text-white"/>-->
<!--              </q-item-section>-->
<!--              <q-item-section>-->
<!--                <q-item-label class="text-white">Crear Venta</q-item-label>-->
<!--              </q-item-section>-->
<!--            </q-item>-->
<!--            &lt;!&ndash;            gestionar ventas&ndash;&gt;-->
<!--            <q-item :inset-level="0.3" dense to="/ventas" clickable class="menu-item" active-class="menu-active" v-close-popup >-->
<!--              <q-item-section avatar>-->
<!--                <q-icon name="sell" class="text-white"/>-->
<!--              </q-item-section>-->
<!--              <q-item-section>-->
<!--                <q-item-label class="text-white">Gestionar Ventas</q-item-label>-->
<!--              </q-item-section>-->
<!--            </q-item>-->
<!--          </q-list>-->
<!--        </q-expansion-item>-->

        <div class="q-pa-md">
          <div class="text-white-7 text-caption">
            Chiken v{{ $version }}
          </div>
          <div class="text-white-7 text-caption">
            © {{ new Date().getFullYear() }} Chicken’s · Garden
          </div>
        </div>

        <q-item clickable class="text-white" @click="logout" v-close-popup>
          <q-item-section avatar>
            <q-icon name="logout" />
          </q-item-section>
          <q-item-section>
            <q-item-label>Salir</q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </q-drawer>

    <!-- PAGE -->
    <q-page-container class="bg-grey-2">
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { computed, getCurrentInstance, ref } from 'vue'
import { useCounterStore } from 'stores/example-store'

const { proxy } = getCurrentInstance()
const store = useCounterStore()

const leftDrawerOpen = ref(false)

function toggleLeftDrawer () {
  leftDrawerOpen.value = !leftDrawerOpen.value
}
function canPermission(permission) {
  return proxy.$store.permissions.includes(permission)
}

function logout () {
  proxy.$alert.dialog('¿Desea salir del sistema?')
    .onOk(() => {
      proxy.$axios.post('/logout')
        .then(() => {
          proxy.$store.isLogged = false
          proxy.$store.user = {}
          proxy.$store.permissions = []
          localStorage.removeItem('tokenChicken')
          proxy.$router.push('/login')
        })
        .catch(() => proxy.$alert.error('Error al cerrar sesión. Intente nuevamente.'))
    })
}
</script>

<style scoped>
.menu-item {
  border-radius: 10px;
  margin: 4px 8px;
  padding: 4px 6px;
}
.menu-active {
  background: rgba(255, 255, 255, 0.15);
  color: #fff !important;
  border-radius: 10px;
}
</style>
